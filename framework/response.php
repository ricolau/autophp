<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc http response package
 *
 */
class response {

    /**
     * reference of php original "setcookie",
     * @param type $name
     * @param type $value
     * @param type $expire
     * @param type $path
     * @param type $domain
     * @param type $secure
     * @param type $httponly
     * @return type
     */
    public static function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public static function header($header) {
        header($header);
    }

    public static function headerRedirect($url, $seconds = 0) {
        if (!headers_sent() && $seconds<=0) {
            self::header('Location: ' . $url);
        } else {
            self::output('<meta http-equiv="refresh" content="'.$seconds.'; url=' . $url . '" />');
        }
        exit;
    }
    public static function redirect($url, $seconds = 3) {
        return self::headerRedirect($url, $seconds);
    }

    public static function outputJson($data) {
        return self::output(json_encode($data));
    }

    public static function output($str) {
        echo $str;
    }


}