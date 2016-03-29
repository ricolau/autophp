<?php

/**
 * @author ricolau<ricolau@qq.com>
 * @version 2012-04
 * @desc render default
 *
 */
class render_default extends render_abstract {

    protected $data = array();

    public function massign($data) {
        if (!is_array($data)) {
            return false;
        }

        $this->data = util::array_merge($this->data, $data);
    }

    public function assign($key, $val) {
        $this->data[$key] = $val;
    }

    public function render($path) {
        auto::isDebugMode() && auto::dqueue('render data for ' . $path, '<pre>' . rico::export($this->data) . '</pre>');
        $data = $this->fetch($path);
        response::output($data);
    }

    public function fetch($path) {
        $file = self::_getTplPath($path);
        $data = self::_fetchTpl($file, $this->data);
        return $data;
    }

    /**
     * @desc
     * @param type $name
     * @param type $data
     * @example TView::slot
     */
    public function slot($name, $isDisplay = true) {
        $file = self::_getSlotPath($name);
        $data = $this->_fetchTpl($file, $this->data);
        if (!$isDisplay)
            return $data;

        response::output($data);
    }

    private function _fetchTpl($filePath, $data) {
        if (!ob_get_level()) {
            ob_start();
        }
        extract($data);
        include $filePath;
        $str = ob_get_clean();
        return $str;
    }

    private static function _getSlotPath($name) {
        if (strpos($name, '/') !== false) {
            $seps = explode('/', $name);
            foreach ($seps as $k => $v) {
                $seps[$k] = util::parseFilename($v);
            }
            $name = implode('/', $seps);
        } else {
            $name = util::parseFilename($name);
        }


        $file = APP_PATH . DS . 'view' . DS . 'slot' . DS . $name . '.php';

        if (!file_exists($file)) {
            throw new exception_render('slot not exist: ' . $name, exception_render::type_slot_not_exist);
        }
        return $file;
    }

    private static function _getTplPath($path) {

        $fileName = $path . '.php';
        $file = APP_PATH . DS . 'view' . DS . 'template' . DS . $fileName;

        if (!file_exists($file)) {
            throw new exception_render('template not exist: ' . $fileName, exception_render::type_tpl_not_exist);
        }
        return $file;
    }

}