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
        if(!isset($this->_confs['connectTimeout'])){
            $this->_confs['connectTimeout'] = 0.05;
        }
        
        
        
        if(!class_exists('Redis')){
            throw new exception_cache(
            'class Redis not exists!' . (!auto::isOnline() ? var_export($this->_confs, true) : ''), exception_cache::type_driver_not_exist
            );
        }
    }

    public function connect() {
        $_debugMicrotime = microtime(true);
        if (!$this->_confs['host'] || !$this->_confs['port']) {
            throw new exception_cache(
                'redis connection host and port error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''),
                exception_cache::type_server_connection_error
            );
        }
        try{
            $this->_redis = new Redis();
            $con = $this->_redis->connect($this->_confs['host'], $this->_confs['port'], $this->_confs['connectTimeout']);
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_alias));
        }catch(Exception $e){
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_alias,'line'=>__LINE__));

            //just ignore, and take to the below flow
        }
        if(!$con){
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__.'::error', $timeCost, array('alias'=>$this->_alias,'line'=>__LINE__));

            //设置重入次数上限,防止程序陷入死循环重入崩溃
            $seqid = md5($this->_alias.__METHOD__);
                if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid]>=self::$_reentrantTimesLimit){
                    throw new exception_cache(
                    'redis connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''),
                    exception_cache::type_server_connection_error
                );
            }
            if(!isset(self::$_reentrantTimes[$seqid])){
                self::$_reentrantTimes[$seqid] =0;
            }
            self::$_reentrantTimes[$seqid] += 1;
            
            $ptx = new plugin_context(__METHOD__, array('conf'=>$this->_confs,'alias'=>$this->_alias,'obj'=>&$this));
            plugin::call(__METHOD__.'::error',$ptx);
            if($ptx->breakOut!==null){
                return $ptx->breakOut;
            }
        }

        return $this;
    }


    public function __call($funcName, $arguments) {
        $method = __CLASS__.'::'.$funcName;
        $_debugMicrotime = microtime(true);
        if (!$this->_redis) {
            throw new exception_cache('connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        try{
            $ret = call_user_func_array(array($this->_redis, $funcName), $arguments);
        }catch (RedisException  $e){
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add($method.'::error', $timeCost, array('alias'=>$this->_alias,'line'=>__LINE__));

            //设置重入次数上限,防止程序陷入死循环重入崩溃
            $seqid = md5($this->_alias.serialize($arguments).$funcName);
            if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid]>=self::$_reentrantTimesLimit){
                throw $e;
            }
            if(!isset(self::$_reentrantTimes[$seqid])){
                self::$_reentrantTimes[$seqid] =0;
                
            }
            self::$_reentrantTimes[$seqid] += 1;
            
            $ptx = new plugin_context($method, array('conf'=>$this->_confs,'alias'=>$this->_alias,
                                                'exception'=>&$e,'obj'=>&$this, 'func'=>$funcName,'args'=>$arguments));
            plugin::call(__METHOD__.'::error',$ptx);
            if($ptx->breakOut!==null){
                return $ptx->breakOut;
            }
            throw $e;
        }
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add($method, $timeCost, array('alias'=>$this->_alias,'args'=>$arguments,'ret'=>performance::summarize($ret,$method) ) );
        
        return $ret;
    }

}