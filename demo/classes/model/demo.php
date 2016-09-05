<?php

/**
 * 如果需要读取 mysql，建议继承 orm model
 */
class model_demo extends orm{
    protected $_dbAlias = 'default';
    protected $_table = 'user';
    
    
    protected $_cacheServer1 = null;
    
    //model construct 会自动调用 _init()
    protected function _init(){
        //$this->_cacheServer1 = cache::instance('mc1');;
    }
    
    
    public function getUserInfo($uin){
        $keyTpl = 's_tframework_debug_cache_key_%s';
        $key  = sprintf($keyTpl, $uin);
        try{
           // $cacheData = $this->_cacheServer1->get($key);
            if($cacheData){ 
                echo "\r\n <br /> use memcache! \r\n <br /> ";
                return $cacheData;
            }
            $tableStructure =  $this->queryFetch('show tables;');

            //第一种得到orm 的方式，根据$this->_dbAlias 和 $this->_table 配置，$this 就是orm 的实例！
            $data11 =  $this->where('id = 2')->getOne();
            /*
            $data12 =  $this->where('id = 2')->getOne();

            $up11 = $this->where('id=2')->update(array('name'=>'testname_'.time()));
            $up12 = $this->where('id=2')->update(array('name'=>'testname_'.time()));

            $data21 =  $this->where('id = 2')->getOne();
            $data22 =  $this->where('id = 2')->getOne();


echo '<pre>';
            de($data11, $data12, $up11, $up12, $data21, $data22);
*/






            if($data){
                $expire = 160;
                echo "\r\n <br /> set memcache! \r\n <br /> ";
                $this->_cacheServer1->set($key, $data,MEMCACHE_COMPRESSED, $expire);
            }
        }catch(exception_mysqlpdo $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
            switch ($code)
            {
                case exception_mysqlpdo::type_conf_error:
                    //config error
                    break;
                case exception_mysqlpdo::type_high_risk_query:
                    // high risk of your query, may effect the hole table
                    break;
                case exception_mysqlpdo::type_query_error:
                    //query failed or some ...
                    break;

                default:
                    break;
            }
        }catch(exception_db $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }catch(exception_cache $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }catch(Exception $e){
            throw $e;
        }
        return $data;
    }
    
    public function getUserInfo2($uin){
        $keyTpl = 's_tframework_debug_cache_key_%s';
        $key  = sprintf($keyTpl, $uin);
        $errmsg = null;
        try{
            $cacheData = cache::instance('rs1')->get($key);
            if($cacheData){ 
                echo "\r\n <br /> use redis! \r\n <br /> ";
                return unserialize($cacheData);
            }
            //$data =  $this->queryFetch('desc ent_package;');

            //第二种调用得到 ORM 的方式！可以得到任意 db 和table 的orm 实例
            $data = orm::instance($this->_dbAlias)->table($this->_table)->where('package_id > 5')->getOne();
            if($data){
                $expire = 16;
                echo "\r\n <br /> set redis! \r\n <br /> ";
                cache::instance('rs1')->set($key, serialize($data), $expire);
            }
        }catch(exception_mysqlpdo $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
            switch ($code)
            {
                case exception_mysqlpdo::type_conf_error:
                    //config error, you can do something here
                    break;
                case exception_mysqlpdo::type_high_risk_query:
                    // high risk of your query, may effect the hole table
                    break;
                case exception_mysqlpdo::type_query_error:
                    //query failed or some ...
                    break;

                default:
                    break;
            }
        }catch(exception_db $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }catch(exception_cache $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }catch(Exception $e){
            throw $e;
        }
        if($errmsg){
            //do something to record the exception error message
            return false;
        }
        return $data;        
    }
    
    public function updateInfo($id, $price=1){
        $data = array('price'=>$price);
        try{
            $ret = orm::instance($this->_dbAlias)->table($this->_table)->where('package_id = ?', array($id))->update($data);
            }catch(exception_mysqlpdo $e){
            $code = $e->getCode();
            $errmsg = $e->getMessage();
            switch ($code)
            {
                case exception_mysqlpdo::type_conf_error:
                    //config error
                    break;
                case exception_mysqlpdo::type_high_risk_query:
                    // high risk of your query, may effect the hole table
                    break;
                case exception_mysqlpdo::type_query_error:
                    //query failed or some ...
                    break;

                default:
                    break;
            }
        }catch(Exception $e){
            throw $e;
        }
        return $ret;
    }
    
    
    
}
