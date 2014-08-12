<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc autophp base exception
 *           any exception may extends of this class
 *
 */
class exception_base extends Exception {

    const TYPE_AUTOPHP_HAS_RUN = -1;
    const TYPE_MAGIC_QUOTES_ON = -21;
    const TYPE_APP_PATH_NOT_DEFINED = -2;
    const TYPE_AUTOPHP_PATH_NOT_DEFINED = -3;

    const ERROR = -10;



    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }

}