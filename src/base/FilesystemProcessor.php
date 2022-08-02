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
use craft\base\Filesystem;
use craft\elements\Asset;
use Throwable;
use yii\base\InvalidConfigException;
use function get_class;

/**
 * FilesystemProcessor
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     4.0.0
 */
class FilesystemProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item): array
    {
        $fileSystem = Craft::$app->fs->createFilesystem([
            'type' => $item['type'],
            'name' => $item['name'],
            'handle' => $item['handle'],
            'hasUrls' => $item['hasUrls'],
            'url' => $item['hasUrls'] ? $item['url'] : null,
            'path' => $item['path'],
        ]);

        return [$fileSystem, null];
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
        $fileSystem = Craft::$app->fs->getFilesystemByHandle($item['handle']);

        $fieldLayout = $this->createFieldLayout($item, Asset::class);
        $fileSystem->setFieldLayout($fieldLayout);

        return $this->save($fileSystem);
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
        return Craft::$app->fs->saveFilesystem($item);
    }

    /**
     * @param $handle
     *
     * @return array
     *
     * @throws InvalidConfigException
     */
    public function exportByHandle($handle): array
    {
        $fileSystem = Craft::$app->fs->getFilesystemByHandle($handle);

        return $this->export($fileSystem);
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
        /** @var Filesystem $item */
        $attributeObj = [];
        $extraAttributes = array_merge($extraAttributes, $this->additionalAttributes(get_class($item)));
        foreach ($extraAttributes as $attribute) {
            $attributeObj[$attribute] = $item->$attribute;
        }

        $hasUrls = (bool)$item->hasUrls;
        $fileSystemObj = array_merge([
            'name' => $item->name,
            'handle' => $item->handle,
            'type' => get_class($item),
            'hasUrls' => $hasUrls,
            'url' => $hasUrls ? $item->url : null,
            'path' => $item->path,
        ], $attributeObj);

        return $this->stripNulls($fileSystemObj);
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

    public function exportById($id)
    {
        // Filesystems do not have IDs.
    }
}
