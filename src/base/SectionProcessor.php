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
use craft\models\Section;
use craft\models\Section_SiteSettings;
use pennebaker\architect\Architect;

/**
 * SectionProcessor
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
    public function parse(array $item): array
    {
        foreach ($item['siteSettings'] as $settingKey => $settings) {
            $siteSettings = new Section_SiteSettings(array_merge($settings, [
                'siteId' => isset($settings['siteId']) ? Craft::$app->sites->getSiteByHandle($settings['siteId'])->id : Craft::$app->sites->getPrimarySite()->id,
            ]));
            if (isset($siteSettings['hasUrls']) && (bool) $siteSettings['hasUrls'] === false) {
                $siteSettings['uriFormat'] = null;
            }
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
        return $additionalAttributes[$class] ?? [];
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
        /** @var Section $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)), $this->additionalAttributes($item->type));
        foreach($extraAttributes as $attribute) {
            if ($attribute === 'propagateEntries') {
                $attributeObj[$attribute] = (bool) $item->$attribute;
            } else {
                $attributeObj[$attribute] = $item->$attribute;
            }
        }

        $sectionObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'type' => $item->type,
            'enableVersioning' => (bool) $item->enableVersioning,
        ], $attributeObj);

        $siteSettings = $item->getSiteSettings();
        $sectionObj['siteSettings'] = [];
        foreach ($siteSettings as $siteSetting) {
            $hasUrls = (bool) $siteSetting->hasUrls;
            $sectionObj['siteSettings'][] = [
                'siteId' => $siteSetting->getSite()->primary ? null : $siteSetting->getSite()->handle,
                'hasUrls' => $hasUrls,
                'uriFormat' => $hasUrls ? $siteSetting->uriFormat : null,
                'template' => $siteSetting->template,
                'enabledByDefault' => (bool) $siteSetting->enabledByDefault,
            ];
        }
        $entryTypes = $item->getEntryTypes();
        $sectionObj['entryTypes'] = [];
        foreach ($entryTypes as $entryType) {
            $sectionObj['entryTypes'][] = Architect::$processors->entryTypes->export($entryType);
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
    public function exportById($id): array
    {
        $section = Craft::$app->sections->getSectionById((int) $id);

        return $this->export($section);
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