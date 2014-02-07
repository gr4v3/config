<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Manager extends Load {
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
        
        $this->load->library('application');
        $this->load->library('environment');
        $this->load->library('namespaces');
        $this->load->library('content');
    }
    public function index() {
        
        $application = $this->load->library('application', TRUE);
        $environment = $this->load->library('environment', TRUE);
        $namespaces = $this->load->library('namespaces', TRUE);
        $content = $this->load->library('content', TRUE);
        
        $list = $this->parser->template('list', TRUE);
        //dir applications
        $app_dir = $application->dir();
        if ( ! empty($app_dir)) $env_dir = $environment->dir(); else $env_dir = NULL;
        if ( ! empty($env_dir)) $name_dir = $namespaces->dir(); else $name_dir = NULL;
        $content = $namespaces->update();

        $view = $list->view(array(
            'applications' => $app_dir,
            'environments' => $env_dir,
            'namespaces' => $name_dir,
            'content' => $content
        ));
        $this->father->render(array($view));
    }
    public function application() {
        $this->application->route();
    }
    public function environment() {
        $this->environment->route();
    }
    public function namespaces() {
        $this->namespaces->route();
    }
    public function content() {
        $this->content->route();
    }
}
?>