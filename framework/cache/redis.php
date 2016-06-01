<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-05-26
 * @desc redis
 *
 */
class cache_redis extends cache_abstract {
    
    protected static $_reentrantTimes = array();
    protected static $_reentrantTimesLimit = 5;
    

    protected $_redis = null;

    public function __construct($alias, $conf) {
        $this->_alias = $alias;
        $this->_confs = $conf;
    }

    public function connect() {
        $_debugMicrotime = microtime(true);
        $this->_redis = new Redis();
        if (!$this->_confs['host'] || !$this->_confs['port']) {
            throw new exception_cache(
                'redis connection host and port error!' . (auto::isDebugMode() ? var_export($this->_confs, true) : ''),
                exception_cache::type_server_connection_error
            );
        }
        $con = $this->_redis->connect($this->_confs['host'], $this->_confs['port']);
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, array('alias'=>$this->_alias)) && auto::isDebugMode() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));

        if(!$con){
            $ptx = new plugin_context(__METHOD__, array('conf'=>$this->_confs,'alias'=>$this->_alias));
            plugin::run('error::'.__METHOD__,$ptx);
            if($ptx->breakOut){
                return $ptx->breakOut;
            }
        }

        return $this;
    }


    public function __call($funcName, $arguments) {
        $_debugMicrotime = microtime(true);
        if (!$this->_redis) {
            throw new exception_cache('connection error!' . (auto::isDebugMode() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        try{
            $ret = call_user_func_array(array($this->_redis, $funcName), $arguments);
        }catch (RedisException  $e){
            //设置重入次数上限,防止程序陷入死循环重入崩溃
            $seqid = md5(serialize($arguments).$funcName);
            if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid]>=self::$_reentrantTimesLimit){
                throw $e;
            }
            if(!isset(self::$_reentrantTimes[$seqid])){
                self::$_reentrantTimes[$seqid] =0;
                
            }
            self::$_reentrantTimes[$seqid] += 1;
            
            $ptx = new plugin_context(__METHOD__, array('conf'=>$this->_confs,'alias'=>$this->_alias,
                                                'exception'=>&$e,'obj'=>&$this, 'func'=>$funcName,'args'=>$arguments));
            plugin::run('error::'.__CLASS__.'::'.$funcName,$ptx);
            if($ptx->breakOut){
                return $ptx->breakOut;
            }
            throw $e;
        }
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, array('alias'=>$this->_alias)) && auto::isDebugMode() && auto::debugMsg(__CLASS__ . '::' . $funcName, 'cost ' . $timeCost . 's, arguments: ' . var_export($arguments, true));
        
        return $ret;
    }

}