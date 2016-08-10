<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2016-03-29
 * @desc controller base
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
        $_debugMicrotime = microtime(true);
        if ($path) {
            $this->_render->render($path);
            
            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('path'=>$path)) && auto::isDebug() && auto::debugMsg(__METHOD__ . '(' . $path . ')', 'cost ' . $timeCost . 's ');
            
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

        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('path'=>$path)) && auto::isDebug() && auto::debugMsg(__METHOD__ . '(' . $path . ')', 'cost ' . $timeCost . 's ');
    }

    public function slot($slot, $isDisplay = false) {
        return $this->_render->slot($slot, $isDisplay);
    }

    public function fetch($path = '') {
        $_debugMicrotime = microtime(true);
        if ($path) {
            $ret = $this->_render->fetch($path);

            ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('path'=>$path)) && auto::isDebug() && auto::debugMsg(__METHOD__ . '(' . $path . ')', 'cost ' . $timeCost . 's ');
            
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

        ($timeCost = microtime(true) - $_debugMicrotime) && performance::add(__METHOD__, $timeCost, array('path'=>$path)) && auto::isDebug() && auto::debugMsg(__METHOD__ . '(' . $path . ')', 'cost ' . $timeCost . 's ');
        
        return $ret;
    }

    public function forward($path) {
        dispatcher::instance()->setPath($path)->dispatch()->run();
    }

    public function assign($key, $val) {
        $this->_render->assign($key, $val);
    }

    public function massign($key) {
        $this->_render->massign($key);
    }

    public function renderJson($data) {
        if(!headers_sent()){
            header('Content-type: application/json');
        }
        response::outputJson($data);
    }

}
