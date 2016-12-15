<?php

function bt() {
	
	$bt = debug_backtrace();
	
	//d($bt[0]);
	
	print 'Backtrace from '.__dbg_file2module($bt[0]['file']).'#'.$bt[0]['line'].":\n";
	
	#d($bt);
	#z();#
	
	//die;
	
	$list = [];
	
	$l = count($bt);
	for ($i = 1; $i < $l; $i++) {
		$list[] = [
			($l - $i).' =>',
			__dbg_file2module($bt[$i]['file']),
			$bt[$i]['line'],
			$bt[$i]['function'].'()'#,
			#$bt[$i - 1]['line'],
		];
	}
	
	$cols = [];
	
	foreach ($list as $item) {
		foreach ($item as $i => $col) {
			if (!isset($cols[$i])) $cols[$i] = 0;
			if (($n = strlen($col)) > $cols[$i]) $cols[$i] = $n;
		}
	}
	
	#print "\n";
	
	foreach ($list as $item) {
		$line = '';
		foreach ($item as $i => $col) {
			$line .= ' ';
			$padding = str_repeat(' ', $cols[$i] - strlen($col));
			switch ($i) {
				case 0: $line = $col; break;
				case 1: $line .= $col.$padding; break;
				case 2: $line .= $padding.$col; break;
				case 3: $line .= '| '.$padding.$col; break;
				#case 4: $line .= '| '.$col; break;
			}
		}
		print $line."\n";
	}
	
}

function __dbg_file2module($filename) {
	
	$filename = substr($filename, strrpos($filename, '/') + 1);
	return substr($filename, 0, strpos($filename, '.'));
	
}

function d() {
	
	global $__dbg__;
	
	$bt = debug_backtrace();

	if (!isset($__dbg__['src'][$bt[0]['file']])) $__dbg__['src'][$bt[0]['file']] = explode("\n", file_get_contents($bt[0]['file']));
	$line = $__dbg__['src'][$bt[0]['file']][$bt[0]['line'] - 1];
	preg_match('/^[ \t]*d\((.*)\);/', $line, $m);
	
	$m = $m[1];
	
	//print_r($bt);
	
	print '('.__dbg_file2module($bt[0]['file']).':'.$bt[0]['line'].') ';
	
	//die;
	
	$argstart = $argend = [];
	$stack = $inquo = $indquo = 0;
	$next = 1;
	$l = strlen($m);
	for ($i = 0; $i < $l; $i++) {
		switch ($m[$i]) {
			case ' ': case "\t": continue 2;
			case '(': case '[': if (!($indquo + $inquo)) $stack++; break;
			case ']': case ')': if (!($indquo + $inquo)) $stack--; break;
			case '"': $indquo = !$indquo; break;
			case "'": $inquo = !$inquo; break;
		}
		if ($next) {
			$argstart[] = $i;
			$next = 0;
		}
		if (!$stack && !$indquo && !$inquo && $m[$i] == ',') {
			$argend[] = $i;
			$next = 1;
		}
	}
	
	$args = [];
	$l = count($argstart);
	for ($i = 0; $i < $l; $i++) {
		if (isset($argend[$i])) {
			$args[] = trim(substr($m, $argstart[$i], $argend[$i]-$argstart[$i]));
		} else {
			$args[] = trim(substr($m, $argstart[$i]));
		}
	}
	
	$values = func_get_args();
	$l = count($values);
	$o = '';
	for ($i = 0; $i < $l; $i++) {
		$o .= $args[$i].': '.__dbg_dump($values[$i]);
		if ($i < $l - 1) $o .= " \e[0;1;7m|\e[0m ";
	}
	
	print $o."\n";
	
}

function __dbg_dump($val, $depth = 1) {
	if (is_null($val)) {
		$o = "null";
	} elseif (is_bool($val)) {
		$o = 'bool: '.($val === true ? 'true' : 'false');
	} elseif (is_float($val)) {
		$s = strpos($val, '.');
		$o = 'float('.$s.'.'.(strlen($val) - $s - 1).'): '.$val;
	} elseif (is_int($val)) {
		$o = 'int('.$val.')';
	} elseif (is_string($val)) {
		
		$l = strlen($val);
		$val2 = '';
		for ($i = 0; $i < $l; $i++) {
			
			$ord = ord($val[$i]);
			
			if ($ord < 32 || $ord > 126) {
				$val2 .= '<'.$ord.'>';
			} else {
				$val2 .= $val[$i];
			}
			
		}
		
		
		$o = 'str('.strlen($val).'): "'.$val2.'"';
		
	} elseif (is_array($val)) {
		$o = 'arr('.count($val).'): [';
		$val2 = '';
		foreach ($val as $k => $v) $val2 .= str_repeat('  ', $depth).(is_string($k) ? "'".$k."'" : $k).': '.__dbg_dump($v, $depth + 1);
		$o .= ($val2 != "" ? "\n".$val2.str_repeat('  ', $depth-1) : '').']';
	} elseif (is_object($val)) {
		//print "A\n"; die;
		$o = __dbg_dump((array)$val, $depth);
	} elseif (is_resource($val)) {
		$o = 'res("'.get_resource_type($val).'#'.((int)$val).'")';
	} elseif (is_callable($val)) {
		print "C\n"; die;
	}
	return $o.($depth > 1 ? "\n" : "");
}

function l($str) {
	print $str."\n";
}

?>
