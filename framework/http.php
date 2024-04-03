<?php

/**
 * @description popular development for http request
 * @author by ricolau<ricolau@qq.com>
 * @version 2023-11-23
 *
 */
class http {

    const arg_url = 'url';
    const arg_method = 'method';
    const arg_proxy = 'proxy';
    const arg_proxy_port = 'proxyport';
    const arg_proxy_type = 'proxytype';
    const arg_user_agent = 'user_agent';

    const arg_connect_timeout = 'connect_timeout';
    const arg_timeout = 'timeout';

    const arg_return_rich_info = 'return_rich_info';

    const arg_retry_times_on_failure = 'retry_times';



    public static function getResponseCode($response){
        if(!$response){
            return false;
        }
        if(!isset($response['info']) || !isset($response['info']['http_code'])){
            return false;
        }
        return $response['info']['http_code'];
    }

    public static function getResponseError($response){
        if(!$response){
            return false;
        }
        if(!isset($response['info']) || !isset($response['info']['error'])){
            return false;
        }
        return $response['info']['error'];
        

    }

    public static function getResponseInfo($response){
        if(!$response){
            return false;
        }
        if(!isset($response['info'])){
            return false;
        }
        return $response['info'];

    }
    public static function getResponseBody($response){
        if(!$response){
            return false;
        }
        if(!isset($response['response']) || !isset($response['response']['content'])){
            return false;
        }
        return $response['response']['content'];
    }
    public static function is200Response($response){
        if(!$response){
            return false;
        }
        $code = self::getResponseCode($response);
        return ($code == 200);
    }
    public static function is30xResponse($response){
        if(!$response){
            return false;
        }
        $code = self::getResponseCode($response);
        //if($code == 301 || $code == 302 || $code ==307){
        return ($code >=300 && $code<=399);

    }
    public static function is50xResponse($response){
        $code = self::getResponseCode($response);
        return ($code >=500 && $code<=599);

    }
    public static function is404Response($response){
        if(!$response){
            return false;
        }
        $code = self::getResponseCode($response);
        return ($code == 400 || $code == 404);
    }

    public static function is403Response($response){
        if(!$response){
            return false;
        }
        $code = self::getResponseCode($response);
        return $code == 403;
    }



    
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
            user_agent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2041.4 Safari/537.36'
            proxy = null,
            proxyport = null
            proxytype  = CURLPROXY_SOCKS5 /  CURLPROXY_HTTP 
            connect_timeout = 3,
            timeout = 10,
            retry_times=0, //retry times when fail
      );
     */

    public static function requestHigh($args) {
        if (!function_exists('curl_init')){
            throw new exception_base('curl module not exist~!', -1);
        }
        if (!$args || !$args['url']) {
            return false;
        }
        extract($args);
        $request_times_max = (isset($retry_times) ?$retry_times:0)+1;
        while($request_times_max>0){
            $request_times_max--;

            $_debugMicrotime = microtime(true);
            
            $method = isset($method) ? $method : 'GET';
            $timeout = isset($timeout) ?$timeout  : 2;

            if (!function_exists('curl_init')){
                exit('Need to open the curl extension');
            }

            $method = strtoupper($method);
            $ci = curl_init();
            $default_ua = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2041.4 Safari/537.36';
            $useragent =  isset($user_agent) ?$user_agent : $default_ua;
            curl_setopt($ci, CURLOPT_USERAGENT, $useragent);
            $return_header = isset($return_header) ? $return_header : false;
            $return_rich_info = isset($return_rich_info) ? $return_rich_info : false;

            curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, (isset($connect_timeout) && $connect_timeout>0)? $connect_timeout : 3 );
            curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ci, CURLOPT_HEADER, ($return_header || $return_rich_info) ? true : false);

            $proxy = isset($proxy) ? $proxy : '';
            if ($proxy && $proxyport) {
                curl_setopt($ci, CURLOPT_PROXY, $proxy);
                curl_setopt($ci, CURLOPT_PROXYPORT, $proxyport);
                curl_setopt($ci, CURLOPT_PROXYTYPE, $proxytype ? $proxytype :  CURLPROXY_HTTP );  // CURLPROXY_SOCKS5
            }
            $cookie = isset($cookie) ? $cookie : '';
            if ($cookie){
                curl_setopt($ci, CURLOPT_COOKIE, $cookie);
            }
            $referer = isset($referer) ? $referer : '';
            if ($referer){
                curl_setopt($ci, CURLOPT_REFERER, $referer);
            }

            $extheaders = isset($extheaders) ? $extheaders : [];
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

            $success = true;
            $response = curl_exec($ci);
            if ($return_rich_info) {
                $info = curl_getinfo($ci);
                $error = curl_error($ci);
                if($error){
                    $info['error'] = $error;
                }

                $ret = array();
                $ret['info'] = $info;
                $ret['response']['header'] = $info['header_size'] ? substr($response, 0, $info['header_size']): false;
                $ret['response']['content'] = $info['header_size'] ? substr($response, $info['header_size']): false;
                
                $success = ($info['http_code'] > 0) ? true : false;
                $response = $ret;
            }else{
                $success = ($response!==false) ? true :false;
            }

            curl_close($ci);
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('args'=>$args,'ret'=>performance::summarize($response,__METHOD__) )) ;
            if($success){//break while retry
                break;
            }
        }//end while
        return $response;
    }

// end function 



    public static function isTimeout($response){
        if(!$response){
            return false;
        }
        if(!isset($response['info']) || !isset($response['info']['error'])){
            return false;
        }
        return self::isTimeoutError($response['info']['error']);
    }

    public static function isTimeoutError($errMsg){
        if(!$errMsg){
            return false;
        }
        //$is = preg_match('/\stimed\sout/i' , $errMsg);
        $is = self::isConnectTimeoutError($errMsg) || self::isOperationTimeoutError($errMsg);
        return $is;
    }

    public static function isOperationTimeoutError($errMsg){
        if(!$errMsg){
            return false;
        }
        $is = preg_match('/Operation\stimed\sout/i' , $errMsg);
        return $is;
    }

    public static function isConnectTimeoutError($errMsg){
        if(!$errMsg){
            return false;
        }
        $is = preg_match('/Connection\stimed\sout/i' , $errMsg);
        return $is;
    }

    public static function isResetByPeerError($errMsg){
        if(!$errMsg){
            return false;
        }

        $is = preg_match('/Connection\sreset\sby\speer/i' , $errMsg);
        return $is;

    }
    public static function isConnectError($errMsg){
        if(!$errMsg){
            return false;
        }

        $is = (strpos('Failed to connect to' , $errMsg)!==false)
        || (strpos('Couldn\'t connect to server' , $errMsg)!==false);
        return $is;

    }
    /**
     * 
     * @param string $url
     * @param string $params
     * @param string $method
     * @param int $timeout
     * @param string $cookie
     * @param string $referer
     * @param array $extheaders
     * @param array $multi
     * @param string $proxy
     * @return string
     */
    public static function request($url, $params = array(), $method = 'GET', $timeout = 3, $cookie = '', $referer = null, $extheaders = array(), $multi = false) {
        if (!function_exists('curl_init')){
            throw new exception_base('curl module not exist~!',-1);
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
                    'multi'=>$multi);
        
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('args'=>$tmpArgs,'ret'=>performance::summarize($response,__METHOD__) ) ) ;

        return $response;
    }

}
