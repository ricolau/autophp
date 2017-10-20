<?php
/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-10-20
 * @desc autophp validator
 * 
 */
class validator {

    const type = 'type';
    
    const string = 'string';
    const int = 'int';
    const float = 'float';
    const number = 'number';
    const mobile = 'mobile';
    const url = 'url';
    const email = 'email';
    
    
    const required = 'required';//是否允许为空
    const maxLen = 'maxlen';//最大长度
    const minLen = 'minlen';//最小长度
    
    
    const mbMaxLen = 'mbMaxLen';
    const mbMinLen = 'mbMinLen';
    
    const maxValue = 'maxValue';
    const minValue = 'minValue';
    
    
    protected $_rules = array();
    protected $_originalRules = array();
    
    protected $_errors = array();
    
    protected function _parseRules($rules){
        $ret = array();
        foreach($rules as $field=>$r){
            $tmp = array();
            foreach($r as $k=>$v){
                
            }
            
        }
        //type, required 需要声明
        
    }
    /**
     * @throws exception_validator
     * @param array $rules
     */
    public function setRules($rules){
        $this->_originalRules = $rules;
        $pr = $this->_parseRules($rules);
        $this->_rules = $pr;
        
    }
    public function getRules(){
        return $this->_rules;
       
    }
    
    
    
    /**
     * 
     * @param array $fields
     * @param array $params, array in (field=>value) format
     * @param bool $isGreedy Description
     * @throws exception_validator
     */
    public function pass($fields, $params){
        
        $this->_errors = array();
        if(empty($fields)){
            throw new exception_validator(   array(array('code'=>exception_validator::error_input,'message'=>'empty fields to pass?'))    );
        }
        foreach($fields as $f){
            $v = $params[$f];
            $rule = $this->_rules[$f];
            
            //start check if required
            if($rule[validator::required] === false && $v === ''){
                continue;
            }elseif($rule[validator::required] === true && $v === ''){
                $this->_errors[$f] = array('code'=>$rule[validator::required]['code'],'message'=>$rule[validator::required]['message']);
                continue;
            }
            unset($rule[validator::required]);//检查完之后,就可以去掉了,后续不需要了
            
            
            //start check type
            if(!$this->checkType($rule[validator::type], $v)){
                $this->_errors[$f] = array('code'=>$rule[$f]['code'],'message'=>$rule[$f]['message']);
                continue;
            }
            unset($rule[validator::type]);//检查完之后,就可以去掉了,后续不需要了
            
            
            //start check value range
            $cv = $this->checkValue($rule, $v);
            if($cv !==true){
                $this->_errors[$f] = $cv;
            }
            
        }//end foreach
        
        if($this->_errors){
            $exp = new exception_validator($this->_errors);
            throw $exp;
        }
        
    }
    
    
    public function checkType($type, $v){
        $types = array();
        $func = 'is'.ucfirst($type);
        if(!method_exists($this, $func)){
            $errors = array(array('code'=>1,'message'=>'type may not defined for:'.$type));
            throw new exception_validator($errors);
        }
        return self::$func($v);
    }
    
    public static function isInt($v){
        if(is_int($v)){
            return true;
        }
        $tr = intval($v);
        if(strlen($tr)==strlen($v) && $tr - $v ==0){
            return true;
        }
        return false;
    }
    public static function isNumber($v){
        if(is_numeric($v)){
            return true;
        }
        return false;
    }
    public static function isString($v){
        if(is_string($v)){
            return true;
        }
        return false;
        
    }
    public static function isMobile($v){
        $pattern = "/^1[34578]\d{9}$/";
        return preg_match($pattern, $v);
    }
    //这个正则,略纠结,现在网上的url 协议和构成太多了还是,全纳入进来是号还是坏?
    public static function isUrl($v){
        $pattern = "/^^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+$/";
        return preg_match($pattern, $v);
    }
    public static function isEmail($v){
        $pattern = "/^[a-z]([a-z0-9]*[-_]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i";
        return preg_match($pattern, $v);        
    }
    
    public function checkValue($rule, $f, $v){
        
    }


}

class exception_validator extends exception_base{
    
    const error_input = 1;
    protected $_errors = array();
    
    public function __construct($errors, $count = null, $previous = null) {
        $this->_errors = $errors;
        $err = current($errors);
        parent::__construct($err['message'], $err['code'], $previous);
    }
    
    
    public function getErrors(){
        return $this->_errors;
    }
}




$rules = array(
    'name'=>array(
        array(validator::type=>validator::string,),
        array(validator::required =>true,'code'=>111,'message'=>'can not empty!'),
        array(validator::mbMaxLen =>20,'code'=>222,'message'=>'msg 222'),
        array(validator::mbMinLen=>5,'code'=>333,'message'=>'msg 3333'),
        
    ),
    'age'=>array()
);







try{
    $vd = new validator();
    $vd->setRules($rules);
    
    //$vd->addType('name',function(){});
    

    $vd->pass($fields, $params, $isGreedy);
}catch(exception_validator $e){
    
    var_dump($e->getCode(), $e->getMessage());
//
//    $vd->getRules();
//    
//    $vd->getAllFake();
//    $vd->getAllCodes();
//    $vd->getAllMessages();
}

