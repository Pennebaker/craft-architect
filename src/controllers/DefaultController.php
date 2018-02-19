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

use Craft;
use craft\web\Controller;
use craft\models\Section_SiteSettings;

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
        $jsonData = Craft::$app->request->getBodyParam('jsonData');

        // Convert json into an array.
        $jsonObj = json_decode($jsonData, true);

        if ($jsonObj === null) {
            $this->renderTemplate('architect/import', [
                'invalidJson' => json_last_error(),
                'jsonData' => $jsonData,
            ]);
            return;
        }

        // Create a database backup in the event of catastrophic failure
        $backup = Craft::$app->getDb()->backup();

        // Did we import everything without errors?
        $noErrors = true;

        // The order things should be processed in.
        $parseOrder = [
            'siteGroups',
            'sites',
            'sections',
            'volumes',
            'transforms',
            'tagGroups',
            'categoryGroups',
            'userGroups',
            'fieldGroups',
            'fields',
            'entryTypes',
            'globalSets',
            'users',
        ];
        // Successfully imported items needed for various post processing procedures.
        $successful = [
            'sections' => [],
            'volumes' => [],
            'tagGroups' => [],
            'categoryGroups' => [],
            'userGroups' => [],
            'users' => [],
        ];
        /**
         * Things to process field layouts for after importing of fields.
         * Things in this list are needed for fields to import properly but can also use fields in field layouts.
         */
        $postProcessFieldLayouts = [
            'volumes',
            'tagGroups',
            'categoryGroups',
        ];
        /**
         * Things to process permissions for after importing of fields.
         * Things in this list are needed for fields to import properly but can also use user groups in fields.
         */
        $postProcessPermissions = [
            'users',
            'userGroups',
        ];
        $addedEntryTypes = [];
        $results = [];
        foreach ($parseOrder as $parseKey) {
            if (isset($jsonObj[$parseKey]) && is_array($jsonObj[$parseKey])) {
                $results[$parseKey] = [];
                foreach ($jsonObj[$parseKey] as $itemKey => $itemObj) {
                    try {
                        if ($parseKey === 'fieldGroups' || $parseKey === 'siteGroups') {
                            list($item, $itemErrors) = Architect::$processors->$parseKey->parse(['name' => $itemObj]);
                        } else {
                            list($item, $itemErrors) = Architect::$processors->$parseKey->parse($itemObj);
                        }

                        if ($parseKey === 'entryTypes' && array_search($itemObj['sectionHandle'], $successful['sections']) === false) {
                            if (!isset($itemObj['name'])) $itemObj['name'] = '';
                            if (!isset($itemObj['handle'])) $itemObj['handle'] = $itemObj['sectionHandle'];
                            $item = false;
                            $itemErrors = [
                                'parent' => [
                                    Architect::t('Section parent "{sectionHandle}" was not imported successfully.', [ 'sectionHandle' => $itemObj['sectionHandle'] ])
                                ]
                            ];
                        }

                        if ($item) {
                            $itemSuccess = Architect::$processors->$parseKey->save($item);
                            if ($parseKey === 'sections') {
                                $itemErrors = [];

                                /** @var mixed $item */
                                foreach ($item->getSiteSettings() as $settings) {
                                    /** @var Section_SiteSettings $settings */
                                    foreach ($settings->getErrors() as $errorKey => $errors) {
                                        if (isset($itemErrors[$errorKey])) {
                                            $itemErrors[$errorKey] = array_merge($itemErrors[$errorKey], $errors);
                                        } else {
                                            $itemErrors[$errorKey] = $errors;
                                        }
                                    }
                                }
                                $itemErrors = array_merge($itemErrors, $item->getErrors());
                            } else {
                                /** @var mixed $item */
                                $itemErrors = $item->getErrors();
                            }
                        } else {
                            $itemSuccess = false;
                        }
                    } catch (\Error $e) {
                        $item = false;
                        $itemSuccess = false;
                        $itemErrors = [
                            'error' => [
                                $e->getMessage()
                            ]
                        ];
                    } catch (\Exception $e) {
                        $item = false;
                        $itemSuccess = false;
                        $itemErrors = [
                            'exception' => [
                                $e->getMessage()
                            ]
                        ];
                    }

                    if (!$itemSuccess) $noErrors = false;

                    if ($parseKey === 'fieldGroups' || $parseKey === 'siteGroups') {
                        $item = ($item) ? $item : ['name' => $itemObj];
                    } else {
                        $item = ($item) ? $item : $itemObj;
                    }
                    if ($itemSuccess) {
                        $jsonObj[$parseKey][$itemKey]['id'] = $item->id;
                        if ($parseKey === 'entryTypes') {
                            $addedEntryTypes[] =  Craft::$app->sections->getSectionById((int) $item->sectionId)->handle . ':' . $item->handle;
                        }
                        switch ($parseKey) {
                            case 'sections':
                                $successful[$parseKey][] = $item->handle;
                                break;
                            case 'volumes':
                            case 'tagGroups':
                            case 'categoryGroups':
                            case 'userGroups':
                            case 'users':
                                $successful[$parseKey][] = $itemKey;
                                break;
                        }
                    }
                    $results[$parseKey][] = [
                        'item' => $item,
                        'success' => $itemSuccess,
                        'errors' => $itemErrors,
                    ];
                }
            }
        }

        /**
         * Post Processing to set Field Layouts
         */
        foreach ($postProcessFieldLayouts as $parseKey) {
            if (isset($jsonObj[$parseKey]) && is_array($jsonObj[$parseKey])) {
                foreach($successful[$parseKey] as $volumeHandle => $itemKey) {
                    $itemObj = $jsonObj[$parseKey][$itemKey];
                    Architect::$processors->$parseKey->setFieldLayout($itemObj);
                }
            }
        }

        /**
         * Post Processing on Users to assign User Groups
         */
        if (isset($jsonObj['users']) && is_array($jsonObj['users'])) {
            foreach ($successful['users'] as $itemKey) {
                $itemObj = $jsonObj['users'][$itemKey];
                if (isset($itemObj['groups']) && is_array($itemObj['groups'])) {
                    $groupIds = [];
                    foreach ($itemObj['groups'] as $groupHandle) {
                        $group = Craft::$app->userGroups->getGroupByHandle($groupHandle);
                        if ($group)
                            $groupIds[] = $group->id;
                    }
                    Craft::$app->users->assignUserToGroups($itemObj['id'], $groupIds);
                }
            }
        }

        /**
         * Post Processing to set permissions
         */
        foreach ($postProcessPermissions as $parseKey) {
            if (isset($successful[$parseKey]) && is_array($successful[$parseKey])) {
                foreach($successful[$parseKey] as $itemKey) {
                    $itemObj = $jsonObj[$parseKey][$itemKey];
                    Architect::$processors->$parseKey->setPermissions($parseKey, $itemObj);
                }
            }
        }

        /**
         * Post Processing on Section Entry Types
         * This is to loop over all entry types in a section and remove entry types that do not match one that was meant to be created.
         * ex. A section was created for Employees but there is only entry types defined for Board Members & Management
         */
        if (isset($jsonObj['sections']) && is_array($jsonObj['sections']) && isset($jsonObj['entryTypes']) && is_array($jsonObj['entryTypes'])) {
            forEach ($successful['sections'] as $sectionHandle) {
                $section = Craft::$app->sections->getSectionByHandle($sectionHandle);
                $entryTypes = $section->getEntryTypes();
                foreach ($entryTypes as $entryType) {
                    if (array_search($section->handle. ':' . $entryType->handle, $addedEntryTypes) === false) {
                        Craft::$app->sections->deleteEntryType($entryType);
                    }
                }
            }
        }

        if ($noErrors) {
            unlink($backup);
        } else {
            Architect::warning('Architect encountered errors performing an import, there is a database backup located at: {backup}', [ 'backup' => $backup ]);
        }

        $this->renderTemplate('architect/import_results', [
            'noErrors' => $noErrors,
            'backupLocation' => $backup,
            'results' => $results,
            'jsonData' => $jsonData,
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
                    $exportObj = Architect::$processors->$processorName->exportById($exportId);

                    if (isset($processorInfo['postProcess'])) {
                        foreach ($processorInfo['postProcess'] as $postProcessKey => $postProcessorName) {
                            switch ($postProcessKey) {
                                case 'groupId':
                                    $groupName = Architect::$processors->$postProcessorName->exportById($exportObj[$postProcessKey]);
                                    if (array_search($groupName, $data[$postProcessorName]) === false) {
                                        array_push($data[$postProcessorName], $groupName);
                                    }
                                    unset($exportObj[$postProcessKey]);
                                    break;
                                case 'entryTypes':
                                    if (isset($exportObj[$postProcessKey]) && is_array($exportObj[$postProcessKey])) {
                                        $data[$postProcessorName] = array_merge($data[$postProcessorName], $exportObj[$postProcessKey]);
                                        unset($exportObj[$postProcessKey]);
                                    }
                                    break;
                            }
                        }
                    }

                    array_push($data[$processorName], $exportObj);
                }
            }
        }

        foreach ($data as $key => $value) {
            if (count($value) <= 0) {
                unset($data[$key]);
            }
        }

        $this->renderTemplate('architect/export_results', [ 'dump' => json_encode($data, JSON_PRETTY_PRINT) ]);
    }
}
