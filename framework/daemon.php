<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc daemon abstract
 *
 */
abstract class daemon {

    public function __construct() {
        if (!auto::isCliMode()) {
            throw new exception_base('cannot run daemon with http request!', -1);
        }
        $this->_init();
    }

    public function _init() {

    }

}