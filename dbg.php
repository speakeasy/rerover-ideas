<?php

register_shutdown_function('z');

function z() {
	$bt = debug_backtrace();
	$i = isset($bt[0]['line']) ? ' (at '.__dbg_file2module($bt[0]['file']).':'.$bt[0]['line'].')' : "";
	print "\n\n\e[7m-- die".$i." --\e[0m\n";
	die;
}

function dbg($text) {
	global $__debug__;
	if ($__debug__ == true) {
		
		$bt = debug_backtrace();
		
		//d($bt);
		
		$file = substr($bt[0]['file'], strrpos($bt[1]['file'], '/') + 1);
		$file = substr($file, 0, strpos($file, '.'));
		
		//$function = $bt[2]['function'];
		
		$line = $bt[0]['line'];
		
		//print '['.$file.'#'.$line.':'.$function.'] '.$text."\n";
		print '['.$file.'#'.$line.'] '.$text."\n";
		
		//die;
		
	}
}


function dbg1($text) {
	global $__debug__;
	if ($__debug__ == true) {
		
		$bt = debug_backtrace();
		
		$file = substr($bt[0]['file'], strrpos($bt[1]['file'], '/') + 1);
		$file = substr($file, 0, strpos($file, '.'));
		
		$line = $bt[0]['line'];
		
		//print '['.$file.'#'.$line.':'.$function.'] '.$text;
		print '['.$file.'#'.$line.'] '.$text;
		
		//die;
		
	}
}

function dbg2($text) {
	
		global $__debug__;
	if ($__debug__ == true) {
		print $text."\n";
	}
	
}