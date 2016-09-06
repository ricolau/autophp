<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc exception_404
 *         when 404 action occured
 *
 */
class exception_db extends exception_base {
    const type_conf_error = 0;
    const type_server_not_exist = -1;
    const type_driver_not_exist = -2;
}