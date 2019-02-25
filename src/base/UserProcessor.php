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
use craft\elements\User;

/**
 * SiteProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class UserProcessor extends Processor
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
        unset($item['permissions'], $item['groups']);
        $item['pending'] = true;

        $user = new User($item);

        $user->username = $item['email'];

        return [$user, $user->getErrors()];
    }

    /**
     * Saves the object to the database
     *
     * @param mixed $item The item to save
     * @param bool $update The item to save
     *
     * @return bool|object
     *
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function save($item, bool $update = false)
    {
        /** @var User $item */
        return Craft::$app->getElements()->saveElement($item);
    }

    /**
     * Exports an object into an array.
     *
     * @param mixed $item The item to save
     * @param array $extraAttributes
     *
     * @return array
     */
    public function export($item, array $extraAttributes = []): array
    {
        /** @var User $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            if ($attribute === 'propagateEntries') {
                $attributeObj[$attribute] = (bool) $item->$attribute;
            } else {
                $attributeObj[$attribute] = $item->$attribute;
            }
        }

        $userObj = array_merge([
            'groups' => [],
            'email' => $item->email,
            'firstName' => $item->firstName,
            'lastName' => $item->lastName,
            'admin' => (bool) $item->admin,
            'permissions' => [],
        ], $attributeObj);

        if ((bool) $item->admin) {
            $userObj['permissions'] = null;
        } else {
            $permissionsFromGroup = [];

            /**
             * This method removes any permissions that might be from belonging to a group.
             * This can cause an incomplete export since permissions assign to the user that it also gets from a group will get filtered out of the user export.
             *
             * Maybe there is a better way to grab permissions assigned directly to the user only.
             */
            foreach ($item->groups as $group) {
                $userObj['groups'][] = $group->handle;
                $groupPermissions = Craft::$app->userPermissions->getPermissionsByGroupId($group->id);
                foreach ($groupPermissions as $permission) {
                    if (\in_array($permission, $permissionsFromGroup, false) === false) {
                        $permissionsFromGroup[] = $permission;
                    }
                }
            }
            $permissions = Craft::$app->userPermissions->getPermissionsByUserId($item->id);
            foreach ($permissions as $permission) {
                if (\in_array($permission, $permissionsFromGroup, false) === false) {
                    $userObj['permissions'][] = $permission;
                }
            }

            $this->unmapPermissions($userObj['permissions']);
        }

        return $this->stripNulls($userObj);
    }

    /**
     * Gets an object from the passed in ID for export.
     *
     * @param $id
     *
     * @return array
     */
    public function exportById($id): array
    {
        $user = Craft::$app->users->getUserById($id);
        return $this->export($user);
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