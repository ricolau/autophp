<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-08
 * @desc autophp plugin tool
 *
 */
final class plugin {

    private static $_plugins = array();
    //private static $_pluginsHasRun = array();

    /**
     * register a plugin to a specific tag
     * @param string $tag
     * @param plugin_abstract $plugin
     */
    public static function register($tag, plugin_abstract $plugin) {
        $pluginName = get_class($plugin);
        self::$_plugins[$tag][$pluginName] = &$plugin;
    }

    /**
     * run plugins for this tag
     * @param string $tag
     * @param plugin_context $ptx
     */
    public static function call($tag, plugin_context &$ptx) {
        if (isset(self::$_plugins[$tag]) && is_array(self::$_plugins[$tag])) {
            foreach (self::$_plugins[$tag] as $pluginName=>&$plugin) {
                
                $_debugMicrotime = microtime(true);
                call_user_func_array(array($plugin,'call'), array($tag, $ptx));
                
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('plugin'=>$pluginName));
                //self::$_pluginsHasRun[] = $plugin;
            }
        }
    }

    /**
     * run a plugin
     * @param type $plugin
     */
//    private static function _execPlugin($className, &$ptx) {
//        if (!class_exists($className)){
//            throw new exception_base('class not exist:' . $className, -1);
//        }
//
//        $class = new ReflectionClass($className);
//        if ($class->isAbstract()) {
//            throw new exception_base('can not run abstract class: ' . $className, -1);
//        }
//        if (!$class->isSubclassOf('plugin_abstract')) {
//            throw new exception_base('plugin '.$className .'must extends of plugin_abstract', -1);
//        }
//        $method = $class->getMethod('main');
//        if (!$method || !$method->isPublic()) {
//            throw new exception_base('no public method main exist in:' . $className, -1);
//        }
//        $method->invoke($class->newInstance(), $ptx);
//    }


    public static function getPluginsByTag($tag){
        if($tag){
            return self::$_plugins[$tag];
        }
    }
    public static function getAllPlugins() {
        return self::$_plugins;
    }

}