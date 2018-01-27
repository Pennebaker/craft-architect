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

/**
 * Processor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
abstract class Processor implements ProcessorInterface
{
    /**
     * @param array|string $sources
     */
    public function mapVolumeSources(&$sources)
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                $source = Craft::$app->volumes->getVolumeByHandle($sourceHandle);
                $sources[$k] = 'folder:' . $source->id;
            }
        } else {
            $sources = '*';
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