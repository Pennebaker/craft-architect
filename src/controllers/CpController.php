<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\controllers;

use pennebaker\architect\Architect;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class CpController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
//    protected $allowAnonymous = [];

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
        $this->redirect('architect/import');
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/architect/cp/import
     *
     * @return mixed
     */
    public function actionImport()
    {
        $this->renderTemplate('architect/import', [ 'invalidJson' => false ]);
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/architect/cp/import
     *
     * @return mixed
     */
    public function actionExport()
    {
        $this->renderTemplate('architect/export');
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/architect/cp/import
     *
     * @return mixed
     */
    public function actionMigrations()
    {
        $this->renderTemplate('architect/migrations');
    }
}
