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
use craft\elements\GlobalSet;

/**
 * GlobalSetProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class GlobalSetProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item): array
    {
        $globalSet = new GlobalSet([
            'name' => $item['name'],
            'handle' => $item['handle'],
        ]);

        return [$globalSet, null];
    }

    /**
     * @param mixed $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->globals->saveSet($item);
    }

    /**
     * @param array $item
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function setFieldLayout($item)
    {
        $globalSet = Craft::$app->globals->getSetByHandle($item['handle']);
        if ($globalSet) {
            $fieldLayout = $this->createFieldLayout($item, GlobalSet::class);
            $globalSet->setFieldLayout($fieldLayout);

            return $this->save($globalSet);
        }
        return false;
    }

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return array
     */
    public function export($item, array $extraAttributes = []): array
    {
        /** @var GlobalSet $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($item->getFieldLayout());
        $globalSetObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'fieldLayout' => $fieldLayout,
            'fieldConfigs' => $fieldConfigs,
        ], $attributeObj);

        return $this->stripNulls($globalSetObj);
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function exportById($id): array
    {
        $globalSet = Craft::$app->globals->getSetById((int) $id);

        return $this->export($globalSet);
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