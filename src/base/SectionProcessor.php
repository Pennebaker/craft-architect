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
class SectionProcessor implements ProcessorInterface
{
    public function parse(array $item)
    {
        // TODO: Implement parse() method.
        $sectionObject = [
            'name' => $item['name'],
            'handle' => $item['handle'],
            'type' => $item['type']
        ];
        $sectionObject['siteSettings'] = [
            new Section_SiteSettings(array_merge($item['siteSettings'], [
                'siteId' => Craft::$app->sites->getPrimarySite()->id,
            ])),
        ];
        $section = new Section($sectionObject);

        return $section;
    }

    public function save($item, bool $update = false)
    {
        // TODO: Implement save() method.

        return Craft::$app->sections->saveSection($item);
    }
}