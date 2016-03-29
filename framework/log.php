<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2015-07-03
 * @desc log
 *
 */

class log {

    protected static $_conf = array();
    protected static $_instance = array();

    public static function addLogger(&$logger) {
        if(!($logger instanceof logger_abstract)){
            throw new exception_logger('logger should be extended of logger_abstract', exception_logger::logger_bad);
        }
        $tmp  = $logger->levels();
        if(!is_array($tmp)){
            return false;
        }
        foreach($tmp as $level){
            self::$_conf[$level][] = &$logger;
        }
        
    }

    
    public static function add($level, $msg){
        if(!isset(self::$_conf[$level])){
            return false;
        }
        foreach(self::$_conf[$level] as &$logger){
            $logger->add($level, $msg);
        }
        
    }

}