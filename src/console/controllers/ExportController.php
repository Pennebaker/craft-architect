<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * kashbfkab
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\console\controllers;

use pennebaker\architect\Architect;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Default Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft architect/export
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     1.0.0
 */
class ExportController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Export a json file structure.
     *
     * @param string $filename
     */
    public function actionIndex($filename)
    {
        echo "Welcome to the console ExportController actionIndex() method\n";
    }
}
