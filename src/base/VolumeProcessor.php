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

use pennebaker\architect\Architect;

use Craft;
use craft\elements\Asset;
use craft\models\FieldLayout;
use craft\base\Volume;

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
        if (sizeof($item['fieldLayout']) > 1 || (sizeof($item['fieldLayout']) === 1 && !isset($item['fieldLayout']['Content']))) {
            $errors = [
                'fieldLayout' => [
                    Architect::t('Field layout can only have 1 tab named "Content".')

                ]
            ];
            return [null, $errors];
        }

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

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return array
     */
    public function export($item, array $extraAttributes = [])
    {
        /** @var Volume $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        $hasUrls = boolval($item->hasUrls);
        $volumeObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'type' => get_class($item),
            'hasUrls' => $hasUrls,
            'url' => ($hasUrls) ? $item->url : null,
            'settings' => $item->settings,
            'fieldLayout' => $this->exportFieldLayout($item->getFieldLayout()),
            'requiredFields' => $this->exportRequiredFields($item->getFieldLayout()),
        ], $attributeObj);

        if (count($volumeObj['requiredFields']) <= 0) {
            unset($volumeObj['requiredFields']);
        }

        return $this->stripNulls($volumeObj);
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function exportById($id)
    {
        $volume = Craft::$app->volumes->getVolumeById($id);

        return $this->export($volume);
    }
}