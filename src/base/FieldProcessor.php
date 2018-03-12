<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
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
use verbb\supertable\fields\SuperTableField;

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
    public function parse(array $item, bool $subField = false)
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
        if (count($matchingFieldTypes) === 1) {
            $item = array_merge($item, [
                'type' => array_pop($matchingFieldTypes)
            ]);
        } else if (count($matchingFieldTypes) <= 0) {
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

        if ($subField === false) $this->convertOld($item);
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
        if ($item['type'] === 'craft\\fields\\Matrix' || $item['type'] === 'verbb\\supertable\\fields\\SuperTableField') {
            $this->mapTypeSettings($item);
            if ($subField) {
                $blockTypes = &$item['typesettings']['blockTypes'];
            } else {
                $blockTypes = &$item['blockTypes'];
            }
        }
        if ($item['type'] === 'craft\\fields\\Matrix') {
            $this->convertBlockTypesToNew($blockTypes);
            foreach ($blockTypes as $blockKey => &$blockType) {
                foreach ($blockType['fields'] as $fieldKey => $field) {
                    list ($field, $errors) = $this->parse($field, true);
                    if ($field === null) return [$field, $errors];
                    $blockType['fields'][$fieldKey] = $field;
                }
            }
        } else if ($item['type'] === 'verbb\\supertable\\fields\\SuperTableField') {
            $newBlockTypes = [
                [
                    'fields' => $blockTypes
                ]
            ];
            $blockTypes = $newBlockTypes;
            $this->convertBlockTypesToNew($blockTypes);
        }

        if ($groupId && Craft::$app->fields->getGroupById((int) $groupId)) {
            $fieldObject = array_merge($item, [
                'groupId' => $groupId
            ]);

            $field = Craft::$app->fields->createField($fieldObject);

            return [$field, $field->getErrors()];
        } else if ($subField) {
            return [$item, null];
        } else {
            $errors = [
                'group' => [
                    Architect::t('No field group matching "{groupName}".', ['groupName' => $item['group']])
                ]
            ];
            return [null, $errors];
        }
    }

    /**
     * @param $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->fields->saveField($item);
    }

    private function getMatchingFieldTypes($fieldType) {
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
                if ($k === 'maxLength') $k = 'charLimit';
                if ($item['type'] === 'craft\\fields\\Categories' && $k === 'limit') $k = 'branchLimit';

                if (($k !== 'useSingleFolder' && $item['type'] === 'craft\\fields\\Categories') || $item['type'] !== 'craft\\fields\\Categories')
                    $item[$k] = $v;
            }
            unset($item['typesettings']);
            unset($item['typesettings']);
        }
    }

    /**
     * @param array $blockTypes
     */
    public function convertBlockTypesToNew(array &$blockTypes) {
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
            $newBlockTypes['new' . (count($newBlockTypes) + 1)] = $blockType;
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
            case 'craft\\fields\\Assets':
                $this->mapVolumeSources($item['sources']);
                $this->mapVolumeSources($item['defaultUploadLocationSource']);
                $this->mapVolumeSources($item['singleUploadLocationSource']);
                if (isset($item['targetSiteId'])) $this->mapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Entries':
                $this->mapSectionSources($item['sources']);
                if (isset($item['targetSiteId'])) $this->mapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Categories':
                if (is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->mapCategorySources($item['source']);
                if (isset($item['targetSiteId'])) $this->mapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Tags':
                if (is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->mapTagSource($item['source']);
                if (isset($item['targetSiteId'])) $this->mapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Users':
                $this->mapUserGroupSources($item['sources']);
                if (isset($item['targetSiteId'])) $this->mapSites($item['targetSiteId']);
                break;
            case 'craft\\redactor\\Field':
                $this->mapVolumeSources($item['availableVolumes'], '');
                $this->mapAssetTransforms($item['availableTransforms'], '');
                break;
            case 'typedlinkfield\\fields\\LinkField':
                $this->mapVolumeSources($item['typeSettings']['asset']['sources']);
                $this->mapCategorySources($item['typeSettings']['category']['sources']);
                $this->mapSectionSources($item['typeSettings']['entry']['sources']);
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
            case 'craft\\fields\\Assets':
                $this->unmapVolumeSources($item['sources']);
                $this->unmapVolumeSources($item['defaultUploadLocationSource']);
                $this->unmapVolumeSources($item['singleUploadLocationSource']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Entries':
                $this->unmapSectionSources($item['sources']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Categories':
                if (is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->unmapCategorySources($item['source']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Tags':
                if (is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->unmapTagSource($item['source']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case 'craft\\fields\\Users':
                $this->unmapUserGroupSources($item['sources']);
                $this->unmapSites($item['targetSiteId']);
                break;
            case 'craft\\redactor\\Field':
                $this->unmapVolumeSources($item['availableVolumes'], '');
                $this->unmapAssetTransforms($item['availableTransforms'], '');
                break;
            case 'typedlinkfield\\fields\\LinkField':
                $this->unmapVolumeSources($item['typeSettings']['asset']['sources']);
                $this->unmapCategorySources($item['typeSettings']['category']['sources']);
                $this->unmapSectionSources($item['typeSettings']['entry']['sources']);
                break;
        }
    }

    /**
     * @param string $class
     *
     * @return array|mixed
     */
    public function additionalAttributes(string $class) {
        $additionalAttributes = [
//            'craft\\fields\\PlainText' => [
//                'placeholder',
//            ],
        ];
        return (isset($additionalAttributes[$class])) ? $additionalAttributes[$class] : [];
    }

    /**
     * @param $item
     * @param array $extraAttributes
     * @param bool $useTypeSettings
     *
     * @return array
     */
    public function export($item, array $extraAttributes = ['group'], bool $useTypeSettings = false) {
        /** @var Field $item*/
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)));
        if (count($item->supportedTranslationMethods()) > 1) {
            $extraAttributes = array_merge($extraAttributes, ['translationMethod', 'translationKeyFormat']);
        }
        foreach($extraAttributes as $attribute) {
            if ($attribute === 'group') {
                $attributeObj[$attribute] = $item->$attribute->name;
                $attributeObj[$attribute . 'Id'] = $item->$attribute->id;
            } else if ($attribute === 'required') {
                $attributeObj[$attribute] = boolval($item->$attribute);
            } else {
                $attributeObj[$attribute] = $item->$attribute;
            }
        }
        if ($useTypeSettings) {
            $fieldObj = array_merge($attributeObj, [
                'name' => $item->name,
                'handle' => $item->handle,
                'instructions' => $item->instructions,
                'type' => get_class($item),
                'typesettings' => $item->getSettings(),
            ]);
        } else {
            $fieldObj = array_merge($attributeObj, [
                'name' => $item->name,
                'handle' => $item->handle,
                'instructions' => $item->instructions,
                'type' => get_class($item),
            ], $item->getSettings());
        }

        if (isset($fieldObj['translationMethod']) && $fieldObj['translationMethod'] === 'none') unset($fieldObj['translationMethod']);

        if (get_class($item) === 'craft\\fields\\Matrix') {
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
                    array_push($blockTypeObj['fields'], $this->export($blockField, [ 'required' ], true));
                }
                array_push($blockTypesObj, $blockTypeObj);
            }
            if ($useTypeSettings) {
                $fieldObj['typesettings']['blockTypes'] = $blockTypesObj;
            } else {
                $fieldObj['blockTypes'] = $blockTypesObj;
            }
        } else if (get_class($item) === 'craft\\fields\\Date') {
            /**
             * @var Date $item
             */
            $fieldObj['dateTime'] = 'show' . (
                    (boolval($fieldObj['showDate']) === false) ? 'Time' : (
                        (boolval($fieldObj['showTime']) === false) ? 'Date' : 'Both'
                    )
                );
            unset($fieldObj['showDate']);
            unset($fieldObj['showTime']);
        } else if (get_class($item) === 'verbb\\supertable\\fields\\SuperTableField') {
            $fieldObj['blockTypes'] = [];
            /**
             * @var SuperTableField $item
             */
            foreach ($item->getBlockTypeFields() as $blockTypeField) {
                $fieldObj['blockTypes'][] = $this->export($blockTypeField, [], true);
            }
        }

        $this->unmapSources($fieldObj);

        return $this->stripNulls($fieldObj);
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function exportById($id)
    {
        $field = Craft::$app->fields->getFieldById((int) $id);

        return $this->export($field);
    }

    /**
     * @param string $handle
     *
     * @return array
     */
    public function exportByHandle(string $handle) {
        $field = Craft::$app->fields->getFieldByHandle($handle);
        return $this->export($field);
    }
}