<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-013
 * @desc tools for exception handler
 * 
 */
class tools_exceptionhandler
{

    public static function topDeal($e)
    {
        if (!auto::isDebugMode() && !auto::isCliMode())
        {
            echo 'sorry, something bad happened!';
        } else
        {
            //debugmode or in sapi mode
            var_dump($e->getMessage(), $e->getCode());
        }
    }

    public static function topDeal404($e)
    {
        if (!auto::isDebugMode() && !auto::isCliMode())
        {
            echo '404, page not found!';
        } else
        {
            //debugmode or in sapi mode
            var_dump($e->getMessage(), $e->getCode());
        }
    }

}