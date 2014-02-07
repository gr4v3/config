<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class User extends load {
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
        $this->load->library('auth');
        $this->load->library('godsql');
        $this->load->library('url');
    }
    /************ methods called by the admin controller **************/
    public function admin() {
        $rows = $this->auth->read();
        $template = $this->parser->template('admin/user/template', TRUE);
        $level_rows = $this->godsql->level->select();
        if ($level_rows) $level_rows = $this->system->normalize_array($level_rows, 'level_id');
        foreach($rows as &$value) {
            if (isset($level_rows[$value['level_id']]))
                $value['level'] = $level_rows[$value['level_id']]['name'];
        }
        $this->father->render(NULL, $template->view(array('user_items' => $rows)), 'admin/user/admin');
    }
    public function create() {
        if ($this->request->post('create')) {
            $name = $this->request->post('name');
            $user = $this->request->post('user');
            $pass = $this->request->post('pass');
            $level_id = $this->request->post('level_id');
            $this->auth->create($name, $user, $pass, $level_id);
            return $this->admin();
        }
        $template = $this->parser->template('admin/user/create', TRUE);
        $row = $this->godsql->level->select();
        $this->father->render(NULL, $template->view(array('options' => $this->godsql->level->select())), 'admin/user/create');
    }
    public function delete($user_id = NULL) {
        if (empty($user_id)) return FALSE;
        $this->godsql->user->where(array('user_id' => $user_id));
        $this->godsql->user->delete();
        return $this->admin();
    }
    public function modify($user_id = NULL) {
        if ($this->request->post('modify')) {
            $this->auth->update($_POST);
            return $this->admin();
        }
        if (empty($user_id)) return FALSE;
        $this->godsql->user->where(array('user_id' => $user_id));
        $row = $this->godsql->user->select();
        if ($row) {
            $user = current($row);
            $row = $this->godsql->level->select();
            $user['options'] = $row;
            $template = $this->parser->template('admin/user/modify', TRUE);
            $this->father->render(NULL, $template->view($user), 'admin/user/modify');
        } else return $this->admin(); 
    }
}
?>