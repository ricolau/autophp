<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-4-29
 * @desc plugin context class
 *
 */
class plugin_context {
    
    protected $_tag;
    protected $_data;
    public $breakOut = null;
    
    public function __construct($tag, $data){
        $this->_tag = $tag;
        $this->_data = &$data;
    }
    
    public function getTag(){
        return $this->_tag;
        
    }
    
    public function getData(){
        
        return $this->_data;
    }

}