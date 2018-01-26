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
class FieldProcessor implements ProcessorInterface
{
    public function parse(array $item)
    {
        // TODO: Implement parse() method.

        if (isset($item['group'])) {
            $groupName = $item['group'];
            unset($item['group']);
            $groupId = $this->getGroupIdByName($groupName);
        }

        if ($groupId && Craft::$app->fields->getGroupById($groupId)) {
            $fieldObject = array_merge($item, [
                'groupId' => $groupId
            ]);

            $field = Craft::$app->fields->createField($fieldObject);

            return $field;
        } else {
            return null;
        }
    }

    /**
     * @param \craft\base\Model|\craft\base\SavableComponentInterface $item
     * @param bool $update
     *
     * @return bool|object
     * @throws \Throwable
     */
    public function save($item, bool $update = false)
    {
        // TODO: Implement save() method.

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
}