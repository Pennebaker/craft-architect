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
        $fieldType = $item['type'];
        $matchingFieldTypes = array_filter(Craft::$app->fields->getAllFieldTypes(), function($haystack) use ($fieldType) {
            return (strpos($haystack, $fieldType) !== false);
        });
        if (count($matchingFieldTypes) === 1) {
            $item = array_merge($item, [
                'type' => array_pop($matchingFieldTypes)
            ]);
        } else if (count($matchingFieldTypes) <= 0) {
            $errors = [
                'type' => [
                    'No field type matching "' . $fieldType . '".'
                ]
            ];
            return [null, $errors];
        } else {
            $errors = [
                'type' => [
                    'To many field types matching "' . $fieldType . '".<br>Possible values:<br>' . implode('<br>', $matchingFieldTypes)
                ]
            ];
            return [null, $errors];
        }

        $this->convertOld($item);
        $this->mapSources($item);

        if ($groupId && Craft::$app->fields->getGroupById($groupId)) {
            $fieldObject = array_merge($item, [
                'groupId' => $groupId
            ]);

            $field = Craft::$app->fields->createField($fieldObject);

            return [$field, $field->getErrors()];
        } else {
            $errors = [
                'group' => [
                    'Group id is invalid.'
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
                $item[$k] = $v;
            }
            unset($item['typesettings']);
        }
    }

    /**
     * @param array $item
     */
    private function mapSources(array &$item)
    {
        if ($item['type'] == 'craft\\fields\\Assets') {
            $this->mapVolumeSources($item['sources']);
        }
    }
}