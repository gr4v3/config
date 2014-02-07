<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Level extends load {
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
        $rows = $this->godsql->level->select();
        $template = $this->parser->template('admin/level/template', TRUE);
        foreach($rows as &$value) {
            if ($value['is_admin'] == 1 ) $value['is_admin'] = 'Yes'; else $value['is_admin'] = 'No';
            if ($value['active'] == 1 ) $value['active'] = 'Yes'; else $value['active'] = 'No';
        }
        $this->father->render(NULL, $template->view(array('level_items' => $rows)), 'admin/level/admin');
    }
    public function create() {
        if ($this->request->post('create')) {
            $name = $this->request->post('name');
            $description = $this->request->post('description');
            $active = $this->request->post('active');
            $this->godsql->level->insert(array(
                'name' => $name, 
                'active' => $active,
                'description' => $description,
                'is_admin' => $this->request->post('is_admin')
            ));
            return $this->admin();
        }
        $template = $this->parser->template('admin/level/create', TRUE);
        $this->father->render(NULL, $template->view(), 'admin/level/create');
    }
    public function delete($level_id = NULL) {
        if (empty($level_id)) return FALSE;
        $this->godsql->level->where(array('level_id' => $level_id));
        $this->godsql->level->delete();
        return $this->admin();
    }
    public function modify($level_id = NULL) {
        if ($this->request->post('modify')) {
            $level_id = (int) $this->request->post('level_id');
            $name = $this->request->post('name');
            $description = $this->request->post('description');
            $active = (int) $this->request->post('active');
            $this->godsql->level->where(array('level_id' => $level_id));
            $this->godsql->level->update(array(
                'name' => $name, 
                'active' => $active,
                'description' => $description,
                'is_admin' => $this->request->post('is_admin')
            ));
            return $this->admin();
        }
        if (empty($level_id)) return FALSE;
        $this->godsql->level->where(array('level_id' => $level_id));
        $row = $this->godsql->level->select();
        if ($row) {
            $user = current($row);
            $template = $this->parser->template('admin/level/modify', TRUE);
            $this->father->render(NULL, $template->view($user), 'admin/level/modify');
        } else return $this->admin(); 
    }
}
?>
