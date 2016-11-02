<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-11-02
 * @desc exception_mysqlpdo
 *
 */
class exception_mysqlpdo extends exception_base {
    const type_conf_error = -1;
    const type_high_risk_query = -10;
    const type_input_data_error = -21;
    const type_query_error = -22;

}