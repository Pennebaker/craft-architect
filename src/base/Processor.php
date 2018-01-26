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

/**
 * Processor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
abstract class Processor implements ProcessorInterface
{
    /**
     * @param array|string $sources
     */
    public function mapVolumeSources(&$sources)
    {
        if (is_array($sources)) {
            foreach ($sources as $k => $sourceHandle) {
                $source = Craft::$app->volumes->getVolumeByHandle($sourceHandle);
                $sources[$k] = 'folder:' . $source->id;
            }
        }
    }
}