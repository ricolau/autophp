<?php


class plugin_ormcall extends plugin_abstract{
    
    const gone_away  = 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away';
    const lost_connect = 'SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query';

    
    public function call($tag, plugin_context &$ptx){
        $data = $ptx->getData();
        if(self::_isGoneAway($data['exception']) || self::_lostConnection($data['exception'])){
            $obj = $data['obj']->forceDbReconnect();
            $ptx->breakOut =  call_user_func_array(array($obj, $data['func']),$data['args']);
            return true;
        }
//        else{
//            //do nothing, and the exception will throw
//        }
    }
    
    private static function _isGoneAway($e){
        $msg = $e->getMessage();
        return ( $msg == self::gone_away || strpos($msg,'gone away') > 0) ? true : false;
        
    }
    
    private static function _lostConnection($e){
        return ($e->getMessage() == self::lost_connect) ? true : false;
    }
    
    
    
}
