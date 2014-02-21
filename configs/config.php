<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Config {
    var $master_address = '178.33.226.230';
    var $master_port = 2002;
    //var $path = '/var/www/html/config_webservice';
    var $path = 'C:\htdocs\config_webservice';
    var $main_controller = 'index';
    var $default_main_controller_method = 'manager';
    var $auth_secret = '8xbizconfigadmin123';
    var $session_lifespan = 60; // 24 * 60 (one hour)
    var $date_format = 'Y-m-d H:i:s';
    public function app_url_root() {
        if (! empty($_SERVER['PHP_SELF'])) return 'http://'.$_SERVER['HTTP_HOST'].next(explode('/', str_replace('index.php','/',$_SERVER['PHP_SELF'])));
        else return '/';
    }
}
?>
