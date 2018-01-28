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
use craft\elements\GlobalSet;

/**
 * GlobalSetProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class GlobalSetProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        $globalSet = new GlobalSet([
            'name' => $item['name'],
            'handle' => $item['handle'],
        ]);

        $fieldLayout = $this->createFieldLayout($item, GlobalSet::class);
        $globalSet->setFieldLayout($fieldLayout);

        return [$globalSet, null];
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
        return Craft::$app->globals->saveSet($item);
    }
}