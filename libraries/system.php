<?php

defined('_CONFIGWEBSERVICE') or die('Restricted access');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class System_Helper {

    private $caller = NULL;
    private $mode = NULL;

    function __construct($caller = NULL, $mode = 'create') {
        $this->caller = $caller;
        $this->mode = $mode;
    }

    public function search($handle = NULL, $index = NULL) {
        $line = fgets($handle);
        if ($line) {
            $row = json_decode($line, TRUE);
            if (!empty($row[$index]))
                return $row[$index];
            else
                return $this->search($handle, $index);
        } else
            return FALSE;
    }

    public function file($params = NULL, $rename = NULL) {
        if (empty($params))
            return FALSE;
        switch ($this->mode) {
            case 'delete':
                return unlink($params);
            case 'rename':
                return rename($params, $rename);
            case 'create':
                return @fopen($params, "w+");
            case 'read':
                if (is_file($params))
                    return file_get_contents($params);
                else
                    return NULL;
            case 'search':
                $handle = @fopen($params, "r");
                $line = $this->search($handle, $params);
                fclose($handle);
                return $line;
            case 'write':
                if (empty($rename))
                    $rename = array();
                if (is_file($params))
                    return file_put_contents($params, json_encode($rename));
                else
                    return FALSE;
            case 'update':
                $handle = fopen($params, "c");

                return TRUE;
        }
    }

    public function folder($params = NULL, $rename = NULL) {
        if (empty($params))
            return FALSE;
        switch ($this->mode) {
            case 'delete':
                return $this->delTree($params);
            case 'rename':
                return rename($params, $rename);
            case 'update':
            case 'write':
            case 'create':
                if (!is_dir($params))
                    return mkdir($params, 0755);
                else
                    return TRUE;
            case 'search':
            case 'read':
                $dir = @scandir($params);
                if (empty($dir))
                    return array();
                $results = array();
                foreach ($dir as $value) {
                    if (!in_array($value, array('.', '..', '.svn')))
                        $results[] = $value;
                }
                return $results;
        }
    }

    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

}

class System {

    function __construct() {
        $this->create = new System_Helper($this, 'create');
        $this->rename = new System_Helper($this, 'rename');
        $this->delete = new System_Helper($this, 'delete');
        $this->read = new System_Helper($this, 'read');
        $this->search = new System_Helper($this, 'search');
        $this->write = new System_Helper($this, 'write');
        $this->update = new System_Helper($this, 'update');
        $this->search = new System_Helper($this, 'search');
    }

    // IP Address to Number
    function inet_aton($ip) {
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
            return 0;
        return sprintf("%u", ip2long($ip));
    }

    // Number to IP Address
    function inet_ntoa($num) {
        $num = trim($num);
        if ($num == "0")
            return "0.0.0.0";
        return long2ip(-(4294967295 - ($num - 1)));
    }

    function normalize_array($arr = NULL, $index = NULL) {
        if (empty($arr) || empty($index) || !is_array($arr))
            return $arr;
        $result = array();
        foreach ($arr as $value) {
            $result[$value[$index]] = $value;
        }
        return $result;
    }

}

?>
