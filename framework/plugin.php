<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-22
 * @desc autophp plugin tool
 *
 */
final class plugin {

    private static $_plugins = array();
    
    private static $_hasInit = false;
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
    
    
    public static function init($plugins){
        if(self::$_hasInit){
            throw new exception_plugin('plugins has init!', exception_plugin::type_plugin_has_init);
        }
        if(!is_array($plugins)|| empty($plugins)){
            throw new exception_plugin('plugins empty!', exception_plugin::type_plugin_empty);
        }
        foreach($plugins as $tag =>&$plugin){
            if(!($plugin instanceof plugin_abstract)){
                throw new exception_plugin('bad plugin!', exception_plugin::type_bad_plugin);
            }
            $pluginName = get_class($plugin);
            self::$_plugins[$tag][$pluginName] = &$plugin;
        }
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


    public static function getPluginsByTag($tag){
        if($tag){
            return self::$_plugins[$tag];
        }
    }
    public static function getAllPlugins() {
        return self::$_plugins;
    }

}