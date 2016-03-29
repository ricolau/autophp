<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-03-29
 * @desc controller abstract
 *
 */
abstract class controller extends base {

    protected $_render = null;

    

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
            auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $path . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');
            return;
        }
        $dir = dispatcher::instance()->getModuleName();
        $controller = dispatcher::instance()->getControllerName();
        $action = dispatcher::instance()->getActionName();

        if (dispatcher::instance()->getPathDeep() == dispatcher::path_deep3) {
            $path = $dir . DS . $controller . DS . $action;
        } else {
            $path = $controller . DS . $action;
        }
        $this->_render->render($path);

        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $path . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');
    }

    public function slot($slot, $isDisplay = false) {
        return $this->_render->slot($slot, $isDisplay);
    }

    public function fetch($path = '') {
        auto::isDebugMode() && $_debugMicrotime = microtime(true);
        if ($path) {
            $ret = $this->_render->fetch($path);
            auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $path . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');

            return $ret;
        }
        $dir = dispatcher::instance()->getModuleName();
        $controller = dispatcher::instance()->getControllerName();
        $action = dispatcher::instance()->getActionName();

        if (dispatcher::instance()->getPathDeep() == dispatcher::path_deep3) {
            $path = $dir . DS . $controller . DS . $action;
        } else {
            $path = $controller . DS . $action;
        }

        $ret = $this->_render->fetch($path);
        auto::isDebugMode() && auto::dqueue(__METHOD__ . '(' . $path . ')', 'cost ' . (microtime(true) - $_debugMicrotime) . 's ');

        return $ret;
    }

    public function forward($path) {
        dispatcher::instance()->setUri($path)->dispatch()->run();
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
