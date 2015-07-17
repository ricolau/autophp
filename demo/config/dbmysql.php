<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc default config file
 *
 */
return array(
    'default' => array(
        'type' => 'mysqlpdo', //定义数据库类型，将直接调用 db_mysqlpdo 驱动
        'conf'=>array(
            db_mysqlpdo::type_server_slave => array(
                'host' => 'localhost',
                'user' => 'database_username',
                'pwd' => 'database_password',
                'dbname' => 'database_name',
                'charset' => 'utf8'
            ),
            db_mysqlpdo::type_server_master => array('host' => 'localhost', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'test', 'charset' => 'utf8')

        ),

    ),
    'microspace' => array(
        'type' => 'mysqlpdo',
        'conf'=>array(
            db_mysqlpdo::type_server_slave => array('host' => 'localhost', 'user' => 'root', 'pwd' => '', 'port' => 3306, 'dbname' => 'database_name2'),
            db_mysqlpdo::type_server_master => array()
        )

    ),
    // 微空间套餐方案库
    'entStock' => array(
        'type' => 'mysqlpdo',
        'conf'=>array(
            db_mysqlpdo::type_server_slave => array('host' => 'localhost', 'user' => 'database_username_slave', 'pwd' => '222222', 'dbname' => 'database_name22', 'charset' => 'utf8'),
            db_mysqlpdo::type_server_master => array('host' => 'localhost', 'user' => 'database_username', 'pwd' => '111111', 'dbname' => 'database_name1', 'charset' => 'utf8')

        )

    )
);