<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc exception_Handler base and demo
 * @desc 本class 只做示例用，请自己定义自己的handler
 *
 */
class exception_handler {

    public static function topDeal($e) {
        if (!auto::isDebug() && !auto::isCli()) {
            //response::top404();
            echo 'error occured!';
        } else {
            //debugmode or in sapi mode
            var_dump($e->getMessage(), $e->getCode());
        }
    }

}