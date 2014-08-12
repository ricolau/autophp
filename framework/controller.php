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

    public function render($controller = '', $action = '') {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        if ($controller === '' && $action === '') {
            $controller = dispatcher::instance()->getControllerName();
            $action = dispatcher::instance()->getActionName();
        }
        $this->_render->render($controller, $action);
        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $controller . DS . $action . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');
    }

    public function slot($slot, $isDisplay = false) {
        return $this->_render->slot($slot, $isDisplay);
    }

    public function fetch() {
        $controller = dispatcher::instance()->getControllerName();
        $action = dispatcher::instance()->getActionName();
        $this->_render->fetch($controller, $action);
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