<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-3-31
 * @desc autophp auto, check running enviroment and more closer to base layer
 *
 */
final class auto {

    const version = '1.6.1';
    const author = 'ricolau<ricolau@qq.com>';
    const mode_http = 0;
    const mode_cli = 1;

    private static $_runtimeStart = 0;
    private static $_runtimeEnd = 0;

    private static $_isCliMode = false;
    private static $_hasRun = false;
    private static $_isDebugMode = false;
    private static $_isDevMode = false;
    
    private static $_classPath = array();

    public static function hasRun() {
        return self::$_hasRun;
    }

    public static function run() {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

	spl_autoload_register(array('auto', 'autoload'));
        register_shutdown_function(array('auto', 'shutdownCall'), array());

        if (self::$_hasRun) {
            throw new exception_base('auto can not run twice!', exception_base::type_autophp_has_run);
        }
		


        self::$_hasRun = true;
        if (php_sapi_name() == 'cli') {
            self::$_isCliMode = true;
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

        
    }

    /**
     * get sapi mode
     * @return type
     */
    public static function isCliMode() {
        return self::$_isCliMode;
    }
    
    public static function isDevMode(){
        return self::$_isDevMode;
    }
    
    public static function setDevMode($isDev = false){
        self::$_isDevMode = $isDev;
    }

    /**
     * check whether running in development enviroment
     * @return type
     */
    public static function isDebugMode() {
        if (is_null(self::$_isDebugMode)) {
            self::$_isDebugMode = false;
        }
        return self::$_isDebugMode;
    }

    /**
     * set debug mode
     * @return type
     */
    public static function setDebugMode($debugMode = false) {
        if ($debugMode === true) {
            ini_set('display_errors', true);
            error_reporting(E_ALL ^ E_NOTICE);
        }
        return self::$_isDebugMode = $debugMode;
    }

    public static function register_shutdown_function($callback = null){
        if(!$callback){
            return;
        }
        self::$shutdownFunction = $callback;
    }


    public static $shutdownFunction = null;

    protected static $_debugQueue = array();


    public static function autoload($className) {
        $className = self::_parseClassname($className);

        if (!$className) {
            if(auto::isDebugMode()){
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
        if(self::$shutdownFunction){
            call_user_func(self::$shutdownFunction);
            return;
        }
        if (!auto::isDebugMode()) {
            return;
        }
        //do not output debug info when ajax request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return;
        }
        $rn = "\n";

        $hasNotRunPlugins = plugin::getHasNotRunPlugin(plugin::type_before_run);
        if ($hasNotRunPlugins) {
            $msg = array('title' => '<font color=red><b>Warning: some plugins NOT RUN(maybe "exit()" used! in your program?)</b></font>', 'msg' => var_export($hasNotRunPlugins, true));
            array_unshift(self::$_debugQueue, $msg);
        }
        $hasNotRunPlugins2 = plugin::getHasNotRunPlugin(plugin::type_after_run);
        if ($hasNotRunPlugins2) {
            $msg = array('title' => '<font color=red><b>Warning: some plugins NOT RUN(maybe "exit()" used in your program?)</b></font>', 'msg' => var_export($hasNotRunPlugins2, true));
            array_unshift(self::$_debugQueue, $msg);
        }


        //total cost
        auto::$_runtimeEnd = microtime(true);
        $msg = array('title' => 'total runtime cost', 'msg' => (auto::$_runtimeEnd - auto::$_runtimeStart));
        array_unshift(self::$_debugQueue, $msg);

        if (auto::isCliMode()) {
            $output = '
#################### debug info : ####################
(you can turn this off by "auto::setDebugMode(false)")
                ';
            foreach (self::$_debugQueue as $item) {
                $tstr = '
>>>>>>' . $item['title'] . '>>>>>> ' . $item['msg'];
                $output .= $tstr;
            }
            $output .= '
                ';

        } else {
            $output = '<style>.autophp_debug_span{width:100%;display:block;border-bottom: dashed 1px gray;margin: 3px 0 3px 0;padding:3px 0 3px 0;font-size: 14px;font-family: Arial}</style>
                <fieldset>
                <span  class="autophp_debug_span"><b>debug info : </b> (you can turn this off by "auto::setDebugMode(false)")</span>';
            foreach (self::$_debugQueue as $item) {
                $tstr = '<span class="autophp_debug_span"><font color=blue>' . $item['title'] . ': </font>' . $item['msg'] . '</span>';
                $output .= $tstr;
            }
            $output .= '</fieldset>';
        }

        echo $output;
    }
    public static function dqueue($title, $msg) {
        self::$_debugQueue[] = array('title' => $title, 'msg' => $msg);
    }
    
    public static function dqueueExport(){
        return self::$_debugQueue;
    }
    

}
