<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Namespaces extends load {

    function __construct() {
        parent::__construct();
        $this->load->config('config');
        $this->load->library('log');
        $this->load->library('parser');
        $this->load->library('request');
        $this->load->library('system');
        $this->load->library('url');
    }

    public function index() {
        
    }

    public function dir() {
        //check if the application has namespaces
        $this->load->library('request');
        $application = $this->request->post('application');
        $environment = $this->request->post('environment');
        $namespaces = $this->request->post('namespaces');
        if (empty($application) || empty($environment))
            return FALSE;
        $list = $this->parser->template('manager/namespaces/list');
        $list_items = $this->parser->template('manager/namespaces/list_items', TRUE);
        $results = array();
        $dir = $this->system->read->folder(ROOT_PATH . DS . 'application' . DS . $application . DS . $environment);
        foreach ($dir as $value) {
            if (!in_array($value, array('.', '..'))) {
                $results[] = $list_items->view(array(
                    'active' => $namespaces == $value ? 'active' : '',
                    'name' => $value
                ));
            }
        }
        return $this->parser->view(array('list_items' => implode('', $results)));
    }

    public function create() {
        $environment = $this->request->post('environment');
        if (empty($environment)) {
            // cannot be empty!
            $this->parser->template('manager/namespaces/error');
            $view = $this->parser->view(array('message' => 'Select an environment first!'));
            return $this->father->father->render(array($view));
        }
        $create = $this->request->post('create');
        $cancel = $this->request->post('cancel');
        if (!empty($cancel))
            header('Location: /' . $this->config->default_main_controller_method);
        if (!empty($create)) {
            $application = $this->request->post('application');
            $environment = $this->request->post('environment');
            $name = $this->request->post('name');
            $path = array(
                'application',
                $application,
                $environment
            );
            $handle = $this->system->create->file(ROOT_PATH . DS . implode(DS, $path) . DS . $name);
            if ($handle) {
                $result_str = 'success!';
                fclose($handle);
            } else
                $result_str = 'failed!';
            $this->log->write('info', 'namespace `' . $name . '` creation ' . $result_str);
            header('Location: ' . $this->url->base() . $this->config->default_main_controller_method .
                    '/?application=' . $application . '&environment=' . $environment . '&namespaces=' . $name);
        }

        $this->parser->template('manager/namespaces/create');
        $view = $this->parser->view();
        $this->father->father->render(array($view));
    }

    public function erase() {
        $application = $this->request->post('application');
        $environment = $this->request->post('environment');
        $namespaces = $this->request->post('namespaces');
        $result = $this->system->delete->file(ROOT_PATH . DS . 'application' . DS . $application . DS . $environment . DS . $namespaces);
        if ($result)
            $result_str = 'success!';
        else
            $result_str = 'failed!';
        $this->log->write('info', 'environment `' . $environment . '` delete ' . $result_str);
        header('Location: ' . $this->url->base() . $this->config->default_main_controller_method . '/?application=' . $application . '&environment=' . $environment);
    }

    public function update() {
        $namespaces = $this->request->post('namespaces');
        $add = $this->request->post('add');
        $update = $this->request->post('update');
        $remove = $this->request->post('remove');
        if (!empty($namespaces)) {
            $application = $this->request->post('application');
            $environment = $this->request->post('environment');
            $remove = $this->request->post('remove');
            $path = ROOT_PATH . 'application' . DS . $application . DS . $environment . DS . $namespaces;

            $file = $this->system->read->file($path);
            $file_content_assoc = json_decode($file, TRUE);
            if (!empty($add) || !empty($update)) {
                $content = $this->request->post('content');
                if (!empty($content)) {
                    foreach ($content['index'] as $index => $value) {
                        $file_content_assoc[$value] = $content['value'][$index];
                    }
                }
                $contentnew = $this->request->post('contentnew');
                if (!empty($contentnew)) {
                    foreach ($contentnew['index'] as $index => $value) {
                        $file_content_assoc[$value] = $contentnew['value'][$index];
                    }
                }
            }
            if (!empty($remove)) {
                if (!empty($remove))
                    unset($file_content_assoc[$remove]);
            }

            $this->system->write->file($path, $file_content_assoc);

            if (empty($file_content_assoc))
                $file_content_assoc = array();
            $content = $this->parser->template('manager/content/list', TRUE);
            $new = $this->parser->template('manager/content/new', TRUE);
            $item = $this->parser->template('manager/content/item', TRUE);
            $items = array();
            foreach ($file_content_assoc as $index => $value) {
                $items[] = $item->view(array(
                    'count' => count($items),
                    'index' => $index,
                    'value' => $value
                ));
            }
            $content = $content->view(array(
                'new' => $new->view(),
                'items' => implode('', $items)
            ));
            return $content;
        } else
            return NULL;
    }

}

?>
