<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-06-06
 * @desc autophp base exception
 *           any exception may extends of this class
 *
 */
class exception_base extends Exception {

    const type_autophp_has_run = -1;
    const type_magic_quotes_on = -21;
    const type_app_path_not_defined = -2;
    const type_autophp_path_not_defined = -3;

    const type_class_not_exist = -4;

    const error = -10;



    public function __construct($message, $code,$previous = null) {
        parent::__construct($message, $code,$previous);
    }

}