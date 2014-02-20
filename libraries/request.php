<?php

defined('_CONFIGWEBSERVICE') or die('Restricted access');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Request extends Load {

    private function secure($params = NULL) {
        if (empty($params))
            return FALSE;
        if (is_array($params)) {
            $valid = FALSE;
            foreach ($params as $index => $value) {
                if (is_array($value)) {
                    foreach ($value as $sub_index => $sub_value) {
                        if (preg_match('/[a-zA-Z0-9\[\]]/i', $sub_index))
                            $valid = TRUE;
                        else
                            $valid = FALSE;
                        if (preg_match('/[a-zA-Z0-9\[\]]/i', $sub_value))
                            $valid = TRUE;
                        else
                            $valid = FALSE;
                    }
                } else {
                    if (preg_match('/[a-zA-Z0-9\[\]]/i', $index))
                        $valid = TRUE;
                    else
                        $valid = FALSE;
                    if (preg_match('/[a-zA-Z0-9\[\]]/i', $value))
                        $valid = TRUE;
                    else
                        $valid = FALSE;
                }
            }
            if ($valid)
                return $params;
            else
                return FALSE;
        } else if (preg_match('/[a-zA-Z0-9\[\]]/i', $params))
            return $params;
        else
            return FALSE;
    }

    public function post($params = NULL, $unsecure = FALSE) {
        if (empty($params) || empty($_POST[$params]))
            return FALSE;
        else if ($unsecure && !empty($_POST[$params]))
            return $_POST[$params];
        else
            return $this->secure($_POST[$params]);
    }

    public function get($params = NULL, $unsecure = FALSE) {
        if (empty($params) || empty($_GET[$params]))
            return FALSE;
        else if ($unsecure && !empty($_GET[$params]))
            return $_GET[$params];
        return $this->secure($_GET[$params]);
    }

    public function all($params = NULL) {
        if (empty($params) || empty($_REQUEST[$params]))
            return FALSE;
        return $this->secure($_REQUEST[$params]);
    }

    public function allowed() {
        $this->load->config('permissions');
        if (!empty($_SERVER['PWD']))
            return TRUE;
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->permissions->host_allow))
            return FALSE;
        else
            return TRUE;
    }

    public function resolve($redirect_method = NULL) {
        $this->load->config('config');
        $this->load->library('url');
        //check login session
        if (!$this->allowed())
            exit('You\'re not allowed here!');
        $request = $this->url->request();
        if (empty($request))
            header('Location: ' . $this->url->base() . $this->config->default_main_controller_method);
        $parsed_exploded = explode('/', $this->url->request());


        array_shift($parsed_exploded);
        array_walk($parsed_exploded, function(&$value) {
            if (empty($value))
                $value = 'index';
        });


        if (!empty($parsed_exploded)) {
            $class_index = NULL;
            $class_name = strtolower(get_class($this->father));
            foreach ($parsed_exploded as $value) {
                if ($class_name == $value) {
                    $class_index = key($parsed_exploded);
                    break;
                }
            }
            if (!empty($class_index))
                $parsed_exploded = array_slice($parsed_exploded, $class_index);
            if (!empty($redirect_method) && method_exists($this->father, $redirect_method))
                call_user_func_array(array($this->father, $redirect_method), $parsed_exploded);
            else {
                $method = array_shift($parsed_exploded);
                if (method_exists($this->father, $method) && is_callable(array($this->father, $method)))
                    call_user_func_array(array($this->father, $method), $parsed_exploded);
                else if (method_exists($this->father, 'index') && is_callable(array($this->father, 'index')))
                    call_user_func_array(array($this->father, 'index'), $parsed_exploded);
                else
                    header('Location: ' . $this->url->base() . $this->config->default_main_controller_method);
            }
        } else {
            header('Location: ' . $this->url->base() . $this->config->default_main_controller_method);
        }
    }

}

?>
