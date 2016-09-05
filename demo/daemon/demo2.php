<?php

include dirname(__FILE__).'/loader.php';

class daemon_demo extends daemon{
    
    public function _init(){
        
    }
    
    public function main(){
        $redis = cache::instance("rs1");
        try{
           $a[] = $redis->set('k1','test'); 
           $a[] = $redis->get('k1');
           $a[] = $redis->hmset('k1',array());

        } catch (Exception $e) {
            de($e);
            exception_handler::topDeal($e);
        }
       

    }    
}


$demo = new daemon_demo();
$demo->main();
