<?php

// no direct access
defined('_CONFIGWEBSERVICE') or die('Restricted access');

class Server extends Load {

    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->config('permissions');
        $this->load->config('commands');
        $this->load->library('log');
    }

    public function command($request = NULL) {
        if (empty($request))
            return FALSE;

        $this->log->write('server', 'client has input data.');
        $this->log->write('server', trim(print_r($request, TRUE), "\n\r"));
        //split the request to see if the user sent valid commands

        return $request;
    }

}

?>
