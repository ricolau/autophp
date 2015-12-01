#autophp

[简体中文](https://github.com/ricolau/autophp/blob/master/README_zh-cn.md)

light php framework with autoload strategy

##demos for use:


###url dispatcher:

the dispatcher supports url analyzation


* level 2 depth url support:
  - config the depth for dispatcher [example](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L79)
* level 3 depth url support:

#### uri detector:
 see [dispatcher::detectUri()](https://github.com/ricolau/autophp/blob/master/framework/dispatcher.php#L126)

###get http request parameters: 
(mainly equest to oroginal php $_GET or $_POST, but may use addslashes() or  htmlspecialchars() deal for input data, depends on the initial setting of framework~)
see initial example: [https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L42](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L42)

* examples for use:<br />
 - request::get('name'); //equals to $_GET['name']<br />
 - request::get('id','int');//equals to intval($_GET['id'])<br />
 - request::getAll();   //equals to $_GET<br /><br />
 - request::post('name');//equals to $_POST['name']<br />
 - request::post('id','int');//equals to intval($_POST['id'])<br />
 - request::postAll();   //equals to $_POST

###class instantiaion:
for app use this framework, class instantiation can be based on autoload, default supported, refers to [autoload defination](https://github.com/ricolau/autophp/blob/master/framework/auto.php#L112), use the [spl_autoload_register()](https://github.com/ricolau/autophp/blob/master/framework/auto.php#L33) function.

you can use it as $a = new model_user();

###get config data:
use [config::get()]() to get configured data, as [example](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L45)

###get cache server:



###database modelorm:



### database wrapper:
use [db:instance()](https://github.com/ricolau/autophp/blob/master/framework/db.php#L22) to get database connection, but first, you should config it with alias to class db using [db::addServer()](https://github.com/ricolau/autophp/blob/master/framework/db.php#L18)

* etc:
 - config database: [https://github.com/ricolau/autophp/blob/master/demo/config/dbmysql.php](https://github.com/ricolau/autophp/blob/master/demo/config/dbmysql.php)
 - add config to database wrapper class db: [https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L57](https://github.com/ricolau/autophp/blob/master/demo/htdocs/index.php#L57)
 - get databse connection:[https://github.com/ricolau/autophp/blob/master/framework/modelorm.php#L70](https://github.com/ricolau/autophp/blob/master/framework/modelorm.php#L70)

 - use modelorm: mostly you may use modelorm for database package especially mysql_pdo,



###mysql_pdo support:












others to coming~
