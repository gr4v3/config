<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Application extends Load {
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
        $this->load->library('session');
        $this->load->library('url');
        $this->load->model('godsql');
    }
    /************ methods called by the admin controller **************/
    public function admin() {
        $rows = $this->godsql->application->select();
        $template = $this->parser->template('admin/application/template', TRUE);
        foreach($rows as &$value) {
            $this->godsql->level->where(array('level_id' => $value['level_id']));
            $level = current($this->godsql->level->select('name'));
            $value['level_id'] = $level['name'];
            if ($value['active'] == 1 ) $value['active'] = 'Yes'; else $value['active'] = 'No';
            $value['created'] = date($this->config->date_format, $value['created']);
        }
        $this->father->render(NULL, $template->view(array('application_items' => $rows)), 'admin/application/admin');
    }
    public function create() {
        if ($this->request->post('create')) {
            $this->godsql->application->insert(array(
                'name' => $this->request->post('name'),
                'description' => $this->request->post('description'),
                'level_id' => $this->request->post('level_id'),
                'created' => mktime()
            ));
            $this->system->create->folder(APPLICATION . DS . $this->request->post('name'));
            return $this->admin();
        }
        $template = $this->parser->template('admin/application/create', TRUE);
        $params = array();
        $params['level_items'] = $this->godsql->level->select();
        $this->father->render(NULL, $template->view($params), 'admin/application/create');
    }
    public function delete($application_id = NULL) {
        if (empty($application_id)) return FALSE;
        $this->godsql->application->where(array('application_id' => $application_id));
        $this->godsql->application->delete();
        return $this->admin();
    }
    public function modify($application_id = NULL) {
        if ($this->request->post('modify')) {
            $application_id = (int) $this->request->post('application_id');
            $this->godsql->application->where(array('application_id' => $application_id));
            $item = current($this->godsql->application->select());
            $this->system->rename->folder(
                    APPLICATION . DS . $item['name'],
                    APPLICATION . DS . $this->request->post('name')
            );
            $this->godsql->application->where(array('application_id' => $application_id));
            $this->godsql->application->update(array(
                'name' => $this->request->post('name'),
                'description' => $this->request->post('description'),
                'level_id' => $this->request->post('level_id'),
                'active' => $this->request->post('active')  
            ));
            return $this->admin();
        }
        if (empty($application_id)) return FALSE;
        $this->godsql->application->where(array('application_id' => $application_id));
        $row = $this->godsql->application->select();
        if ($row) {
            $item = current($row);
            $template = $this->parser->template('admin/application/modify', TRUE);
            $this->father->render(NULL, $template->view($item), 'admin/application/modify');
        } else return $this->admin(); 
    }
    
    /************ methods called by the manager controller **************/
    public function assign() {
        $create = $this->request->post('create');
        $cancel = $this->request->post('cancel');
        if ( ! empty($cancel)) header('Location: ' . $this->url->base() .$this->config->default_main_controller_method);
        if ( ! empty($create)) {
            $name = $this->request->post('name');
            $result = $this->system->create->folder(APPLICATION . DS . $name);
            if ($result) $result_str = 'success!'; else $result_str = 'failed!';
            $this->log->write('info', 'application `'.$name.'` creation ' . $result_str);
            header('Location: ' . $this->url->base() . $this->config->default_main_controller_method . '/?application=' . $name);
        }
        $this->parser->template('manager/application/create');
        $params = array();
        
        $current_session = $this->session->read(session_id());
        if ($current_session) {
            $this->godsql->application->where(array('level_id' => $current_session['level_id']));
            $applications = $this->godsql->application->select();
            $view = $this->parser->view(array(
                'application_items' => $applications
            ));
        } else $view = NULL;
        $this->father->father->render(array($view));
    }
    public function unassign() {
        $application_name = $this->request->post('application');
        if ( ! empty($application_name)) {
            $result = $this->system->delete->folder(APPLICATION . DS . $application_name);
            if ($result) $result_str = 'success!'; else $result_str = 'failed!';
            $this->log->write('info', 'application `'.$application_name.'` creation ' . $result_str);
            header('Location: ' . $this->url->base() .$this->config->default_main_controller_method);
        }
    }
    public function dir() {
        $applications = NULL;
        $results = array();
        //check if the application has namespaces
        $application = $this->request->post('application');

        $current_session = $this->session->read(session_id());
        if ($current_session) {
            
            $this->godsql->level->where(array('level_id' => $current_session['level_id']));
            $current_level = current($this->godsql->level->select());
            if ($current_level && $current_level['is_admin'] == 1) {
                
                $applications = $this->godsql->application->select();
                
            } else {
                
                $this->godsql->application->where(array('level_id' => $current_session['level_id']));
                $applications = $this->godsql->application->select();
                
            }
            $list_items = $this->parser->template('manager/application/list_items', TRUE);
            foreach($applications as $value) {
                $results[] = $list_items->view(array(
                    'active' => $application == $value['name']?'active':'',
                    'name' => $value['name']
                ));
            }
        }
        $list = $this->parser->template('manager/application/list');
        return $this->parser->view(array('list_items' => implode('', $results)));
    }
}
?>