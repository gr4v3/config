<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Content extends load {
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
    }
    public function index() { }
    public function update() {

    }
    public function erase() {
       
    }
}
?>
