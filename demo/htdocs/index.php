<?php

ini_set('display_errors', 1);
error_reporting(7);
/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2013-03
 * @desc http request entrance file
 * 
 */
//============================  定义 app 路径（必须）================================
define('APP_PATH', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

//============================ 定义框架地址（必须）============================
define('AUTOPHP_PATH', dirname(APP_PATH) . '/framework');


try {
    //============================ 开始加载框架！============================
    require AUTOPHP_PATH . DS . 'auto.php';
    //auto::run() 当前主要负责加载autoload 和一些常量定义的检测
    //关掉或开启debugMode，此处可以不处理，默认为关闭！
    $debugMode = true;
    auto::setDebugMode($debugMode);

    auto::run();

    //设置时区
    date_default_timezone_set("Asia/Shanghai");


    //============================ 对 plugin 进行定义 ============================
    //如果需要，定义一些（个数不限）在 action 执行之前预执行 和 后执行的程序
    plugin::add('plugin_wbinit', plugin::type_before_run);
    plugin::add('plugin_backEnd', plugin::type_after_run);
    //plugin::add('Plugin_Requestend', plugin::TYPE_AFTER_RUN);
    //============================ 对request 的数据进行处理 ============================
    //（必要）此处主要是为了提高一些获取效率，进行一次读入，同时也对原生 $_POST 等做了销毁
    $antiXssModeOn = true; //是否开启对 $_POST,  $_GET,  $_COOKIE 的防跨站处理
    $addslashesModeOn = true;
    request::init($antiXssModeOn, $addslashesModeOn);
    
    //add log conf
    $logconf = config::get('log.default');
    log::addLogger(new logger_default($logconf));


    //============================ 定义一些快捷的function 别名之类，此处非必须 ============================
    util::loadMiscellaneous();


    //============================ 开始定义database 和 cache相关资源 ============================
    //定义database server
    $dbconf = config::get('dbmysql'); //读取配置文件，注意配置文件中的格式
    foreach ($dbconf as $alias => $conf) {
        db::addServer($alias, $conf);
    }
    //定义cache server
    $cacheConf = config::get('cache'); //读取配置文件，注意配置文件中的格式
    foreach ($cacheConf as $alias => $conf) {
        cache::addServer($alias, $conf);
    }

    //============================ 开始路由和执行controller 层中 ============================
    //检测并获取到uri，当然也可以自己指定
    $uri = dispatcher::detectUri();
    //$uri = '/';
    /*
     * 可以自己随意接受一些参数然后设置 uri 进行转发
      $controller = request::get('c');
      $action = request::get('a');
      $uri = '/'.$action.'/'.$action;

     */

    // 开始路由和执行
    dispatcher::instance()->//获取实例
            setPathDeep(dispatcher::path_deep2)->
            setDefaultModule('index')->//设置默认的controller ，当controller 为空的时候执行
            setDefaultController('index')->//设置默认的controller ，当controller 为空的时候执行
            setDefaultAction('index')->//设置默认的action ，action 为空的时候执行
            //setControllerName('index')->setActionName('index')->

            setUri($uri)->//设置uri，可以随意设置任意 uri，注意要设置为  /controller/action  类似的格式才会被解析为对应的controller中
            dispatch()->//开始路由，获取到底要执行哪个controller 和 action，准备就绪
            run();                          //开始执行上一步路由后的 controller 和 action
    //dispatcher::instance()->setControllerName('index')->setActionName('index')->run();
    //============================ 几种常见的异常 ============================
//} catch (TException_404 $e) {               //当 controller 和 action 不存在时，捕获到的 404 错误。daemon 状态下应该没有
//    tools_exceptionhandler::topDeal404($e);
} catch (ReflectionException $e) {          //一般来说，这种情况不太可能发生
    tools_exceptionhandler::topDeal404($e);
} catch (Exception $e) {                    //做个最后的兼容
    tools_exceptionhandler::topDeal($e);
}
