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

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

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
     * Import a json file structure.
     *
     * @param string $filename
     */
    public function actionIndex($filename)
    {
        echo "Welcome to the console ImportController actionIndex() method\n";
    }

    /**
     * Import a yaml file structure.
     *
     * @param string $filename
     */
    public function actionYaml($filename)
    {
		try {
			$data = Yaml::parse(file_get_contents($filename));
		} catch (ParseException $exception) {
			printf("unable to parse the YAML file provided: %s", $exception->getMessage());
		}

		$dataAsJson = json_encode($data);

		list($jsonError, $noErrors, $backup, $results) = Architect::$plugin->architectService->import($dataAsJson, false);

		if ($jsonError) {
			printf("invalid json: %s", json_last_error());
			return;
		}

		foreach ($results as $value) {
			foreach ($value as $item) {
				if ($item['success'] != true) {
					foreach ($item['errors'] as $err) {
						printf("Error: %s\n", $err[0]);
					}
				}
			}
		}

		return 1;
    }
}
