<?php
/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-07-19
 * @desc performance log
 *
 */
class performance {


    protected static $_performance = array();
    protected static $_sizeLimit = 128;
    
    protected static $_hostKey = '__auto_performance_key';
    
    protected static $_currentSize = 0;
    
    public static function setHostKey($key){
        if($key!==null){
            return self::$_hostKey = $key;
        }
        return false;
    }
    public static function getHostKey(){
        return self::$_hostKey;
    }
    
    public static function setSizeLimit($top = 128){
        self::$_sizeLimit = $top>0 ? $top : 128;
    }
    
    public static function getCurrentSize(){
        return self::$_currentSize;
    }

//    public static function tagSet($tag, $mode = 1, $options = null){
//        
//    }

    public static function add($tag, $timecost, $info = array()){
        
        $pf = array('time'=>time(),'tag'=>$tag,'timecost'=>$timecost, 'info'=>$info);
        queue::in(self::$_hostKey, $pf);
        
        if(self::$_currentSize===0){
            self::$_currentSize = queue::size(self::$_hostKey);
        }else{
            self::$_currentSize++;
        }
        if(self::$_currentSize - self::$_sizeLimit > 2 && self::$_currentSize % 3==0){

            $ptx = new plugin_context(__METHOD__, array());
            plugin::run('notice::'.__METHOD__,$ptx);
            if($ptx->breakOut){
                return $ptx->breakOut;
            }
  
            queue::out(self::$_hostKey);
            queue::out(self::$_hostKey);
            queue::out(self::$_hostKey);
            self::$_currentSize -= 3;
        }
        return true;
    }
    public static function dump(){
        return queue::dump(self::$_hostKey);
    }
    public static function dumpClear(){
        self::$_currentSize = 0;
        return queue::dumpClear(self::$_hostKey);
    }

    
}