<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON/YAML data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect;

use pennebaker\architect\base\Processors;
use pennebaker\architect\services\ArchitectService;
use pennebaker\architect\variables\ArchitectVariable;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\FileHelper;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
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

    public static $configPath;

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
     * @throws \yii\base\Exception
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$processors = new Processors();
        self::$configPath = Craft::$app->getPath()->getConfigPath() . DIRECTORY_SEPARATOR . 'architect';

        // Ensure architect config path exists
        if (!file_exists(self::$configPath)) {
            FileHelper::createDirectory(self::$configPath);
        }

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'pennebaker\architect\console\controllers';
        }

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('architect', ArchitectVariable::class);
            }
        );

        // Register our CP routes
         Event::on(
             UrlManager::class,
             UrlManager::EVENT_REGISTER_CP_URL_RULES,
             function (RegisterUrlRulesEvent $event) {
                 $event->rules['architect/'] = 'architect/cp';
                 $event->rules['GET architect/import'] = 'architect/cp/import';
                 $event->rules['GET architect/export'] = 'architect/cp/export';
                 $event->rules['GET architect/blueprints'] = 'architect/cp/blueprints';
                 $event->rules['POST architect/import'] = 'architect/default/import';
                 $event->rules['POST architect/export'] = 'architect/default/export';
                 $event->rules['POST architect/blueprints'] = 'architect/default/blueprints';
             }
         );

        self::info('{name} plugin loaded', ['name' => $this->name]);
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    public static function t($message, array $params = []): string
    {
        return Craft::t('architect', $message, $params);
    }

    public static function debug($message, array $params = [])
    {
        Craft::debug(self::t($message, $params), __METHOD__);
    }

    public static function info($message, array $params = [])
    {
        Craft::info(self::t($message, $params), __METHOD__);
    }

    public static function warning($message, array $params = [])
    {
        Craft::warning(self::t($message, $params), __METHOD__);
    }

    public static function error($message, array $params = [])
    {
        Craft::error(self::t($message, $params), __METHOD__);
    }

    public static function getRouteByUid($uid)
    {
        $routes = Craft::$app->getProjectConfig()->get('routes');
        if (is_array($routes)) {
            foreach ($routes as $routeUid => $route) {
                if ($routeUid === $uid) {
                    $route['uid'] = $routeUid;
                    return $route;
                }
            }
        }
    }

    public static function createRouteUriPattern($uriParts)
    {
        // Compile the URI parts into a regex pattern
        $uriPattern = '';
        $uriParts = array_filter($uriParts);
        $subpatternNameCounts = [];

        foreach ($uriParts as $part) {
            if (is_string($part)) {
                $uriPattern .= $part;
            } else if (is_array($part)) {
                // Is the name a valid handle?
                if (preg_match('/^[a-zA-Z]\w*$/', $part[0])) {
                    $subpatternName = $part[0];
                } else {
                    $subpatternName = 'any';
                }

                // Make sure it's unique
                if (isset($subpatternNameCounts[$subpatternName])) {
                    $subpatternNameCounts[$subpatternName]++;

                    // Append the count to the end of the name
                    $subpatternName .= $subpatternNameCounts[$subpatternName];
                } else {
                    $subpatternNameCounts[$subpatternName] = 1;
                }

                // Add the var as a named subpattern
                $uriPattern .= "<{$subpatternName}:{$part[1]}>";
            }
        }
        return $uriPattern;
    }

    public static function createRouteUriDisplay($uriParts)
    {
        // Compile the URI parts into a regex pattern
        $uriPattern = '';
        $uriParts = array_filter($uriParts);
        $subpatternNameCounts = [];

        foreach ($uriParts as $part) {
            if (is_string($part)) {
                $uriPattern .= $part;
            } else if (is_array($part)) {
                // Is the name a valid handle?
                if (preg_match('/^[a-zA-Z]\w*$/', $part[0])) {
                    $subpatternName = $part[0];
                } else {
                    $subpatternName = 'any';
                }

                // Make sure it's unique
                if (isset($subpatternNameCounts[$subpatternName])) {
                    $subpatternNameCounts[$subpatternName]++;

                    // Append the count to the end of the name
                    $subpatternName .= $subpatternNameCounts[$subpatternName];
                } else {
                    $subpatternNameCounts[$subpatternName] = 1;
                }

                // Add the var as a named subpattern
                $uriPattern .= "<{$subpatternName}>";
            }
        }
        return $uriPattern;
    }

    public static function routeExists(array $uriParts, string $template, string $siteUid = null)
    {
        $routes = Craft::$app->getProjectConfig()->get('routes');
        if (is_array($routes)) {
            foreach ($routes as $routeUid => $route) {
                if ($route['siteUid'] === $siteUid && $route['template'] === $template && $route['uriPattern'] === self::createRouteUriPattern($uriParts)) {
                    return $routeUid;
                }
            }
        }
        return false;
    }

    // Protected Methods
    // =========================================================================
}
