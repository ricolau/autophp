<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-07-20
 * @desc daemon abstract
 *
 */
abstract class daemon {

    const plugin_construct = 'daemon_construct';
    const plugin_destruct = 'daemon_destruct';
    
    public function __construct() {
       
        if (!auto::isCli()) {
            throw new exception_base('cannot run daemon with http request!', -1);
        }
        $ptx = new plugin_context(__METHOD__,array());
        plugin::run(daemon::plugin_construct, $ptx);
        
        $this->_init();
    }

    public function _init() {

    }
    
    public function __destruct(){
        
        $ptx = new plugin_context(__METHOD__,array());
        plugin::run(daemon::plugin_destruct, $ptx);
    }

}