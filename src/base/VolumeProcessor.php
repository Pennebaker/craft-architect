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

use pennebaker\architect\Architect;

use Craft;
use craft\elements\Asset;
use craft\models\FieldLayout;
use craft\base\Volume;

/**
 * VolumeProcessor
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
    public function parse(array $item): array
    {
        if (\count($item['fieldLayout']) > 1) {
            $errors = [
                'fieldLayout' => [
                    Architect::t('Field layout can only have 1 tab.')

                ]
            ];
            return [null, $errors];
        }

        $volume = Craft::$app->volumes->createVolume([
            'type' => $item['type'],
            'name' => $item['name'],
            'handle' => $item['handle'],
            'hasUrls' => $item['hasUrls'],
            'url' => $item['hasUrls'] ? $item['url'] : null,
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
    public function setFieldLayout($item)
    {
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
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function export($item, array $extraAttributes = []): array
    {
        /** @var Volume $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(\get_class($item)));
        foreach($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        $hasUrls = (bool) $item->hasUrls;
        list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($item->getFieldLayout());
        $volumeObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'type' => \get_class($item),
            'hasUrls' => $hasUrls,
            'url' => $hasUrls ? $item->url : null,
            'settings' => $item->settings,
            'fieldLayout' => $fieldLayout,
            'fieldConfigs' => $fieldConfigs,
        ], $attributeObj);

        return $this->stripNulls($volumeObj);
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
        $volume = Craft::$app->volumes->getVolumeById((int) $id);

        return $this->export($volume);
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