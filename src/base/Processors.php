<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\base;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
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
    public $entryTypes;
    public $volumes;
    public $transforms;
    public $tagGroups;
    public $categoryGroups;
    public $fields;
    public $fieldGroups;
    public $globalSets;

    public function __construct()
    {
        $this->siteGroups = new SiteGroupProcessor();
        $this->sites = new SiteProcessor();
        $this->sections = new SectionProcessor();
        $this->entryTypes = new EntryTypeProcessor();
        $this->volumes = new VolumeProcessor();
        $this->transforms = new TransformProcessor();
        $this->tagGroups = new TagGroupProcessor();
        $this->categoryGroups = new CategoryGroupProcessor();
        $this->fields = new FieldProcessor();
        $this->fieldGroups = new FieldGroupProcessor();
        $this->globalSets = new GlobalSetProcessor();
    }
}