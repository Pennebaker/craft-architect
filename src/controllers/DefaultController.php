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

use craft\models\Section_SiteSettings;
use pennebaker\architect\Architect;

use Craft;
use craft\web\Controller;

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
            'fieldGroups',
            'fields',
            'entryTypes',
            'globalSets',
        ];
        // Successfully imported items needed for various post processing procedures.
        $successful = [
            'sections' => [],
            'volumes' => [],
            'tagGroups' => [],
            'categoryGroups' => [],
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
                        if ($parseKey === 'entryTypes') {
                            $addedEntryTypes[] =  Craft::$app->sections->getSectionById($item->sectionId)->handle . ':' . $item->handle;
                        }
                        switch ($parseKey) {
                            case 'sections':
                                $successful['sections'][] = $item->handle;
                                break;
                            case 'volumes':
                                $successful['volumes'][] = $itemKey;
                                break;
                            case 'tagGroups':
                                $successful['tagGroups'][] = $itemKey;
                                break;
                            case 'categoryGroups':
                                $successful['categoryGroups'][] = $itemKey;
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
                    $item = $jsonObj[$parseKey][$itemKey];
                    Architect::$processors->$parseKey->setFieldLayout($item);
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
            Architect::warning(Architect::t('Architect encountered errors performing an import, there is a database backup located at: {backup}', [ 'backup' => $backup ]));
        }

        $this->renderTemplate('architect/import_results', [
            'noErrors' => $noErrors,
            'backupLocation' => $backup,
            'results' => $results,
            'jsonData' => $jsonData,
        ]);
    }
}
