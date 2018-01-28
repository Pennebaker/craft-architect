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
use craft\elements\Asset;
use craft\models\FieldLayout;

/**
 * VolumeProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class VolumeProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        $volume = Craft::$app->volumes->createVolume([
            'type' => $item['type'],
            'name' => $item['name'],
            'handle' => $item['handle'],
            'hasUrls' => $item['hasUrls'],
            'url' => $item['url'],
            'settings' => $item['settings'],
        ]);
        $fieldLayout = new FieldLayout();
        $fieldLayout->type = Asset::class;

        $volume->setFieldLayout($fieldLayout);

        return [$volume, null];
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
        return Craft::$app->volumes->saveVolume($item);
    }

    /**
     * @param array $item
     *
     * @return bool|object
     *
     * @throws \Throwable
     */
    public function setFieldLayout($item) {
        $volume = Craft::$app->volumes->getVolumeByHandle($item['handle']);

        $fieldLayout = $this->createFieldLayout($item, Asset::class);
        $volume->setFieldLayout($fieldLayout);

        return $this->save($volume);
    }
}