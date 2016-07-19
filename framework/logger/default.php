<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-04-07
 * @desc logger
 *
 */

class logger_default extends logger_abstract {
    
    protected $_logPath = '';
    protected $_conf = array();
    
    protected $_rotationPeriod = '';

    /**
     * 
     * @param array $conf = array(
         
            'fatal'=>array('path'=>'', 'rotation'=>''),
            'error'=>array('path'=>'', 'rotation'=>''),,
        );
    */
    
    public function __construct($conf) {
        
        
        $this->_conf = $conf;
        
        if($this->_logPath==''){
            $this->_logPath = APP_PATH .DS.'log';
        }
        
        foreach($this->_conf as $level=>$c){
            if(!$c['path']){
                $this->_conf[$level]['path'] = &$this->_logPath;
            }
            if(!$c['rotation']){
                $this->_conf[$level]['rotation']  = '';
            }
        }
        
    }
 
    
    
    protected function _getRotationByLevel($level){
        
        if(isset($this->_conf[$level]['rotation']) && $this->_conf[$level]['rotation']){
            $ret = date($this->_conf[$level]['rotation']);
        }else{
            $ret = '';
        }
        return $ret;
        
    }
    public function setLogPath($path){
        $this->_logPath = $path;
    }
    
    public function levels() {
        return array_keys($this->_conf);
    }

    public function add($level, $msg){
        //as a logger, i won't throw any exception to interrupt your program
        if(!isset($this->_conf[$level])){
            if(auto::isDebug() || auto::isDevMode()){
                auto::debugMsg('<font color=red>warning</font>', 'log got no conf for level:'.$level);
            }else{
                return false;
            }
        }
        $rotation = $this->_getRotationByLevel($level);
        if(!$rotation){
            $rotation = 'default';
        }
        $dir = $this->_conf[$level]['path'];
        
        if(!is_dir($dir)){
            $mk = @mkdir($dir, 0777, true);
            if(!$mk){
                auto::isDebug() && auto::debugMsg('<font color=red>warning</font>', 'failed for make log dir:'.$dir);
                return false;
            }
        }
        $logFile = $dir.DS.$rotation.'.log';
        if(!is_scalar($msg)){
            $msg = var_export($msg, true);
        }
        $msg = date('Y-m-d H:i:s')."\t".$level."\t".$msg."\n";
        file_put_contents($logFile, $msg, FILE_APPEND);
    }
}