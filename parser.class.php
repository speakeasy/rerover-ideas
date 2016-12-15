<?php

class parser {
	
	function __construct($db) {
		$this->db = $db;
		
		$this->buffers = [];
		
	}
	
	function parse($data, $url, $socket) {
		
		dbg('Parsing');
		
		#d($data);
		
		if ($socket !== null) {
		
			if (!isset($this->buffers[$socket])) {
				$this->buffers[$socket] = '';
			}
			
			$this->buffers[$socket] .= $data;
			
			$buffer = $this->buffers[$socket];
			
		} else {
			
			//d('X');
			
			dbg('Parsing from file.');
			
			$buffer = $data;
			
		}
		
		//print $buffer;
		//die;
		
		$s = strpos($buffer, "\r\n\r\n");
		if ($s == null) return false;
		
		$rawheaders = trim(substr($buffer, 0, $s));
		$content = substr($buffer, $s + 4);
		
		//print $rawheaders;
		
		foreach (explode("\r\n", substr($rawheaders, strpos($rawheaders, "\r\n") + 2)) as $line) {
			
			//d($line);
			
			//die;
			
			list($k, $v) = explode(":", $line, 2);
			
			//d($k, $v);
			
			$k = trim($k);
			$v = trim($v);
			
			$headers[strtolower($k)] = $v;
			
		}
		
		//d($headers);
		
		//die;
		
		//if 
		
		//die;
		
		//d($content);
		
		//d(strlen($content));
		
		//print $data;
		
		//die;
		
		//d(strlen($buffer), $headers);
		
		//d($content);
		
		//d($buffer);
		
		//die;
		
		if (strlen($content) == $headers['content-length']) {
			dbg('Parsing for '.$socket.' complete');
			
			if ($socket !== null) {
				$this->buffers[$socket] = '';
				
				if (isset($this->cfg->cachedir)) {
					
					$hash = hash('sha256', $url);
					
					$file = $this->cfg->cachedir.$hash;
					
					file_put_contents($file, $buffer);
					
				}
				
			}
			return true;
		} else {
			dbg('Parsing for '.$socket.' incomplete');
			return false;
		}
		
		
		
	}
	
	function parse_object() {
		
		
		
		
		
	}
	
	function parse_viewcomments() {
		
		
		
		
	}
	
	function parse_rview() {
		
		
		
		
		
	}
	
}