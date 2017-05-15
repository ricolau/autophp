<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-05-15
 * @desc database mysql with php pdo extension
 * @example
 *         db::instance($dbAlias)->connect(db::server_type_slave);
 */
class db_mysqlpdo extends db_abstract {


    protected static $_reentrantTimes = array();
    protected static $_reentrantTimesLimit = 3;
    protected $_confs = null;
    protected $_pdoCon = null;
    protected $_alias = null;

    public function __construct($alias, $conf) {
        if(empty($conf)) {
            throw new exception_mysqlpdo('bad conf data!', exception_mysqlpdo::type_conf_error);
        }
        $this->_confs = $conf;
        $this->_alias = $alias;
    }

    public function connect($type = null) {
        if($this->_confs['balance'] == db::balance_master_slave) {
            if($type === null) {
                throw new exception_mysqlpdo('no type specified for mysqlpdo connection with type db::balance_master_slave', exception_mysqlpdo::type_conf_error);
            }
        }

        $_debugMicrotime = microtime(true);
        //every time run "new db_mysqlpdo($conf)->connect($type)" would reconnect the mysql database!
        $this->_pdoCon = $this->_connect($type);

        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias' => $this->_alias,'balance'=>$this->_confs['balance'],'connectType'=>$type));
        return $this->_pdoCon;
    }

    protected function _connect($type) {
        if($this->_confs['balance'] == db::balance_random) {
            $id = array_rand($this->_confs['servers']);
            $server = $this->_confs['servers'][$id];
        }elseif($this->_confs['balance'] == db::balance_single){ 
            $server = reset($this->_confs['servers']);
        }else {
            if(!isset($this->_confs['servers'][$type])) {
                throw new exception_mysqlpdo('undefined pdo conf type:' . $type, exception_mysqlpdo::type_conf_error);
            }
            $server = $this->_confs['servers'][$type];
        }
        if(!$server['host'] || !$server['dbname']){
            throw new exception_mysqlpdo('db host/dbname empty for: '.$this->_alias, exception_mysqlpdo::type_conf_error);
        }

        $dsn = 'mysql:dbname=' . $server['dbname'] . ';host=' . $server['host'] . ';port=' . $server['port'];

        $options = (isset($server['options']) && is_array($server['options'])) ? $server['options'] : array();

        try {
            $instance = new PDO($dsn, $server['user'], $server['pwd'], $options);
        } catch(PDOException $e) {
            try {
                $instance = new PDO($dsn, $server['user'], $server['pwd'], $options);
            } catch(PDOException $e) {

                //设置重入次数上限,防止程序陷入死循环重入崩溃
                $seqid = md5($this->_alias . $type);
                if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid] >= self::$_reentrantTimesLimit) {
                    throw $e;
                }
                if(!isset(self::$_reentrantTimes[$seqid])) {
                    self::$_reentrantTimes[$seqid] = 0;
                }
                self::$_reentrantTimes[$seqid] += 1;


                $ptx = new plugin_context(__METHOD__, array('conf' => $this->_confs, 'alias' => $this->_alias, 'type' => $type, 'exception' => &$e, 'obj' => &$this));
                plugin::call(__METHOD__ . '::error', $ptx);
                if($ptx->breakOut !== null) {
                    return $ptx->breakOut;
                }
                throw $e;
            }
        }
        if(!isset($options[PDO::ATTR_EMULATE_PREPARES])) {
            //tell the mysql pdo do not stringfy field values!~!
            $instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        }

        if(isset($server['charset']) && $server['charset']) {
            $instance->query('SET NAMES ' . $server['charset']);
        }
        return $instance;
    }

}
