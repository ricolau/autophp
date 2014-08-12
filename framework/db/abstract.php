<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc database base
 *
 */
abstract class db_abstract {


    abstract function connect($type = null);

}