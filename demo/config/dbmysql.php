<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc default config file
 *
 */

$pdo_server_options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT=>2,
);


$dev = array(
    'default' => array(
        'type' => 'mysqlpdo', //定义数据库类型，将直接调用 db_mysqlpdo 驱动
        'balance'=>db::balance_single,
        'servers'=>array(
            db::server_type_slave => array(
                'host' => '127.0.0.1',
                'user' => 'root',
                'pwd' => 'root',
                'dbname' => 'test',
                'port'=>3306,
                'charset' => 'utf8',
                'options'=>$pdo_server_options
            ),
            db::server_type_master => array('host' => '127.0.0.1', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test', 'charset' => 'utf8','options'=>$pdo_server_options)

        ),

    ),
    'test' => array(
        'type' => 'mysqlpdo',
        'balance'=>db::balance_random,
        'servers'=>array(
            array('host' => '127.0.0.1', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test','options'=>$pdo_server_options),
            array('host' => '127.0.0.1', 'user' => 'user_test', 'pwd' => '', 'port' => 3306, 'dbname' => 'test','options'=>$pdo_server_options),
        )
    ),
    
    'singledb' => array(
        'type' => 'mysqlpdo',
        'balance'=>db::balance_single,
        'servers'=>array(
            array('host' => '127.0.0.1', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test','options'=>$pdo_server_options),
        )

    ),
    'entStock' => array(
        'type' => 'mysqlpdo',
        'balance'=>db::balance_single,
        'servers'=>array(
            db::server_type_slave => array('host' => '127.0.0.1', 'user' => 'database_username_slave', 'pwd' => '222222', 'dbname' => 'database_name22', 'charset' => 'utf8','options'=>$pdo_server_options),
            db::server_type_master => array('host' => '127.0.0.1', 'user' => 'database_username', 'pwd' => '111111', 'dbname' => 'database_name1', 'charset' => 'utf8','options'=>$pdo_server_options)

        )

    )
);

$test = $dev;

$online = array(
    'default' => array(
        'type' => 'mysqlpdo', //定义数据库类型，将直接调用 db_mysqlpdo 驱动
        'balance'=>db::balance_master_slave,
        'servers'=>array(
            db::server_type_slave => array(
                'host' => '127.0.0.1',
                'user' => 'root',
                'pwd' => '',
                'dbname' => 'test',
                'port'=>3306,
                'charset' => 'utf8',
                'options'=>$pdo_server_options
            ),
            db::server_type_master => array('host' => '127.0.0.1', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test', 'charset' => 'utf8','options'=>$pdo_server_options)

        ),

    ),
    'test' => array(
        'type' => 'mysqlpdo',
        'balance'=>db::balance_random,
        'servers'=>array(
            array('host' => '127.0.0.1', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test','options'=>$pdo_server_options),
            array('host' => '127.0.0.1', 'user' => 'user_test', 'pwd' => '', 'port' => 3306, 'dbname' => 'test','options'=>$pdo_server_options),
        )
    ),
    
    'singledb' => array(
        'type' => 'mysqlpdo',
        'balance'=>db::balance_single,
        'servers'=>array(
            array('host' => '127.0.0.1', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test','options'=>$pdo_server_options),
        )

    ),
    'entStock' => array(
        'type' => 'mysqlpdo',
        'servers'=>array(
            db::server_type_slave => array('host' => '127.0.0.1', 'user' => 'database_username_slave', 'pwd' => '222222', 'dbname' => 'database_name22', 'charset' => 'utf8'),
            db::server_type_master => array('host' => '127.0.0.1', 'user' => 'database_username', 'pwd' => '111111', 'dbname' => 'database_name1', 'charset' => 'utf8')

        )

    )
);



if(auto::isDevMode()){
    return $dev;
}elseif(auto::isTestMode()){
    return $test;
}else{
    return $online;
}
