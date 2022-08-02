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
use craft\base\Volume;
use craft\elements\Asset;
use craft\models\FieldLayout;
use pennebaker\architect\Architect;
use Throwable;
use yii\base\InvalidConfigException;
use function count;
use function get_class;


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
        if (count($item['fieldLayout']) > 1) {
            $errors = [
                'fieldLayout' => [
                    Architect::t('Field layout can only have 1 tab.')

                ]
            ];
            return [null, $errors];
        }

        $volume = new $item['type']([
            'name' => $item['name'],
            'handle' => $item['handle'],
            'fsHandle' => $item['fsHandle'],
            'transformFsHandle' => $item['transformFsHandle'] ?? null,
            'transformSubpath' => $item['transformSubpath'] ?? null,
            'titleTranslationMethod' => $item['titleTranslationMethod'] ?? 'site',
            'titleTranslationKeyFormat' => $item['titleTranslationKeyFormat'] ?? null,
        ]);
        $fieldLayout = new FieldLayout();
        $fieldLayout->type = Asset::class;

        $volume->setFieldLayout($fieldLayout);

        return [$volume, null];
    }

    /**
     * @param array $item
     *
     * @return bool|object
     *
     * @throws Throwable
     */
    public function setFieldLayout($item)
    {
        $volume = Craft::$app->volumes->getVolumeByHandle($item['handle']);

        $fieldLayout = $this->createFieldLayout($item, Asset::class);
        $volume->setFieldLayout($fieldLayout);

        return $this->save($volume);
    }

    /**
     * @param mixed $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws Throwable
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->volumes->saveVolume($item);
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @throws InvalidConfigException
     */
    public function exportById($id): array
    {
        $volume = Craft::$app->volumes->getVolumeById((int)$id);

        return $this->export($volume);
    }

    /**
     * @param $item
     * @param array $extraAttributes
     *
     * @return array
     *
     * @throws InvalidConfigException
     */
    public function export($item, array $extraAttributes = []): array
    {
        /** @var Volume $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)));
        foreach ($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        list ($fieldLayout, $fieldConfigs) = $this->exportFieldLayout($item->getFieldLayout());
        $volumeObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'type' => get_class($item),
            'fsHandle' => $item->fsHandle,
            'transformFsHandle' => $item->transformFsHandle,
            'transformSubpath' => $item->transformSubpath,
            'titleTranslationMethod' => $item->titleTranslationMethod,
            'titleTranslationKeyFormat' => $item->titleTranslationKeyFormat,
            'fieldLayout' => $fieldLayout,
            'fieldConfigs' => $fieldConfigs,
        ], $attributeObj);

        return $this->stripNulls($volumeObj);
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
