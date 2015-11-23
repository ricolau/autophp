#autophp


light php framework with autoload strategy



##demos for use:

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


others to coming~
