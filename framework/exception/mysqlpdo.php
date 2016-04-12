<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-03-18
 * @desc exception_Mysqlpdo
 *
 */
class exception_mysqlpdo extends exception_base {
    const type_conf_error = -1;
    const type_high_risk_query = -10;
    const type_inpiut_data_error = -21;
    const type_query_error = -22;

}