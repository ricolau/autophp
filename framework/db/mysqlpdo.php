<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-05-26
 * @desc database mysql with php pdo extension
 * @example
 *         db::instance($dbAlias)->connect(db_mysqlpdo::TYPE_SLAVE);
 */
class db_mysqlpdo extends db_abstract {
    const type_server_slave = 'slave';
    const type_server_master = 'master';

     
    protected static $_reentrantTimes = array();
    protected static $_reentrantTimesLimit = 3;
    

    
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
    public function connect($type = null) {
        if($type===null){
            throw new exception_mysqlpdo('no type specified for mysqlpdo connection!', exception_mysqlpdo::type_conf_error);
        }
        $_debugMicrotime = microtime(true);
        //$type = self::TYPE_SERVER_MASTER;
        //every time run "new db_mysqlpdo($conf)->connect($type)" would reconnect the mysql database!
        $this->_pdoCon = $this->_getPdo($type);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, array('alias'=>$this->_alias)) && auto::isDebug() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));
        return $this->_pdoCon;

    }

    protected function _getPdo($type) {
        if (!isset($this->_confs['conf'][$type])) {
            throw new exception_mysqlpdo('undefined pdo conf type:' . $type, exception_mysqlpdo::type_conf_error);
        }

        $server = $this->_confs['conf'][$type];
        $dsn = 'mysql:dbname=' . $server['dbname'] . ';host=' . $server['host'] . ';port=' . $server['port'];

        $options = (isset($server['options']) && is_array($server['options']))? $server['options'] :array();
        
        try {
            $instance = new PDO($dsn, $server['user'], $server['pwd'], $options);
        } catch (PDOException $e) {
            try {
                $instance = new PDO($dsn, $server['user'], $server['pwd'], $options);
            } catch (PDOException $e) {
                
                //设置重入次数上限,防止程序陷入死循环重入崩溃
                $seqid = md5($this->_alias.$type);
                if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid]>=self::$_reentrantTimesLimit){
                    throw $e;
                }
                if(!isset(self::$_reentrantTimes[$seqid])){
                    self::$_reentrantTimes[$seqid] =0;
                }
                self::$_reentrantTimes[$seqid] += 1;
                
                
                $ptx = new plugin_context(__METHOD__, array('conf'=>$this->_confs,'alias'=>$this->_alias,'type'=>$type,'exception'=>&$e,'obj'=>&$this));
                plugin::run('error::'.__METHOD__,$ptx);
                if($ptx->breakOut){
                    return $ptx->breakOut;
                }
                throw $e;
            }
        }
        if(!isset($options[PDO::ATTR_EMULATE_PREPARES])){
            //tell the mysql pdo do not stringfy field values!~!
            $instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        }
               
        if (isset($server['charset']) && $server['charset']) {
            $instance->query('SET NAMES ' . $server['charset']);
        }
        return $instance;
    }

}

