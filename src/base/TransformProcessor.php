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
use craft\models\AssetTransform;

/**
 * TransformProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class TransformProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        $transform = new AssetTransform();

        foreach ($item as $k => $v) {
            $transform->$k = $v;
        }

        return [$transform, null];
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
        return Craft::$app->assetTransforms->saveTransform($item);
    }

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return array
     */
    public function export($item, array $extraAttributes = [])
    {
        /** @var AssetTransform $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        $transformObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'mode' => $item->mode,
            'position' => $item->position,
            'width' => (int) $item->width,
            'height' => (int) $item->height,
            'quality' => (int) $item->quality,
            'interlace' => $item->interlace,
            'format' => $item->format,
        ], $attributeObj);

        return $transformObj;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function exportById($id)
    {
        $transform = Craft::$app->assetTransforms->getTransformById($id);

        return $this->export($transform);
    }
}