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

/**
 * ProcessorInterface
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
interface ProcessorInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the object of parsed data.
     *
     * @param array $item The item to save
     *
     * @return array
     */
    public function parse(array $item): array;

    /**
     * Saves the object to the database
     *
     * @param mixed $item The item to save
     * @param bool $update The item to save
     *
     * @return object
     */
    public function save($item, bool $update);

    /**
     * Exports an object into an array.
     *
     * @param mixed $item The item to save
     * @param array $extraAttributes
     *
     * @return mixed
     */
    public function export($item, array $extraAttributes);

    /**
     * Gets an object from the passed in ID for export.
     *
     * @param $id
     *
     * @return mixed
     */
    public function exportById($id);

    /**
     * Gets an object from the passed in UID for export.
     *
     * @param $uid
     *
     * @return mixed
     */
    public function exportByUid($uid);
}
