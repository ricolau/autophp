<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-03-18
 * @desc comm i18n pack, i18n = internationalization = 20letters
 *
 *
 * @usage
 * APP_PATH/i18n/zh-cn.php
 * {
 *  return array(
 *      'title'=>'hello title',
 *      'tplDemo'=>'%s world, you are a %s'
 *          );
 * }
 *
 * $key = 'title';
 * i18n::setLanguag('zh-cn');
 * $text = i18n::get($key);
 * //output "hello title"
 *
 * $key = 'tplDemo';
 * $fill = array('hello', 'boy');
 * $text = i18n::vget($key, $fill);
 * //output "hello world, you are a boy"
 */
final class i18n {
    public static $language = null;

    protected static $_languageData = array();

    private static $_confPaths = array();

    public static function addPath($path){
        self::$_confPaths[] = $path;
    }

    public static function get($key, $default = null) {

        if ($key === null)
            return $default;

        if (!isset(self::$_languageData[self::$language])) {
            self::$_languageData[self::$language] = self::_getDataByFilename(self::$language);
        }
        if (isset(self::$_languageData[self::$language])) {
            return isset(self::$_languageData[self::$language][$key]) ? self::$_languageData[self::$language][$key] : $default;
        }
        return self::$_languageData[self::$language][$key] !== null ? self::$_languageData[self::$language][$key] : $default;

    }

    protected static function _getDataByFilename($alias) {
        $alias = util::parseFilename($alias);
        $fileName = $alias . '.php';

        $file = APP_PATH . DS . 'i18n' . DS . $fileName;
        $tmp = util::loadFile($file);
        if($tmp === null && self::$_confPaths){
            foreach(self::$_confPaths as $path){
                $file = $path. DS. $fileName;
                $tmp = util::loadFile($file);
                if($tmp!==null){
                    break;
                }
            }
        }
        if ($tmp === null) {
            throw new exception_i18n('language do not exist of:' . $alias, exception_i18n::type_language_not_exist);
        }
        return $tmp;
    }


    public static function vget($key, $args = array()) {
        $tpl = self::get($key);
        return vsprintf($tpl, $args);
    }

    public static function setLanguage($language = null) {
        if($language !== null){
            self::$language = $language;
        }
        
    }
    public static function getLanguage() {
        return self::$language;
    }


}