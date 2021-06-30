<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\base;

use pennebaker\architect\Architect;

use Craft;
use craft\base\Field;
use craft\fields\Date;
use craft\fields\Matrix;
use craft\models\FieldLayout;
use craft\models\MatrixBlockType;
use craft\helpers\Json;
use verbb\supertable\fields\SuperTableField;
use benf\neo\Field as Neo;
use benf\neo\elements\Block as NeoBlock;
use benf\neo\models\BlockType as NeoBlockTypeModel;
use benf\neo\records\BlockType as NeoBlockType;
use benf\neo\records\BlockTypeGroup as NeoBlockTypeGroup;

/**
 * FieldProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class FieldProcessor extends Processor
{
    /**
     * @param array $item
     * @param bool $subField
     *
     * @return array
     */
    public function parse(array $item, bool $subField = false): array
    {
        $groupId = false;
        if ($subField === false) {
            // Attempt to find and set group id.
            if (isset($item['group'])) {
                $groupId = $this->getGroupIdByName($item['group']);
                if ($groupId) {
                    unset($item['group']);
                }
            } else {
                $errors = [
                    'type' => [
                        Architect::t('Field group is missing.')
                    ]
                ];
                return [null, $errors];
            }
        }

        // Attempt to find matching field type.
        $matchingFieldTypes = $this->getMatchingFieldTypes($item['type']);
        if (\count($matchingFieldTypes) === 1) {
            $item = array_merge($item, [
                'type' => array_pop($matchingFieldTypes)
            ]);
        } else if (\count($matchingFieldTypes) <= 0) {
            $errors = [
                'type' => [
                    Architect::t('No field type matching "{fieldType}".', ['fieldType' => $item['type']])
                ]
            ];
            return [null, $errors];
        } else {
            $errors = [
                'type' => [
                    Architect::t('Too many field types matching "{fieldType}"', ['fieldType' => $item['type']]) . '<br>' . Architect::t('Possible values:') . '<br>' . implode('<br>', $matchingFieldTypes)
                ]
            ];
            return [null, $errors];
        }
        if ($subField === false) {
            $this->convertOld($item);
        }
        try {
            $this->mapSources($item);
        } catch (\Exception $e) {
            $errors = [
                'source' => [
                    Architect::t('There was an error mapping the source handles to existing sources.')
                ]
            ];
            return [null, $errors];
        }
        if ($item['type'] === Matrix::class || $item['type'] === SuperTableField::class || $item['type'] === Neo::class) {
            $this->mapTypeSettings($item);
            if ($subField) {
                $blockTypes = &$item['typesettings']['blockTypes'];
            } else {
                $blockTypes = &$item['blockTypes'];
            }
        }
        if ($item['type'] === Matrix::class) {
            if (isset($blockTypes[0])) {
                $this->convertBlockTypesToNew($blockTypes);
            }
            foreach ($blockTypes as $blockKey => &$blockType) {
                foreach ($blockType['fields'] as $fieldKey => $field) {
                    list ($field, $errors) = $this->parse($field, true);
                    if ($field === null) {
                        return [$field, $errors];
                    }
                    $blockType['fields'][$fieldKey] = $field;
                }
            }
            unset($blockType);
        } else if ($item['type'] === Neo::class) {
            if (isset($blockTypes[0])) {
                $this->convertBlockTypesToNew($blockTypes);
            }
            foreach ($blockTypes as &$blockType) {
                if (!array_key_exists('childBlocks', $blockType)) {
                    $blockType['childBlocks'] = null;
                }
                if (!array_key_exists('maxSiblingBlocks', $blockType)) {
                    $blockType['maxSiblingBlocks'] = null;
                }
                $blockType['elementPlacements'] = [];
                $blockType['elementConfigs'] = [];
                $fieldLayoutConfig = $this->createFieldLayoutConfig($blockType, NeoBlock::class);
                foreach ($fieldLayoutConfig['tabs'] as $tabConfig) {
                    $tabName = $tabConfig['name'];
                    $fieldConfigs = $tabConfig['elements'];
                    $blockType['elementPlacements'][$tabName] = [];
                    if (is_array($fieldConfigs)) {
                        foreach ($fieldConfigs as $fieldConfig) {
                            $rndKey = substr(base64_encode(mt_rand()), 2, 12);
                            $blockType['elementPlacements'][$tabName][] = $rndKey;
                            $blockType['elementConfigs'][$rndKey] = json_encode($fieldConfig);

                        }
                    }
                }
                unset($fields);
                unset($blockType['fieldLayout']);
                unset($blockType['fieldConfigs']);
                unset($blockType['requiredFields']);
            }
            unset($blockType);
        } else if ($item['type'] === SuperTableField::class) {
            if (!isset(array_values($blockTypes)[0]['fields'])) {
                $newBlockTypes = [
                    [
                        'fields' => $blockTypes
                    ]
                ];
                $blockTypes = $newBlockTypes;
                $this->convertBlockTypesToNew($blockTypes);
            }
            foreach ($blockTypes as $blockKey => &$blockType) {
                foreach ($blockType['fields'] as $fieldKey => &$field) {
                    list ($field, $errors) = $this->parse($field, true);
                    if ($field === null) {
                        return [$field, $errors];
                    }
                    $blockType['fields'][$fieldKey] = $field;
                }
                unset($field);
            }
            unset($blockType);
        }

        if ($groupId && Craft::$app->fields->getGroupById((int) $groupId)) {
            $fieldObject = array_merge($item, [
                'groupId' => $groupId
            ]);

            $moveToSettings = [];
            if ($item['type'] === Neo::class) {
                $moveToSettings = ['blockTypes', 'propagationMethod', 'minBlocks', 'maxBlocks', 'maxTopBlocks'];
            }

            if (\count($moveToSettings)) {
                $fieldObject['settings'] = [];
            }
            foreach ($moveToSettings as $moveKey) {
                if (isset($fieldObject[$moveKey])) {
                    $fieldObject['settings'][$moveKey] = $fieldObject[$moveKey];
                    unset($fieldObject[$moveKey]);
                }
            }

            $field = Craft::$app->fields->createField($fieldObject);

            return [$field, $field->getErrors()];
        }
        if ($subField) {
            return [$item, null];
        }
        $errors = [
            'group' => [
                Architect::t('No field group matching "{groupName}".', ['groupName' => $item['group']])
            ]
        ];
        return [null, $errors];
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function handlesToIds($fields): array
    {
        foreach ($fields as &$fieldHandle) {
            // Replace field handles with their respective IDs
            $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
            if ($field) {
                $fieldHandle = $field->id;
            } else {
                $fieldHandle = null;
            }
        }
        return $this->stripNulls($fields);
    }

    /**
     * @param $item
     * @param bool $update Attempt to update an existing field instead of making a new one.
     *
     * @return bool|object|array
     *
     * @throws \Throwable
     */
    public function save($item, bool $update = false)
    {
        if ($update || $item->id) {
            if ($item->id) {
                $field = Craft::$app->fields->getFieldById($item->id);
            } else {
                $field = Craft::$app->fields->getFieldByHandle($item->handle);
            }
            if ($field) {
                if (\get_class($item) !== \get_class($field)) {
                    $error = Architect::t('Type does not match existing type: "{fieldType}".', ['fieldType' => \get_class($field) ]);
                    $item->addError('type', $error);
                    return false;
                }
            }
        }
        return Craft::$app->fields->saveField($item);
    }

    /**
     * @param array $item
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function setFieldLayout($item)
    {
        $field = Craft::$app->fields->getFieldByHandle($item['handle']);
        if ($field) {
            if ($item['type'] === Neo::class) {
                /* @var Neo $field */
                list($field) = $this->parse($item); // This shouldn't fail because it only got to this point by succeeding the first time.
                $configBlockTypes = [];
                foreach ($item['blockTypes'] as $blockType) {
                    $configBlockTypes[$blockType['handle']] = $blockType;
                }
                $blockTypes = $field->getBlockTypes();
                foreach ($blockTypes as &$blockType) {
                    /* @var NeoBlockTypeModel $blockType */
                    if (isset($configBlockTypes[$blockType->handle])) {
                        $fieldLayout = $this->createFieldLayout($configBlockTypes[$blockType->handle], NeoBlock::class);
                        $fieldLayout->id = $blockType->fieldLayoutId;
                        Craft::$app->fields->saveLayout($fieldLayout);
                        $blockType->fieldLayoutId = $fieldLayout->id;
                    }
                }
                unset($blockType);
                $field->setBlockTypes($blockTypes);
                return $this->save($field, true);
            }
        }
        return false;
    }

    /**
     * @param array $itemObj
     *
     * @return array|null
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function update(array &$itemObj)
    {
        if (isset($itemObj['id'])) {
            $field = Craft::$app->fields->getFieldById($itemObj['id']);
        } else {
            $field = Craft::$app->fields->getFieldByHandle($itemObj['handle']);
            if ($field) {
                $itemObj['id'] = $field->id;
            }
        }
        if ($field) {
            if ($itemObj['type'] !== \get_class($field)) {
                $errors = [
                    'type' => [
                        Architect::t('Type does not match existing type: "{fieldType}".', ['fieldType' => \get_class($field) ])
                    ]
                ];
                return $errors;
            }
            switch ($itemObj['type']) {
                case Matrix::class:
                    /* @var Matrix $field */
                    $itemObj['blockTypes'] = $this->mergeBlockTypes($field->getBlockTypes(), $itemObj['blockTypes']);
                    break;
                case SuperTableField::class:
                    /* @var SuperTableField $field */
                    $itemObj['blockTypes'] = $this->mergeSuperTableBlockTypes($field->getBlockTypes(), $itemObj['blockTypes']);
                    break;
                case Neo::class:
                    /* @var Neo $field */
                    $itemObj['blockTypes'] = $this->mergeNeoBlockTypes($field->getBlockTypes(), $itemObj['blockTypes']);
                    break;
            }
        }
        return null;
    }

    /**
     * @param MatrixBlockType[] $oldBlockTypes
     * @param array $newBlockTypes
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function mergeBlockTypes(array $oldBlockTypes, array $newBlockTypes): array
    {
        $oldIDs = array_map(function($a) { return $a['id']; }, $oldBlockTypes);
        $oldHandles = array_map(function($a) { return $a['handle']; }, $oldBlockTypes);
        $updatedBlockTypes = [];
        $newCount = 1;
        foreach ($newBlockTypes as $newIndex => $blockType) {
            if (isset($blockType['id'])) {
                $oldIndex = array_search($blockType['id'], $oldIDs, false);
            } else {
                $oldIndex = array_search($blockType['handle'], $oldHandles, false);
            }
            if ($oldIndex !== false) {
                $oldFieldLayout = $oldBlockTypes[$oldIndex]->getFieldLayout();
            } else {
                $oldFieldLayout = new FieldLayout();
            }
            $newFields = $blockType['fields'];
            $blockType['fields'] = $this->mergeFieldLayout($oldFieldLayout, $newFields);
            if ($oldIndex !== false) {
                $updatedBlockTypes[$oldBlockTypes[$oldIndex]['id']] = $blockType;
            } else {
                $updatedBlockTypes['new' . $newCount] = $blockType;
                $newCount++;
            }
        }
        return $updatedBlockTypes;
    }

    /**
     * @param FieldLayout $oldFieldLayout
     * @param array $newFields
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function mergeFieldLayout(FieldLayout $oldFieldLayout, array $newFields): array
    {
        $oldFields = $oldFieldLayout->getFields();
        $oldIDs = array_map(function($a) { return $a['id']; }, $oldFields);
        $oldHandles = array_map(function($a) { return $a['handle']; }, $oldFields);
        $updatedFields = [];
        $newCount = 1;
        foreach ($newFields as $newIndex => $field) {
            if (isset($field['id'])) {
                $oldIndex = array_search($field['id'], $oldIDs, false);
            } else {
                $oldIndex = array_search($field['handle'], $oldHandles, false);
            }
            switch ($field['type']) {
                case Matrix::class:
                    /* @var Matrix $field */
                    $field['typesettings']['blockTypes'] = $this->mergeBlockTypes($oldFields[$oldIndex]->getBlockTypes(), $field['typesettings']['blockTypes']);
                    break;
                case SuperTableField::class:
                    /* @var SuperTableField $field */
                    $field['typesettings']['blockTypes'] = $this->mergeSuperTableBlockTypes($oldFields[$oldIndex]->getBlockTypes(), $field['typesettings']['blockTypes']);
                    break;
                case Neo::class:
                    /* @var Neo $field */
                    $field['typesettings']['blockTypes'] = $this->mergeNeoBlockTypes($oldFields[$oldIndex]->getBlockTypes(), $field['typesettings']['blockTypes']);
                    break;
            }
            if ($oldIndex !== false) {
                $field['id'] = $oldFields[$oldIndex]['id'];
                $updatedFields[$oldFields[$oldIndex]['id']] = $field;
            } else {
                $updatedFields['new' . $newCount] = $field;
                $newCount++;
            }
        }
        return $updatedFields;
    }

    /**
     * @param array $oldBlockTypes
     * @param array $newFields
     *
     * @return array
     */
    private function mergeSuperTableBlockTypes(array $oldBlockTypes, array $newFields): array
    {
        $updatedBlockTypes = [];
        $blockTypeId = $oldBlockTypes[0]->id;
        $updatedBlockTypes[$blockTypeId] = [];
        $updatedBlockTypes[$blockTypeId]['fields'] = $this->mergeFieldLayout($oldBlockTypes[0]->getFieldLayout(), $newFields);

        return $updatedBlockTypes;
    }

    /**
     * @param NeoBlockType[] $oldBlockTypes
     * @param array $newBlockTypes
     *
     * @return array
     */
    private function mergeNeoBlockTypes(array $oldBlockTypes, array $newBlockTypes): array
    {
        $oldIDs = array_map(function($a) { return $a['id']; }, $oldBlockTypes);
        $oldHandles = array_map(function($a) { return $a['handle']; }, $oldBlockTypes);
        $updatedBlockTypes = [];
        $newCount = 1;
        foreach ($newBlockTypes as $newIndex => $blockType) {
            if (isset($blockType['id'])) {
                $oldIndex = array_search($blockType['id'], $oldIDs, false);
            } else {
                $oldIndex = array_search($blockType['handle'], $oldHandles, false);
            }
            if ($oldIndex !== false) {
                $oldId = $oldBlockTypes[$oldIndex]['id'];
                $updatedBlockTypes[$oldId] = $blockType;
                $updatedBlockTypes[$oldId]['id'] = $oldId;
                $updatedBlockTypes[$oldId]['fieldId'] = $oldBlockTypes[$oldIndex]['fieldId'];
                $updatedBlockTypes[$oldId]['fieldLayoutId'] = $oldBlockTypes[$oldIndex]['fieldLayoutId'];

            } else {
                $updatedBlockTypes['new' . $newCount] = $blockType;
                $newCount++;
            }
        }
        return $updatedBlockTypes;
    }

    /**
     * @param string $fieldType
     * @return array
     */
    private function getMatchingFieldTypes($fieldType): array
    {
        return array_filter(Craft::$app->fields->getAllFieldTypes(), function($haystack) use ($fieldType) {
            return (strpos($haystack, $fieldType) !== false);
        });
    }

    /**
     * @param string $name
     *
     * @return int|bool
     */
    private function getGroupIdByName(string $name)
    {
        $fieldGroups = Craft::$app->fields->getAllGroups();
        foreach ($fieldGroups as $fieldGroup) {
            if ($fieldGroup->name === $name) {
                return $fieldGroup->id;
            }
        }
        return false;
    }

    /**
     * @param array $item
     */
    public function convertOld(array &$item)
    {
        if (isset($item['typesettings'])) {
            foreach ($item['typesettings'] as $k => $v) {
                if ($k === 'maxLength') {
                    $k = 'charLimit';
                }
                if ($k === 'limit' && $item['type'] === \craft\fields\Categories::class) {
                    $k = 'branchLimit';
                }
                if (($k !== 'useSingleFolder' && $item['type'] === \craft\fields\Categories::class) || $item['type'] !== \craft\fields\Categories::class) {
                    $item[$k] = $v;
                }
            }
            unset($item['typesettings'], $item['typesettings']);
        }
    }

    /**
     * @param array $blockTypes
     */
    public function convertBlockTypesToNew(array &$blockTypes)
    {
        $newBlockTypes = [];
        foreach ($blockTypes as $blockType) {
            if (isset($blockType['fields'][0])) {
                $newFields = [];
                $fieldCount = 1;
                foreach ($blockType['fields'] as $field) {
                    $newFields['new'.$fieldCount] = $field;
                    $fieldCount++;
                }
                $blockType['fields'] = $newFields;
            }
            $newBlockTypes['new' . (\count($newBlockTypes) + 1)] = $blockType;
        }
        $blockTypes = $newBlockTypes;
    }

    /**
     * @param array $item
     */
    private function mapSources(array &$item)
    {
        $type = $item['type'];
        if (isset($item['typesettings'])) {
            $item = &$item['typesettings'];
        }
        switch ($type) {
            case \craft\fields\Assets::class:
                $this->map($item['sources'], 'asset');
                $this->map($item['defaultUploadLocationSource'], 'asset');
                $this->map($item['singleUploadLocationSource'], 'asset');
                if (isset($item['targetSiteId'])) {
                    $this->mapSites($item['targetSiteId']);
                }
                break;
            case \craft\fields\Entries::class:
                $this->map($item['sources'], 'section');
                if (isset($item['targetSiteId'])) {
                    $this->mapSites($item['targetSiteId']);
                }
                break;
            case \craft\fields\Categories::class:
                if (\is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->map($item['source'], 'group');
                if (isset($item['targetSiteId'])) {
                    $this->mapSites($item['targetSiteId']);
                }
                break;
            case \craft\fields\Tags::class:
                if (\is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->map($item['source'], 'taggroup');
                if (isset($item['targetSiteId'])) {
                    $this->mapSites($item['targetSiteId']);
                }
                break;
            case \craft\fields\Users::class:
                $this->map($item['sources'], 'group');
                if (isset($item['targetSiteId'])) {
                    $this->mapSites($item['targetSiteId']);
                }
                break;
            case 'craft\\redactor\\Field':
                $this->map($item['availableVolumes'], 'volume', false);
                $this->map($item['availableTransforms'], 'transform', false);
                break;
            case 'typedlinkfield\\fields\\LinkField':
                $this->map($item['typeSettings']['asset']['sources'], 'folder');
                $this->map($item['typeSettings']['category']['sources'], 'group');
                $this->map($item['typeSettings']['entry']['sources'], 'section');
                $this->map($item['typeSettings']['user']['sources'], 'group');
                /* Fix old export problem with unmap/map sites. */
                if ($item['typeSettings']['site'] === []) {
                    $item['typeSettings']['site'] = [
                        'sites' => '*'
                    ];
                }
                $this->mapSites($item['typeSettings']['site']['sites'], '', true);
                break;
        }
    }

    /**
     * @param array $item
     */
    private function unmapSources(array &$item)
    {
        $type = $item['type'];
        if (isset($item['typesettings'])) {
            $item = &$item['typesettings'];
        }
        switch ($type) {
            case \craft\fields\Assets::class:
                $this->unmap($item['sources']);
                $this->unmap($item['defaultUploadLocationSource']);
                $this->unmap($item['singleUploadLocationSource']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case \craft\fields\Users::class:
            case \craft\fields\Entries::class:
                $this->unmap($item['sources']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case \craft\fields\Tags::class:
            case \craft\fields\Categories::class:
                if (\is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->unmap($item['source']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case 'craft\\redactor\\Field':
                $this->unmap($item['availableVolumes'], 'volume');
                $this->unmap($item['availableTransforms'], 'transform');
                break;
            case 'typedlinkfield\\fields\\LinkField':
                $this->unmap($item['typeSettings']['asset']['sources'], 'folder');
                $this->unmap($item['typeSettings']['category']['sources'], 'group');
                $this->unmap($item['typeSettings']['entry']['sources'], 'section');
                $this->unmap($item['typeSettings']['user']['sources'], 'group');
                $this->unmapSites($item['typeSettings']['site']['sites']);
                break;
        }
    }

    /**
     * @param string $class
     *
     * @return array|mixed
     */
    public function additionalAttributes(string $class)
    {
        $additionalAttributes = [
//            'craft\\fields\\PlainText' => [
//                'placeholder',
//            ],
        ];
        return $additionalAttributes[$class] ?? [];
    }

    /**
     * @param $item
     * @param array $extraAttributes
     * @param bool $useTypeSettings
     *
     * @return array
     */
    public function export($item, array $extraAttributes = ['group'], bool $useTypeSettings = false): array
    {
        /** @var Field $item*/
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        if (\count($item::supportedTranslationMethods()) > 1) {
            $extraAttributes = array_merge($extraAttributes, ['translationMethod', 'translationKeyFormat']);
        }
        foreach($extraAttributes as $attribute) {
            if ($attribute === 'group') {
                $attributeObj[$attribute] = $item->$attribute->name;
                $attributeObj[$attribute . 'Id'] = $item->$attribute->id;
            } else if ($attribute === 'required') {
                $attributeObj[$attribute] = (bool) $item->$attribute;
            } else {
                $attributeObj[$attribute] = $item->$attribute;
            }
        }
        if ($useTypeSettings) {
            $fieldObj = array_merge($attributeObj, [
                'name' => $item->name,
                'handle' => $item->handle,
                'instructions' => $item->instructions,
                'type' => \get_class($item),
                'typesettings' => $item->getSettings(),
            ]);
        } else {
            $fieldObj = array_merge($attributeObj, [
                'name' => $item->name,
                'handle' => $item->handle,
                'instructions' => $item->instructions,
                'type' => \get_class($item),
            ], $item->getSettings());
        }

        if (isset($fieldObj['translationMethod']) && $fieldObj['translationMethod'] === 'none') {
            unset($fieldObj['translationMethod']);
        }

        if ($item instanceof Matrix) {
            /**
             * @var Matrix $item
             */
            $blockTypesObj = [];
            foreach ($item->getBlockTypes() as $blockType) {
                $blockTypeObj = [
                    'name' => $blockType->name,
                    'handle' => $blockType->handle,
                    'fields' => [],
                ];
                foreach ($blockType->getFields() as $blockField) {
                    $blockTypeObj['fields'][] = $this->export($blockField, [ 'required' ], true);
                }
                $blockTypesObj[] = $blockTypeObj;
            }
            if ($useTypeSettings) {
                $fieldObj['typesettings']['blockTypes'] = $blockTypesObj;
            } else {
                $fieldObj['blockTypes'] = $blockTypesObj;
            }
            unset($fieldObj['contentTable']);
        } else if ($item instanceof Neo) {
            $blockTypesObj = [];
            $blockTypeGroupsObj = [];
            /**
             * @var Neo $item
             */
            foreach ($item->getGroups() as $group) {
                /* @var NeoBlockTypeGroup $blockType */
                $blockTypeGroupsObj[] = [
                    'name' => $group->name,
                    'sortOrder' => (int) $group->sortOrder
                ];
            }
            $fieldObj['groups'] = $blockTypeGroupsObj;
            foreach ($item->getBlockTypes() as $blockType) {
                /* @var NeoBlockType $blockType */
                list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($blockType->getFieldLayout());
                $blockTypesObj[] = [
                    'name' => $blockType->name,
                    'handle' => $blockType->handle,
                    'sortOrder' => (int) $blockType->sortOrder,
                    'maxBlocks' => (int) $blockType->maxBlocks,
                    'maxSiblingBlocks' => (int) $blockType->maxSiblingBlocks,
                    'childBlocks' => is_string($blockType->childBlocks) ? Json::decodeIfJson((string) $blockType->childBlocks) : $blockType->childBlocks,
                    'maxChildBlocks' => (int) $blockType->maxChildBlocks,
                    'topLevel' => (bool) $blockType->topLevel,
                    'fieldLayout' => $fieldLayout,
                    'fieldConfigs' => $fieldConfigs,
                ];
            }
            $fieldObj['blockTypes'] = $blockTypesObj;
        } else if ($item instanceof Date) {
            /**
             * @var Date $item
             */
            if ($useTypeSettings) {
                $fieldObj['typesettings']['dateTime'] = 'show' . (
                    ((bool) $fieldObj['typesettings']['showDate'] === false) ? 'Time' : (
                        ((bool) $fieldObj['typesettings']['showTime'] === false) ? 'Date' : 'Both'
                    )
                );
                unset($fieldObj['typesettings']['showDate'], $fieldObj['typesettings']['showTime']);
            } else {
                $fieldObj['dateTime'] = 'show' . (
                    ((bool) $fieldObj['showDate']) === false ? 'Time' : (
                        ((bool) $fieldObj['showTime']) === false ? 'Date' : 'Both'
                    )
                );
                unset($fieldObj['showDate'], $fieldObj['showTime']);
            }
        } else if ($item instanceof SuperTableField) {
            /**
             * @var SuperTableField $item
             */
            if ($useTypeSettings) {
                $fieldObj['typesettings']['blockTypes'] = [];
                foreach ($item->getBlockTypeFields() as $blockTypeField) {
                    $fieldObj['typesettings']['blockTypes'][] = $this->export($blockTypeField, [ 'required' ], true);
                }
            } else {
                $fieldObj['blockTypes'] = [];
                foreach ($item->getBlockTypeFields() as $blockTypeField) {
                    $fieldObj['blockTypes'][] = $this->export($blockTypeField, [ 'required' ], true);
                }
            }
            unset(
                $fieldObj['contentTable'], // SuperTable will auto-generate this for us.
                $fieldObj['columns'] // Cannot be set during first save, so we wont export this for now.
            );
        }

        $this->unmapSources($fieldObj);

        return $this->stripNulls($fieldObj);
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function exportById($id): array
    {
        $field = Craft::$app->fields->getFieldById((int) $id);

        return $this->export($field);
    }

    /**
     * @param string $handle
     *
     * @return array
     */
    public function exportByHandle(string $handle): array
    {
        $field = Craft::$app->fields->getFieldByHandle($handle);
        return $this->export($field);
    }

    /**
     * Gets an object from the passed in UID for export.
     *
     * @param $uid
     *
     * @return mixed
     */
    public function exportByUid($uid)
    {
        // TODO: Implement exportByUid() method.
    }
}
