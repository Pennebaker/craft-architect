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

use Craft;
use pennebaker\architect\Architect;

/**
 * FieldProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class FieldProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        // Attempt to find and set group id.
        $groupId = false;
        if (isset($item['group'])) {
            $groupName = $item['group'];
            unset($item['group']);
            $groupId = $this->getGroupIdByName($groupName);
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

        $this->convertOld($item);
        $this->mapSources($item);
        if ($item['type'] === 'craft\\fields\\Matrix') {
            $this->createNewMatrix($item);
            $this->mapTypeSettings($item);
            foreach ($item['blockTypes'] as $blockKey => $blockType) {
                foreach ($blockType['fields'] as $fieldKey => $field) {
                    if (isset($field['typesettings'])) {
                        foreach ($field['typesettings'] as $k => $v) {
                            $newK = $k;
                            if ($k === 'maxLength') $newK = 'charLimit';
                            if ($field['type'] === 'craft\\fields\\Categories' && $k === 'limit') $newK = 'branchLimit';
                            $field['typesettings'][$newK] = $v;
                            if ($newK !== $k) unset($field['typesettings'][$k]);
                        }
                    }
                    // Attempt to find matching field type.
                    $matchingFieldTypes = $this->getMatchingFieldTypes($field['type']);
                    if (count($matchingFieldTypes) === 1) {
                        $field = array_merge($field, [
                            'type' => array_pop($matchingFieldTypes)
                        ]);
                    } else if (count($matchingFieldTypes) <= 0) {
                        $errors = [
                            'type' => [
                                Architect::t('No field type matching "{fieldType}".', ['fieldType' => $field['type']])
                            ]
                        ];
                        return [null, $errors];
                    } else {
                        $errors = [
                            'type' => [
                                Architect::t('Too many field types matching "{fieldType}"', ['fieldType' => $field['type']]) . '<br>' . Architect::t('Possible values:') . '<br>' . implode('<br>', $matchingFieldTypes)
                            ]
                        ];
                        return [null, $errors];
                    }
                    $item['blockTypes'][$blockKey]['fields'][$fieldKey] = $field;
                }
            }
        }

        if ($groupId && Craft::$app->fields->getGroupById($groupId)) {
            $fieldObject = array_merge($item, [
                'groupId' => $groupId
            ]);

            $field = Craft::$app->fields->createField($fieldObject);

            return [$field, $field->getErrors()];
        } else {
            $errors = [
                'group' => [
                    Architect::t('Group id is invalid.')
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

    private function getGroupIdByName(string $name)
    {
        $fieldGroups = Craft::$app->fields->getAllGroups();
        foreach ($fieldGroups as $fieldGroup) {
            if ($fieldGroup->name === $name) {
                return $fieldGroup->id;
            }
        }
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
     * @param array $matrix
     */
    public function createNewMatrix(array &$matrix) {
        if (isset($matrix['blockTypes'][0])) {
            $newBlockTypes = [];
            $blockCount = 1;
            foreach ($matrix['blockTypes'] as $blockType) {
                if (isset($blockType['fields'][0])) {
                    $newFields = [];
                    $fieldCount = 1;
                    foreach ($blockType['fields'] as $field) {
                        $newFields['new'.$fieldCount] = $field;
                        $fieldCount++;
                    }
                    $blockType['fields'] = $newFields;
                }
                $newBlockTypes['new'.$blockCount] = $blockType;
                $blockCount++;
            }
            $matrix['blockTypes'] = $newBlockTypes;
        }
    }

    /**
     * @param array $item
     */
    private function mapSources(array &$item)
    {
        switch ($item['type']) {
            case 'craft\\fields\\Assets':
                $this->mapVolumeSources($item['sources']);
                $this->mapVolumeSources($item['defaultUploadLocationSource']);
                $this->mapVolumeSources($item['singleUploadLocationSource']);
                break;
            case 'craft\\fields\\Entries':
                $this->mapSectionSources($item['sources']);
                break;
            case 'craft\\fields\\Categories':
                if (is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->mapCategorySource($item['source']);
                break;
            case 'craft\\fields\\Tags':
                if (is_array($item['source'])) {
                    $item['source'] = $item['source'][0];
                }
                $this->mapTagSource($item['source']);
                break;
            case 'craft\\fields\\Users':
                $this->mapUserGroupSources($item['sources']);
                break;
            case 'craft\\redactor\\Field':
                $this->mapVolumeSources($item['availableVolumes'], '');
                $this->mapAssetTransforms($item['availableTransforms'], '');
                break;
        }
    }
}