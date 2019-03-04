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
use craft\records\Route;
use pennebaker\architect\Architect;

/**
 * SectionProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class RouteProcessor extends Processor
{
    /**
     * Returns the object of parsed data.
     *
     * @param array $item The item to save
     *
     * @return array
     */
    public function parse(array $item): array
    {
        $siteUid = null;
        try {
            $site = Craft::$app->sites->getSiteByHandle($item['siteId']);
            $siteUid = $site->uid;
        } catch (\Exception $e) {}
        return [
            [ $item['uriParts'], $item['template'], $siteUid ],
            false
        ];
    }

    /**
     * Saves the object to the database
     *
     * @param mixed $item The item to save
     * @param bool $update The item to save
     *
     * @return mixed
     */
    public function save($item, bool $update)
    {
        $routeUid = Craft::$app->routes->saveRoute(...$item);
        if ($routeUid) {
            return Architect::getRouteByUid($routeUid);
        }
        return false;
    }

    /**
     * Exports an object into an array.
     *
     * @param mixed $item The item to save
     * @param array $extraAttributes
     *
     * @return mixed
     */
    public function export($item, array $extraAttributes = [])
    {
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes('route'));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        $routeObj = array_merge([
            'template' => $item['template'],
            'uriParts' => $item['uriParts'],
        ], $attributeObj);

        if ($item['siteUid']) {
            try {
                $routeObj['siteId'] = Craft::$app->sites->getSiteByUid($item['siteUid'])->handle;
            } catch (\craft\errors\SiteNotFoundException $e) {}
        }

        return $this->stripNulls($routeObj);
    }

    /**
     * Gets an object from the passed in ID for export.
     *
     * @param $id
     *
     * @return mixed
     */
    public function exportById($id)
    {
        // TODO: Implement exportById() method.
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
        return $this->export(Architect::getRouteByUid($uid));
    }
}