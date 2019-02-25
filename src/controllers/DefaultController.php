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

use Craft;
use craft\web\Controller;

use pennebaker\architect\Architect;

/**
 * Default Controller
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================
    /**
     * Handle importing json object,
     * e.g.: actions/architect/default/import
     *
     * @throws \Throwable
     */
    public function actionImport()
    {
        // Load posted json data into a variable.
        $importData = Craft::$app->request->getBodyParam('importData');

        $updateExisting = (bool) Craft::$app->request->getBodyParam('updateExisting');

        list($parseError, $noErrors, $backup, $results) = Architect::$plugin->architectService->import($importData, false, $updateExisting);

        if ($parseError) {
            $this->renderTemplate('architect/import', [
                'invalidJSON' => $results[0],
                'invalidYAML' => $results[1],
                'updateExisting' => $updateExisting,
                'importData' => $importData,
            ]);
            return;
        }

        $this->renderTemplate('architect/import_results', [
            'noErrors' => $noErrors,
            'backupLocation' => $backup,
            'results' => $results,
            'updateExisting' => $updateExisting,
            'importData' => $importData,
        ]);
    }

    /**
     * Handle exporting structures,
     * e.g.: actions/architect/default/export
     */
    public function actionExport() {
        // Initialize export array.
        $data = [
            'siteGroups' => [],
            'sites' => [],
            'routes' => [],
            'fieldGroups' => [],
            'volumes' => [],
            'transforms' => [],
            'tagGroups' => [],
            'categoryGroups' => [],
            'sections' => [],
            'fields' => [],
            'entryTypes' => [],
            'globalSets' => [],
            'userGroups' => [],
            'users' => [],
        ];
        // The list of exportable items.
        $exportList = [
            'sites' => [
                'bodyParam' => 'siteSelection',
                'postProcess' => [
                    'groupId' => 'siteGroups'
                ],
            ],
            'sections' => [
                'bodyParam' => 'sectionSelection',
                'postProcess' => [
                    'entryTypes' => 'entryTypes'
                ],
            ],
            'routes' => [
                'bodyParam' => 'routeSelection',
            ],
            'volumes' => [
                'bodyParam' => 'volumeSelection',
            ],
            'transforms' => [
                'bodyParam' => 'assetTransformSelection',
            ],
            'tagGroups' => [
                'bodyParam' => 'tagSelection',
            ],
            'categoryGroups' => [
                'bodyParam' => 'categorySelection',
            ],
            'fields' => [
                'bodyParam' => 'fieldSelection',
                'postProcess' => [
                    'groupId' => 'fieldGroups'
                ],
            ],
            'globalSets' => [
                'bodyParam' => 'globalSelection',
            ],
            'userGroups' => [
                'bodyParam' => 'userGroupSelection',
            ],
            'users' => [
                'bodyParam' => 'userSelection',
            ]
        ];

        foreach ($exportList as $processorName => $processorInfo) {
            $exportIds = Craft::$app->request->getBodyParam($processorInfo['bodyParam']);
            if ($exportIds) {
                foreach ($exportIds as $exportId) {
                    if ($processorName === 'routes') {
                        $exportObj = Architect::$processors->$processorName->exportByUid($exportId);
                    } else {
                        $exportObj = Architect::$processors->$processorName->exportById($exportId);
                    }

                    if (isset($processorInfo['postProcess'])) {
                        foreach ($processorInfo['postProcess'] as $postProcessKey => $postProcessorName) {
                            switch ($postProcessKey) {
                                case 'groupId':
                                    $groupName = Architect::$processors->$postProcessorName->exportById($exportObj[$postProcessKey]);
                                    if (\in_array($groupName, $data[$postProcessorName], false) === false) {
                                        $data[$postProcessorName][] = $groupName;
                                    }
                                    unset($exportObj[$postProcessKey]);
                                    break;
                                case 'entryTypes':
                                    if (isset($exportObj[$postProcessKey]) && \is_array($exportObj[$postProcessKey])) {
                                        $data[$postProcessorName] = array_merge($data[$postProcessorName], $exportObj[$postProcessKey]);
                                        unset($exportObj[$postProcessKey]);
                                    }
                                    break;
                            }
                        }
                    }

                    $data[$processorName][] = $exportObj;
                }
            }
        }

        foreach ($data as $key => $value) {
            if (\count($value) <= 0) {
                unset($data[$key]);
            }
        }

        $this->renderTemplate('architect/export_results', [ 'dump' => json_encode($data, JSON_PRETTY_PRINT) ]);
    }
}
