<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-4-12
 * @desc exception_msg 
 *
 */
class exception_msg extends Exception {
    
    public function __construct($message = "", $code = 0, \Exception $previous = null){
        parent::__construct($message, $code, $previous);
    }
    public function getCode(){
        return parent::getCode();
    }
    public function getMessage(){
        return parent::getMessage();
    }
    public function getFile(){
        return parent::getFile();
    }
    public function getLine(){
        return parent::getLine();
    }
    public function getTrace(){
        return parent::getTrace();
    }
    public function getTraceAsString(){
        return parent::getTraceAsString();
    }
    public function getPrevious(){
        return parent::getPrevious();
    }
}