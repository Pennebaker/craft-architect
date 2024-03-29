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
use craft\models\FieldGroup;
use Throwable;

/**
 * FieldGroupProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class FieldGroupProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item): array
    {
        return [new FieldGroup($item), null];
    }

    /**
     * @param mixed $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws Throwable
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->fields->saveGroup($item);
    }

    /**
     * @param $id
     *
     * @return string
     */
    public function exportById($id): string
    {
        $fieldGroup = Craft::$app->fields->getGroupById((int)$id);

        return $this->export($fieldGroup);
    }

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return string
     */
    public function export($item, array $extraAttributes = []): string
    {
        /** @var FieldGroup $item */
        return $item->name;
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
