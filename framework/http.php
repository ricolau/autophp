<?php

/**
 * @description popular development for http request
 * @author by ricolau<ricolau@qq.com>
 * @version 2016-08-09
 *
 */
class http {

    /**
     * $args = array(
            url,
            params = array(),
            method = GET( or POST),
            multi = false(or true),
            extheaders = array(),
            cookie = '', full str,
            referer = null,
            return_header = null,
            return_rich_info = null,
            useragent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2041.4 Safari/537.36'
            proxy = null,
            timeout = 10,
      );
     */
    public static function requestHigh($args) {
        if (!function_exists('curl_init')){
            throw new exception_base('curl module not exist~!');
        }
        if (!$args || !$args['url']) {
            return false;
        }
        $_debugMicrotime = microtime(true);
        extract($args);

        $method = $method ? : 'GET';
        $timeout = $timeout ? : 3;

        if (!function_exists('curl_init')){
            exit('Need to open the curl extension');
        }

        $method = strtoupper($method);
        $ci = curl_init();
        $default_ua = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2041.4 Safari/537.36';
        $useragent = $user_agent ? : $default_ua;
        curl_setopt($ci, CURLOPT_USERAGENT, $useragent);
        
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, ($return_header || $return_rich_info) ? true : false);

        if ($proxy) {
            curl_setopt($ci, CURLOPT_PROXY, $proxy);
        }

        if ($cookie){
            curl_setopt($ci, CURLOPT_COOKIE, $cookie);
        }

        if ($referer){
            curl_setopt($ci, CURLOPT_REFERER, $referer);
        }

        $headers = (array) $extheaders;
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params)) {
                    if ($multi) {
                        foreach ($multi as $key => $file) {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    } else {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
                    }
                }
                break;
            case 'PUT':
            case 'PATCH':
                curl_setopt($ci, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method);
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                            . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ci);
        if ($return_rich_info) {
            $info = curl_getinfo($ci);
            $ret = array();
            $ret['info'] = $info;
            $ret['response']['header'] = substr($response, 0, $info['header_size']);
            $ret['response']['content'] = substr($response, $info['header_size']);
            
            $response = $ret;
        }

        curl_close($ci);
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('args'=>$args,'ret'=>performance::summarize($response,__METHOD__) )) ;

        return $response;
    }

// end function 

    /**
     * 
     * @param string $url
     * @param string $params
     * @param type $method
     * @param type $timeout
     * @param type $cookie
     * @param type $referer
     * @param type $extheaders
     * @param type $multi
     * @param type $proxy
     * @return type
     */
    public static function request($url, $params = array(), $method = 'GET', $timeout = 3, $cookie = '', $referer = null, $extheaders = array(), $multi = false, $proxy = null) {
        if (!function_exists('curl_init')){
            throw new exception_base('curl module not exist~!');
        }
        $_debugMicrotime = microtime(true);

        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:11.0) Gecko/20100101 Firefox/11.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);

        if ($proxy) {
            curl_setopt($ci, CURLOPT_PROXY, $proxy);
        }

        if ($cookie){
            curl_setopt($ci, CURLOPT_COOKIE, $cookie);
        }

        if ($referer){
            curl_setopt($ci, CURLOPT_REFERER, $referer);
        }


        $headers = (array) $extheaders;
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params)) {
                    if ($multi) {
                        foreach ($multi as $key => $file) {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    } else {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, is_array($params)?http_build_query($params):$params);
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                            . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ci);
        curl_close($ci);
        
        $tmpArgs = array('url'=>$url,'method'=>$method,'params'=>$params,'timeout'=>$timeout,'cookie'=>$cookie,'referer'=>$referer,'extheaders'=>$extheaders,
                    'multi'=>$multi,'proxy'=>$proxy);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('args'=>$tmpArgs,'ret'=>performance::summarize($response,__METHOD__) ) ) ;

        return $response;
    }

}
