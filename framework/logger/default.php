<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2023-11-09
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
                //throw new
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
                //THROW EXCEPTION ?
                return false;
            }
        }
        if($level){
            $rotation = $level.'-'.$rotation;
        }
        $logFile = $dir.DS.$rotation.'.log';
        if(!is_scalar($msg)){
            $msg = var_export($msg, true);
        }
        $msg = $this->getDateStrWithMilliSecond()."\t".$level."\t".$msg."\n";
        file_put_contents($logFile, $msg, FILE_APPEND);
    }
    public function getDateStrWithMilliSecond(){
        $mtime = explode(' ',microtime());
        $dates = date('Y-m-d H:i:s', $mtime[1]);
        $sub = round($mtime[0],6);
        $sub = substr($sub, strpos($sub,'.'));
        $all = $dates.$sub;
        return $all;
    }
}
