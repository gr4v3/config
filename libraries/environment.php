<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Environment extends load {
    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
        $this->load->model('godsql');
        $this->load->library('url');
    }
    
    /************ methods called by the admin controller **************/
    public function admin() {
        $rows = $this->godsql->environment->select();
        $template = $this->parser->template('admin/environment/template', TRUE);
        foreach($rows as &$value) {
            $value['created'] = date($this->config->date_format, $value['created']);
            $value['ip'] = $this->system->inet_ntoa($value['ip']);
        }
        $this->father->render(NULL, $template->view(array('environment_items' => $rows)), 'admin/environment/admin');
    }
    public function create() {
        if ($this->request->post('create')) {
            $this->godsql->environment->insert(array(
                'name' => $this->request->post('name'),
                'domain' => $this->request->post('domain'),
                'ip' => $this->system->inet_aton($this->request->post('ip')),
                'created' => mktime()
            ));
            return $this->admin();
        }
        $template = $this->parser->template('admin/environment/create', TRUE);
        $this->father->render(NULL, $template->view(), 'admin/environment/create');
    }
    public function delete($environment_id = NULL) {
        if (empty($environment_id)) return FALSE;
        $this->godsql->environment->where(array('environment_id' => $environment_id));
        $this->godsql->environment->delete();
        return $this->admin();
    }
    public function modify($environment_id = NULL) {
        if ($this->request->post('modify')) {
            $environment_id = (int) $this->request->post('environment_id');
            $this->godsql->environment->where(array('environment_id' => $environment_id));
            $this->godsql->environment->update(array(
                'name' => $this->request->post('name'),
                'domain' => $this->request->post('domain'),
                'ip' => $this->system->inet_aton($this->request->post('ip'))
            ));
            return $this->admin();
        }
        if (empty($environment_id)) return FALSE;
        $this->godsql->environment->where(array('environment_id' => $environment_id));
        $row = $this->godsql->environment->select();
        if ($row) {
            $item = current($row);
            $item['ip'] = $this->system->inet_ntoa($item['ip']);
            $template = $this->parser->template('admin/environment/modify', TRUE);
            $this->father->render(NULL, $template->view($item), 'admin/environment/modify');
        } else return $this->admin(); 
    }
    
    /************ methods called by the manager controller **************/
    public function assign() {
        $application = $this->request->post('application');
        if (empty($application)) {
            // cannot be empty!
            $this->parser->template('manager/environment/error');
            $view = $this->parser->view(array('message' => 'Select an application first!'));
            return $this->father->father->render(array($view));
        }
        $create = $this->request->post('create');
        if (! empty($create)) {
            $name = $this->request->post('name');
            $result = $this->system->create->folder(ROOT_PATH . DS . 'application' . DS . $application . DS . $name);
            if ($result) $result_str = 'success!'; else $result_str = 'failed!';
            $this->log->write('info', 'environment `'.$name.'` creation ' . $result_str);
            header('Location: ' . $this->url->base() . $this->config->default_main_controller_method . '/?application=' . $application . '&environment=' . $name);
        }
        $this->parser->template('manager/environment/create');
        $view = $this->parser->view(array('options' => $this->godsql->environment->select()));
        $this->father->father->render(array($view));
    }
    public function unassign() {
        $application = $this->request->post('application');
        $environment = $this->request->post('environment');
        $result = $this->system->delete->folder(ROOT_PATH . DS .'application' . DS . $application . DS . $environment);
        if ($result) $result_str = 'success!'; else $result_str = 'failed!';
        $this->log->write('info', 'environment `'.$environment.'` delete ' . $result_str);
        header('Location: ' . $this->url->base() . $this->config->default_main_controller_method . '/?application=' . $application);
    }
    public function dir() {
        //check if the application has namespaces
        $this->load->library('request');
        $application = $this->request->post('application');
        $environment = $this->request->post('environment');
        if (empty($application)) return FALSE;
        $list = $this->parser->template('manager/environment/list');
        $list_items = $this->parser->template('manager/environment/list_items', TRUE);
        $results = array();
        $dir = $this->system->read->folder(ROOT_PATH . DS . 'application' . DS . $application);
        foreach($dir as $value) {
            if (!in_array($value,array('.','..'))) {
                $results[] = $list_items->view(array(
                    'active' => $environment == $value?'active':'',
                    'name' => $value
                ));
            }
        }
        return $this->parser->view(array('list_items' => implode('', $results)));
    }
}
?>
