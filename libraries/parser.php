<?php

defined('_CONFIGWEBSERVICE') or die('Restricted access');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Parser_Helper {

    protected $caller = NULL;

    function __construct($caller = NULL) {
        $this->caller = $caller;
    }

    public function css($params = NULL, $declaration = FALSE) {
        if (empty($params))
            return FALSE;
        if ($declaration)
            $this->caller->css[] = '<style type="text/css">' . $params . '</style>';
        else
            $this->caller->css[] = $this->link($this->caller->css_base_url . '/' . $params, $declaration);
    }

    public function js($params = NULL, $declaration = FALSE) {
        if (empty($params))
            return FALSE;
        if ($declaration)
            $this->caller->js[] = '<script type="text/javascript">' . $params . '</script>';
        else
            $this->caller->js[] = $this->script($this->caller->js_base_url . '/' . $params, $declaration);
    }

    private function script($src = NULL) {
        if (empty($src))
            return FALSE;
        return '<script type="text/javascript" src="' . $src . '.js"></script>';
    }

    private function link($src = NULL, $declaration = FALSE) {
        if (empty($src))
            return FALSE;
        return '<link rel="stylesheet" type="text/css" href="' . $src . '.css">';
    }

}

class Parser extends Load {

    private $views_path = NULL;
    private $template_name = NULL;
    private $template_content = NULL;
    private $template_parsed = NULL;
    public $css = array();
    public $js = array();
    public $base_url = NULL;
    public $js_base_url = NULL;
    public $css_base_url = NULL;
    public $add = NULL;

    function __construct() {
        parent::__construct();
        $this->load->library('url');
        $this->base_url = $this->url->base();
        $this->js_base_url = $this->base_url . 'js';
        $this->css_base_url = $this->base_url . 'css';
        $this->views_path = VIEW . DS;
        $this->add = new Parser_Helper($this);
    }

    function template($params = NULL, $return = FALSE) {
        if (empty($params))
            return FALSE;
        if (strpos($params, '/')) {
            // the template is nested with folders
            $nested = explode('/', $params);
            // the last one should be the template file 
            $template_name = end($nested);
        } else
            $template_name = $params;

        if ($return) {
            $clone = new Parser();
            $clone->template($params);
            return $clone;
        } else {
            $this->template_name = $template_name;
            $file = NULL;
            if (is_file($this->views_path . $params . '.php'))
                $file = $params . '.php';
            else if (is_file($this->views_path . $params))
                $file = $params;
            if (empty($file))
                return FALSE;
            $this->template_content = file_get_contents($this->views_path . $file);
        }
    }

    function view($params = NULL) {
        if (empty($params) || !is_array($params))
            $params = array();
        $params['base_url'] = $this->base_url;
        $this->template_parsed = $this->template_content;
        $single_items = array();
        foreach ($params as $index => $value) {
            if (is_array($value)) {
                if (preg_match('#\{' . $index . '\}(.*?)\{/' . $index . '\}#s', $this->template_parsed, $matches)) {
                    $sub_template = end($matches);
                    $result = array();
                    foreach ($value as $sub_value) {
                        $clone = new Parser();
                        $clone->template_content = $sub_template;
                        $result[] = $clone->view($sub_value);
                    }
                    $this->template_parsed = preg_replace('#\{' . $index . '\}(.*?)\{/' . $index . '\}#s', implode('', $result), $this->template_parsed);
                }
            } else
                $single_items[$index] = $value;
        }
        foreach ($single_items as $index => $value) {
            $this->template_parsed = preg_replace('/\{' . $index . '\}/i', $value, $this->template_parsed);
        }
        return $this->template_parsed;
    }

}

?>
