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
use craft\models\FieldLayout;

/**
 * Processor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
abstract class Processor implements ProcessorInterface
{
    public function createFieldLayout($item, $type) {
        $fieldLayout = new FieldLayout();

        if (isset($item['fieldLayout'])) {
            foreach ($item['fieldLayout'] as $tab => $fields) {
                foreach ($item['fieldLayout'][$tab] as $k => $fieldHandle) {
                    $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
                    if ($field) {
                        $item['fieldLayout'][$tab][$k] = $field->id;
                    } else {
                        unset($item['fieldLayout'][$tab][$k]);
                    }
                }
            }
            if (isset($item['requiredFields']) && is_array($item['requiredFields'])) {
                foreach ($item['requiredFields'] as $k => $fieldHandle) {
                    $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
                    if ($field) {
                        $item['requiredFields'][$k] = $field->id;
                    } else {
                        unset($item['requiredFields'][$k]);
                    }
                }
            } else {
                $item['requiredFields'] = [];
            }
            $fieldLayout = Craft::$app->fields->assembleLayout($item['fieldLayout'], $item['requiredFields']);
        }
        $fieldLayout->type = $type;

        return $fieldLayout;
    }

    /**
     * @param array|string $sources
     * @param string $prefix
     */
    public function mapVolumeSources(&$sources, $prefix = 'folder:')
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                $source = Craft::$app->volumes->getVolumeByHandle($sourceHandle);
                if ($source) {
                    $sources[$k] = $prefix . $source->id;
                } else {
                    unset($sources[$k]);
                }
            }
        } else if (is_string($sources)) {
            $source = Craft::$app->volumes->getVolumeByHandle($sources);
            if ($source) {
                $sources = $prefix . $source->id;
            } else {
                $sources = '*';
            }
        } else {
            $sources = '*';
        }
    }

    /**
     * @param array|string $transforms
     * @param string $prefix
     */
    public function mapAssetTransforms(&$transforms, $prefix = 'transform:')
    {
        if (is_array($transforms)) {
            foreach ($transforms as $k => $transformHandle) {
                $transform = Craft::$app->assetTransforms->getTransformByHandle($transformHandle);
                if ($transform) {
                    $transforms[$k] = $prefix . $transform->id;
                } else {
                    unset($transforms[$k]);
                }
            }
        } else if (is_string($transforms)) {
            $transform = Craft::$app->assetTransforms->getTransformByHandle($transforms);
            if ($transform) {
                $transforms = $prefix . $transform->id;
            } else {
                $transforms = '*';
            }
        } else {
            $transforms = '*';
        }
    }

    /**
     * @param array|string $sources
     */
    public function mapSectionSources(&$sources)
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                if ($sourceHandle !== 'singles') {
                    $source = Craft::$app->sections->getSectionByHandle($sourceHandle);
                    $sources[$k] = 'section:' . $source->id;
                }
            }
        } else {
            $sources = '*';
        }
    }

    /**
     * @param array|string $sourceHandle
     */
    public function mapCategorySource(&$sourceHandle)
    {
        $source = Craft::$app->categories->getGroupByHandle($sourceHandle);
        if ($source) {
            $sourceHandle = 'group:' . $source->id;
        }
    }

    /**
     * @param array|string $sourceHandle
     */
    public function mapTagSource(&$sourceHandle)
    {
        $source = Craft::$app->tags->getTagGroupByHandle($sourceHandle);
        if ($source) {
            $sourceHandle = 'taggroup:' . $source->id;
        }
    }

    /**
     * @param array|string $sources
     */
    public function mapUserGroupSources(&$sources)
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                if ($sourceHandle !== 'admins') {
                    $source = Craft::$app->userGroups->getGroupByHandle($sourceHandle);
                    $sources[$k] = 'group:' . $source->id;
                }
            }
        } else {
            $sources = '*';
        }
    }

    /**
     * @param array $matrix
     */
    public function mapTypeSettings(array &$matrix)
    {
        if (isset($matrix['blockTypes']) && is_array($matrix['blockTypes'])) {
            foreach ($matrix['blockTypes'] as $blockKey => $blockType) {
                if (isset($blockType['fields']) && is_array($blockType['fields'])) {
                    foreach ($blockType['fields'] as $fieldKey => $field) {
                        $newField = [
                            'name' => $field['name'],
                            'handle' => $field['handle'],
                            'instructions' => $field['instructions'],
                            'required' => $field['required'],
                            'type' => $field['type'],
                        ];
                        unset($field['name']);
                        unset($field['handle']);
                        unset($field['instructions']);
                        unset($field['required']);
                        unset($field['type']);

                        $oldTypeSettings = [];
                        if (isset($field['typesettings'])) $oldTypeSettings = $field['typesettings'];
                        unset($field['typesettings']);

                        $newField['typesettings'] = array_merge($oldTypeSettings, $field);
                        $matrix['blockTypes'][$blockKey]['fields'][$fieldKey] = $newField;
                    }
                }
            }
        }
    }
}