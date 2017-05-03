<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2017-5-3
 * @desc autophp dispatcher
 *
 */
class dispatcher {

    private static $_instance = null;
    private static $_path = null;
    private static $_defaultModule = 'default';
    private static $_defaultController = 'default';
    private static $_defaultAction = 'default';
    private static $_currentModule = null;
    private static $_currentController = null;
    private static $_currentAction = null;
    private static $_pathDeep = self::path_deep2;

    const path_deep3 = 3;
    const path_deep2 = 2;
    
    const plugin_before_run = 'dispatcher_before_run';
    const plugin_after_run = 'dispatcher_after_run';
    
    
    protected function __construct() {
        
    }

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
          if (!auto::isCli()) {
          self::_httpRoute();
          } else {
          self::_cliRoute();
          }
         */
        return self::$_instance;
    }

    private static function _httpRoute() {
        $path = self::$_instance->getPath();
        $path = trim($path, '/');
        $moduleName = null;
        if ($path == '') {
            if (self::$_pathDeep == self::path_deep3) {
                $moduleName = self::$_defaultModule;
            }
            $controllerName = self::$_defaultController;
            $actionName = self::$_defaultAction;
        } else {
            $us = explode('/', $path);

            if (self::$_pathDeep == self::path_deep3) {
                $moduleName = array_shift($us);
                if (count($us) > 0) {
                    $controllerName = array_shift($us);
                    if (count($us) > 0) {
                        $actionName = array_shift($us);
                    } else {
                        $actionName = self::$_defaultAction;
                    }
                } else {
                    $controllerName = self::$_defaultController;
                    $actionName = self::$_defaultAction;
                }
            } else {
                $controllerName = array_shift($us);
                $actionName = array_shift($us);
                $actionName = $actionName !== null ? $actionName : self::$_defaultAction;
            }


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
        $moduleName = util::baseCharsAndNumbers($moduleName);
        $controllerName = util::baseCharsAndNumbers($controllerName);
        $actionName = util::baseCharsAndNumbers($actionName);

        self::$_instance->setModuleName($moduleName);
        self::$_instance->setControllerName($controllerName);
        self::$_instance->setActionName($actionName);
        return;
    }

    /*
      private static function _cliRoute() {
      return null;
      }
     */

    /**
     * map original path to a real path wanted!
     * @param array $rules as array()
     * @param string $path
     */
    public function mapPath($rules, $path){
        $rules = array(
            '/list/[id:\d+]'=>'/list/list/index',
            '/list/[id:\w+]'=>'/list/list/<id>',
            
            '*'=>'/index/404',
        );
    }
    
    /**
     * set path for dispatch
     * @param string $path
     * @return type
     */
    public function setPath($path) {
        self::$_path = $path;
        return self::$_instance;
    }

    /**
     * get current path
     * @return type
     */
    public function getPath() {
        if (null === self::$_path) {
            self::$_path = self::detectPath();
        }
        return self::$_path;
    }

    public function setPathDeep($deep = self::path_deep2) {
        self::$_pathDeep = ($deep == self::path_deep2) ? self::path_deep2 : self::path_deep3;
        return self::$_instance;
    }

    public function getPathDeep() {
        return self::$_pathDeep;
    }

    public static function detectPath() {
        if (!empty($_SERVER['SCRIPT_URL'])) {
            $path = $_SERVER['SCRIPT_URL'];
        } else {
            // as: /m/test?saadf=esdf
            if (isset($_SERVER['REQUEST_URI'])) {
                $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if (false !== $request_uri) {
                    $path = $request_uri;
                } elseif ($_SERVER['REQUEST_URI'] && strpos($_SERVER['REQUEST_URI'], '?') !== false) {
                    $path = strstr($_SERVER['REQUEST_URI'], '?', true);
                }
            } else {
                $path = $_SERVER['PHP_SELF'];
            }
        }
        return $path;
    }

    public function setDefaultModule($module = 'default') {
        self::$_defaultModule = $module;
        return self::$_instance;
    }

    public function getControllerModule() {
        return self::$_defaultModule;
    }

    public function getModuleName() {
        return self::$_currentModule;
    }

    public function setModuleName($moduleName = 'default') {
        self::$_currentModule = $moduleName;
        return self::$_instance;
    }

    public function setDefaultController($controller = 'default') {
        self::$_defaultController = $controller;
        return self::$_instance;
    }

    public function setDefaultAction($action = 'default') {
        self::$_defaultAction = $action;
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
        
        $ptx = new plugin_context(__METHOD__,array());
        plugin::call(dispatcher::plugin_before_run, $ptx);

        $_debugMicrotime = microtime(true);

        if (self::$_pathDeep == self::path_deep3) {
            $className = 'controller_' . self::$_currentModule . '_' . self::$_currentController;
        } else {
            $className = 'controller_' . self::$_currentController;
        }

        if (!class_exists($className)) {
            throw new exception_404('controller not exist: ' . $className, exception_404::type_controller_not_exist);
        }
        $actionName = self::$_currentAction . 'Action';

        $class = new ReflectionClass($className);
        if ($class->isAbstract()) {
            throw new exception_404('can not run abstract class: ' . $className, exception_404::type_controller_is_abstract);
        }

        $method = $class->getMethod($actionName);
        if (!$method || !$method->isPublic()) {
            throw new exception_404('no public method ' . $method . ' exist in class:' . $className, exception_404::type_action_not_public);
        }
        $method->invoke($class->newInstance());
        
        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('controller'=>$className,'action'=>$actionName)) ;

        plugin::call(dispatcher::plugin_after_run, new plugin_context(__METHOD__,array()));
    }

}
