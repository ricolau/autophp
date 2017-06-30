<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-06-30
 * @desc autophp plugin tool
 *
 */
final class plugin {

    private static $_plugins = array();
    
//    private static $_hasInit = false;

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
                call_user_func_array(array($plugin,'call'), array($tag, &$ptx));
                
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('tag'=>$tag,'plugin'=>$pluginName, 'context'=>$ptx));
            }
        }
    }


    public static function getPluginsByTag($tag){
        if($tag){
            return self::$_plugins[$tag];
        }
    }
    public static function getAllPlugins() {
        return self::$_plugins;
    }

}