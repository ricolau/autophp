<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc plugin abstract class
 *
 */
abstract class plugin_abstract {

    abstract function call($tag,plugin_context &$ptx);

}