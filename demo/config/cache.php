<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-10
 * @desc default config file
 *
 */

$options = array(Redis::OPT_READ_TIMEOUT=> 1);

return array(
//配置一个memcache 的集群
    'default' => array(
        'type' => 'memcache',//数据驱动，将直接去调用 cache_memcache
        'servers' => array(
            array('host' => '10.100.100.1', 'port' => 11217, 'weight' => 10),
            array('host' => '10.100.100.2', 'port' => 11217, 'weight' => 40),
            array('host' => '10.100.100.3', 'port' => 11217, 'weight' => 50),
        )
    ),
    
    'codis' => array(
        'type' => 'codis',//codis
        'servers' => array(
            array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 10,'connectTimeout'=>0.05,'options'=>$options),
            array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 40,'connectTimeout'=>0.05,'options'=>$options),
            array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 50,'connectTimeout'=>0.05,'options'=>$options),
        )
    ),
    //配置第二个memcache 的集群
    'mc1' => array(
        'type' => 'memcache',
        'servers' => array(
            array('host' => '10.100.100.4', 'port' => 11217, 'weight' => 40),
            array('host' => '10.100.100.5', 'port' => 11217, 'weight' => 10),
            array('host' => '10.100.100.6', 'port' => 11217, 'weight' => 50),
        )
    ),
    //配置第二个memcache 的集群
    'rs1' => array(
        'type' => 'redis',
        'host'=>'127.0.0.1',
        'port'=>6379,
        'connectTimeout'=>0.05,
        'options'=>$options
        
    ),
    'rs2' => array(
        'type' => 'redis',
        'host'=>'127.0.0.1',
        'port'=>6379,
        'connectTimeout'=>0.01,
        'options'=>$options
    )
);

/**
 * @usage
 * cache::instance('mc1')->set($key, $value, $expire);
 * cache::instance('mc1')->get($key);
 */