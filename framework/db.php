<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03
 * @desc db wrappers
 *
 */

class db {
//    const SERVER_TYPE_SLAVE = 'slave';
//    const SERVER_TYPE_MASTER = 'master';
//    const SERVER_TYPE_AUTO = 'auto';

    protected static $_conf = array();
    protected static $_instance = array();

    public static function addServer($alias, $conf) {
        self::$_conf[$alias] = $conf;
    }

    public static function instance($alias, $type = null) {
        if (!isset(self::$_instance[$alias])) {
            self::$_instance[$alias] = self::_getInstance($alias);
        }
        return self::$_instance[$alias]->connect($type);
    }

    protected static function _getInstance($alias) {
        if (empty($alias) || empty(self::$_conf[$alias])) {
            throw new exception_db('database conf: ' . $alias . ' not exist!', exception_db::TYPE_SERVER_NOT_EXIST);
        }
        $driverType = self::$_conf[$alias]['type'];
        $driverClass = 'db_' . $driverType;
        if (!class_exists($driverClass)) {
            throw new exception_db('database driver: ' . $driverClass . ' not exist!', exception_db::TYPE_DRIVER_NOT_EXIST);
        }
        $instance = new $driverClass($alias, self::$_conf[$alias]);
        if (!($instance instanceof db_abstract)) {
            throw new exception_db('database driver: ' . $driverClass . ' must extends of db_abstract!', exception_db::TYPE_DRIVER_NOT_EXIST);
        }
        return $instance;
    }


}