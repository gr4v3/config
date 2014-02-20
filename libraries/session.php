<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Session extends Load {

    public $user_data = NULL;

    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('auth');
        $this->load->library('log');
        $this->load->library('url');
        $this->load->library('request');
        $this->load->library('parser');
        $this->load->model('godsql');
    }

    /*     * ************ methods called from admin controller ************* */

    public function index() {
        $rows = $this->godsql->session->select();
        $template = $this->parser->template('admin/session/template', TRUE);
        foreach ($rows as &$value) {
            if (!empty($value['user_id'])) {
                $this->godsql->user->where(array('user_id' => $value['user_id']));
                $user = $this->godsql->user->select('name');
                $this->godsql->level->where(array('level_id' => $value['level_id']));
                $level = $this->godsql->level->select('name');
                $user = current($user);
                $level = current($level);
                $value['user_id'] = $user['name'];
                $value['level_id'] = $level['name'];
                $value['modified'] = date($this->config->date_format, $value['modified']);
                $value['created'] = date($this->config->date_format, $value['created']);
            }
        }
        $this->father->render(NULL, $template->view(array('session_items' => $rows)), '/admin/session/view');
    }

    public function modify($session_id = NULL) {
        if ($this->request->post('modify')) {
            $this->godsql->session->where(array('session_id' => $this->request->post('session_id')));
            $params = array();
            $params['logged'] = $this->request->post('logged');
            $params['redirect'] = $this->request->post('redirect');
            $params['level_id'] = $this->request->post('level_id');
            $params['modified'] = strtotime($this->request->post('modified'));
            $params['created'] = strtotime($this->request->post('created'));
            $this->godsql->session->update($params);
            return $this->index();
        }
        if (empty($session_id))
            return FALSE;
        $this->godsql->session->where(array('session_id' => $session_id));
        $row = $this->godsql->session->select();
        if ($row) {
            $item = current($row);
            $item['modified'] = date($this->config->date_format, $item['modified']);
            $item['created'] = date($this->config->date_format, $item['created']);
            $item['levels'] = $this->godsql->level->select();
            $user = current($this->godsql->user->where(array('user_id' => $item['user_id']))->select('name'));
            $item['user'] = $user['name'];
            $template = $this->parser->template('admin/session/modify', TRUE);
            $this->father->render(NULL, $template->view($item), '/admin/session/modify');
        } else
            return $this->index();
    }

    public function delete($session_id = NULL) {
        if (empty($session_id))
            return $this->index();
        $this->godsql->session->where(array('session_id' => $session_id));
        $this->godsql->session->delete();
        return $this->index();
    }

    /*     * *************************************************************** */

    public function read($session_id = NULL) {
        if (empty($session_id))
            return FALSE;
        $now = mktime();
        $this->godsql->session->where(array('hash' => $session_id));
        $this->godsql->session->update(array('modified' => $now));
        $this->godsql->session->where(array(
            'modified' => '<' . ($now - $this->config->session_lifespan),
            'user_id' => ''
        ));
        $this->godsql->session->delete();
        $this->godsql->session->where(array('hash' => $session_id));
        $results = $this->godsql->session->select();
        return empty($results) ? FALSE : current($results);
    }

    private function write($session_id = NULL, $session_data = NULL) {
        if (empty($session_id))
            return FALSE;
        $row = $this->read($session_id);
        if ($row) {
            $this->godsql->session->where(array('hash' => $session_id));
            $this->godsql->session->update($session_data);
            $this->log->write('info', 'session to update');
        } else {
            $session_data['hash'] = $session_id;
            $results = $this->godsql->session->insert($session_data);
            $this->log->write('info', 'session to insert');
        }
    }

    private function erase($session_id = NULL) {
        if (empty($session_id))
            return FALSE;
        $this->godsql->session->where(array('hash' => $session_id));
        return $this->godsql->session->delete();
    }

    private function create($session_id = NULL, $extra = NULL) {
        if (empty($session_id))
            return FALSE;
        $now = mktime();
        $session_data = array(
            'modified' => $now,
            'created' => $now,
            'logged' => 1,
            'hash' => $session_id
        );
        if (!empty($extra))
            $session_data = array_merge($session_data, $extra);
        $insert = $this->godsql->session->insert($session_data);
        $this->log->write('info', 'created a new session ' . $insert);
        return $insert;
    }

    private function redirect($session_id = NULL, $redirect = NULL) {
        if (empty($session_id))
            return FALSE;
        $action = $this->request->post('action');
        if ($action == 'login') {

            $this->log->write('info', 'request to login');

            $user = $this->request->post('user');
            $pass = $this->request->post('pass');
            $access = $this->auth->validate($user, $pass);
            $this->log->write('session', 'user: ' . $user);
            $this->log->write('session', 'pass: ' . $pass);
            $this->log->write('session', 'validate: ' . print_r($access, TRUE));
            if ($access !== FALSE) {
                // the login information is valid
                $this->write($session_id, array(
                    'logged' => 2,
                    'user_id' => $access['user_id'],
                    'level_id' => $access['level_id'],
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
                $this->log->write('auth', 'user ' . $access['name'] . ' well authenticated!');
                $this->log->write('auth', print_r($access, TRUE));
                header('Location: ' . $redirect);
            } else {
                // someone trying to hack ??? login information invalid.
                $this->erase($session_id);
                header('Location: ' . $this->url->base() . 'login');
            }
        } else if ($action == 'firsttime') {

            $this->log->write('info', 'request to create admin account');
            $user = $this->request->post('user');
            $pass = $this->request->post('pass');
            $this->auth->create('administrator', $user, $pass, 0);
            header('Location: ' . $this->url->base() . 'login');
        } else
            header('Location: ' . $this->url->base() . 'login');
    }

    public function check() {
        if (!empty($_SERVER['PWD']))
            return TRUE;

        $uri = preg_match('/login/i', $_SERVER['REQUEST_URI']) ? TRUE : FALSE;
        $action = $this->request->post('action');

        if ($uri && empty($action))
            return FALSE;

        $session_id = session_id();
        $session_user = $this->read($session_id);
        $this->log->write('info', 'this session_id does already exist! ' . print_r($session_user, TRUE));
        // check first if the session exists
        if (!$session_user) {
            // if it is the first time ever 
            $firsttime = NULL;
            $results = $this->godsql->user->select();
            if (empty($results))
                $firsttime = '/firsttime';
            if ($this->url->request() == '/login')
                $this->create($session_id, array('redirect' => $this->config->default_main_controller_method));
            else
                $this->create($session_id, array('redirect' => ltrim($this->url->request(), '/')));
            header('Location: ' . $this->url->base() . 'login' . $firsttime);
        } else if ($session_user['logged'] == 0) {
            $this->write($session_id, array('logged' => 1));
            header('Location: ' . $this->url->base() . 'login');
        } else if ($session_user['logged'] == 1) {
            $this->redirect($session_id, $session_user['redirect']);
        } else if ($session_user['logged'] == 2) {
            $this->user_data = $session_user;
            return TRUE;
        }
    }

    public function destroy() {
        if (php_sapi_name() == "cli")
            return FALSE;
        $session_id = session_id();
        $this->erase($session_id);
        header('Location: ' . $this->url->base() . 'login');
    }

    public function present() {
        if (php_sapi_name() == "cli")
            return FALSE;
        $session_id = session_id();
        $session_user = $this->read($session_id);
        if ($session_user && $session_user['logged'] == 2)
            header('Location: ' . $session_user['redirect']);
    }

}

?>