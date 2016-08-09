<?php
/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-08-08
 * @desc autophp auto, check running enviroment and more closer to base layer
 * @link https://github.com/ricolau/autophp
 *
 */
class auto {

    const version = '2.1.5';//2.1.0 update about plugin, not compatible with version before 2.1.0
    
    
    const author = 'ricolau<ricolau@qq.com>';

    
    const mode_dev = 'dev';
    const mode_test = 'test';
    const mode_online = 'online';
    
    const plugin_shutdown = 'auto_shutdown';
    
    private static $_runMode = self::mode_online;

    private static $_runtimeStart = 0;
    private static $_runtimeEnd = 0;

    private static $_isCli = false;
    private static $_hasRun = false;
    private static $_isDebug = false;
    
    private static $_runId = null;
    private static $_sapiName = null;

    private static $_classPath = array();
    

    public static function hasRun() {
        return self::$_hasRun;
    }

    public static function run($runId = null) {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

        spl_autoload_register(array('auto', 'autoload'));
        register_shutdown_function(array('auto', 'shutdownCall'), array());

        if (self::$_hasRun) {
            throw new exception_base('auto can not run twice!', exception_base::type_autophp_has_run);
        }

        self::$_hasRun = true;
        
        //may got as:  fpm-fcgi,cli ....etc.
        self::$_sapiName = php_sapi_name();
        if (self::$_sapiName == 'cli') {
            self::$_isCli = true;
        }
        if (get_magic_quotes_gpc()) {
            throw new exception_base('magic quotes should be turned off~ ', exception_base::type_magic_quotes_on);
        }
        if (!defined('APP_PATH')) {
            throw exception_base('APP_PATH not defined!', exception_base::type_app_path_not_defined);
        }
        if (!defined('AUTOPHP_PATH')) {
            throw exception_base('AUTOPHP_PATH not defined!', exception_base::type_autophp_path_not_defined);
        }
        self::$_runtimeStart = microtime(true);
        
        self::$_runId = $runId ?: uniqid(substr(self::$_sapiName,0,1).'_');
        
        performance::add(__METHOD__, 0, array('runId'=>self::$_runId,'sapi'=>self::$_sapiName    ));

    }
        
    public static function getSapiName(){
        return self::$_sapiName;
    }
    
    
    public static function getRunId(){
        return self::$_runId;
    }
    
//    public static function setRunId($id){
//        self::$_runId = $id;
//    }

    /**
     * get sapi mode whether cli
     * @return type
     * 
     */
    public static function isCli(){
        return self::$_isCli;
    }

    
      
    public static function setMode($mode = self::mode_online){
        $list = array(
            self::mode_dev=>true,
            self::mode_test=>true,
            self::mode_online=>true,
        );
        if(!isset($list[$mode])){
            $mode = self::mode_online;
        }
        self::$_runMode = $mode;
    }
    
    public static function getMode(){
        return self::$_runMode;
    }
    public static function isTestMode(){
        return self::$_runMode === self::mode_test ? true : false;
    }
    public static function isOnlineMode(){
        return self::$_runMode === self::mode_online ? true : false;
    }
    public static function isDevMode(){
        return self::$_runMode === self::mode_dev;
    }

    /**
     * check whether running in development mode
     * @return type
     *
     */

    public static function isDebug(){
        return self::$_isDebug;
    }
    
  
    public static function setDebug($debugMode = false){
        if ($debugMode === true) {
            ini_set('display_errors', true);
            error_reporting(E_ALL ^ E_NOTICE);
        }
        return self::$_isDebug = $debugMode;
    }



    protected static $_debugMsg = array();


    public static function autoload($className) {
        $className = self::_parseClassname($className);

        if (!$className) {
            if(auto::isDebug()){
                throw new exception_base('class name error' . $className, exception_base::TYPE_CLASS_NOT_EXISTS);
            }
        }else{
            $file = self::_getClassPath($className);

            if (!file_exists($file)) {
                //to enable multi autoloader~
//                if(auto::isDebugMode()){
//                    throw new exception_base('class "'.$className.'" file not exist in path: '.$file, exception_base::error);
//                }
            }else{
                require $file;
            }
        }
        return class_exists($className);
    }

    protected static $_frameworkClass = array(
        'cache_abstract'=>true,
        'cache_memcache'=>true,
        'cache_memcached'=>true,
        'cache_redis'=>true,
        'db_abstract'=>true,
        'db_mysqlpdo'=>true,
        'exception_404'=>true,
        'exception_base'=>true,
        'exception_cache'=>true,
        'exception_core'=>true,
        'exception_db'=>true,
        'exception_handler'=>true,
        'exception_i18n'=>true,
        'exception_mysqlpdo'=>true,
        'exception_render'=>true,
        'exception_logger'=>true,
        'exception_msg'=>true,
        'plugin_abstract'=>true,
        'plugin_context'=>true,
        'render_default'=>true,
        'render_abstract'=>true,
        'render_smarty'=>true,
        'logger_abstract'=>true,
        'logger_default'=>true,
    );
    protected static function _getClassPath($className) {
        //framework class with no _ in class name
        if (!strpos($className, '_')) {
            $file = AUTOPHP_PATH . DS . $className . '.php';
            return $file;
        }
        list($dir1, $dir2, $name) = explode('_', $className, 3);
        $dir1 = strtolower($dir1);
        $dir2 = strtolower($dir2);
        if($name===null){
            $dir = $dir1;
            $name  = $dir2;
        }else{
            $dir = $dir1 .DS. $dir2;
            $name = strtolower($name);
        }
        //framework class with _ in class name
        if(isset(self::$_frameworkClass[$className])){
            $file = AUTOPHP_PATH . DS . $dir . DS . $name . '.php';
            return $file;
        }
        //user class in APP_PATH
        $file = APP_PATH . DS . 'classes' . DS . $dir . DS . $name . '.php';
        if(file_exists($file)){
            return $file;
        }
        //user class in self::$_classPath, as public class
        $file = null;
        if(self::$_classPath){
            foreach(self::$_classPath as $path){
                $tmp = $path  . DS . $dir . DS . $name . '.php';
                if(file_exists($tmp)){
                    $file = $tmp;
                    break;
                }
            }
        }

        return $file;
    }

    public static function addClassPath($path){
        self::$_classPath[] = $path;
    }



    /**
     * @desc 此处和 util::baseChars() 重合，因为一般autoload 的时候还没有加载到 util，所以此处故意冗余
     * @param type $str
     * @return type
     */
    protected static function _parseClassname($str) {
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-0123456789';
        $left = trim($str, $base);
        if ($left === '') {
            return $str;
        } else {
            $ret = trim($str, $left);
        }
        return $ret;
    }

    public static function getRuntimeStart(){
        return self::$_runtimeStart;
    }


    public static function shutdownCall() {
        //为什么这个performance  不加到 plugin::call(auto::plugin_shutdown, $ptx) 后面,是因为防止有人在里面执行 exit(),导致这个performance 无法执行记录 
        performance::add(__METHOD__, microtime(true) - self::$_runtimeStart,array('runId'=>self::$_runId,'sapi'=>self::$_sapiName,'uri'=>dispatcher::instance()->getUri(),'runMode'=>self::$_runMode    ));

        $ptx = new plugin_context(__METHOD__,array());
        plugin::call(auto::plugin_shutdown, $ptx);
          
        if($ptx->breakOut!==null){
            return $ptx->breakOut;
        }

        if (!auto::isDebug()) {
            return;
        }
        //do not output debug info when ajax request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return;
        }
        $rn = "\n";


        //total cost
        auto::$_runtimeEnd = microtime(true);
        $msg = array('title' => 'total runtime cost', 'msg' => (auto::$_runtimeEnd - auto::$_runtimeStart));
        array_unshift(self::$_debugMsg, $msg);

        if (auto::isCli()) {
            $output = '
#################### debug info : ####################
(you can turn this off by "auto::setDebug(false)")
                ';
            foreach (self::$_debugMsg as $item) {
                $tstr = '
>>>>>>' . $item['title'] . '>>>>>> ' . $item['msg'];
                $output .= $tstr;
            }
            $output .= '
                ';

        } else {
            $output = '<style>.autophp_debug_span{width:100%;display:block;border-bottom: dashed 1px gray;margin: 3px 0 3px 0;padding:3px 0 3px 0;font-size: 14px;font-family: Arial}</style>
                <fieldset>
                <span  class="autophp_debug_span"><b>debug info : </b> (you can turn this off by "auto::setDebug(false)")</span>';
            foreach (self::$_debugMsg as $item) {
                $tstr = '<span class="autophp_debug_span"><font color=blue>' . $item['title'] . ': </font>' . $item['msg'] . '</span>';
                $output .= $tstr;
            }
            $output .= '</fieldset>';
        }

        echo $output;
    }
    public static function debugMsg($title, $msg) {
        self::$_debugMsg[] = array('title' => $title, 'msg' => $msg);
    }


}