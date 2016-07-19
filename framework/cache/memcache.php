<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-03
 * @desc memcache
 *
 */

class cache_memcache extends cache_abstract {
    protected $_memcache = null;
    protected $_alias = null;

    public function __construct($alias, $confs) {
        $this->_alias = $alias;
        $this->_confs = $confs;
    }

    public function connect() {

        $_debugMicrotime = microtime(true);
        
        $memcacheClass = 'Memcache';
        if(!auto::autoload($memcacheClass)){
            throw new exception_cache('class not exist for '.$memcacheClass.', check your php extensions~', exception_cache::type_memcache_not_exist);
        }
       
        $this->_memcache = new $memcacheClass();
        $servers = $this->_confs['servers'];
        foreach ($servers as $server) {
            $this->_memcache->addServer($server['host'], $server['port'], false, $server['weight']);
        }
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, array('alias'=>$this->_alias)) &&        auto::isDebug() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));

        //return $this->_memcache;
        return $this;
    }

    /**
     * just proxy for memcache::set()
     * may use as                                   memcache::set($key, $value,$isCompressed = MEMCACHE_COMPRESSED/false, $expire)
     *      also capable with 3 arguments as        memcache::set($key, $value,$expire)
     * @return data
     * @throws exception_cache
     */
    public function set() {
        $_debugMicrotime = microtime(true);
        $arguments = func_get_args();
        $argc = func_num_args();
        if ($argc < 3) {
            throw new exception_cache('argument number error for: ' . __METHOD__, exception_cache::type_argument_error);
        }
        if ($argc == 3) {
            $arguments = array($arguments[0], $arguments[1], MEMCACHE_COMPRESSED, $arguments[2]);
        }
        if (!$this->_memcache) {
            throw new exception_cache('connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        $ret = call_user_func_array(array($this->_memcache, 'set'), $arguments);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__METHOD__, $timeCost, array('alias'=>$this->_alias)) &&        auto::isDebug() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's, arguments: ' . var_export($arguments, true));
        
        return $ret;
    }

    public function __call($funcName, $arguments) {
        $_debugMicrotime = microtime(true);
        if (!$this->_memcache) {
            throw new exception_cache('connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        $ret = call_user_func_array(array($this->_memcache, $funcName), $arguments);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && auto::performance(__CLASS__ . '::' . $funcName, $timeCost, array('alias'=>$this->_alias)) &&  auto::isDebug() && auto::debugMsg(__CLASS__ . '::' . $funcName, 'cost ' . $timeCost . 's, arguments: ' . var_export($arguments, true));
        return $ret;
    }

}