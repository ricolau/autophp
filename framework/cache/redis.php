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
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        $this->_redis = new Redis();
        if (!$this->_confs['host'] || !$this->_confs['port']) {
            throw new exception_cache(
                'redis connection host and port error!' . (auto::isDebugMode() ? var_export($this->_confs, true) : ''),
                exception_cache::type_server_connection_error
            );
        }
        $this->_redis->connect($this->_confs['host'], $this->_confs['port']);
        auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost ' . (microtime(true) - $_debugMicrotime) . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));

        return $this;
    }


    public function __call($funcName, $arguments) {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        if (!$this->_redis) {
            throw new exception_cache('connection error!' . (auto::isDebugMode() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        $ret = call_user_func_array(array($this->_redis, $funcName), $arguments);
        auto::isDebugMode() && auto::dqueue(__CLASS__ . '::' . $funcName, 'cost ' . (microtime(true) - $_debugMicrotime) . 's, arguments: ' . var_export($arguments, true));
        return $ret;
    }

}