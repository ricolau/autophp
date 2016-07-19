<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-05-18
 * @desc daemon abstract
 *
 */
abstract class daemon {

    public function __construct() {
       
        if (!auto::isCli()) {
            throw new exception_base('cannot run daemon with http request!', -1);
        }
        $ptx = new plugin_context(__METHOD__,array());
        plugin::run('before_run', $ptx);
        
        $this->_init();
    }

    public function _init() {

    }
    
    public function __destruct(){
        
        $ptx = new plugin_context(__METHOD__,array());
        plugin::run('after_run', $ptx);
    }

}