<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\base;

/**
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 *
 * @property  Processors $processors
 */
class Processors
{

    public $siteGroups;
    public $sites;
    public $sections;
    public $routes;
    public $entryTypes;
    public $volumes;
    public $transforms;
    public $tagGroups;
    public $categoryGroups;
    public $fields;
    public $fieldGroups;
    public $globalSets;
    public $userGroups;
    public $users;

    public function __construct()
    {
        $this->siteGroups = new SiteGroupProcessor();
        $this->sites = new SiteProcessor();
        $this->sections = new SectionProcessor();
        $this->routes = new RouteProcessor();
        $this->entryTypes = new EntryTypeProcessor();
        $this->volumes = new VolumeProcessor();
        $this->transforms = new TransformProcessor();
        $this->tagGroups = new TagGroupProcessor();
        $this->categoryGroups = new CategoryGroupProcessor();
        $this->fields = new FieldProcessor();
        $this->fieldGroups = new FieldGroupProcessor();
        $this->globalSets = new GlobalSetProcessor();
        $this->userGroups = new UserGroupProcessor();
        $this->users = new UserProcessor();
    }
}