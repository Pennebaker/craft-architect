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
use pennebaker\architect\Architect;

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

    /**
     * @param string $class
     *
     * @return array|mixed
     */
    public function additionalAttributes(string $class) {
        $additionalAttributes = [
            'structure' => [
                'maxLevels',
            ],
            'channel' => [
                'propagateEntries'
            ],
        ];
        return (isset($additionalAttributes[$class])) ? $additionalAttributes[$class] : [];
    }

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function export($item, array $extraAttributes = [])
    {
        /** @var Section $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)), $this->additionalAttributes($item->type));
        foreach($extraAttributes as $attribute) {
            if ($attribute === 'propagateEntries') {
                $attributeObj[$attribute] = boolval($item->$attribute);
            } else {
                $attributeObj[$attribute] = $item->$attribute;
            }
        }

        $sectionObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'type' => $item->type,
            'enableVersioning' => boolval($item->enableVersioning),
        ], $attributeObj);

        $siteSettings = $item->getSiteSettings();
        $sectionObj['siteSettings'] = [];
        foreach ($siteSettings as $siteSetting) {
            array_push($sectionObj['siteSettings'], [
                'siteId' => ($siteSetting->getSite()->primary) ? null : $siteSetting->getSite()->handle,
                'hasUrls' => $siteSetting->hasUrls,
                'uriFormat' => $siteSetting->uriFormat,
                'template' => $siteSetting->template,
                'enabledByDefault' => boolval($siteSetting->enabledByDefault),
            ]);
        }
        $entryTypes = $item->getEntryTypes();
        $sectionObj['entryTypes'] = [];
        foreach ($entryTypes as $entryType) {
            array_push($sectionObj['entryTypes'], Architect::$processors->entryTypes->export($entryType));
        }

        return $this->stripNulls($sectionObj);
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function exportById($id)
    {
        $section = Craft::$app->sections->getSectionById((int) $id);

        return $this->export($section);
    }
}