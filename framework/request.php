<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2014-03
 * @desc http request lib
 *
 */
class request {

    protected static $_hasInit = false;
    protected static $_getData = array();
    protected static $_postData = array();
    protected static $_cookie = array();
    protected static $_antiXssMode = true;
    protected static $_addslashesMode = false;

    public static function init($antiXssModeOn = true, $addslashesModeOn = false) {
        if(!self::$_hasInit){
            self::$_hasInit = true;
        }else{
            throw new exception_base('request can not be inited for twice~', exception_base::ERROR);
        }

        self::$_antiXssMode = $antiXssModeOn;
        self::$_addslashesMode = $addslashesModeOn;

        self::$_getData = self::_formatDeep($_GET);
        self::$_postData = self::_formatDeep($_POST);
        self::$_cookie = self::_formatDeep($_COOKIE);
        self::_destroyOriginalData();
    }
    protected function _checkInit(){
        if(!self::$_hasInit){
            self::init(true, true);
        }
    }

    public static function getAntiXssMode() {
        return self::$_antiXssMode;
    }

    public static function getAddslashesMode(){
        return self::$_addslashesMode;
    }


    /**
     * get $_GET parameter
     * @param str $key
     * @param enum $type = str / int
     * @param type $default
     * @return
     */
    public static function get($key, $type = 'str', $default = null) {
        return self::_deal(self::$_getData, $key, $type, $default);
    }

    public static function getAll() {
        self::_checkInit();
        return self::$_getData;
    }

    /**
     *
     * @param str $key
     * @param enum $type = str / int
     * @param type $default
     * @return
     * @desc get post data
     */
    public static function post($key, $type = 'str', $default = null) {
        return self::_deal(self::$_postData, $key, $type, $default);
    }

    /**
     * @desc get all post data
     * @return just as the original $_POST
     */
    public static function postAll() {
        self::_checkInit();
        return self::$_postData;
    }

    /**
     * @desc get array from $_GET
     * @param str $key
     * @return array
     */
    public static function getArray($key) {
        return self::_deal(self::$_getData, $key, 'array', array());
    }

    /**
     * @desc get cookie
     * @param str $key
     * @param enum $type = str/int
     * @param type $default, default return value when got null
     * @return type
     */
    public static function cookie($key, $type = 'str', $default = null) {
        return self::_deal(self::$_cookie, $key, $type, $default);
    }

    public static function cookieAll(){
        self::_checkInit();
        return self::$_cookie;
    }

    /**
     * @desc get post array from $_POST
     * @param str $key
     * @return type
     */
    public static function postArray($key) {
        return self::_deal(self::$_postData, $key, 'array', null);
    }

    /**
     * @desc none $_REQUEST arguments get support~ just for safe reason!
     * @param str $key
     * @param enum $type = str/int
     * @param type $default, default value
     * @return null
     */
//    public static function request($key, $type = 'int', $default = null) {
//        return null;
//    }
    public function request($key, $type = 'int', $default = null){
        return null;
    }

    /**
     * @set request arguments by key/value
     * @param str $key
     * @param type $val
     * @param enum $type = get / post
     * @return type
     */
    public static function setParam($key, $val, $type = 'get') {
        self::_checkInit();
        if ($type == 'get') {
            self::$_getData[$key] = $val;
        } else {
            self::$_postData[$key] = $val;
        }
        return true;
    }

    /**
     * can set params batch by method
     * @param array $data
     * @param enum $type = get / post
     * @return bool
     */
    public static function setParams($data, $type = 'get') {
        self::_checkInit();
        if (!is_array($data))
            return false;

        if ($type == 'get') {
            $_GET = util::array_merge($_GET, $data);
            self::$_getData = util::array_merge(self::$_getData, self::_formatDeep($data));
        } else {
            $_POST = util::array_merge($_POST, $data);
            self::$_postData = util::array_merge(self::$_postData, self::_formatDeep($data));
        }
    }

    protected static function _deal($data, $key, $type, $default) {
        self::_checkInit();
        if ($key == null || !isset($data[$key]))
            return $default;

        switch ($type) {
            case 'int':
                return intval($data[$key]);
                break;

            case 'str' || 'string' || 'array':
                return $data[$key];
                break;

            default:
                return $default;
                break;
        }
    }

    public static function formatText($txt) {
        self::_checkInit();
        $txt = trim($txt);
        if (self::$_antiXssMode) {
            $txt = htmlspecialchars($txt);
        }
        if(self::$_addslashesMode){
            $txt = addslashes($txt);
        }
        return $txt;
    }

    protected static function _formatDeep($data) {
        if (!is_array($data)) {
            return self::formatText($data);
        } else {
            foreach ($data as $key => $val) {
                $key = self::_formatDeep($key);
                $val = self::_formatDeep($val);
                $data[$key] = $val;
            }
            return $data;
        }
    }

    /**
     * @desc destroy original request arguments
     */
    protected static function _destroyOriginalData() {
        $_GET = NULL;
        $_POST = NULL;
        $_REQUEST = NULL;
        $_COOKIE = null;
    }

}