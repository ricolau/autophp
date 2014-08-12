<?php

/**
 * @author ricolau<ricolau@foxmail.com>
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

    public static function headerRedirect($url) {
        if (!headers_sent()) {
            self::header('Location: ' . $url);
        } else {
            self::output('<meta http-equiv="refresh" content="0; url=' . $url . '" />');
        }
        exit;
    }

    public static function outputJson($data) {
        return self::output(StrFormat::jsonEncode($data));
    }

    public static function output($str) {
        echo $str;
    }


}