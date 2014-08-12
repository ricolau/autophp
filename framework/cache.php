<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03
 * @desc cache
 *
 */

class cache {

    protected static $_conf = array();
    protected static $_instance = array();

    public static function addServer($alias, $conf) {
        self::$_conf[$alias] = $conf;
    }

    public static function instance($alias) {
        if (self::$_instance[$alias] === null) {
            self::$_instance[$alias] = self::_getInstance($alias);
        }
        return self::$_instance[$alias];
    }

    protected static function _getInstance($alias) {
        if (empty($alias) || empty(self::$_conf[$alias])) {
            throw new exception_cache('cache conf: ' . $alias . ' not exist!', exception_cache::TYPE_SERVER_NOT_EXIST);
        }
        $driverType = self::$_conf[$alias]['type'];
        $driverClass = 'cache_' . $driverType;
        if (!class_exists($driverClass)) {
            throw new exception_cache('cache driver: ' . $driverClass . ' not exist!', exception_cache::TYPE_DRIVER_NOT_EXIST);
        }
        $cacheServer = new $driverClass($alias, self::$_conf[$alias]);
        return $cacheServer->connect();
    }

}