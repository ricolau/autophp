<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2015-07-03
 * @desc logger base
 *
 */
abstract class logger_abstract {

    abstract function add($level, $msg);
    /**
     * return levels for current logger
     */
    abstract function levels();

}