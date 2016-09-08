


文件名，classname  全小写，多个单词间 下划线 分隔
function name 驼峰命名，多个单词间不分隔，通过驼峰区分




####################################################################
               autophp general introduction file

                last update at 2014-07-20

                by ricolau@qq.com

#################### 部署方式##############################

1. 复制一份 autophp 目录以及其目录下的所有代码到指定路径
2. 建立一个http 请求入口文件，如 htdocs/index.php，并配置，请参考本demo 中的此文件
3. 建立apache 虚拟机，可以参考 docs/apache.conf.txt 中内容
4. 开发代码， 参考本demo 中的代码结构即可



#################### 兼容性##############################
在没有 classname 和 functionname 冲突的情况下，本框架与任何代码兼容。
主要请care  关于autophp autoload 机制限定下的类名classname 命名规范不要与其它framework代码冲突



#################### http 请求入口文件 ##############################


建议放在htdocs/index.php 下
rewrite 规则，请参考  apache.conf.txt 文件

###################关于 controller 和 action ########################

本框架由于采用了 autoload，因此框架下开发的代码可以相对灵活，但还是建议使用MVC结构进行开发




################### 代码规范（或读 code-format.txt ）########################

1. 文件名和类名：
	由于文件名、文件夹名、类名三者相关。因此需参考如 类名class Controller_Default {} 对应文件为 controller/default.php。
	注意：文件名全部小写，但文件内的类名单词首字母大写
2. function命名：
	驼峰式命名，首单词小写，后续单词首字母大写，如：function checkUserLogin(){}。
	注意：类的私有方法，命名需要在前面加下划线。如 private function _checkUserLogin(){} 。
3. 变量命名：与 2 function 命名一样




################### 禁止事项 与 建议事项 ########################
禁止：
	1. 禁止在 controller  中直接调用 cache   和 db 相关封装。正确的方式是  control 层中“只能”调用 model、view、tools 等

建议与注意：
1. 不要使用global 变量
	不建议使用 global 语句引用全局变量，项目庞大时，全局变量容易被修改或难以查找问题等。
	对于此类需求，可使用 autophp.0 新引入的 util 替代。 如： util::set($name, $value),    util::get($name)


为何要禁止？
	其实禁止事项，完全可以通过修改框架和代码结构来强制约束，
	但autophp 为了开发的高效，面向的是中高级engineer，不希望因此丧失自身的灵活性有点。
	因此，希望通过建议的方式禁止使用！~



###################debug 模式 ########################

autophp 的debug 模式和相关信息：

debug 模式的打开和关闭：
	可以通过 auto::setDebugMode(true ) 打开，通过 auto::setDebugMode(false )关闭

debug 模式的判断：
	auto::isDebugMode()，  返回  bool  型

debug 的报错级别（打开时会自动设置为以下级别，即：warning 及以上级别的错误会提示）：
	ini_set('display_errors', true);
	error_reporting(E_ALL ^ E_NOTICE);

debug 信息手动增加（队列操作）：
	debug 采用 auto 内置队列方式，通过 auto::dqueue($title, $message) 向队尾增加一个成员。

debug 信息输出：
	在 auto::isDebugMode() == true  且程序运行结束时输出队列内所有信息，开发时不需要care。
注意：
	debug信息的输出，“只有”在 auto::isDebugMode() == true 时才会输出。





debug 手动布码方式和示例：
        
方法：
	auto::dqueue($title, $message)
示例：
	开始处： auto::isDebugMode() &&  rico::elapseStart($key);
	结尾处：auto::isDebugMode() && auto::dqueue(__METHOD__, 'cost '.rico::elapseEnd($key).'s of arguments: '.var_export($arguments, true));

示例中的变量：
	$key 或固定字符串，作用域需要在  “开始 和 结尾处之间” 都有效！！！
	$arguments 可以把自己想打印的传入参数放在这里


关于 class  rico：
	rico 主要是提供了用于debug 的各种工具和 方式



###################  分特性介绍 ########################

主要特性和考虑如下：
1. http 请求单入口文件
2. autoload 实现类加载，统一类class 的命名规范，并尽量做到完全面向对象化
3. mvc 结构，但由于基于统一规范的命名化，所以调用的层次更松散自由。方便非mvc 的结构调用，只需遵守autoload 命名规范即可


@ framework：	框架及所在目录完全独立可剥离，独立于业务之外的任意目录。只需在入口文件中定义 AUTOPHP_PATH 即可
@ http 请求单入口：由  htdocs/index.php 入口
@ daemon和crontab 请求单入口：
	autophp 的daemon 需要执行如  php daemon/demo.php 这样，CLI 模式下 dispatcher 废弃，不可用，请参考  daemon/loader.php 的配置

@ 静态文件：		如 js/css 放在 htdocs/static 目录下。需要配合 apache rewrite使用
@ autoload：	加载框架的class 和 项目的class 通过 autoload 实现，不必再到处写 require
@ 面向对象： framework 定义的调用方式，采取完全的面向对象开发
@ config配置：	通过 config::get() 读取 config 目录下的配置文件
@ i18n:			i18n::get()
@ http request： 通过 request class 实现所有对 request 的处理
@ http response：通过 response class 实现所有对 response 的处理和输出
@ filter 插件机制：可以定义在 controller 执行前 before_run 和执行后 after_run 时刻加入filter 执行。可以编写自己的 filter 继承 TFilter_Abstract 即可
@ render 实现：	 当前渲染主要是controller 层会用到，默认为 render_default。即 php形式的代码渲染。
		render 封装位于 autophp/trender 下

		可以通过 controller 及其继承的子类中 controller::setRenderEngine($renderEngineObject) 方式重设渲染引擎。
		通过这样，autophp可以非常灵活的使框架支持多引擎渲染。
@smarty 的封装支持
		当前框架已经基本提供了 smarty 的支持，将 smarty放在 autophp/trender/smarty.class.php 中即可，
		之后调用 render_smarty()->getSmartyObj() 即可获得 smarty 原型实例。
		但如果想要接入 controller 中通用（controller::setRenderEngine($renderEngineObject)），还是需要 render_smarty() 本身的实例，而非 smarty 原型

@ cookie get: request::cookie($name, $type = );
@ cookie set: response::setCookie() // arguments same with php original setcookie function

@ mysql	：	使用pdo扩展来操作mysql
@面向数据库的ORM封装，orm , 通过传递2个参数简单创建数据库表映射对象，更简洁的实现 CURD 操作
