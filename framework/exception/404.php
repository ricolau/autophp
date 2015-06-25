<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc exception_404
 *         when 404 action occured
 *
 */
class exception_404 extends exception_base {
    const type_controller_not_exist = -1;
    const type_action_not_public = -2;
    const type_controller_is_abstract = -3;

}