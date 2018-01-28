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
    public $siteGroup;
    public $site;
    public $section;
    public $entryType;
    public $volume;
    public $transform;
    public $field;
    public $fieldGroup;

    public function __construct()
    {
        $this->siteGroup = new SiteGroupProcessor();
        $this->site = new SiteProcessor();
        $this->section = new SectionProcessor();
        $this->entryType = new EntryTypeProcessor();
        $this->volume = new VolumeProcessor();
        $this->transform = new TransformProcessor();
        $this->field = new FieldProcessor();
        $this->fieldGroup = new FieldGroupProcessor();
    }
}