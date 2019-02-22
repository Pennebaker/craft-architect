<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\controllers;

use craft\web\Controller;

/**
 * Cp Controller
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class CpController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/architect/cp
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect('architect/import');
    }

    /**
     * Handle a request going to our plugin's import URL,
     * e.g.: actions/architect/cp/import
     *
     * @return mixed
     */
    public function actionImport()
    {
        return $this->renderTemplate('architect/import', [ 'invalidJSON' => false ]);
    }

    /**
     * Handle a request going to our plugin's export URL,
     * e.g.: actions/architect/cp/export
     *
     * @return mixed
     */
    public function actionExport()
    {
        return $this->renderTemplate('architect/export');
    }

    /**
     * Handle a request going to our plugin's migrations URL,
     * e.g.: actions/architect/cp/migrations
     *
     * @return mixed
     */
    public function actionMigrations()
    {
        return $this->renderTemplate('architect/migrations');
    }
}
