<?php
// no direct access
defined( '_CONFIGWEBSERVICE' ) or die( 'Restricted access' );
class Core extends Load
{
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('debug');
    }
    public function run($controller = NULL) {
        if ( ! empty($controller)) {
            $this->load->controller($controller);
            $class_name = $controller;
        } else {
            $this->load->controller($this->config->main_controller);
            $class_name = $this->config->main_controller;
        }
        if (method_exists($class_name, 'index')) $this->{$class_name}->index();
        $this->debug->view();
    }
}
?>