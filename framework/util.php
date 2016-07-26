<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-03-31
 * @desc autophp utils, just some tools here~
 *
 */
final class util {
    private static $_vars;
    
    private static $_classSingleton = array();

    /**
     * @description to set a value for a special key
     * @param string $key
     * @param data $value
     */
    public static function set($key, $value) {
        self::$_vars[$key] = $value;
    }

    /**
     * @description to get a value by key
     * @param string $key
     * @return type
     */
    public static function get($key) {
        return self::$_vars[$key];
    }

    public static function incr($key, $step = 1) {
        self::$_vars[$key] += $step;
    }

    public static function decr($key, $step = 1) {
        self::$_vars[$key] -= $step;
    }




    /**
     * parse string for filename, mainly for safe concerns
     * @param str $str
     * @return str
     */
    public static function parseFilename($str, $onlyCharacterBase = false) {
        return self::baseChars($str, $onlyCharacterBase);
    }

    /**
     * 此处不要用正则，正则表达式效率太差
     * 用这个方法，得到的不是正则替换的期望值，但是还是可以用的
     * @param type $str
     * @return type
     */
    public static function baseChars($str, $onlyCharacterBase = false) {
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/\_-0123456789';
        if ($onlyCharacterBase) {
            $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $left = trim($str, $base);
        if ($left === '') {
            return $str;
        } else {
            //$ret = str_replace($left,'',$str);
            $ret = '';//return empty value, 2015-11-16
        }
        return $ret;
    }

    public static function loadFile($filepath) {
        if (file_exists($filepath)) {
            return include $filepath;
        }
        return null;
    }

    public static function array_merge($ar1, $ar2) {
        if (is_array($ar1) && is_array($ar2)) {
            return array_merge($ar1, $ar2);
        } elseif (is_array($ar1) && !is_array($ar2)) {
            return $ar1;
        } elseif (!is_array($ar1) && is_array($ar2)) {
            return $ar2;
        }
        return null;
    }


    //Miscellaneous reads [ˌmɪsə'leɪniəs]
    public static function loadMiscellaneous() {

        if (!function_exists('a')) {

            function a() {
                echo 'yeah, dear, im RicoLau<ricolau@qq.com>, i leave this!';
            }

        }
        //can be used instead of  rico::dump($a, $b ...), as d($a, $b)
        if (!function_exists('d')) {

            function d() {
                $args = func_get_args();
                call_user_func_array(array('rico', 'dump'), $args);
            }

        }
        if (!function_exists('de')) {

            function de() {
                $args = func_get_args();
                call_user_func_array(array('rico', 'dump'), $args);
                exit;
            }

        }
        //can be used instead of config::get($key), as  c('default.domain')  ;
        if (!function_exists('config')) {

            function config($key) {
                if (class_exists('config') && auto::hasRun()) {
                    return config::get($key);
                }
                return null;
            }

        }
 
        //can be used instead of  echo htmlspecialchars()
        if (!function_exists('e') && !function_exists('eg')) {
            function e($string, $flags = ENT_COMPAT, $encoding = 'UTF-8', $double_encode = true) {
                echo htmlspecialchars($string, $flags, $encoding, $double_encode);
            }
            function eg($string, $flags = ENT_COMPAT, $encoding = 'UTF-8', $double_encode = true) {
                return htmlspecialchars($string, $flags, $encoding, $double_encode);
            }
        }

        //can be used instead of i18n::get($key) or  i18n::vget($key, $fillData);
        if (!function_exists('lang')) {
            function lang($key, $args = null) {
                if (!class_exists('i18n') || !auto::hasRun()) {
                    return null;
                }
                if ($args === null) {
                    return call_user_func_array(array('i18n', 'get'), array($key));
                }
                return call_user_func_array(array('i18n', 'vget'), array($key, $args));
            }

        }

        if (!function_exists('url')) {
            function url($path,$args = array()) {
                return url::get($path, $args);
            }
        }

        if (!function_exists('domain')) {
            function domain($domain = null) {
                if ($domain === null) {
                    return url::getDomain();
                } else {
                    return url::setDomain($domain);
                }
            }

        }

    }
    /**
     * singleton class factory~
     * @param type $className
     * @return \className
     */
    public static function classFactory($className){
        if(!$className){
            return null;
        }
        if(isset(self::$_classSingleton[$className]) && self::$_classSingleton[$className]){
            return self::$_classSingleton[$className];
        }
        if(class_exists($className)){
            self::$_classSingleton[$className] =  new $className();
            return self::$_classSingleton[$className];
        }
        return null;
        
    }



}
