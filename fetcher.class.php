<?php

class fetcher {
	
	function __construct($parser) {
		$this->parser = $parser;
		$this->urlqueue = [];
		$this->nextid = 0;
		
		
	}
	
	function cfg($cfg) {
		$this->cfg = (object)$cfg;
		
		if (isset($this->cfg->cachedir)) {
			
			if (substr($this->cfg->cachedir, -1) != '/') $this->cfg->cachedir .= '/';
			
			if (!file_exists($this->cfg->cachedir)) {
				
				mkdir($this->cfg->cachedir);
				
				dbg('Created '.$this->cfg->cachedir.'.');
				
			}
			
		}
		
	}
	
	function get_next_url() {
		
		if (count($this->urlqueue) > 0) {
			$nexturl = array_pop($this->urlqueue);
		} else {
			$nexturl = $this->nextid++;
		}
		
		dbg('Next URL: '.$nexturl);
		return $nexturl;
		
	}
	
	function enqueue($url) {
		
		dbg('Enqueuing URL: '.$url);
		
		$this->urlqueue[] = $url;
		
		
	}
	
	function create_socket() {
		
		$newsocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		//socket_set_nonblock($newsocket);
		
		socket_connect($newsocket, $this->cfg->ip, 80);
		
		$this->connpool[$newsocket] = [
			'socket' => $newsocket,
			'active' => 0,
			'url' => null
		];
		
		dbg('Created new socket: '.$newsocket);
		
	}
	
	function remove_socket($socket) {
		
		socket_close($socket);
		
		unset($this->connpool[$socket]);
		
	}
	
	function go() {
		
		global $__debug__;
		
		dbg('Starting up');
		
		$this->connpool = [];
		
		$active_conn_count = 0;
		
		for (;;) {
			
			print "ACTIVE CONNECTIONS: ".$active_conn_count."\n";
			
			//if (@$q++ == 100) z();
			
			if (isset($this->cfg->cachedir)) {
				
				$hash = hash('sha256', $nexturl);
				$file = $this->cfg->cachedir.$hash;
				
				if (file_exists($file)) {
					
					$nexturl = $this->get_next_url();
			
					print "Retrieving ".$nexturl." from cache\n";
					
					$data = file_get_contents($file);
					
					$r = parser::parse($data, $nexturl, null);
					
					if ($r === false) {
						print "Cached file error - ".$file."\n";
						print "Quitting\n";
						die;
					} else {
						
						// skip net code for this URL
						continue;
						
					}
					
				}
				
			}
			
			//die;
			
			
			
			
			
			
			if ($active_conn_count < $this->cfg->conncount) {
				
				$this->create_socket();
				$active_conn_count++;
				
			}
			
			$read = $write = [];
			foreach ($this->connpool as $c) {
				$read[] = $c['socket'];
				
				if ($c['active'] == 0) $write[] = $c['socket'];
				
				//if ($__debug__) {
			}
			
			//d($read, $write);
			
			//d($sockets);
			//d($this->connpool);
			#die;
			
			dbg1("Selecting:");
			
			//die;
			
			$r = socket_select($read, $write, $null, NULL);
			
			dbg2("<".$r."> R:[".implode(', ', $read)."]  W:[".implode(', ', $write)."]");
			
			if ($r === NULL) {
				print "SELECT FAILED\n";
			}
			
			//d($read, $write);
			
			//die;
			//$sockets = array_unique($read + $write);
			
			//d($sockets);
			
			//d($r);
			
			foreach ($write as $socket) {
				
				if ($this->connpool[$socket]['active'] == 0) {
					
					//socket_set_block($socket);
					
					//print 'using '.$socket."\n";
					
					$nexturl = $this->get_next_url();
					
					print "Fetching URL [".$socket."]: ".$nexturl."\n";
					
					dbg('Sending request to '.$socket);
					
					$this->connpool[$socket]['active'] = 1;
					$this->connpool[$socket]['url'] = $nexturl;
				
					socket_write($socket,
						"GET ".$nexturl. " HTTP/1.1\r\n".
						"Host: rover.info\r\n".
						"Connection: Keep-alive\r\n".
						"\r\n"
					);
					
				}
				
			}
			
			foreach ($read as $socket) {
				
				dbg1("recv <");
				
				$r = socket_recv($socket, $buffer, $this->cfg->recvbuflen, NULL);
				//$r = socket_read($socket, $this->cfg->recvbuflen);
				
				dbg2("OK>");
				
				//d($r);
				
				if ($r === FALSE) {
					
					dbg('Socket error!');
					
					// socket error
					// readd URL, if error happens max times, fail (and exit?)
					
					d(socket_strerror(socket_last_error($socket)));
					
				} elseif ($r == 0) {
					
					dbg('Socket closed');
					
					$this->remove_socket($socket);
					$active_conn_count--;
					
				} else {
					
					dbg('Socket has data');
					
					switch (parser::parse($buffer, $this->connpool[$socket]['url'], $this->connpool[$socket]['socket'])) {
						
						case true: //complete
							dbg('Socket '.$socket.' is complete');
							$this->connpool[$socket]['active'] = 0;
							break;
					
						case false: //needs more data
						dbg('Socket '.$socket.' not yet complete');
							$this->connpool[$socket]['active'] = 1;
							break;
							
					}
					
				}
				
			}
			
		}
		
	}
	
}