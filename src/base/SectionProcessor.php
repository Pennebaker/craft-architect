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
use craft\models\Section;
use craft\models\Section_SiteSettings;

/**
 * SectionProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class SectionProcessor extends Processor
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
        foreach ($item['siteSettings'] as $settingKey => $settings) {
            $siteSettings = new Section_SiteSettings(array_merge($settings, [
                'siteId' => (isset($settings['siteId'])) ? Craft::$app->sites->getSiteByHandle($settings['siteId'])->id : Craft::$app->sites->getPrimarySite()->id,
            ]));
            $item['siteSettings'][$settingKey] = $siteSettings;
        }
        $section = new Section($item);

        return [$section, null];
    }

    /**
     * @param $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws \Throwable
     * @throws \craft\errors\SectionNotFoundException
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->sections->saveSection($item);
    }
}