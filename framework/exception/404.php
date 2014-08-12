<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc exception_404
 *         when 404 action occured
 *
 */
class exception_404 extends exception_base {
    const TYPE_CONTROLLER_NOT_EXIST = -1;
    const TYPE_ACTION_NOT_PUBLIC = -2;
    const TYPE_CONTROLLER_IS_ABSTRACT = -3;

}