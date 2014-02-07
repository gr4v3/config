<?php
defined( '_CONFIGWEBSERVICE' ) or die( 'Restricted access' );
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Debug extends Load {
    public $log = array();
    function __construct() {
        parent::__construct();
        $this->load->config('config');
    }
    function set($params = NULL) {
        if (empty($params)) return FALSE;
        $this->log[] = $params;   
    }
    function view() {
        if (empty($this->log)) return FALSE;
        $html = array('<pre>');
        $html[] = implode($this->config->debug_break, $this->log);
        $html[] = '</pre>';
        echo $html;
    }
}
?>
