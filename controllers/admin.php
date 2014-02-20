<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Admin extends Load {

    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
        $this->load->library('session');
        $this->load->library('admin/user');
        $this->load->library('admin/level');
        $this->load->library('environment');
        $this->load->library('application');
        $this->parser->template('admin/main');
    }

    public function index() {
        $this->render();
    }

    public function user() {
        $this->user->route();
    }

    public function level() {
        $this->level->route();
    }

    public function session() {
        $this->session->route();
    }

    public function application() {
        $this->application->route();
    }

    public function environment() {
        $this->environment->route();
    }

    public function render($head = null, $content = null, $action = NULL) {

        echo $this->parser->view(array(
            'head' => implode($this->father->parser->css) . implode($this->father->parser->js) . $head,
            'content' => $content,
            'action' => $action
        ));
    }

}

?>