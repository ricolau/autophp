<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-22
 * @desc validator
 *
 */
class validator {
    
//    
//    const error_empty = 1;
//    const error_type = 2;
//    const error_min = 3;
//    const error_max = 4;
//    
//    const error_minlen=  5;
//    const error_maxlen = 6;
//    
//    const error_type_string = 21;
//    const error_type_bool = 22;
//    const error_type_int = 23;
//    const error_type_float = 24;
//    const error_type_double = 25;
//    const error_type_class = 26;
//    
//    const error_type_email = 31;
//    const error_type_url = 32;
//    const error_type_ip = 33;
//    const error_type_mac = 34;
//    const error_type_chineseid = 35;
//    
    
    
    public static function demo(){
//        $patterns = array(
//            'name'=>'required=false,type=string,maxlen=12,minlen=5,default=rico',
//            'age'=>'required=true,type=int,max=99,min=18,default=18,',
//            'url'=>'required=true,type=url,options=',
//            'email'=>'required=true,type=email,',
//            'cnname'=>'required=true,type=mbstring,maxlen=12,minlen=6',
//            'instance'=>'required=true,type=class,instanceof=plugin_abstract,',
//            'names'=>'required=true,type=array,maxlen=12, minlen=5',
//            'shenfenzheng'=>'required=true,type=chineseid',
//            'lng'=>'require=true,type=float,',
//            'ip'=>'required=true,type=ip,',
//            'mac'=>'required=true,type=mac,',
//            'isMan'=>'required=true,type=bool,',
//            
//        );
//        
//        
        $pattern2 = array(
            'name'=>array(
                'required:true'=>'必填字段,不能为空',
                //'required:false'=>'default value here!',
                'type:string'=>'需要输入数字类型',
                'maxlen:10'=>'最大长度为10个字符',
                'minlen:2'=>'最小需要输入长度为2个字符',
                '?useDefault:true'=>22323,//当 require==false 的时候要用
                //'callback'=>array(),
                ),
            'age'=>array(
                'required:false'=>'选填字段,不能为空',
                'type:int'=>'需要输入数字类型',
                'max:10'=>'最大长度为10个字符',
                'min:2'=>'最小需要输入长度为2个字符',
                ),
        );
        
        $args = request::getAll();
        
        
        //$ret = bool
        $ret = validator::validate( $pattern2, $args, $errorInfo,$strickMode = false   );
        
        // $ret = array or bool false
        $ret = validator::format($pattern2, $args, $errorInfo);
        
        $errorInfo = array(
            'name'=>array('required:true'=>'必填字段,不能为空'),//第一条不匹配的规则即可
            'name'=>array('min:2'=>'必填字段,不能为空'),//第一条不匹配的规则即可
            );
                
    }
    
    public static function required($base,$var){
        
    }
    public static function type($base, $var){
    }
    public static function max(){
    }
    
    public static function min(){
        
    }
    
    
    
    public static function check($patterns, $args){
        
    }
    
    
    
    
    
    /**
     * 作为 public 的内容,单独调用验证单条规则
     */
    public static function isEmail(){
        
    }
    
    public static function isUrl(){
        
        
    }
    
    
}

