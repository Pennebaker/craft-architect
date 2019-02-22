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
        $configPath = Craft::$app->getPath()->getConfigPath() . DIRECTORY_SEPARATOR . 'architect';

        $importData = file_get_contents($configPath . DIRECTORY_SEPARATOR . $file);

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
                    $item = $item['name'];
                }
            }
            unset($item);
        }
        unset($obj);

        return $importObj;
    }
}