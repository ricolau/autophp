<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-07-20
 * @desc autophp plugin tool
 *
 */
final class plugin {

    private static $_plugins = array();
    //private static $_pluginsHasRun = array();

    /**
     * add a plugin for run
     * @param object $plugin
     * @param str $tag
     */
    public static function add($tag, plugin_abstract $plugin) {
        $pluginName = get_class($plugin);
        self::$_plugins[$tag][$pluginName] = &$plugin;
    }

    /**
     * run plugins of this type
     * @param type $tag
     */
    public static function run($tag, plugin_context &$ptx) {
        if (isset(self::$_plugins[$tag]) && is_array(self::$_plugins[$tag])) {
            foreach (self::$_plugins[$tag] as $pluginName=>&$plugin) {
                
                $_debugMicrotime = microtime(true);
                auto::isDebug() && auto::debugMsg(__METHOD__ . ' ('.  $pluginName.') ', 'start ---->>>>');
                call_user_func_array(array($plugin,'run'), array($tag, $ptx));
                
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('plugin'=>$pluginName)) && auto::isDebug() && auto::debugMsg(__METHOD__ . " ('$pluginName') ", 'end,<<<<---- cost ' . $timeCost . 's');
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