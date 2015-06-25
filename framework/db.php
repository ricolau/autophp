<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03
 * @desc db wrappers
 *
 */

class db {
//    const server_type_slave = 'slave';
//    const server_type_master = 'master';
//    const server_type_auto = 'auto';

    protected static $_conf = array();
    protected static $_instance = array();

    public static function addServer($alias, $conf) {
        self::$_conf[$alias] = $conf;
    }

    public static function instance($alias, $type = null) {
        if (!isset(self::$_instance[$alias][$type])) {
            self::$_instance[$alias][$type] = self::_getInstance($alias)->connect($type);
        }
        return self::$_instance[$alias][$type];
    }

    protected static function _getInstance($alias) {
        if (empty($alias) || empty(self::$_conf[$alias])) {
            throw new exception_db('database conf: ' . $alias . ' not exist!', exception_db::type_server_not_exist);
        }
        $driverType = self::$_conf[$alias]['type'];
        $driverClass = 'db_' . $driverType;
        if (!class_exists($driverClass)) {
            throw new exception_db('database driver: ' . $driverClass . ' not exist!', exception_db::type_driver_not_exist);
        }
        $instance = new $driverClass($alias, self::$_conf[$alias]);
        if (!($instance instanceof db_abstract)) {
            throw new exception_db('database driver: ' . $driverClass . ' must extends of db_abstract!', exception_db::type_driver_not_exist);
        }
        return $instance;
    }


}