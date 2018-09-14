<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\variables;

use craft\elements\User;

class ArchitectVariable
{
    // Public Methods
    // =========================================================================
    /**
     * @return array
     */
    public function getAllUsers(): array
    {
        return User::findAll();
    }
}