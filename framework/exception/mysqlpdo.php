<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03-18
 * @desc exception_Mysqlpdo
 *
 */
class exception_mysqlpdo extends exception_base {
    const TYPE_CONF_ERROR = 1;
    const TYPE_HIGH_RISK_QUERY = 10;
    const TYPE_INPUT_DATA_ERROR = 21;
    const TYPE_QUERY_ERROR = 22;

}