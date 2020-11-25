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
use craft\elements\Entry;
use craft\models\EntryType;

/**
 * EntryTypeProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class EntryTypeProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item): array
    {
        $section = Craft::$app->sections->getSectionByHandle($item['sectionHandle']);

        $item['sectionId'] = $section->id;

        $sectionEntryTypes = $section->getEntryTypes();
        if ($section->type === 'single') {
            $item['id'] = $sectionEntryTypes[0]->id;
            $item['name'] = $sectionEntryTypes[0]->name;
            $item['handle'] = $sectionEntryTypes[0]->handle;
        } else {
            foreach ($sectionEntryTypes as $sectionEntryType) {
                if ($sectionEntryType->handle === $item['handle']) {
                    $item['id'] = $sectionEntryType->id;
                }
            }
        }

        $entryType = isset($item['id']) ? Craft::$app->sections->getEntryTypeById((int) $item['id']) : new EntryType();
        $entryType->sectionId = $section->id;
        $entryType->name = $item['name'];
        $entryType->handle = $item['handle'];
        $entryType->hasTitleField = $item['hasTitleField'];
        if (!(bool) $item['hasTitleField']) {
            $entryType->titleFormat = $item['titleFormat'];
        }

        $fieldLayout = $this->createFieldLayout($item, Entry::class);

        $item['fieldLayout'] = $fieldLayout;

        $entryType->setFieldLayout($fieldLayout);

        return [$entryType, null];
    }

    /**
     * @param $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws \Throwable
     * @throws \craft\errors\EntryTypeNotFoundException
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->sections->saveEntryType($item);
    }

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function export($item, array $extraAttributes = []): array
    {
        /** @var EntryType $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }
        $hasTitleField = (bool) $item->hasTitleField;

        list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($item->getFieldLayout());
        $entryTypeObj = array_merge([
            'sectionHandle' => $item->getSection()->handle,
            'name' => $item->name,
            'handle' => $item->handle,
            'hasTitleField' => $hasTitleField,
            'titleFormat' => (!$hasTitleField) ? $item->titleFormat : '',
            'fieldLayout' => $fieldLayout,
            'fieldConfigs' => $fieldConfigs,
        ], $attributeObj);

        return $this->stripNulls($entryTypeObj);
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function exportById($id): array
    {
        $entryType = Craft::$app->sections->getEntryTypeById((int) $id);

        return $this->export($entryType);
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