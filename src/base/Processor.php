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
use craft\base\Field;

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
     * @param $item
     * @param $type
     *
     * @return FieldLayout
     */
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
     * @param array $obj
     * @return array
     */
    public function stripNulls(array $obj) {
        $allowedNulls = [
            'maxLevels'
        ];
        foreach ($obj as $key => $value) {
            if (is_array($value)) {
                $obj[$key] = $this->stripNulls($value);
            } else if ($value === null && !in_array($key, $allowedNulls)) {
                unset($obj[$key]);
            }
        }
        return $obj;
    }

    /**
     * @param array|string $sites
     * @param string $prefix
     */
    public function mapSites(&$sites, $prefix = '')
    {
        if (is_array($sites)) {
            foreach ($sites as $k => $siteHandle) {
                $site = Craft::$app->sites->getSiteByHandle($siteHandle);
                if ($site) {
                    $sites[$k] = $prefix . $site->id;
                } else {
                    unset($sites[$k]);
                }
            }
        } else {
            $site = Craft::$app->sites->getSiteByHandle($sites);
            if ($site) {
                $sites = $prefix . $site->id;
            } else {
                $sites = null;
            }
        }
    }

    /**
     * @param array|string $sites
     * @param string $prefix
     */
    public function unmapSites(&$sites, $prefix = '')
    {
        if (is_array($sites)) {
            foreach ($sites as $k => $siteRef) {
                $siteId = substr($siteRef, strlen($prefix));
                $site = Craft::$app->sites->getSiteById($siteId);
                if ($site) {
                    $sites[$k] = $site->handle;
                } else {
                    unset($sites[$k]);
                }
            }
        } else if (is_string($sites)) {
            $siteId = substr($sites, strlen($prefix));
            $site = Craft::$app->sites->getSiteById($siteId);
            if ($site) {
                $sites = $site->handle;
            } else {
                $sites = null;
            }
        } else {
            $sites = null;
        }
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
     * @param array|string $sources
     * @param string $prefix
     */
    public function unmapVolumeSources(&$sources, $prefix = 'folder:')
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceRef) {
                $sourceId = substr($sourceRef, strlen($prefix));
                $source = Craft::$app->volumes->getVolumeById($sourceId);
                if ($source) {
                    $sources[$k] = $source->handle;
                } else {
                    unset($sources[$k]);
                }
            }
        } else if ($sources !== '*') {
            $sourceId = substr($sources, strlen($prefix));
            $source = Craft::$app->volumes->getVolumeById($sourceId);
            if ($source) {
                $sources = $source->handle;
            } else {
                $sources = '*';
            }
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
     * @param array|string $transforms
     * @param string $prefix
     */
    public function unmapAssetTransforms(&$transforms, $prefix = 'transform:')
    {
        if (is_array($transforms)) {
            foreach ($transforms as $k => $transformRef) {
                $transformId = substr($transformRef, strlen($prefix));
                $transform = Craft::$app->assetTransforms->getTransformById($transformId);
                if ($transform) {
                    $transforms[$k] = $transform->handle;
                } else {
                    unset($transforms[$k]);
                }
            }
        } else if (is_string($transforms)) {
            $transformId = substr($transforms, strlen($prefix));
            $transform = Craft::$app->assetTransforms->getTransformById($transformId);
            if ($transform) {
                $transforms = $transform->handle;
            } else {
                $transforms = '*';
            }
        }
    }

    /**
     * @param array|string $sources
     * @param string $prefix
     */
    public function mapSectionSources(&$sources, $prefix = 'section:')
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                if ($sourceHandle !== 'singles') {
                    $source = Craft::$app->sections->getSectionByHandle($sourceHandle);
                    $sources[$k] = $prefix . $source->id;
                }
            }
        } else {
            $sources = '*';
        }
    }

    /**
     * @param array|string $sources
     * @param string $prefix
     */
    public function unmapSectionSources(&$sources, $prefix = 'section:')
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceRef) {
                if ($sourceRef !== 'singles') {
                    $sourceId = substr($sourceRef, strlen($prefix));
                    $source = Craft::$app->sections->getSectionById($sourceId);
                    $sources[$k] = $source->handle;
                }
            }
        } else {
            $sources = '*';
        }
    }

    /**
     * @param array|string $sourceHandle
     * @param string $prefix
     */
    public function mapCategorySource(&$sourceHandle, $prefix = 'group:')
    {
        $source = Craft::$app->categories->getGroupByHandle($sourceHandle);
        if ($source) {
            $sourceHandle = $prefix . $source->id;
        }
    }

    /**
     * @param array|string $source
     * @param string $prefix
     */
    public function unmapCategorySource(&$source, $prefix = 'group:')
    {
        $sourceId = substr($source, strlen($prefix));
        $categoryGroup = Craft::$app->categories->getGroupById($sourceId);
        if ($categoryGroup) {
            $source = $categoryGroup->handle;
        }
    }

    /**
     * @param array|string $sourceHandle
     * @param string $prefix
     */
    public function mapTagSource(&$sourceHandle, $prefix = 'taggroup:')
    {
        $source = Craft::$app->tags->getTagGroupByHandle($sourceHandle);
        if ($source) {
            $sourceHandle = $prefix . $source->id;
        }
    }

    /**
     * @param array|string $source
     * @param string $prefix
     */
    public function unmapTagSource(&$source, $prefix = 'taggroup:')
    {
        $sourceId = substr($source, strlen($prefix));
        $tagGroup = Craft::$app->tags->getTagGroupById($sourceId);
        if ($tagGroup) {
            $source = $tagGroup->handle;
        }
    }

    /**
     * @param array|string $sources
     * @param string $prefix
     */
    public function mapUserGroupSources(&$sources, $prefix = 'group:')
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                if ($sourceHandle !== 'admins') {
                    $source = Craft::$app->userGroups->getGroupByHandle($sourceHandle);
                    $sources[$k] = $prefix . $source->id;
                }
            }
        } else {
            $sources = '*';
        }
    }

    /**
     * @param array|string $sources
     * @param string $prefix
     */
    public function unmapUserGroupSources(&$sources, $prefix = 'group:')
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceRef) {
                if ($sourceRef !== 'admins') {
                    $sourceId = substr($sourceRef, strlen($prefix));
                    $userGroup = Craft::$app->userGroups->getGroupById($sourceId);
                    $sources[$k] = $userGroup->handle;
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

    /**
     * @param string $class
     *
     * @return array|mixed
     */
    public function additionalAttributes(string $class) {
        $additionalAttributes = [];
        return (isset($additionalAttributes[$class])) ? $additionalAttributes[$class] : [];
    }

    /**
     * @param FieldLayout $fieldLayout
     *
     * @return array
     */
    public function exportFieldLayout($fieldLayout) {
//        Craft::dump($fieldLayout);
        $fieldLayoutObj = [];
        $tabs = $fieldLayout->getTabs();
        usort($tabs, function($a, $b) {
            return $a->sortOrder > $b->sortOrder;
        });
        foreach ($tabs as $tab) {
            $fieldLayoutObj[$tab->name] = [];
            foreach ($tab->getFields() as $field) {
                array_push($fieldLayoutObj[$tab->name], $field->handle);
            }
        }
        return $fieldLayoutObj;
    }

    /**
     * @param FieldLayout $fieldLayout
     *
     * @return array
     */
    public function exportRequiredFields($fieldLayout) {
//        Craft::dump($fieldLayout);
        $requiredFieldsObj = [];
        $tabs = $fieldLayout->getTabs();
        usort($tabs, function($a, $b) {
            return $a->sortOrder > $b->sortOrder;
        });
        foreach ($tabs as $tab) {
            foreach ($tab->getFields() as $field) {
                /** @var Field $field */
                if (boolval($field->required)) {
                    array_push($requiredFieldsObj, $field->handle);
                }
            }
        }
        return $requiredFieldsObj;
    }
}