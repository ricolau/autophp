<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc config
 *
 */
final class config {

    /**
     * @desc get config settings, map pattern supported
     * @param str $key as
     * @param type $default default return value instead of null
     * @return
     *
     * @example
     * config::get('default')  reads config/default.php
     * config::get('default._env') reads config/default.php  _env
     *
     */
    private static $_map = array();

    private static $_confPaths = array();

    public static function addPath($path){
        self::$_confPaths[] = $path;
    }

    /**
     * get config value by key name
     * @param str $key
     * @param * $default
     * @return type
     * @example
     *         config::get('default')
     *         config::get('default._env')
     */
    public static function get($key, $default = null) {
        if (!$key)
            return false;

        $tmp = explode('.', $key, 2);
        $alias = $tmp[0];
        if (!isset(self::$_map[$alias])) {
            self::$_map[$alias] = self::_getDataByFilename($alias);
        }
        if (isset($tmp[1])) {
            return (is_array(self::$_map[$alias]) && isset(self::$_map[$alias][$tmp[1]])) ? self::$_map[$alias][$tmp[1]] : $default;
        }
        return isset(self::$_map[$alias]) ? self::$_map[$alias] : $default;
    }
    
    public static function set($alias, $val = null){
        self::$_map[$alias] = $val;
    }

    private static function _getDataByFilename($alias) {
        $alias = util::parseFilename($alias);
        $fileName = $alias . '.php';

        $file = APP_PATH . DS . 'config' . DS . $fileName;
        $tmp = util::loadFile($file);
        if($tmp === null && self::$_confPaths){
            foreach(self::$_confPaths as $path){
                $file = $path. DS. $fileName;
                $tmp = util::loadFile($file);
                if($tmp!==null){
                    break;
                }
            }
        }
        return $tmp;
    }

}