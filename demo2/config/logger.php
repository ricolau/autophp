<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2015-07-03
 * @desc  log config file
 *
 */

$path = APP_PATH.DS.'log';


return array(
    'default'=>array(
        'fatal'=>array('path'=>$path,),
        'error'=>array('path'=>$path,),
        'warning'=>array('path'=>$path,),
        'info'=>array('path'=>$path,'rotation'=>'Y-m-d'),
        'notice'=>array('path'=>$path,),
    ),
);