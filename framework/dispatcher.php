<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc autophp dispatcher
 *
 */
class dispatcher {

    private static $_instance = null;
    private static $_uri = null;
    private static $_defualtController = 'default';
    private static $_defualtAction = 'default';
    private static $_currentController = null;
    private static $_currentAction = null;

    public static function instance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function dispatch() {
        self::_httpRoute();
        /*
         *
        if (!auto::isCliMode()) {
            self::_httpRoute();
        } else {
            self::_cliRoute();
        }
        */
        return self::$_instance;
    }

    private static function _httpRoute() {
        $uri = self::$_instance->getUri();
        $uri = trim($uri, '/');
        if ($uri == '') {
            $controllerName = self::$_defualtController;
            $actionName = self::$_defualtAction;
        } else {
            $us = explode('/', $uri);
            $controllerName = array_shift($us);
            $actionName = array_shift($us);
            $actionName = $actionName !== null ? $actionName : self::$_defualtAction;

            if (count($us) > 0) { // no default action
                $data = array();
                $total = count($us);
                for ($i = 0; $i < $total; $i += 2) {
                    $k = $us[$i];
                    $v = $us[$i + 1];
                    $data[$k] = $v;
                }
                //anti-sql inject and anti-XSS sets would be run in request::setParams()
                request::setParams($data, 'get');
            }
        }
        $controllerName = util::baseChars($controllerName);
        $actionName = util::baseChars($actionName);

        self::$_instance->setControllerName($controllerName);
        self::$_instance->setActionName($actionName);
        return;
    }
/*
    private static function _cliRoute() {
        return null;
    }
*/
    public function setUri($uri) {
        self::$_uri = $uri;
        return self::$_instance;
    }

    public function getUri() {
        if (null === self::$_uri) {
            self::$_uri = self::detectUri();
        }
        return self::$_uri;
    }

    public static function detectUri() {
        if (!empty($_SERVER['SCRIPT_URL'])) {
            $uri = $_SERVER['SCRIPT_URL'];
        } else {
            // as: /m/test?saadf=esdf
            if (isset($_SERVER['REQUEST_URI'])) {
                $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if (false !== $request_uri) {
                    $uri = $request_uri;
                }
            } else {
                $uri = $_SERVER['PHP_SELF'];
            }
        }
        return $uri;
    }

    public function setDefaultController($controller = 'default') {
        self::$_defualtController = $controller;
        return self::$_instance;
    }

    public function setDefaultAction($action = 'default') {
        self::$_defualtAction = $action;
        return self::$_instance;
    }

    public function getControllerName() {
        return self::$_currentController;
    }

    public function setControllerName($controller = 'default') {
        self::$_currentController = strtolower($controller);
        return self::$_instance;
    }

    public function getActionName() {
        return self::$_currentAction;
    }

    public function setActionName($action = 'default') {
        self::$_currentAction = strtolower($action);
        return self::$_instance;
    }

    public function run() {
        plugin::run('before_run');

        auto::isDebugMode() && $_debugMicrotime = microtime(true);

        $className = 'controller_' . self::$_currentController;

        if (!class_exists($className)) {
            throw new exception_404('controller not exist: ' . $className, exception_404::TYPE_CONTROLLER_NOT_EXIST);
        }
        $actionName = self::$_currentAction . 'Action';
        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $className . '->' . $actionName . ')', 'start ---->>>>');

        $class = new ReflectionClass($className);
        if ($class->isAbstract()) {
            throw new exception_404('can not run abstract class: ' . $className, exception_404::TYPE_CONTROLLER_IS_ABSTRACT);
        }
        $method = $class->getMethod($actionName);
        if (!$method || !$method->isPublic()) {
            throw new exception_404('no public method ' . $method . ' exist in class:' . $className, exception_404::TYPE_ACTION_NOT_PUBLIC);
        }
        $method->invoke($class->newInstance());
        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $className . '->' . $actionName . ')', 'end,<<<<---- cost ' . (microtime(true) - $_debugMicrotime) . 's');

        plugin::run('after_run');
    }

}