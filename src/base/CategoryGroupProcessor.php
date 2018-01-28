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
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;

/**
 * CategoryGroupProcessor defines the common interface to be implemented by plugin classes.
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
    public function parse(array $item)
    {
        $siteSettings = [];
        foreach ($item['siteSettings'] as $settingKey => $settings) {
            $siteId = (isset($settings['siteId'])) ? Craft::$app->sites->getSiteByHandle($settings['siteId'])->id : Craft::$app->sites->getPrimarySite()->id;
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
    public function setFieldLayout($item) {
        $categoryGroup = Craft::$app->categories->getGroupByHandle($item['handle']);

        $fieldLayout = $this->createFieldLayout($item, Tag::class);
        $categoryGroup->setFieldLayout($fieldLayout);

        return $this->save($categoryGroup);
    }
}