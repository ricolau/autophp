<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03
 * @desc autophp plugin tool
 *
 */
final class plugin {

    private static $_plugins = array();
    private static $_pluginsHasRun = array();
    private static $_allPlugins = array();

    const type_before_run = 'before_run';
    const type_after_run = 'after_run';

    /**
     * add a plugin for run
     * @param str $pluginName
     * @param str $type
     */
    public static function add($pluginName, $type = 'before_run') {
        self::$_plugins[$type][] = $pluginName;
        self::$_allPlugins[$type][] = $pluginName;
    }

    /**
     * run plugins of this type
     * @param type $type
     */
    public static function run($type) {
        if (isset(self::$_plugins[$type]) && is_array(self::$_plugins[$type])) {
            while (self::$_plugins[$type]) {
                $plugin = array_shift(self::$_plugins[$type]);
                auto::isDebugMode() && $_debugMicrotime = microtime(true);
                auto::isDebugMode() && auto::dqueue(__METHOD__ . " ('$plugin') ", 'start ---->>>>');
                self::_execPlugin($plugin);
                auto::isDebugMode() && auto::dqueue(__METHOD__ . " ('$plugin') ", 'end,<<<<---- cost ' . (microtime(true) - $_debugMicrotime) . 's');
                self::$_pluginsHasRun[] = $plugin;
            }
        }
    }

    /**
     * run a plugin
     * @param type $plugin
     */
    private static function _execPlugin($className) {
        if (!class_exists($className)){
            throw new exception_base('class not exist:' . $className, -1);
        }

        $class = new ReflectionClass($className);
        if ($class->isAbstract()) {
            throw new exception_base('can not run abstract class: ' . $className, -1);
        }
        if (!$class->isSubclassOf('plugin_abstract')) {
            throw new exception_base('plugin '.$className .'must extends of plugin_abstract', -1);
        }
        $method = $class->getMethod('main');
        if (!$method || !$method->isPublic()) {
            throw new exception_base('no public method main exist in:' . $className, -1);
        }
        $method->invoke($class->newInstance());
    }


    public static function getAllPlugins() {
        return self::$_allPlugins;
    }

    public static function getHasRunPlugin($type) {
        if (!$type) {
            return;
        }
        return self::$_pluginsHasRun[$type];

    }

    public static function getHasNotRunPlugin($type) {
        if (!$type) {
            return;
        }
        return self::$_plugins[$type];

    }

}