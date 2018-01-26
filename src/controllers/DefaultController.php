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
class DefaultController extends Controller
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
     * Handle importing json object,
     * e.g.: actions/architect/default/import
     */
    public function actionImport()
    {
        $jsonData = Craft::$app->request->getBodyParam('jsonData');

        $jsonObj = json_decode($jsonData, true);

        if ($jsonObj === null) {
            $this->renderTemplate('architect/import', [
                'invalidJson' => json_last_error(),
                'jsonData' => $jsonData,
            ]);
            return;
        }


        $fieldGroupResults = [];
        foreach ($jsonObj['groups'] as $groupName) {
            list($fieldGroup, $fieldGroupErrors) = Architect::$processors->fieldGroup->parse(['name' => $groupName]);

            if ($fieldGroup) {
                $fieldGroupSuccess = Architect::$processors->fieldGroup->save($fieldGroup);;
                $fieldGroupErrors = $fieldGroup->getErrors();
            } else {
                $fieldGroupSuccess = false;
            }

            $fieldGroupResults[] = [
                'item' => ($fieldGroup) ? $fieldGroup : ['name' => $groupName],
                'success' => $fieldGroupSuccess,
                'errors' => $fieldGroupErrors,
            ];
        }

        $fieldResults = [];
        foreach ($jsonObj['fields'] as $fieldObj) {
            list($field, $fieldErrors) = Architect::$processors->field->parse($fieldObj);

            if ($field) {
                $fieldSuccess = Architect::$processors->field->save($field);
                $fieldErrors = $field->getErrors();
            } else {
                $fieldSuccess = false;
            }

            $fieldResults[] = [
                'item' => ($field) ? $field : $fieldObj,
                'success' => $fieldSuccess,
                'errors' => $fieldErrors,
            ];
        }

        $this->renderTemplate('architect/import_results', [
            'fieldGroupResults' => $fieldGroupResults,
            'fieldResults' => $fieldResults,
            'jsonData' => $jsonData,
        ]);

    }
}
