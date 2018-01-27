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
use craft\models\Site;

/**
 * SiteProcessor defines the common interface to be implemented by plugin classes.
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
    public function parse(array $item)
    {
        // TODO: Implement parse() method.
        $item['groupId']= $this->getGroupByName($item['group'])->id;
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
    private function getGroupByName($name) {
        foreach (Craft::$app->sites->getAllGroups() as $group) {
            if ($group->name === $name) {
                return $group;
            }
        }
        return null;
    }
}