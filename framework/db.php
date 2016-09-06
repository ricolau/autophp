<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-09-05
 * @desc db wrappers
 *
 */
class db {

    protected static $_conf = array();
    protected static $_instance = array();

    const server_type_slave = 'slave';
    const server_type_master = 'master';
    
    const balance_single = 'single';
    const balance_random = 'random';
    const balance_master_slave = 'master_slave';

    public static function addServer($alias, $conf) {
        if(!isset($conf['balance']) || !in_array($conf['balance'], array(db::balance_master_slave, db::balance_random, db::balance_single))) {
            throw new exception_db('database conf balance type error for:' . $alias . ','.$conf['balance'], exception_db::type_conf_error);
        }
        self::$_conf[$alias] = $conf;
    }

    /**
     * 
     * @param string $alias
     * @param string/null $type
     * @param bool $newConnection
     * @return type
     */
    public static function instance($alias, $type = null, $newConnection = false) {
        if(empty($alias) || empty(self::$_conf[$alias])) {
            throw new exception_db('database conf: ' . $alias . ' not exist!', exception_db::type_server_not_exist);
        }
        
        if(self::$_conf[$alias]['balance'] == db::balance_master_slave) {
            $instanceKey = self::$_conf[$alias]['balance'] . '::' . $type;
        } else {
            $instanceKey = self::$_conf[$alias]['balance'];
        }
        if(!isset(self::$_instance[$alias][$instanceKey]) || $newConnection) {
            self::$_instance[$alias][$instanceKey] = self::_getInstance($alias)->connect($type);
        }
        return self::$_instance[$alias][$instanceKey];
    }

    protected static function _getInstance($alias) {
        $driverType = self::$_conf[$alias]['type'];
        $driverClass = 'db_' . $driverType;
        if(!class_exists($driverClass)) {
            throw new exception_db('database driver: ' . $driverClass . ' not exist!', exception_db::type_driver_not_exist);
        }
        $instance = new $driverClass($alias, self::$_conf[$alias]);
        if(!($instance instanceof db_abstract)) {
            throw new exception_db('database driver: ' . $driverClass . ' must extends of db_abstract!', exception_db::type_driver_not_exist);
        }
        return $instance;
    }

}
