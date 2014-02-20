<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Godsql extends Load {

    public $session = NULL;
    public $user = NULL;
    public $level = NULL;
    public $application = NULL;
    public $environment = NULL;

    function __construct() {
        parent::__construct();
        $this->include->file('nosql');
        $nosql = new Nosql('session');
        $nosql->structure(array(
            'session' => array(
                'columns' => array(
                    'hash' => array('length' => 26, 'type' => 'TEXT'),
                    'logged' => array('length' => 2, 'type' => 'INTEGER'),
                    'modified' => array('length' => 64, 'type' => 'INTEGER'),
                    'created' => array('length' => 64, 'type' => 'INTEGER'),
                    'redirect' => array('length' => 250, 'type' => 'TEXT'),
                    'user_id' => array('length' => 12, 'type' => 'INTEGER'),
                    'ip' => array('length' => 20, 'type' => 'TEXT'),
                    'level_id' => array('length' => 12, 'type' => 'INTEGER')
                ),
                'index' => array('logged', 'hash', 'level_id')
            ),
            'user' => array(
                'columns' => array(
                    'user' => array('length' => 20, 'type' => 'TEXT'),
                    'name' => array('length' => 20, 'type' => 'TEXT'),
                    'access' => array('length' => 32, 'type' => 'TEXT'),
                    'level_id' => array('length' => 8, 'type' => 'INTEGER'),
                ),
                'index' => array('access', 'level_id')
            ),
            'level' => array(
                'columns' => array(
                    'name' => array('length' => 20, 'type' => 'TEXT'),
                    'description' => array('length' => 250, 'type' => 'TEXT'),
                    'is_admin' => array('length' => 2, 'type' => 'INTEGER'),
                    'active' => array('length' => 2, 'type' => 'INTEGER')
                ),
                'index' => array('active')
            )
                )
        );
        $this->session = $nosql->session;
        $this->user = $nosql->user;
        $this->level = $nosql->level;
        $nosql = new Nosql('manager');
        $nosql->structure(array(
            'application' => array(
                'columns' => array(
                    'name' => array('length' => 20, 'type' => 'TEXT'),
                    'description' => array('length' => 250, 'type' => 'TEXT'),
                    'level_id' => array('length' => 8, 'type' => 'INTEGER'),
                    'created' => array('length' => 64, 'type' => 'INTEGER'),
                    'active' => array('length' => 2, 'type' => 'INTEGER'),
                ),
                'index' => array('level_id', 'active')
            ),
            'environment' => array(
                'columns' => array(
                    'name' => array('length' => 26, 'type' => 'TEXT'),
                    'domain' => array('length' => 250, 'type' => 'TEXT'),
                    'ip' => array('length' => 10, 'type' => 'INTEGER'),
                    'created' => array('length' => 64, 'type' => 'INTEGER'),
                    'active' => array('length' => 2, 'type' => 'INTEGER')
                ),
                'index' => array('ip', 'active')
            )
                )
        );
        $this->application = $nosql->application;
        $this->environment = $nosql->environment;
    }

}

?>
