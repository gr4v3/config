<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class configServer extends socketServer {
}




class configServerClient extends socketServerClient {
	private $max_total_time = 450;
	private $max_idle_time  = 150;
	private $keep_alive = true;
	private $accepted;
	private $last_action;
        private $appcore = NULL;
        
        function __construct($socket) {
            global $appcore;
            parent::__construct($socket);
            $this->appcore = $appcore;
            $this->appcore->load->controller('server');
        }

	private function handle_request($request = NULL)
	{
            return $this->appcore->server->command($request);
	}

	public function on_read()
	{
            $this->last_action = time();
            /*
            $request = array();
            if ((strpos($this->read_buffer,"\r\n")) !== FALSE || (strpos($this->read_buffer,"\n\n")) !== FALSE) {
                $headers = $this->read_buffer;
            }*/
            $this->write($this->handle_request($this->read_buffer));
            $this->read_buffer = '';
	}

	public function on_connect()
	{
            $host = gethostbyaddr($this->remote_address);
            echo "[configServerClient] accepted connection from {$this->remote_address}:{$this->remote_port}";
            echo " @{$host}\n";
            $this->accepted    = time();
            $this->last_action = $this->accepted;
	}

	public function on_disconnect()
	{
            //echo "[httpServerClient] {$this->remote_address} disconnected\n";
	}

	public function on_write()
	{
            /*
            if (strlen($this->write_buffer) == 0 && !$this->keep_alive) {
                    $this->disconnected = true;
                    $this->on_disconnect();
                    $this->close();
            }
            */
	}

	public function on_timer()
	{
            $idle_time  = time() - $this->last_action;
            $total_time = time() - $this->accepted;
            if ($total_time > $this->max_total_time || $idle_time > $this->max_idle_time) {
                    echo "[httpServerClient] Client keep-alive time exceeded ({$this->remote_address})\n";
                    $this->close();
            }
	}
}


