<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc render base
 *
 */
abstract class render_abstract {

    abstract function assign($key, $val);

    abstract function fetch($path);

    abstract function render($path);
}