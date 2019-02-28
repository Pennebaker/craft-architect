<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\variables;

use pennebaker\architect\Architect;

use Craft;
use craft\elements\User;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ArchitectVariable
{
    // Public Methods
    // =========================================================================
    /**
     * @return array
     */
    public function getAllUsers(): array
    {
        return User::findAll();
    }

    public function getData($file): array
    {
        $importData = file_get_contents(Architect::$configPath . DIRECTORY_SEPARATOR . $file);

        // Convert json into an array.
        $importObj = json_decode($importData, true);
        // Attempt yaml parsing if json_decode failed.
        if (json_last_error() !== JSON_ERROR_NONE) {
            try {
                $importObj = Yaml::parse($importData);
            } catch (ParseException $exception) {
                return [
                    'error' => 'Parse Error'
                ];
            }
        }

        foreach ($importObj as $type => &$obj) {
            foreach ($obj as &$item) {
                if (is_array($item)) {
                    if ($type === 'routes') {
                        $item = Architect::createRouteUriDisplay($item['uriParts']);
                    } else {
                        $item = $item['name'];
                    }
                }
            }
            unset($item);
        }
        unset($obj);

        return $importObj;
    }

    public function getIcon($key): string
    {
        $icons = [
            'siteGroups' => '@app/icons/world.svg',
            'sites' => '@app/icons/world.svg',
            'routes' => '@app/icons/routes.svg',
            'sections' => '@app/icons/newspaper.svg',
            'volumes' => '@app/icons/photo.svg',
            'transforms' => '@app/icons/photo.svg',
            'tagGroups' => '@app/icons/tags.svg',
            'categoryGroups' => '@app/icons/folder-open.svg',
            'userGroups' => '@app/icons/users.svg',
            'fieldGroups' => '@app/icons/field.svg',
            'fields' => '@app/icons/field.svg',
            'entryTypes' => '@app/icons/newspaper.svg',
            'globalSets' => '@app/icons/globe.svg',
            'users' => '@app/icons/users.svg',
            'buildOrder' => '@app/icons/wand.svg',
        ];
        return $icons[$key];
    }
}
