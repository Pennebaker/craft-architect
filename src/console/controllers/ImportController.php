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

use craft\helpers\Console;
use yii\console\Controller;
use yii\console\ExitCode;

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
 * ./craft architect/import
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     1.0.0
 */
class ImportController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Import a json/yaml file structure.
     * ./craft architect/import
     *
     * @param string $filename
     *
     * @return int
     *
     * @throws \Throwable
     * @throws \craft\errors\ShellCommandException
     * @throws \yii\base\Exception
     */
    public function actionIndex($filename): int
    {
        return $this->import($filename);
    }

    /**
     * Import a json/yaml file structure updating any existing elements. (Only fields will update at this time)
     * ./craft architect/import/update
     *
     * @param string $filename
     *
     * @return int
     *
     * @throws \Throwable
     * @throws \craft\errors\ShellCommandException
     * @throws \yii\base\Exception
     */
    public function actionUpdate($filename): int
    {
        return $this->import($filename, true);
    }

    /**
     * @param string $filename
     * @param bool $update
     *
     * @return int
     *
     * @throws \Throwable
     * @throws \craft\errors\ShellCommandException
     * @throws \yii\base\Exception
     */
    private function import($filename, $update = false): int
    {
        list($parseError, , , $results) = Architect::$plugin->architectService->import(file_get_contents($filename), false, $update);

        if ($parseError) {
            $this->stdout('JSON: ', Console::FG_RED);
            $this->stdout($results[0] . PHP_EOL);
            $this->stdout('YAML: ', Console::FG_RED);
            $this->stdout($results[1] . PHP_EOL);

            return ExitCode::DATAERR;
        }

        foreach ($results as $value) {
            foreach ($value as $item) {
                if ($item['success'] === true) {
                    $this->stdout("Success: \n", Console::FG_GREEN);
                    $this->stdout('- ' . get_class($item['item']) . ' ' . $item['item']['name'] . PHP_EOL);
                } else {
                    $this->stdout("Error: \n", Console::FG_RED);
                    foreach ($item['errors'] as $err) {
                        $this->stdout('- ' . $err[0] . PHP_EOL);
                    }
                }
            }
        }

        return ExitCode::OK;
    }
}
