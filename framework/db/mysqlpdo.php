<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03-18
 * @desc database mysql with php pdo extension
 * @example
 *         db::instance($dbAlias)->connect(db_mysqlpdo::TYPE_SLAVE);
 */
class db_mysqlpdo extends db_abstract {
    const type_server_slave = 'slave';
    const type_server_master = 'master';

    protected $_confs = null;
    protected $_pdoCon = null;

    protected $_alias = null;

    public function __construct($alias, $conf) {
        if (empty($conf)) {
            throw new exception_mysqlpdo('bad conf data!', exception_mysqlpdo::type_conf_error);
        }
        $this->_confs = $conf;
        $this->_alias = $alias;
    }
/*
    protected function _connectSlave() {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $type = self::TYPE_SERVER_SLAVE;
        //every time run "new db_mysqlpdo($conf)->connect($type)" would reconnect the mysql database!
        $this->_pdoCon = $this->_getPdo($type);
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));
        return $this->_pdoCon;
    }

    protected function _connectMaster() {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $type = self::TYPE_SERVER_MASTER;
        //every time run "new db_mysqlpdo($conf)->connect($type)" would reconnect the mysql database!
        $this->_pdoCon = $this->_getPdo($type);
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));
        return $this->_pdoCon;
    }
*/
    public function connect($type = null) {
        if($type===null){
            throw new exception_mysqlpdo('no type specified for mysqlpdo connection!', exception_mysqlpdo::type_conf_error);
        }
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        //$type = self::TYPE_SERVER_MASTER;
        //every time run "new db_mysqlpdo($conf)->connect($type)" would reconnect the mysql database!
        $this->_pdoCon = $this->_getPdo($type);
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));
        return $this->_pdoCon;

    }

    protected function _getPdo($type) {
        if (!isset($this->_confs['conf'][$type])) {
            throw new exception_mysqlpdo('undefined pdo conf type:' . $type, exception_mysqlpdo::type_conf_error);
        }

        $server = $this->_confs['conf'][$type];
        $dsn = 'mysql:dbname=' . $server['dbname'] . ';host=' . $server['host'] . ';port=' . $server['port'];

        $con = self::_connect($dsn, $server['user'], $server['pwd']);
        if (isset($server['charset']) && $server['charset']) {
            $con->query('SET NAMES ' . $server['charset']);
        }
        return $con;
    }

    protected static function _connect($dsn, $user, $pwd, $dwp = array()) {
        try {
            $instance = new PDO($dsn, $user, $pwd, $dwp);
        } catch (PDOException $e) {
            try {
                $instance = new PDO($dsn, $user, $pwd, $dwp);
            } catch (PDOException $e) {
                throw $e;
            }
        }
        return $instance;
    }

}

