<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\services;

use pennebaker\architect\Architect;
use craft\models\Section_SiteSettings;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Craft;
use craft\base\Component;

/**
 * ArchitectService Service
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class ArchitectService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This is the import function.
     *
     * From any other plugin file, call it like this:
     *
     *     Architect::$plugin->architectService->import()
     *
     * @param string $importData
     * @param bool $runBackup
     *
     * @return mixed
     *
     * @throws \Throwable
     * @throws \craft\errors\ShellCommandException
     * @throws \yii\base\Exception
     */
    public function import($importData, $runBackup = false, $update = false)
    {
        \Craft::Client;
        // Convert json into an array.
        $importObj = json_decode($importData, true);
        // Attempt yaml parsing if json_decode failed.
        if (json_last_error() !== JSON_ERROR_NONE) {
            try {
                $importObj = Yaml::parse($importData);
            } catch (ParseException $exception) {
                return [true, null, null, [json_last_error(), $exception->getMessage()]];
            }
        }

        if ($runBackup) {
            // Create a database backup in the event of catastrophic failure
            $backup = Craft::$app->getDb()->backup();
        } else {
            $backup = false;
        }

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
        // Has just strings
        $onlyStrings = [
            'siteGroups',
            'fieldGroups'
        ];
        // Successfully imported items needed for various post processing procedures.
        $successful = [
            'fields' => [],
            'sections' => [],
            'volumes' => [],
            'tagGroups' => [],
            'categoryGroups' => [],
            'userGroups' => [],
            'users' => [],
            'globalSets' => [],
        ];
        /**
         * Things to process field layouts for after importing of fields.
         * Things in this list are needed for fields to import properly but can also use fields in field layouts.
         */
        $postProcessFieldLayouts = [
            'fields',
            'volumes',
            'tagGroups',
            'categoryGroups',
            'globalSets',
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
            if (isset($importObj[$parseKey]) && \is_array($importObj[$parseKey])) {
                $results[$parseKey] = [];
                foreach ($importObj[$parseKey] as $itemKey => $itemObj) {
                    try {
                        if ($update) {
                            $itemErrors = Architect::$processors->$parseKey->update($itemObj);
                            $results[$parseKey][] = [
                                'item' => false,
                                'success' => false,
                                'errors' => $itemErrors,
                            ];
                            if ($itemErrors) {
                                continue;
                            }
                        }
                        if ($parseKey === 'fieldGroups' || $parseKey === 'siteGroups') {
                            list($item, $itemErrors) = Architect::$processors->$parseKey->parse(['name' => $itemObj]);
                        } else {
                            list($item, $itemErrors) = Architect::$processors->$parseKey->parse($itemObj);
                        }
                        if ($parseKey === 'entryTypes' && \in_array($itemObj['sectionHandle'], $successful['sections'], false) === false) {
                            if (!isset($itemObj['name'])) {
                                $itemObj['name'] = '';
                            }
                            if (!isset($itemObj['handle'])) {
                                $itemObj['handle'] = $itemObj['sectionHandle'];
                            }
                            $item = false;
                            $itemErrors = [
                                'parent' => [
                                    Architect::t('Section parent "{sectionHandle}" was not imported successfully.', [ 'sectionHandle' => $itemObj['sectionHandle'] ])
                                ]
                            ];
                        }

                        if ($item) {
                            $itemSuccess = Architect::$processors->$parseKey->save($item, $update);
                            if ($parseKey === 'sections') {
                                $itemErrors = [];
                                /** @var mixed $item */
                                foreach ($item->getSiteSettings() as $settings) {
                                    /** @var Section_SiteSettings $settings */
                                    foreach ($settings->getErrors() as $errorKey => $errors) {
                                        if (isset($itemErrors[$errorKey])) {
//                                            $itemErrors[$errorKey] = array_merge($itemErrors[$errorKey], $errors);
                                            array_push($itemErrors[$errorKey], ...$errors);
                                        } else {
                                            $itemErrors[$errorKey] = $errors;
                                        }
                                    }
                                }
                                foreach ($item->getErrors() as $errorKey => $errors) {
                                    if (isset($itemErrors[$errorKey])) {
//                                            $itemErrors[$errorKey] = array_merge($itemErrors[$errorKey], $errors);
                                        array_push($itemErrors[$errorKey], ...$errors);
                                    } else {
                                        $itemErrors[$errorKey] = $errors;
                                    }
                                }
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

                    if (!$itemSuccess) {
                        $noErrors = false;
                    }

                    if ($parseKey === 'fieldGroups' || $parseKey === 'siteGroups') {
                        $item = $item ?: ['name' => $itemObj];
                    } else {
                        $item = $item ?: $itemObj;
                    }
                    if ($itemSuccess) {
                        if (\in_array($parseKey, $onlyStrings)) {
                            $importObj[$parseKey][$itemKey] = [
                                'name' => $itemObj,
                                'id' => $item->id
                            ];
                        } else {
                            $importObj[$parseKey][$itemKey]['id'] = $item->id;
                        }
                        if ($parseKey === 'entryTypes') {
                            $addedEntryTypes[] =  Craft::$app->sections->getSectionById((int) $item->sectionId)->handle . ':' . $item->handle;
                        }
                        switch ($parseKey) {
                            case 'fields':
                                $successful[$parseKey][] = $itemKey;
                                break;
                            case 'sections':
                                $successful[$parseKey][] = $item->handle;
                                break;
                            case 'volumes':
                            case 'tagGroups':
                            case 'categoryGroups':
                            case 'userGroups':
                            case 'users':
                            case 'globalSets':
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
            if (isset($importObj[$parseKey]) && \is_array($importObj[$parseKey])) {
                foreach($successful[$parseKey] as $volumeHandle => $itemKey) {
                    $itemObj = $importObj[$parseKey][$itemKey];
                    Architect::$processors->$parseKey->setFieldLayout($itemObj);
                }
            }
        }

        /**
         * Post Processing on Users to assign User Groups
         */
        if (isset($importObj['users']) && \is_array($importObj['users'])) {
            foreach ($successful['users'] as $itemKey) {
                $itemObj = $importObj['users'][$itemKey];
                if (isset($itemObj['groups']) && \is_array($itemObj['groups'])) {
                    $groupIds = [];
                    foreach ($itemObj['groups'] as $groupHandle) {
                        $group = Craft::$app->userGroups->getGroupByHandle($groupHandle);
                        if ($group) {
                            $groupIds[] = $group->id;
                        }
                    }
                    Craft::$app->users->assignUserToGroups($itemObj['id'], $groupIds);
                }
            }
        }

        /**
         * Post Processing to set permissions
         */
        foreach ($postProcessPermissions as $parseKey) {
            if (isset($successful[$parseKey]) && \is_array($successful[$parseKey])) {
                foreach($successful[$parseKey] as $itemKey) {
                    $itemObj = $importObj[$parseKey][$itemKey];
                    Architect::$processors->$parseKey->setPermissions($parseKey, $itemObj);
                }
            }
        }

        /**
         * Post Processing on Section Entry Types
         * This is to loop over all entry types in a section and remove entry types that do not match one that was meant to be created.
         * ex. A section was created for Employees but there is only entry types defined for Board Members & Management
         */
        if (isset($importObj['sections'], $importObj['entryTypes']) && \is_array($importObj['sections']) && \is_array($importObj['entryTypes'])) {
            forEach ($successful['sections'] as $sectionHandle) {
                $section = Craft::$app->sections->getSectionByHandle($sectionHandle);
                $entryTypes = $section->getEntryTypes();
                foreach ($entryTypes as $entryType) {
                    if (\in_array($section->handle . ':' . $entryType->handle, $addedEntryTypes) === false) {
                        Craft::$app->sections->deleteEntryType($entryType);
                    }
                }
            }
        }

        if ($runBackup) {
            if ($noErrors) {
                unlink($backup);
            } else {
                Architect::warning('Architect encountered errors performing an import, there is a database backup located at: {backup}', [ 'backup' => $backup ]);
            }
        }

        return [false, $noErrors, $backup, $results];
    }
}
