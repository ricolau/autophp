<?php

/**
 * @author ricolau<ricolau@foxmail.com>
 * @version 2012-04
 * @desc controller abstract
 *
 */
abstract class controller {

    protected $_render = null;

    /**
     * controller entrance function
     */
    public function __construct() {

        $this->_init();
    }

    protected function _init() {
        $this->_render = new render_default();
    }

    public function setRenderEngine($obj) {
        $this->_render = $obj;
    }

    public function render($path = '') {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        if ($path) {
            $this->_render->render($path);
            auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $controller . DS . $action . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');
            return;
        }
        $dir = dispatcher::instance()->getDirName();
        $controller = dispatcher::instance()->getControllerName();
        $action = dispatcher::instance()->getActionName();

        if (dispatcher::instance()->getPathDeep() == dispatcher::PATH_DEEP3) {
            $path = $dir . DS . $controller . DS . $action;
        } else {
            $path = $controller . DS . $action;
        }
        $this->_render->render($path);

        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $controller . DS . $action . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');
    }

    public function slot($slot, $isDisplay = false) {
        return $this->_render->slot($slot, $isDisplay);
    }

    public function fetch($path = '') {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        if ($path) {
            $ret = $this->_render->fetch($path);
            auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $controller . DS . $action . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');

            return $ret;
        }
        $dir = dispatcher::instance()->getDirName();
        $controller = dispatcher::instance()->getControllerName();
        $action = dispatcher::instance()->getActionName();

        if (dispatcher::instance()->getPathDeep() == dispatcher::PATH_DEEP3) {
            $path = $dir . DS . $controller . DS . $action;
        } else {
            $path = $controller . DS . $action;
        }

        $ret = $this->_render->fetch($path);
        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $controller . DS . $action . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');

        return $ret;
    }

    public function forward($controller, $action) {
        dispatcher::instance()->setControllerName($controller)->setActionName($action)->run();
    }

    public function assign($key, $val) {
        $this->_render->assign($key, $val);
    }

    public function massign($key) {
        $this->_render->massign($key);
    }

    public function renderJson($data) {
        response::outputJson($data);
    }

}
