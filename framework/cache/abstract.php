<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-03
 * @desc cache base
 *
 */
abstract class cache_abstract {
    protected $_confs = array();

    abstract function __construct($alias, $conf);

    abstract function connect();

}