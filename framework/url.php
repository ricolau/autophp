<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2015-07
 * @desc url
 *
 */
class url {
    protected static $_domain = '';
    protected static $_urlBase = '';
    protected static $_urlPatternRest = false;
    
    public static function setDomain($domain){
        self::$_domain = $domain;
    }
    public static function getDomain(){
        return self::$_domain;
    }
    
    public static function getUrlBase(){
        return self::$_urlBase;
        
    }
    public static function setUrlBase($urlBase){
        self::$_urlBase = $urlBase;
    }
    
    public static function switchUrlPatternRest($on = true){
        self::$_urlPatternRest = $on;
    }
    
    public static function get($path, $args = array()){
        $url = '';
        if (self::$_domain != '') {
            $url .= 'http://' . self::$_domain;
        }
        $url .= self::$_urlBase . ($path{0}==='/' ? $path : '/'.$path);
        if (is_string($args) && $args !== '') {
            $url .= '?' . $args;
        } elseif ($args && is_array($args)) {
            $tmp = http_build_query($args);
            if (self::$_urlPatternRest) {
                $tmp = str_replace(array('&', '='), '/', $tmp);
                $url .= '/' . $tmp;
            } else {
                $url .= '?' . $tmp;
            }
        }
        return $url;
        
    }
    
}

