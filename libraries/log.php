<?php
defined( '_CONFIGWEBSERVICE' ) or die( 'Restricted access' );
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Log extends Load {
    private $path = NULL;
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $full_path = explode(DS, $_SERVER['SCRIPT_FILENAME']);
        array_pop($full_path);
        $this->path = implode(DS, $full_path) . DS . 'logs' . DS;
    }
    function write($level = 'info', $content = '') {
        $handle = fopen($this->path . $level . '_' .date('Y-m-d'). '.log', "ab");
        $content.= "
";
        fwrite($handle, $content, strlen($content));
        fclose($handle);
        
    }
}
?>
