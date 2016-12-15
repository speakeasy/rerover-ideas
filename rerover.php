<?php

include 'd.php';

include 'fetcher.class.php';
include 'parser.class.php';
include 'db.class.php';
include 'dbg.php';

$db = new db();

$parser = new parser($db);

$fetcher = new fetcher($parser);

$fetcher->cfg([
	'cachedir' => 'fetcher-test1',
	'conncount' => 180,
	'ip' => '192.168.0.103',
	'recvbuflen' => 8192
]);

$__debug__ = false;
#$__debug__ = true;

$list = explode("\n", file_get_contents('/home/i336/rerover.filelist'));

//d($list);
//die;

foreach ($list as $url) {
	#$fetcher->enqueue('/home/i336/pacaurtmp-i336/lighttable/.git/'.$url);
	$fetcher->enqueue('//home/i336/chrome2/Default/Extensions/'.$url);
}


$fetcher->go();