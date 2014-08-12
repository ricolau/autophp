<?php

/**
 * @description popular development tools
 *                just use for debug please!
 * @author by ricolau<ricolau@foxmail.com>
 * @version 2014-02-13
 * @usage
 *      rico::export()
 *      rico::dump(*...);
 *
 */


final class rico {

    private static $_timeline;
    private static $_vars;
    private static $_r = "\n";

    public static function set($key, $value) {
        self::$_vars[$key] = $value;
    }

    public static function get($key) {
        return self::$_vars[$key];
    }

    public static function incr($key, $step = 1) {
        self::$_vars[$key] += $step;
    }

    public static function decr($key, $step = 1) {
        self::$_vars[$key] -= $step;
    }

    public static function export($data) {
        return self::_dump($data, '', false);
    }

    public static function elapseStart($id) {
        if ($id === null) {
            return false;
        }

        return self::$_timeline[$id] = microtime(true);
    }

    public static function elapseEnd($id) {
        if ($id === null) {
            return false;
        }

        return self::$_timeline[$id] = microtime(true) - self::$_timeline[$id];
    }

    public static function elapseGet($id, $isPrint = false) {
        if (true !== $isPrint) {
            return self::$_timeline[$id];
        }
        self::dump(self::$_timeline[$id]);
    }

    public static function elapseDump($isPrint = true) {
        if (true !== $isPrint) {
            return self::$_timeline;
        }
        self::dump(self::$_timeline);
    }

    /**
     * @static
     * @waring currently can not be used to dump() variableds or functions of reference~`~! may cause the stack overflow
     * @return mixed
     */
    public static function dump() {
        $args = func_get_args();
        $nums = func_num_args();
        if ($nums <= 0) {
            self::_dump(NULL, '', true);
            return;
        }
        array_map(array('self', '_dump'), $args, array_fill(0, $nums, ''), array_fill(0, $nums, true));
    }

    private static function _dump($obj, $name = '', $isPrint = true) {
        $str = $pre = '';
        if (!is_array($obj)) {
            if (is_string($obj)) {
                $str .= 'string(' . strlen($obj) . ') "' . $obj . '"';
            } elseif (is_int($obj)) {
                $str .= 'int(' . $obj . ')';
            } elseif (is_float($obj)) {
                $str .= 'float(' . $obj . ')';
            } elseif (is_bool($obj)) {
                $str .= 'bool(' . var_export($obj, true) . ')';
            } elseif (is_object($obj)) {
                $str .= 'object(' . var_export($obj, true) . ')';
            } else {
                $str .= var_export($obj, true);
            }
        } else {
            $str .= 'array(' . count($obj) . '){' . self::$_r;
            foreach ($obj as $key => $value) {
                $str .= $name . '["' . $key . '"]=>' . self::_dump($value, $name . '["' . $key . '"]', false);
            }
            $str .= '}' . self::$_r;
        }
        if (true !== $isPrint) {
            return $str . self::$_r;
        }
        echo $str . self::$_r;
    }

    public static function array2code($array, $name = '', $isPrint = true) {
        if ($name === '')
            $name = '$GLOBALS';

        if (!is_array($array))
            return 'NO_ARRAY';

        $str = self::_array2line($array, $name, false);

        if (!$isPrint)
            return $str;
        else
            echo $str;
    }

    private static function _array2line($obj, $name, $isPrint = false) {
        $str = $pre = '';
        if (!is_array($obj)) {
            if (is_string($obj)) {
                $str .= '\'' . $obj . '\'';
            } elseif (is_bool($obj)) {
                $str .= var_export($obj, true);
            } elseif (is_object($obj)) {
                $str .= '\'' . serialize($obj) . '\'';
            } else {
                $str .= $obj;
            }
            $str .= ';';
        } else {
            foreach ($obj as $key => $value) {
                if (!is_array($value)) {
                    $str .= $name . '[\'' . $key . '\'] = ' . self::_array2Line($value, $name . '[\'' . $key . '\']', false);
                    $str .= self::$_r;
                } else {
                    $str .= self::_array2Line($value, $name . '[\'' . $key . '\']', false);
                }
            }
        }
        if (true !== $isPrint) {
            return $str;
        }
        echo $str . self::$_r;
    }

}
