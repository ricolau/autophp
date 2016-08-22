<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-22
 * @desc validator
 *
 */
class validator {
    
    
    const error_empty = 1;
    const error_type = 2;
    const error_min = 3;
    const error_max = 4;
    
    const error_minlen=  5;
    const error_maxlen = 6;
    
    const error_type_string = 21;
    const error_type_bool = 22;
    const error_type_int = 23;
    const error_type_float = 24;
    const error_type_double = 25;
    const error_type_class = 26;
    
    const error_type_email = 31;
    const error_type_url = 32;
    const error_type_ip = 33;
    const error_type_mac = 34;
    const error_type_chineseid = 35;
    
    
    
    public static function demo(){
        $patterns = array(
            'name'=>'required=false,type=string,maxlen=12,minlen=5,default=rico',
            'age'=>'required=true,type=int,max=99,min=18,default=18,',
            'url'=>'required=true,type=url,options=',
            'email'=>'required=true,type=email,',
            'cnname'=>'required=true,type=mbstring,maxlen=12,minlen=6',
            'instance'=>'required=true,type=class,instanceof=plugin_abstract,',
            'names'=>'required=true,type=array,maxlen=12, minlen=5',
            'shenfenzheng'=>'required=true,type=chineseid',
            'lng'=>'require=true,type=float,',
            'ip'=>'required=true,type=ip,',
            'mac'=>'required=true,type=mac,',
            'isMan'=>'required=true,type=bool,',
            
        );
        
        $ret = array(
            array('filed'=>'name','errortype'=>'length')
            );
                
    }
    
    public static function check($patterns, $args){
        
    }
    
    public static function isEmail(){
        
    }
    
    public static function isUrl(){
        
        
    }
    
    
}

