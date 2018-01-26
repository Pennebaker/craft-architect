<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect;

use pennebaker\architect\services\ArchitectService;
use pennebaker\architect\base\Processors;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

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
 * @property  ArchitectService $architectService
 */
class Architect extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Architect::$plugin
     *
     * @var Architect
     */
    public static $plugin;

    public static $processors;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Architect::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$processors = new Processors();

        // Register our site routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['siteActionTrigger1'] = 'architect/default';
        //     }
        // );

        // Register our CP routes
         Event::on(
             UrlManager::class,
             UrlManager::EVENT_REGISTER_CP_URL_RULES,
             function (RegisterUrlRulesEvent $event) {
                 $event->rules['architect/'] = 'architect/cp';
                 $event->rules['architect/import'] = 'architect/cp/import';
                 $event->rules['architect/export'] = 'architect/cp/export';
                 $event->rules['architect/migrations'] = 'architect/cp/migrations';
             }
         );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */

        Craft::info(
            Architect::t(
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    public static function t($message, array $params = [])
    {
        return Craft::t('architect', $message, $params);
    }

    public static function trace($message)
    {
        Craft::trace(self::t($message), __METHOD__);
    }

    public static function info($message)
    {
        Craft::info(self::t($message), __METHOD__);
    }

    public static function warning($message)
    {
        Craft::warning(self::t($message), __METHOD__);
    }

    public static function error($message)
    {
        Craft::error(self::t($message), __METHOD__);
    }

    // Protected Methods
    // =========================================================================

}
