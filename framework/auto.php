<?php
/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-06-15
 * @desc autophp core, check running enviroment and more closer to base layer
 * @link https://github.com/ricolau/autophp
 * 
 * 
 * @comment  本框架特性描述
 * 
 * ########### 基础和通用特性 ##############
 * ## MVC 分层;
 * ## 单一入口;
 * ## View 层独立的模板引擎,不使用smarty, 为了追求高效的执行效率.
 * ## i18n 多语言支持;
 * ## pdo 支持;
 * ## 支持 develop/test/online 三种模式,可以配置三种配置问价;
 * 
 * 
 * ########### 独立特性 ##############
 * ## 支持url 的二级或 三级 path 深度解析,即:支持 controler/action 和  module/controller/action 两种方式的url;
 * ## 全面的autoload,支持无限中间层扩展,MVC to  DMVC  MVVM等任何数量的层次扩展支持;
 * ## 完善的 performance 性能log布点,能够获取到任何的 db/cache/http 等外部资源消耗的请求参数/耗时/返回结果,也支持用户自定义的布点,通过plugin 支持自定义记录log和过滤. 便于各种报警和监控;
 * ## php 的强类型支持, 独立的struct 定义,可完美替代php array, 使得php 也支持强类型.
 * ## 高效独立的 orm 封装,链式操作,并全面支持struct 方式的query; orm 具有自动支持主从等众多特性;
 * ## 完善的 storage driver 分层, 灵活支持 memcache/ memcached,  redis/codis 等多种存储的驱动,实现单例资源连接,调用方不需要关心底层连接.
 * ## 支持钩子,定义为plugin,提供framework 级别的钩子,用户也可以自定义plugin;(db 错误/cache各种错误 等都有plugin 可以用来定义,增加报警或重试, 且一个action 可以注册多个钩子)
 * ## 所有class 都支持单例模式,在base class 中实现了 base::instance()  和  base::singleton() 方法,可全局支持任何class 单例;
 * ## db 驱动支持多种方式: 读写主从/单实例/随机的负载均衡 等;
 * ## url "路由过程" 高度可定制化; dispatcher 执行流程简洁清晰,方便支持各种自定义的路由分发和执行过程;
 * ## 完善的安全防护, 对request 数据进行安全过滤处理, 销毁原生的  $_GET/$_POST/$_COOKIE 
 * ## 先进强大的debug 模式,打开 auto::setDebug(true) 之后,可以看到所有的 performance 布点信息,极其方便开发过程中的调试!
 *
 * ## 简洁,性能超越同类框架一倍以上;
 */
class auto {

    const version = '2.2.8';
    
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
        
        if(!self::$_isCli){
            performance::setSummarizeMode(performance::summarize_mode_fully);
        }
        
        performance::add(__METHOD__, 0, array('runId'=>self::$_runId,'sapi'=>self::$_sapiName,'reqPath'=>(!self::$_isCli ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : $_SERVER['PHP_SELF'])    ));

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
        'cache_codis'=>true,
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
        'exception_plugin'=>true,
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
        performance::add(__METHOD__, microtime(true) - self::$_runtimeStart,array('runId'=>self::$_runId,'sapi'=>self::$_sapiName,'runPath'=>dispatcher::instance()->getPath(),'runMode'=>self::$_runMode    ));

        $performance = performance::dump();

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
//        $msg = array('title' => 'total runtime cost', 'msg' => (auto::$_runtimeEnd - auto::$_runtimeStart));
        
        $pfsize = count($performance);
        if (auto::isCli()) {
            $output = '
#################### performance info (of last '.$pfsize.' items): ####################
(you can turn this off by "auto::setDebug(false)")
                ';
            foreach ($performance as $item) {
                $item['msg'] = 'timecost '.$item['timecost'].", $rn info:".util::export($item['info']);

                $tstr = '
>>>>>>' . $item['tag'] . '>>>>>> ' . $item['msg'];
                $output .= $tstr;
            }
            $output .= '
                ';

        } else {
            $output = '<style>.autophp_debug_span{width:100%;display:block;border-bottom: dashed 1px gray;margin: 3px 0 3px 0;padding:3px 0 3px 0;font-size: 14px;font-family: Arial}</style>
                <fieldset>
                <span  class="autophp_debug_span"><b>debug info(of last '.$pfsize.' items): </b> (you can turn this off by "auto::setDebug(false)")</span>';
            foreach ($performance as $item) {
                $item['msg'] = 'timecost '.$item['timecost'].', <br /><pre>info:'.util::export($item['info']).'</pre>';
                $tstr = '<span class="autophp_debug_span"><font color=blue>' . $item['tag'] . ': </font>' . $item['msg'] . '</span>';
                $output .= $tstr;
            }
            $output .= '</fieldset>';
        }

        echo $output;
    }


}
