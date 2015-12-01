#autophp

这是一个使用autoload 的轻量级framework

##d使用案例:


###url dispatcher路由:

the dispatcher supports url analyzation


* 对于2级深度的url 解析:
  - config the depth for dispatcher [example](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L79)
* 3级别深度的url 解析:

#### uri detector:
 see [dispatcher::detectUri()](https://github.com/ricolau/autophp/blob/master/framework/dispatcher.php#L126)

###http 请求参数获取: 
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
 
###类实例化：
本framework 默认支持了 autoload，, 具体定义参照 [autoload defination](https://github.com/ricolau/autophp/blob/master/framework/auto.php#L112), use the [spl_autoload_register()](https://github.com/ricolau/autophp/blob/master/framework/auto.php#L33) function.

比如实例化一个类： $a = new model_user();

###获取 config 配置信息:
使用 [config::get()]() 用来获取config 的数据, 比如 [示例](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L45)

###get cache server:



###database modelorm:



### 数据库 wrapper层:
使用 [db:instance()](https://github.com/ricolau/autophp/blob/master/framework/db.php#L22) 用来获取数据库连接，但是首先，你需要配置一下数据库的别名 [db::addServer()](https://github.com/ricolau/autophp/blob/master/framework/db.php#L18)

* 比如:
 - 数据库的config 文件配置: [https://github.com/ricolau/autophp/blob/master/demo/config/dbmysql.php](https://github.com/ricolau/autophp/blob/master/demo/config/dbmysql.php)
 - 把数据库的config 文件读取，并配置别名: [https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L57](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L57)
 - 获取数据库连接:[https://github.com/ricolau/autophp/blob/master/framework/modelorm.php#L70](https://github.com/ricolau/autophp/blob/master/framework/modelorm.php#L70)

 - 使用 modelorm: 比较推荐使用 modelorm 而不是直接使用db连接，尤其是对于 mysql pdo 的支持



###mysql_pdo support:











持续更新中~
