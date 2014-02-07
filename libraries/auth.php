<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 
class Auth extends Load {

    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('godsql');
    }
    public function read() { 
        return $this->godsql->user->select();
    }
    public function is_admin($user_data = NULL) {
        if (empty($user_data)) return FALSE;
    }
    public function validate($user = NULL, $pass = NULL) {
        $secret = $this->config->auth_secret;
        $hash = md5($user . $pass . $secret);
        $this->log->write('auth', $user . ' is trying to auth');
        $this->godsql->user->where(array('access' => $hash, 'user' => $user));
        $result = $this->godsql->user->select();
        if (empty($result)) return FALSE; else return current($result);
    }
    public function create($name = NULL, $user = NULL, $pass = NULL, $level_id = NULL) {
        if (empty($user) || empty($pass)) return FALSE;
        $secret = $this->config->auth_secret;
        $hash = md5($user . $pass . $secret);
        $data = array(
            'user' => $user,
            'name' => $name,
            'access' => $hash,
            'level_id' => $level_id
        );
        $insert = $this->godsql->user->insert($data);
        $this->log->write('auth', 'inserted a new user with name ' . $user);
        return $insert;
    }
    public function update($user_params = NULL) {
        if (empty($user_params)) return FALSE;
        $secret = $this->config->auth_secret;
        $user = $user_params['user'];
        $pass = $user_params['pass'];
        $confirmpass = $user_params['confirmpass'];
        $name = $user_params['name'];
        $level_id = $user_params['level_id'];
        $data = array(
            'user' => $user,
            'name' => $name,
            'level_id' => $level_id
        );
        if ( ! empty($pass) && $pass == $confirmpass) $data['access'] = md5($user . $pass . $secret);
        $this->godsql->user->where(array('user_id' => $user_params['user_id']));
        $update = $this->godsql->user->update($data);
        $this->log->write('auth', 'updated user with name ' . $user);
        return $update;
    }
}
?>