<?php

// no direct access
defined('_CONFIGWEBSERVICE') or die('Restricted access');

class Index extends Load {

    function __construct() {
        parent::__construct();
        $this->load->library('log');

        $this->load->config('config');
        $this->load->config('permissions');
        $this->load->model('godsql');
        
        
        //$this->include->folder('sockets');

        $this->include->file('sockets/socket');
        $this->include->file('sockets/socketClient');
        $this->include->file('sockets/socketDaemon');
        $this->include->file('sockets/socketServer');
        $this->include->file('sockets/socketServerClient');
        $this->include->file('configServer');

        //set the default template
        if (!empty($_SERVER['HTTP_HOST'])) {
            $this->load->library('request');
            $this->load->library('session');
            $this->load->controller('manager');
            $this->load->controller('admin');
            $this->load->library('parser');
            $this->parser->add->js('jquery.min');
            $this->parser->add->js('bootstrap.min');
            $this->parser->add->js('index');
            $this->parser->add->css('bootstrap.min');
            $this->parser->add->css('index');
            $this->parser->template('main');
            $this->session->check();
            $this->request->resolve();
        }
    }

    public function start() {
        
        
        ini_set('mbstring.func_overload', '0');
        ini_set('output_handler', '');
        error_reporting(E_ALL | E_STRICT);
        @ob_end_flush();
        set_time_limit(0);

        $daemon = new socketDaemon();
        $server = $daemon->create_server('configServer', 'configServerClient', $this->config->master_address, $this->config->master_port);
        $daemon->process();
    }

    public function manager() {
        return $this->manager->route();
    }

    public function admin() {
        return $this->admin->route();
    }

    public function login($firsttime = NULL) {
        $this->session->present();
        // if it is the first time ever 
        if (!empty($firsttime))
            $auth_template = $this->parser->template('login/firsttime', TRUE)->view();
        else
            $auth_template = $this->parser->template('login/auth', TRUE)->view();
        $this->render(array($auth_template));
    }

    public function logout() {
        $this->session->destroy();
    }

    public function render($content = array(), $head = array()) {
        if ($this->session->check()) {
            if ($this->session->user_data['level_id'] == 1 || $this->session->user_data['level_id'] == 0) {
                array_unshift($content, $this->parser->template('admin/logout', TRUE)->view());
            } else
                array_unshift($content, $this->parser->template('login/logout', TRUE)->view());
        }
        $this->parser->add->js("window.base_url = '" . $this->session->url->base() . "';", TRUE);
        echo $this->parser->view(array(
            'head' => implode($this->parser->css) . implode($this->parser->js) . implode('', $head),
            'content' => implode('', $content),
            'application_post' => $this->request->all('application'),
            'environment_post' => $this->request->all('environment'),
            'namespaces_post' => $this->request->all('namespaces'),
            'content_post' => $this->request->all('content_post')
        ));
    }

}

?>