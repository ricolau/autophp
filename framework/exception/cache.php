<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc exception_404
 *         when 404 action occured
 *
 */
class exception_cache extends exception_base {
    const type_server_not_exist = 1;
    const type_driver_not_exist = 2;
    const type_server_connection_error = 3;
    const type_argument_error = 4;
    const type_memcache_not_exist = 5;
}