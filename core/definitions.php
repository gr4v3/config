<?php

define('_CONFIGWEBSERVICE', 1);
define('DS', "/"); //define('DS', DIRECTORY_SEPARATOR);
//define('DS', DIRECTORY_SEPARATOR);
// absolute root of the application
//internal paths
if (!empty($_SERVER['PWD']))
    define('ROOT_PATH', $_SERVER['PWD'] . DS);
else
    define('ROOT_PATH', str_replace(DS . 'index.php', '', $_SERVER['SCRIPT_FILENAME']) . DS);
define('AUTH_PATH', ROOT_PATH . 'auth' . DS . 'auth');
define('GROUP_PATH', ROOT_PATH . 'auth' . DS . 'groups');
define('ROUTE_PATH', ROOT_PATH . 'auth' . DS . 'routes');
define('SESSION_PATH', ROOT_PATH . 'auth' . DS . 'session');
define('DATABASE_PATH', ROOT_PATH . 'database' . DS);
define('APPLICATION', ROOT_PATH . 'application');
define('CONFIG', ROOT_PATH . 'configs');
define('CSS', ROOT_PATH . 'css');
define('JS', ROOT_PATH . 'js');
define('LIBRARY', ROOT_PATH . 'libraries');
define('LOG', ROOT_PATH . 'logs');
define('MODEL', ROOT_PATH . 'models');
define('VIEW', ROOT_PATH . 'views');
define('CONTROLLER', ROOT_PATH . 'controllers');
define('INCLUDES', ROOT_PATH . 'includes');
define('CACHE', ROOT_PATH . 'includes');
?>