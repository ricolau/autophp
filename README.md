#autophp

[>>English Introduction](https://github.com/ricolau/autophp/blob/master/README_en.md)

[>>简体中文版介绍](https://github.com/ricolau/autophp/blob/master/README.md)

这是一个使用autoload 的轻量级framework


### 概念定义：
 * app，是指一个有唯一入口的http 的site，一般指一个网站。
 * framework，是指 framework 目录下的所有文件构成。


### 新建一个app的步骤：
 * 定义 AUTOPHP_PATH，framework 的磁盘路径，比如：define('AUTOPHP_PATH', '/usr/local/php/framework');
 * 定义 APP_PATH, app 的磁盘路径，比如 define('AUTOPHP_PATH', dirname(APP_PATH) . '/framework');
 * require 'auto.php';//load 框架代码
 * auto::run();//app入口启动方式：
 * 关闭php的 magic_quota 通过 php.ini 
 * 创建classes 目录的 controller、model 等目录，比如 [https://github.com/ricolau/autophp/tree/master/demo/classes](https://github.com/ricolau/autophp/tree/master/demo/classes)
 * 完整的示例请见[https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php)


### app 目录树解释：
 * classes  ，class定义
 - classes/controller,  controller class定义文件
 - classes/model， model class定义
 - classes/plugin， 插件class定义
 * config , config 目录，用于放配置文件
 * daemon ，用于放置守护进程或者crontab 等脚本
 * htdocs， http请求默认的目录
 - htdocs/index.php http 请求默认的唯一入口，所有的url 都rewrite 到这个文件上来
 * i18n， 多语言支持文件夹，用于放语言包，形式基本和 config 类似
 * view， 模板层，所有的模板放在这里
 - view/slot slot文件目录，用于存储slot 内容
 - view/template 模板文件目录
 - view/template/index/index.php  ，示例用，默认为 controller=index & action=index 时的模板引用
 

## 实际应用：

### 类实例化：
 * 本framework 默认支持了 autoload，, 具体定义参照 [autoload defination](https://github.com/ricolau/autophp/blob/master/framework/auto.php#L112), use the [spl_autoload_register()](https://github.com/ricolau/autophp/blob/master/framework/auto.php#L33) function.
 * 比如实例化一个类： $a = new model_user();
 * 优缺点：
 - 优点：大家都能想到，就是可以按照实例化的方式去实例化了。也可以直接用现代化的ide 比如netbeans、phpstorm 之类的来点击到类方法定义了。
 - 缺点：无法强制控制class 的实例化场合，比如无法强制控制只能在 model层才能调用cache、导致control 等层都可以随意调用cache，甚至model 和view 层也可以调用 request 获取用户请求参数。这个就要靠代码规范来约束了。
 - 缺点2：无法实现工厂模式，关于类的工厂化，可以参考 util::set() 等来实现。

### url 路由相关:
* dispatcher 路由：
 - path_deep，表示url 解析深度。
 - url 解析，默认为2层级的url 深度解析，也可以定制为3层级的url 深度解析支持（最大3级）。
 - url 解析深度设置： [示例](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L79)
  
* 对于path_deep = 2（层）级深度的url 解析:
 - 链式解析调用：<br />
    dispatcher::instance()->           //获取实例
            setPathDeep(dispatcher::path_deep2)->           //设置url解析深度，2层<br />
            setDefaultController('index')->           //设置默认的controller ，当controller 为空的时候执行 <br /> 
            setDefaultAction('index')->           //设置默认的action ，action 为空的时候执行 <br />
            setUri($uri)->           //设置uri，可以随意设置任意 uri，注意要设置为  /controller/action 
类似的格式才会被解析正确 <br />
            dispatch()->           //开始路由，获取到底要执行哪个controller 和 action，准备就绪 <br />
            run();     ·                     //开始执行上一步路由后的 controller 和 action <br />

* 对于 path_deep = 3（层）级别深度的url 解析:
 - 3层比2层，会在controller 之上多了一层module 层（注意不是model！）。对应的 classes、view/template 等都需要多出来一层！
 - 链式解析调用：<br />
  dispatcher::instance()->           //获取实例
            setPathDeep(dispatcher::path_deep3)->           //设置url 解析深度为3层 <br />
            setDefaultModule('index')->                //设置默认的module
            setDefaultController('index')->           //设置默认的controller ，当controller 为空的时候执行 <br /> 
            setDefaultAction('index')->           //设置默认的action ，action 为空的时候执行 <br />
            setUri($uri)->           //设置uri，可以随意设置任意 uri，注意要设置为  /controller/action  类似的格式才会被解析正确 <br />
            dispatch()->           //开始路由，获取到底要执行哪个controller 和 action，准备就绪 <br />
            run();                          //开始执行上一步路由后的 controller 和 action <br />
* 404异常，url 找不到对应的controller、action 用来执行时，
 - 会抛出异常exception_404，可以在调用dispatcher::run() 的时候捕获。
* 大于path_deep 的url 处理：
 - 对于大于 path_deep 的url，dispatcher 会按照key/value 的方式放到 $_GET 中，也就是 request::get() 的方式可以获取。
 - 比如当path_deep=2时：<br />
    /view/detail/id/251/name/rico   的url 会被解析到 controller=view, action=detail 中执行。然后将 id=251&name=rico当作 http url get的方式获取的参数放到 request 中，通过 request::get('id'),  request::get('name') 的方式可以获取到。

* 其他方法：
 - 获取当前正在执行的 module（只针对3层目录结构有效）：dispatcher::instance()->getModuleName();
 - 获取当前正在执行的 controller：dispatcher::instance()->getControllerName();
 - 获取当前正在执行的 action：dispatcher::instance()->getActionName();
* 高级方法：
 - dispatcher::setUri() ，在当前版本中，框架没有提供比较优雅的router 等正则方案，但是并不意味着不能支持优雅的实现。<br />
比如： /view/51 ，可以通过php coding的代码来解析此url 并转化为 /view/detail/id/51 的路径，通过 setUri() 的形式设置，然后dispatcher 解析并执行。

### uri detector:
 * 用来获取request uri 的方法，请见： [dispatcher::detectUri()](https://github.com/ricolau/autophp/blob/master/framework/dispatcher.php#L126)

### http 请求参数获取: 
(基本和原生的 $_GET,  $_POST 类似，但是对输入数据会加入 addslashes() 和  htmlspecialchars() 处理, 这主要看你在引用框架时是否做了设定)
查看引用框架的初始化设定： [https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L42](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L42)

* 示例：<br />
 - request::get('name'); // 相当于 $_GET['name']<br />
 - request::get('id','int');//相当于 intval($_GET['id'])<br />
 - request::getAll();   //相当于 $_GET<br /><br />
 - request::post('name');//相当于 $_POST['name']<br />
 - request::post('id','int');//相当于 intval($_POST['id'])<br />
 - request::postAll();   //相当于 to $_POST
 - request::cookieAll();   //相当于 to $_COOKIE
 


### 使用config 类获取配置:
* 使用 [config::get()]() 用来获取config 的数据, 比如config::get('default.sitename'); 将会获取 APP_PATH/config/default.php<br /> 中下标为 sitename 的数组键值，若没有将返回null；[示例](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L45)<br />
* config 新特性，多目录的config 支持：从 version 1.5 开始，可以支持多个config 目录的配置了，（默认支持 APP_PATH./config/* 目录下的文件） 。<br />
 - 如果想支持更多文件，可以在htdocs/index.php 中加入更多config 目录。添加多个config<br /> 目录：如config::addPath('/usr/local/phpenv/conf/') 、config::addPath('/usr/local/phpenv/conf2/') 等。<br />
 - 优先级：会优先从 APP_PATH./config/ 目录去寻找，找不到则按照配置顺序依次寻找其他目录。<br />

###get cache server:



###database modelorm:



### 数据库 wrapper层:
 * 使用 [db:instance()](https://github.com/ricolau/autophp/blob/master/framework/db.php#L22) 用来获取数据库连接，但是首先，你需要配置一下数据库的别名 [db::addServer()](https://github.com/ricolau/autophp/blob/master/framework/db.php#L18)

* 比如:
 - 数据库的config 文件配置: [https://github.com/ricolau/autophp/blob/master/demo/config/dbmysql.php](https://github.com/ricolau/autophp/blob/master/demo/config/dbmysql.php)
 - 把数据库的config 文件读取，并配置别名: [https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L57](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L57)
 - 获取数据库连接:[https://github.com/ricolau/autophp/blob/master/framework/modelorm.php#L70](https://github.com/ricolau/autophp/blob/master/framework/modelorm.php#L70)

 - 使用 modelorm: 比较推荐使用 modelorm 而不是直接使用db连接，尤其是对于 mysql pdo 的支持



###mysql_pdo support:




###debug mode:
* 可以通过以下方式打开或者关闭，
$debugMode = true;
auto::setDebugMode($debugMode);
* 建议放在 auto::run()  之前，这个是全局开关，打开后每个http request 下方会print 出来很多debug 信息。（在ajax 请求里不会打印debug 信息）






持续更新中~
