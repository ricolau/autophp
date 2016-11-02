<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-10-29
 * @desc struct base
 * 
 * @comment 特性: 期望可完全替代php 的弱类型数组,当做强类型靠谱使用;
 *              1.使用前,struct 的所有property 必须声明才可以;不声明的 set 和 get 都将throw exception;
 *              2.赋值时,类型检测,强类型检测;
 *              3.property默认值均为 null;
 *              4.json 转换支持;
 *              5.数组转换支持,支持最高8维数组!!性能考虑;
 *              6.子类struct,无法定义 public 的property,必须使用 struct 规定的声明方式;
 *              7.支持iterator 数组形式的遍历;
 *              8.支持unset(),可以将实例化的 struct 中某个property unset 掉.但不影响struct 定义,可以重新赋值.
 *              9.禁用 isset()支持,由于 php 语言没有 undefined,对于 null和 undefined 都认为是false,所以直接禁用.
 *              10.继承自 class base,支持  struct::instance() 形式的初始化;
 *              11.暂时不考虑对于 __serialize()  __clone() 等的处理
 * 
 * @uses below

    



    //@uses ======================= style one, with recommendation ======================= 
    class b extends struct {
        
        //public $age = 18;//this will throw exception

        protected $_structDefine = array(
            'gender' => struct::type_bool,
            'age' => struct::type_int,
            'obj' => struct::type_int,
        );
        protected $_strictMode = true;     //whether throw exception when property type not match with definations

    }

    try {
        
        echo "######### demo use one ############\n";
        $b2 = new b();
        $b2->age = 20000;

        $pa = new b();
        $pa->age = 20;
        $pa->obj = $b2;

       

        echo "=======================\n";
        echo "convert to json:";
        var_dump($pa->toJson());


        
        
        echo "=======================\n";
        echo "convert to array:";
        var_dump($pa->toArray());
        
        echo "=======================\n";
        echo "each property show in list:\n";
        foreach($pa as $k => $v) {
            var_dump($k,'=>', $v);
            echo "----------\n";
        }
        
        
        
        echo "=======================\n";
        echo "exception test: \n";
        
        $b2->age = '20';
        $a->fff = 2222; // this will throw exception
        isset($a->fffff);
        
    } catch(Exception $e) {
        var_dump("exception caught :",$e);
    }
    //exit;
    //@uses =======================  the other style, deprecated ======================= 


    try {
        
        echo "######### demo use the other one  ############\n";

        $st = array("name" => struct::type_string, 'age' => struct::type_int, 'gender' => struct::type_int);
        $a = new struct($st);


        $a->age = 20;
        $a->name = 'rico,hahahahaha';
        //var_dump($a->fff); // this will throw exception

        echo "=======================\n";
        echo "test property exist age:";
        var_dump($a->propertyExist('age'));
        
        
        

        echo "=======================\n";
        echo "convert to json:";
        var_dump($a->toJson());
        
        
        
        echo "=======================\n";
        echo "after unset property of this object (will not affect the struct defination):\n";
        unset($a->age);
        var_dump($a->toJson());



        echo "=======================\n";
        echo "each property show in list:\n";
        foreach($a as $k => $v) {
            var_dump($k,'=>', $v);
            echo "----------\n";
        }
        
         
        echo "=======================\n";
        echo "exception test: \n";
        
        var_dump(isset($a->age));//will throw exception, 
        
        
    } catch(Exception $e) {
        var_dump($e);
    }
 

 */


class struct extends base implements IteratorAggregate {

    const err_property_not_exist = 5;
    const err_property_type_invalid = 2;
    const err_init = 1;
    const err_recursive_limit = 6;
    const err_base = 0;
    
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
        if($rf->getProperties(ReflectionProperty::IS_PUBLIC)){//禁止声明 public property
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
            if($type == $this->_structDefine[$name] || ($this->_structDefine[$name] == struct::type_struct && $type == 'object' && ($value instanceof struct)) ){
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
    
    /**
     * @description php语言的 isset()  和null 配合, 设计得不合理, 针对于 $a = array('name'=>null);  isset($a['name']) 和 isset($a['age']) 都返回false
     *              所以,禁用 isset() 了,直接可以使用  struct::propertyExist($name) 来判断一个属性是否存在
     * @param type $name
     * @throws Exception
     */
    public function __isset($name){
        throw new Exception('can not use isset() for stuct property', self::err_base);
        //return array_key_exists($name, $this->_data);
    }
    public function __unset($name){
        if(isset($this->_structDefine[$name]) && array_key_exists($name, $this->_data)){
            unset($this->_data[$name]);
            //unset($this->_structDefine[$name]);
        }
    }

    //递归实现针对子元素的 struct 数组转化
    private static function _recursiveArrayConvert($dt, $recursiveDepth = 0) {
        if($recursiveDepth > self::recursive_depth_limit){//递归深度控制
            throw new Exception('too much levels recursived, oversize :' . self::recursive_depth_limit, self::err_recursive_limit);
        }
        if(is_array($dt) || (is_object($dt) && $dt instanceof struct)) {
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

    public static function typeExist($name){
        return isset(self::$_typeList[$name]);
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

