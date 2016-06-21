<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-06-21
 * @desc autophp queue
 *
 */
final class queue {
    
    private static $_queue;
    
    
    public static function in($k, $v){
        if($k===null){
            return null;
        }
        if(!isset(self::$_queue[$k])){
            self::$_queue[$k] = array();
        }
        self::$_queue[$k][] = $v;
        return true;
    }
    
    public static function out($k){
        if($k===null){
            return null;
        }
        if(!isset(self::$_queue[$k]) || empty(self::$_queue[$k])){
            return null;
        }
        $out = array_shift(self::$_queue[$k]);
        return $out;
        
    }

    public static function isEmpty($k){
        if($k===null){
            return null;
        }
        if(!isset(self::$_queue[$k])){
            return true;
        }
        return empty(self::$_queue[$k]);
    }
    
    public static function size($k){
        if($k===null){
            return null;
        }
        if(!isset(self::$_queue[$k])){
            return 0;
        }
        return count(self::$_queue[$k]);
    }
    
    public static function clear($k){
        if($k===null){
            return null;
        }
        if(!isset(self::$_queue[$k])){
            return true;
        }
        self::$_queue[$k] = array();
        
    }
    
    public static function dump($k){
        if($k===null){
            return null;
        }
        if(!isset(self::$_queue[$k])){
            return null;
        }
        //$tmp = self::$_queue[$k];
        //self::$_queue[$k] = array();
        return self::$_queue[$k];
        
    }
    
    public static function dumpClear($k){
        $tmp = self::dump($k);
        self::clear($k);
        return $tmp;
    }


}
