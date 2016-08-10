<?php
/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-10
 * @desc performance log
 *
 */
class performance {


    protected static $_performance = array();
    protected static $_sizeLimit = 256;
    
    protected static $_hostKey = '__auto_performance';
    
    protected static $_currentSize = 0;
    
    
    protected static $_openStatus = true;
    const tag_mode_fully = 'fully';
    const tag_mode_sampling = 'sampling';
    const tag_mode_close = 'close';
    
    protected static $_tagModes = array();
    
    protected static $_samplingCounts = array();
    
     
    const summarize_mode_default = 0;
    const summarize_mode_fully = 1;
    
    protected static $_summarizeMode = self::summarize_mode_default;
    
    /**
     * set performance storage key in class "queue"
     * @param string $key
     * @return boolean
     */
    public static function setHostKey($key){
        if($key!==null){
            return self::$_hostKey = $key;
        }
        return false;
    }
    /*
    public static function getHostKey(){
        return self::$_hostKey;
    }
     * 
     */
    
    
    public static function switchOpenStatus($isOpen = true){
        self::$_openStatus = $isOpen ? true : false;
    }
    
    /**
     * set size limit top
     * @param type $top
     */
    public static function setSizeLimit($top = 256){
        self::$_sizeLimit = $top>0 ? $top : 256;
    }
    
    public static function getSizeLimit(){
        return self::$_sizeLimit;
    }
    
    public static function getCurrentSize(){
        return self::$_currentSize;
    }

    public static function setTagMode($tag, $mode = self::tag_mode_fully, $options = null){
        if($tag && $mode){
            if($mode===self::tag_mode_sampling){//sampling 抽样概率是多少
                $options = intval($options);
                $options = $options>0 ? $options : 100;
            }
            self::$_tagModes[$tag] = array('mode'=>$mode, 'options'=>$options);
        }
    }
    /**
     * performance info add, this function should only return true!
     * @param string $tag
     * @param float $timecost
     * @param array $info
     * @return true!!!
     */
    public static function add($tag, $timecost, $info = array()){
        if(false === self::$_openStatus){
            return true;
        }
        if(isset(self::$_tagModes[$tag]) && self::$_tagModes[$tag]['mode']==self::tag_mode_close){
            return true;
        }elseif(isset(self::$_tagModes[$tag]) && self::$_tagModes[$tag]['mode']==self::tag_mode_sampling){
            
            if(!isset(self::$_samplingCounts[$tag])){
                self::$_samplingCounts[$tag]=0;
            }
            if(self::$_samplingCounts[$tag] !== self::$_tagModes[$tag]['options']){
                self::$_samplingCounts[$tag]++;
                return true;
            }
            self::$_samplingCounts[$tag] = 0;
        }
        
        $pf = array('time'=>time(),'tag'=>$tag,'timecost'=>$timecost, 'info'=>$info);
        queue::in(self::$_hostKey, $pf);
        
        if(self::$_currentSize<=0){
            self::$_currentSize = queue::size(self::$_hostKey);
        }else{
            self::$_currentSize++;
        }
        if(self::$_currentSize - self::$_sizeLimit > 2 && self::$_currentSize % 3==0){

            $ptx = new plugin_context(__METHOD__, array());
            plugin::call(__METHOD__.'::notice',$ptx);
            if($ptx->breakOut){
                return $ptx->breakOut;
            }
            if(self::$_currentSize===0){
                return true;   
            }
  
            queue::out(self::$_hostKey);
            queue::out(self::$_hostKey);
            queue::out(self::$_hostKey);
            self::$_currentSize -= 3;
        }
        return true;
    }
    
//    
//    public static function setSummarizeModeByTag($tag, $mode){
//        
//    }
   
    
    public static function setSummarizeMode($mode = self::summarize_mode_default){
        self::$_summarizeMode = ($mode == self::summarize_mode_default)? self::summarize_mode_default : self::summarize_mode_fully;
    }
    
    
    
    /**
     * get summary info for a variable
     * @param type $var
     * @return type
     */
    public static function summarize($var, $tag = null){
        
        if(self::$_summarizeMode!==self::summarize_mode_default){
            return $var;
        }
        if( is_bool($var) || is_numeric($var) || is_null($var)){
            $ret = $var;
        }elseif(is_string($var)){
            $ret = 'string('.strlen($var).')';
        }else{
            $ret = gettype($var);
        }     
        return $ret;
        
    }
    
    public static function dump(){
        return queue::dump(self::$_hostKey);
    }
    public static function dumpClear(){
        self::$_currentSize = 0;
        return queue::dumpClear(self::$_hostKey);
    }

    
}