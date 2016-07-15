<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-07-14
 * @desc base class
 *
 */
class base {
    
    private static $_singleton = array();
    
    public function __construct() {
        $this->_init();
    }

    protected function _init() {

    }
    
    /**
     * get class instance,support multiple arguments ...
     * @return called class instance
     */
    public static function instance(){
        $args = func_get_args();
        $className = get_called_class();
        $ref = new ReflectionClass($className);
        //no check for the public level of class __construct function
        return $ref->newInstanceArgs($args);
    }
    
    /**
     * get singleton class instance, without any arguments!
     * @return called class instance
     */
    public static function singleton(){
        $className = get_called_class();
        if(!isset(self::$_singleton[$className])){
            //no check for the public level of class __construct function
            self::$_singleton[$className] = new static();
        }
        return self::$_singleton[$className];
    }
}

