<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-03
 * @desc redis
 *
 */
class cache_redis extends cache_abstract {

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
        $ret = call_user_func_array(array($this->_redis, $funcName), $arguments);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, array('alias'=>$this->_alias)) && auto::isDebugMode() && auto::debugMsg(__CLASS__ . '::' . $funcName, 'cost ' . $timeCost . 's, arguments: ' . var_export($arguments, true));
        
        return $ret;
    }

}