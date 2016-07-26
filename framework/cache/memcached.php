<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2015-7
 * @desc memcached
 *
 */

class cache_memcached extends cache_abstract {
    protected $_memcached = null;
    protected $_alias = null;

    public function __construct($alias, $confs) {
        $this->_alias = $alias;
        $this->_confs = $confs;
    }

    public function connect() {
        
//    $m = new Memcache();
//    $m->addServer('localhost', 11211);
//    $v = $m->get('counter');
//    $m->set('counter', $v + 1);
//
//    $md = new Memcached();
//    $md->addServer('localhost', 11211);
//    $v = $md->get('counter', null, $token);
//    $v = $md->set('counter', null,1, $token);
        
        
        $_debugMicrotime = microtime(true);
        
        $memcachedClass = 'Memcached';
        if(!auto::autoload($memcachedClass)){
            throw new exception_cache('class not exist for '.$memcachedClass.', check your php extensions~', exception_cache::type_memcache_not_exist);
        }
       
        $this->_memcached = new $memcachedClass();
        $servers = $this->_confs['servers'];
        foreach ($servers as $server) {
            $this->_memcached->addServer($server['host'], $server['port'], $server['weight']);
        }
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_alias)) && auto::isDebug() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's, alias: ' . $this->_alias . ',conf ' . var_export($this->_confs, true));

        //return $this->_memcached;
        return $this;
    }

    /**
     * just proxy for memcached::set()
     * may use as  memcached::set($key, $value,$expire)
     * @return data
     * @throws exception_cache
     */
    public function set($key, $val, $expire) {
        $_debugMicrotime = microtime(true);        
        $ret = call_user_func_array(array($this->_memcached, 'set'), array($key, $val, $expire));
        
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias'=>$this->_alias)) && auto::isDebug() && auto::debugMsg(__METHOD__, 'cost ' . $timeCost . 's, arguments: ' . var_export(array($key,$val,$expire), true));
        
        return $ret;
    }

    public function __call($funcName, $arguments) {
        $method = __CLASS__.'::'.$funcName;
        $_debugMicrotime = microtime(true);
        if (!$this->_memcached) {
            throw new exception_cache('connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        $ret = call_user_func_array(array($this->_memcached, $funcName), $arguments);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add($method, $timeCost, array('alias'=>$this->_alias)) && auto::isDebug() && auto::debugMsg($method, 'cost ' . $timeCost . 's, arguments: ' . var_export($arguments, true));

        return $ret;
    }

}