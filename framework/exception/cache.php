<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc exception_404
 *         when 404 action occured
 *
 */
class exception_cache extends exception_base {
    const TYPE_SERVER_NOT_EXIST = 1;
    const TYPE_DRIVER_NOT_EXIST = 2;
    const TYPE_SERVER_CONNECTION_ERROR = 3;
    const TYPE_ARGUMENT_ERROR = 4;
}