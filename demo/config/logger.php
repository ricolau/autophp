<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc default config file
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