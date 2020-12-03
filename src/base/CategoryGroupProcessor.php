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
use craft\elements\Tag;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;

/**
 * CategoryGroupProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class CategoryGroupProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     *
     * @throws \craft\errors\SiteNotFoundException
     */
    public function parse(array $item): array
    {
        $siteSettings = [];
        foreach ($item['siteSettings'] as $settingKey => $settings) {
            $siteId = isset($settings['siteId']) ? Craft::$app->sites->getSiteByHandle($settings['siteId'])->id : Craft::$app->sites->getPrimarySite()->id;
            unset($settings['siteId']);
            $siteSettings[$siteId] = new CategoryGroup_SiteSettings(array_merge($settings, [
                'siteId' => $siteId,
                'hasUrls' => !empty($settings['uriFormat']),
            ]));
        }
        $categoryGroup = new CategoryGroup([
            'name' => $item['name'],
            'handle' => $item['handle'],
            'maxLevels' => $item['maxLevels'],
        ]);

        $categoryGroup->setSiteSettings($siteSettings);

        return [$categoryGroup, null];
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
        return Craft::$app->categories->saveGroup($item);
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
        $categoryGroup = Craft::$app->categories->getGroupByHandle($item['handle']);

        if ($categoryGroup) {
            $fieldLayout = $this->createFieldLayout($item, Tag::class);
            $categoryGroup->setFieldLayout($fieldLayout);

            return $this->save($categoryGroup);
        }
        return false;
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
        /** @var CategoryGroup $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($item->getFieldLayout());
        $categoryGroupObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'maxLevels' => $item->maxLevels,
            'siteSettings' => [],
            'fieldLayout' => $fieldLayout,
            'fieldConfigs' => $fieldConfigs,
        ], $attributeObj);

        $siteSettings = $item->getSiteSettings();
        foreach ($siteSettings as $siteSetting) {
            $categoryGroupObj['siteSettings'][] = [
                'siteId' => $siteSetting->getSite()->primary ? null : $siteSetting->getSite()->handle,
                'uriFormat' => $siteSetting->uriFormat,
                'template' => $siteSetting->template,
            ];
        }

        return $this->stripNulls($categoryGroupObj);
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
        $categoryGroup = Craft::$app->categories->getGroupById((int) $id);

        return $this->export($categoryGroup);
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