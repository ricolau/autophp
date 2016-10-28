<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-10-27
 * @desc struct base
 * 
 * @uses below

    

    //@uses ======================= style one ======================= 
    class b extends struct {
        
        //public $age = 18;//this will throw exception

        protected $_structDefine = array(
            'gender' => struct::type_bool,
            'age' => struct::type_int,
            'obj' => struct::type_object,
        );
        protected $_strictMode = false;     //whether throw exception when property type not match with definations

    }

    try {
        $pa = new b();
        $b2 = new b();
        $b2->age = 20000;


        $pa->age = '20';
        $pa->obj = $b2;

        //var_dump($a->fff); // this will throw exception

        var_dump(isset($pa->fff), $pa->toJson(), $pa->toArray());


        foreach($pa as $k => $v) {
            var_dump("============", $k, $v);
        }
    } catch(Exception $e) {
        var_dump($e);
    }


    //@uses =======================  the other style ======================= 


    try {
        $st = array('name' => struct::type_string, 'age' => struct::type_int, 'gender' => struct::type_int);
        $a = new struct($st);


        $a->age = 20;
        $a->name = 'rico,hahahahaha';
        //var_dump($a->fff); // this will throw exception


        var_dump(isset($a->fff), $a->propertyExist('age'),$a->toJson());


        foreach($a as $k => $v) {
            var_dump("============", $k, $v);
        }
    } catch(Exception $e) {
        var_dump($e);
    }

 */


class struct implements IteratorAggregate {

    const err_property_not_exist = 5;
    const err_property_type_invalid = 2;
    const err_init = 1;
    const err_recursive_limit = 6;
    
    const type_null = 'NULL';
    const type_bool = 'boolean';
    const type_int = 'integer';
    const type_string = 'string';
    const type_float = 'double'; //float also returns double with gettype()
    const type_double = 'double';
    //const type_array = 'array';//array type is not allowed
    const type_struct = 'struct';

    private static $_typeList = array(
        self::type_null => true,
        self::type_bool => true,
        self::type_int => true,
        self::type_string => true,
        self::type_float => true,
        self::type_double => true,
        //self::type_array => true,
        self::type_struct => true,
    );
    
    private $_data = array();
    const recursive_depth_limit = 8;    
    
    protected $_structDefine = array(
    );
    protected $_strictMode = true;//whether throw exception when property type not match with definations

    public function __construct($define = array(), $strictMode = null) {

        if(!is_array($define)) {
            throw new Exception('class construct argument[0] should be an array', self::err_init);
        }
        if($this->_structDefine && $define){
            throw new Exception('property has been defined! can not define it with ::__construct() !', self::err_init);
        }
        if($define) {
            $this->_structDefine = $define;
        }
        
        $rf = new ReflectionClass(get_called_class()); 
        $vars  = $rf->getProperties(ReflectionProperty::IS_PUBLIC); //获取类中方法
        if(count($vars)>0){
            throw new Exception('no public property declearation is allowed for class:'.get_called_class(),self::err_init);
        }

        foreach($this->_structDefine as $name => $type) {
            if(!isset(self::$_typeList[$type])) {
                throw new Exception('type :' . $type . ' not valid for class struct!', self::err_init);
            }
            $this->_data[$name] = null;
        }
        if($strictMode !== null) {
            $this->_strictMode = $strictMode;
        }
    }

    public function getIterator() {
        return new ArrayIterator($this->_data);
    }

    public function __set($name, $value) {
        if(!isset($this->_structDefine[$name])) {
            throw new Exception('set property not exist for:' . get_called_class() . '->' . $name, self::err_property_not_exist);
        }

        if($this->_strictMode) {
            $valid = false;
            $type = gettype($value);
            if($type == $this->_structDefine[$name] || ($type == 'object' && ($value instanceof struct)) ){
                $valid = true;
            }
            if(!$valid){
                throw new Exception('property type not match to set for:' . get_called_class() . '->' . $name, self::err_property_type_invalid);
            }
            
        }
        $this->_data[$name] = $value;
    }

    public function __get($name) {
        if(!isset($this->_structDefine[$name])) {
            throw new Exception('get property not exist for:' . get_called_class() . '->' . $name, self::err_property_not_exist);
        }
        return $this->_data[$name];
    }
    
    public function __isset($name){
        return $this->propertyExist($name);
    }
    public function __unset($name){
        if(isset($this->_structDefine[$name])){
            unset($this->_data[$name]);
            unset($this->_structDefine[$name]);
            
        }
    }

    //递归实现针对子元素的 struct 数组转化
    private static function _recursiveArrayConvert($dt, $recursiveDepth = 0) {
        if($recursiveDepth > self::recursive_depth_limit){//递归深度控制
            throw new Exception('too much levels recursived, oversize :' . self::recursive_depth_limit, self::err_recursive_limit);
        }
        
        if(is_object($dt) && $dt instanceof struct) {
            $ret = array();
            foreach($dt as $k => $v) {
                $ret[$k] = self::_recursiveArrayConvert($v, $recursiveDepth+1);
            }
            return $ret;
        } else {
            return $dt;
        }
    }
    
    public function propertyExist($name){
        return isset($this->_structDefine[$name]);
    }
    
    public function getPropertyType($name){
        if(isset($this->_structDefine[$name])){
            return $this->_structDefine[$name];
        }else{
            return false;
        }
        
    }

    public function toJson() {
        $dt = $this->toArray();
        return json_encode($dt);
    }
    
    public function toArray(){
        $dt =   self::_recursiveArrayConvert($this->_data);
        return $dt;
    }

}






