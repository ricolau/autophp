<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @vesion 2016-08-11
 * @description 用户在 cache_codis::__call() 异常时回调
 * 
 * */
class plugin_codiserror extends plugin_abstract{
    
    public function call($tag, plugin_context &$ptx){
        /**
         * 需要注册到事件:  plugin::register( 'cache_codis::__call::error', new plugin_codiserror())
         * 
         */
         //maybe caught exception, but just throw


       $data = $ptx->getData(); 
       $cacheRedis = $data['obj'];
       $cacheRedis->connect();
       $ptx->breakOut = call_user_func(array(&$cacheRedis,$data['func']), $data['args']);
        
    }


}
