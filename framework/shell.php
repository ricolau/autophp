<?php
/**
 * @description popular development for system shell
 * @author by ricolau<ricolau#foxmail.com>(replace # with @)
 * @version 2015-07-23
 *
*/

class shell{
    
    public static function execute($cmd, $runBackground = false, $redirect = '/dev/null'){

        if($runBackground){
            $str .= ' &';
        }
        if($redirect){
            $str = $cmd .' > '.$redirect;
        }
        return self::run($str);
    }
    public static function run($str){
        $hdl = @popen($str, "r");
        if(!$hdl){
            return false;
        }
        $ret = '';
        while(!feof($hdl)){
            $ret .=fread($hdl, 1024);
        }
        @pclose($hdl);
        return trim($ret);
    }


    public static function currentCommand(){
        $cmd = implode(' ',$GLOBALS['argv']);
        return $cmd;
    }

}