<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Url extends Load {

    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
    }

    private function project() {
        $script_filename = explode('/', $_SERVER['SCRIPT_FILENAME']);
        array_pop($script_filename);
        return end($script_filename);
    }

    private function enviroment() {
        $REQUEST_URI = $_SERVER['REQUEST_URI'];
        if (strpos($REQUEST_URI, $this->project()))
            return 'alias';
        else
            return 'vhost';
    }

    public function base() {
        $enviroment = $this->enviroment();
        $this->log->write('enviroment', $enviroment);
        if ($enviroment == 'alias')
            return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->project() . '/';
        else
            return 'http://' . $_SERVER['HTTP_HOST'] . '/';
    }

    public function request() {
        $enviroment = $this->enviroment();
        if ($enviroment == 'alias')
            return str_replace('/' . $this->project(), '', $_SERVER['REQUEST_URI']);
        else
            return $_SERVER['REQUEST_URI'];
    }

}

?>