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
use craft\models\Site;

/**
 * SiteProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class SiteProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item): array
    {
        $item['groupId'] = $this->getGroupByName($item['group'])->id;
        unset($item['group']);
        $site = new Site($item);

        return [$site, null];
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
        return Craft::$app->sites->saveSite($item);
    }

    /**
     * @param $name
     *
     * @return \craft\models\SiteGroup|null
     */
    private function getGroupByName($name)
    {
        foreach (Craft::$app->sites->getAllGroups() as $group) {
            if ($group->name === $name) {
                return $group;
            }
        }
        return null;
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
        /** @var Site $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            if ($attribute === 'propagateEntries') {
                $attributeObj[$attribute] = (bool) $item->$attribute;
            } else {
                $attributeObj[$attribute] = $item->$attribute;
            }
        }

        $siteObj = array_merge([
            'groupId' => $item->getGroup()->id,
            'group' => $item->getGroup()->name,
            'name' => $item->name,
            'handle' => $item->handle,
            'language' => $item->language,
            'primary' => $item->primary,
            'hasUrls' => (bool) $item->hasUrls,
            'baseUrl' => $item->baseUrl,
        ], $attributeObj);

        return $this->stripNulls($siteObj);
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
        $site = Craft::$app->sites->getSiteById((int) $id);

        return $this->export($site);
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