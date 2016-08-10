<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-10
 * @desc codis
 *
 */
class cache_codis extends cache_abstract{

    protected static $_reentrantTimes = array();
    protected static $_reentrantTimesLimit = 5;
    protected $_redis = null;

    public function __construct($alias, $conf){
        $this->_alias = $alias;
        $this->_confs = $conf;

        $this->_confFormated = false;
    }

    protected function _getServer(){

        if(!$this->_confFormated){
            $this->_confs['serversFormat'] = array();

            $weightTotal = 0;
            foreach($this->_confs['servers'] as $k => $svr){
                $this->_confs['serversFormat'][$k] = $svr;

                if(isset($svr['weight'])){
                    $svr['weight'] = intval($svr['weight']);
                }else{
                    $svr['weight'] = 1;
                }

                $this->_confs['serversFormat'][$k]['weight'] = ($svr['weight'] <= 0) ? 1 : $svr['weight'];
                $this->_confs['serversFormat'][$k]['timeout'] = !isset($svr['connectTimeout']) ? $svr['connectTimeout'] : 0.05;
                $weightTotal += $this->_confs['serversFormat'][$k]['weight'];
            }
            $this->_confs['weightTotal'] = $weightTotal;
            $this->_confs['serverCount'] = count($this->_confs['serversFormat']);

            $this->_confFormated = true;
            self::$_reentrantTimesLimit = $this->_confs['serverCount'] + 1;
        }


        if($this->_confs['serversFormat']){
            $hitRate = rand(0, $this->_confs['weightTotal']);
            $weightCollect = 0;
            $hitSvr = array();
            foreach($this->_confs['serversFormat'] as $k => $svr){
                $weightCollect += $svr['weight'];
                if($hitRate <= $weightCollect){
                    $hitSvr = array('key' => $k, 'server' => $svr);
                    break;
                }
            }
            if(!$hitSvr){
                $key = $this->_confs['serverCount'] - 1;
                $hitSvr = array('key' => $key, 'server' => $this->_confs['serversFormat'][$key]);
            }

            $this->_confs['weightTotal'] -= $this->_confs['serversFormat'][$key]['weight'];
            unset($this->_confs['serversFormat'][$key]);

            return $hitSvr;
        }else{
            $this->_confFormated = false;
            return false;
        }
    }

    public function connect(){
        $_debugMicrotime = microtime(true);

        if(!$this->_confs['servers']){
            throw new exception_cache(
            'codis connection host and port empty!' . (!auto::isOnline() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error
            );
        }


        while(true){
            $server = $this->_getServer();
            if(!$server){
                //所有的server 全部连接失败!!!!!太严重了
                $ptx = new plugin_context(__METHOD__, array('conf' => $this->_confs, 'alias' => $this->_alias, 'obj' => &$this, 'hitServer' => $server,));
                plugin::call('fatal::' . __METHOD__, $ptx);
                if($ptx->breakOut){
                    return $ptx->breakOut;
                }
                continue;
            }


            if(!$server['server']['host'] || !$server['server']['port']){
                throw new exception_cache(
                'redis connection host and port error!' . (auto::isDebug() ? var_export($server, true) : ''), exception_cache::type_server_connection_error
                );
            }
            try{
                $this->_redis = new Redis();
                $con = $this->_redis->connect($server['server']['host'], $server['server']['port'], $server['server']['timeout']);
                ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('alias' => $this->_alias, 'hitServer' => $server, 'ret' => performance::summarize($con)));
            }catch(Exception $e){
                //catch exception just ignore and take it to the below flow
                //continue;
            }
            if($con){
                break;
            }
            //设置重入次数上限,防止程序陷入死循环重入崩溃
            $seqid = md5($this->_alias . __METHOD__);
            if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid] >= self::$_reentrantTimesLimit){
                throw new exception_cache(
                    'redis connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error
                );
            }
            if(!isset(self::$_reentrantTimes[$seqid])){
                self::$_reentrantTimes[$seqid] = 0;
            }
            self::$_reentrantTimes[$seqid] += 1;

            $ptx = new plugin_context(__METHOD__, array('conf' => $this->_confs, 'alias' => $this->_alias, 'obj' => &$this, 'hitServer' => $server,));
            plugin::call('error::' . __METHOD__, $ptx);
            if($ptx->breakOut){
                return $ptx->breakOut;
            }
        }

        return $this;
    }

    public function __call($funcName, $arguments){
        $method = __CLASS__ . '::' . $funcName;
        $_debugMicrotime = microtime(true);
        if(!$this->_redis){
            throw new exception_cache('connection error!' . (auto::isDebug() ? var_export($this->_confs, true) : ''), exception_cache::type_server_connection_error);
        }
        try{
            $ret = call_user_func_array(array($this->_redis, $funcName), $arguments);
        }catch(RedisException $e){
            //设置重入次数上限,防止程序陷入死循环重入崩溃
            $seqid = md5($this->_alias . serialize($arguments) . $funcName);
            if(isset(self::$_reentrantTimes[$seqid]) && self::$_reentrantTimes[$seqid] >= self::$_reentrantTimesLimit){
                throw $e;
            }
            if(!isset(self::$_reentrantTimes[$seqid])){
                self::$_reentrantTimes[$seqid] = 0;
            }
            self::$_reentrantTimes[$seqid] += 1;

            $ptx = new plugin_context($method, array('conf' => $this->_confs, 'alias' => $this->_alias,
                'exception' => &$e, 'obj' => &$this, 'func' => $funcName, 'args' => $arguments));
            plugin::call('error::' . $method, $ptx);
            if($ptx->breakOut){
                return $ptx->breakOut;
            }
            throw $e;
        }
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add($method, $timeCost, array('alias' => $this->_alias, 'args' => $arguments, 'ret' => performance::summarize($ret, $method)));

        return $ret;
    }

}
