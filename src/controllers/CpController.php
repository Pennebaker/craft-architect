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

use pennebaker\architect\Architect;

use Craft;
use craft\web\Controller;
use craft\helpers\FileHelper;

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
        $filename = Architect::$configPath . DIRECTORY_SEPARATOR . Craft::$app->request->getQueryParam('file');
        $fileContents = '';
        if (is_file($filename)) {
            $fileContents = file_get_contents($filename);
        }
        return $this->renderTemplate('architect/import', [ 'importData' => $fileContents, 'invalidJSON' => false ]);
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
     * Handle a request going to our plugin's blueprints URL,
     * e.g.: actions/architect/cp/blueprints
     *
     * @return mixed
     */
    public function actionBlueprints()
    {
        $configFiles = FileHelper::findFiles(Architect::$configPath, [
            'only' => ['*.json', '*.yaml'],
            'recursive' => false
        ]);

        foreach ($configFiles as &$file) {
            $file = pathinfo($file, PATHINFO_BASENAME);
        }
        unset($file);

        return $this->renderTemplate('architect/blueprints', [
            'files' => $configFiles
        ]);
    }
}
