<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2014-03
 * @desc render Smarty
 *
 * @important before use this, make sure that AUTOPHP_PATH .'/render/smarty.class.php' exist, and Smarty in it!
 *
 *
 */
class render_smarty extends render_abstract {
    protected $_smarty = null;

    public function __construct() {

        $smartyFile = AUTOPHP_PATH . '/trender/smarty.class.php';
        if (file_exists($smartyFile)) {
            include_once $smartyFile;
        }
        if (!class_exists('Smarty')) {
            throw new exception_render('render engine of Smarty not exist!', exception_render::type_render_engine_not_exist);
        }
        $this->_smarty = new Smarty();
        $this->_smarty->template_dir = APP_PATH.DS.'view'.DS.'template';
        $this->_smarty->compile_dir  = APP_PATH.DS.'view'.DS.'cache'.DS.'compile';
        $this->_smarty->cache_dir  = APP_PATH.DS.'view'.DS.'cache'.DS.'cache';
    }

    public function assign($key, $val) {
        $this->_smarty->assign($key, $val);
    }

    public function render($path) {
        $tplFile = $path . '.tpl';
        $this->_smarty->display($tplFile);
    }

    public function fetch($path) {
        $tplFile = $path . '.tpl';
        $data = $this->_smarty->fetch($tplFile);
        return $data;
    }

    public function getSmarty() {
        return $this->_smarty;
    }

}