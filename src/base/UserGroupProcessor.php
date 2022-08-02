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

use Craft;
use craft\errors\WrongEditionException;
use craft\models\UserGroup;
use function get_class;

/**
 * SiteProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class UserGroupProcessor extends Processor
{
    /**
     * Returns the object of parsed data.
     *
     * @param array $item The item to save
     *
     * @return array
     */
    public function parse(array $item): array
    {
        unset($item['permissions']);
        $userGroup = new UserGroup($item);
        return [$userGroup, $userGroup->getErrors()];
    }

    /**
     * Saves the object to the database
     *
     * @param mixed $item The item to save
     * @param bool $update The item to save
     *
     * @return bool|object
     *
     * @throws WrongEditionException
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->userGroups->saveGroup($item);
    }

    /**
     * Gets an object from the passed in ID for export.
     *
     * @param $id
     *
     * @return mixed
     */
    public function exportById($id)
    {
        $userGroup = Craft::$app->userGroups->getGroupById($id);
        return $this->export($userGroup);
    }

    /**
     * Exports an object into an array.
     *
     * @param mixed $item The item to save
     * @param array $extraAttributes
     *
     * @return mixed
     */
    public function export($item, array $extraAttributes = [])
    {
        /** @var UserGroup $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)));
        foreach ($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        $userObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'permissions' => [],
        ], $attributeObj);

        $permissions = Craft::$app->userPermissions->getPermissionsByGroupId($item->id);
        foreach ($permissions as $permission) {
            $userObj['permissions'][] = $permission;
        }

        $this->unmapPermissions($userObj['permissions']);

        return $this->stripNulls($userObj);
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
