<?php

require_once 'definitions.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Include_helper {

    private $caller = NULL;

    function __construct($caller = NULL) {
        $this->caller = $caller;
    }

    function file($filename = NULL) {
        if (empty($filename))
            return FALSE;
        if (array_key_exists($filename, $this->caller->includes))
            return TRUE;
        $component_path = INCLUDES;
        if (strpos($filename, DS))
            $realfilename = end(explode(DS, $filename));
        else
            $realfilename = $filename;
        $file = $component_path . DS . $filename;
        if (is_dir($component_path)) {
            if (is_file($file . '.php'))
                $file_input = $file . '.php';
            else if (is_file($file))
                $file_input = $file;
            if (empty($file_input))
                return FALSE;
            include_once $file_input;
            return TRUE;
        } else
            return FALSE;
    }

    function folder($foldername = NULL) {
        if (empty($foldername))
            return FALSE;
        if (array_key_exists($foldername, $this->caller->includes))
            return TRUE;
        $component_path = INCLUDES;
        if (is_dir($component_path . DS . $foldername)) {
            $handle = dir($component_path . DS . $foldername);
            while ($entry = $handle->read()) {
                if ($entry != '.' && $entry != '..') {
                    if (is_file($component_path . DS . $foldername . DS . $entry))
                        include_once $component_path . DS . $foldername . DS . $entry;
                }
            }
            $handle->close();
            $this->caller->includes[$foldername] = TRUE;
            return TRUE;
        } else
            return FALSE;
    }

}

;

class Load_helper {

    public $caller = NULL;

    function __construct($caller = NULL) {
        $this->caller = $caller;
    }

    public function model($params = NULL, $return = FALSE) {
        if (empty($params))
            return FALSE;
        return $this->_run(MODEL, $params, $return);
    }

    public function controller($params = NULL, $return = FALSE) {
        if (empty($params))
            return FALSE;
        return $this->_run(CONTROLLER, $params, $return);
    }

    public function config($params = NULL, $return = FALSE) {
        if (empty($params))
            return FALSE;
        return $this->_run(CONFIG, $params, $return);
    }

    public function library($params = NULL, $return = FALSE) {
        if (empty($params))
            return FALSE;
        return $this->_run(LIBRARY, $params, $return);
    }

    protected function _check($type = NULL, $name = NULL) {
        if (empty($type) || empty($name))
            return FALSE;
        if (is_dir($type)) {
            $file = $type . DS . strtolower($name);
            if (is_file($file . '.php'))
                $file_input = $file . '.php';
            else if (is_file($file))
                $file_input = $file;
            else
                $file_input = NULL;
            if (!empty($file_input)) {
                include_once $file_input;
                return TRUE;
            } else
                return FALSE;
        } else
            return FALSE;
    }

    protected function _cache($caller = NULL, $realfilename = NULL) {
        if (empty($caller))
            return FALSE;
        // check if this caller have a father and neste fathers
        if (property_exists($caller, $realfilename)) {
            if (property_exists($caller, 'log'))
                $caller->log->write('cache', "class $realfilename is cached from father class " . get_class($caller));
            return $caller->{$realfilename};
        } else if (property_exists($caller, 'father'))
            return $this->_cache($caller->father, $realfilename);
    }

    protected function _run($type = NULL, $params = NULL, $return = FALSE) {
        if (empty($type) || empty($params))
            return FALSE;
        if (strpos($params, DS))
            $realfilename = end(explode(DS, $params));
        else
            $realfilename = $params;
        // check if the class allready exist in cache
        $class_object = $this->_cache($this->caller, $realfilename);
        if ($class_object) {
            if ($return)
                return $class_object;
            else {
                $this->caller->{$realfilename} = $class_object;
                return TRUE;
            }
        }
        // if it goes to here then the class needs to be loaded from disk
        // first check if the class exists in php enviroment
        $params_class_name = ucfirst($realfilename);
        if (!class_exists($params_class_name))
            $this->_check($type, $params);
        $class_object = new $params_class_name();
        $class_object->father = $this->caller;
        if ($return)
            return $class_object;
        $this->caller->{$realfilename} = $class_object;
    }

}

;

class Load {

    public $load = NULL;
    public $includes = array();
    public $models = array();
    public $controllers = array();
    public $configs = array();
    public $libraries = array();
    public $father = NULL;

    public function __construct() {
        $this->load = new Load_helper($this);
        $this->load->caller = $this;
        $this->include = new Include_helper($this);
        return $this->load;
    }

    public function route($method = NULL) {
        $this->request->resolve($method);
    }

}

?>