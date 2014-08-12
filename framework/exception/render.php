<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03
 * @desc exception_auto
 *        generally throw from auto only!
 *
 */
class exception_render extends exception_base {
    const TYPE_TPL_NOT_EXIST = 1;
    const TYPE_SLOT_NOT_EXIST = 2;
    const TYPE_RENDER_ENGIN_NOT_EXIST = 3;
}