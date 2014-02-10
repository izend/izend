<?php
define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require_once 'config.inc';

require_once 'dump.php';

require_once 'urlencodeaction.php';

$id=1;
$p=array('mail' => 'izend@izend.org');

$s64=urlencodeaction($id, $p);
echo $s64, PHP_EOL;

$r=urldecodeaction($s64);

if ($r) {
	list($actionid, $timestamp, $param)=$r;
}

echo 'id=', $actionid, PHP_EOL;
echo 'timestamp=', date('Y-m-d H:i:s', $timestamp), PHP_EOL;
echo 'mail=', $param['mail'], PHP_EOL;

$id=0;
$p='izend@izend.org';

$s64=urlencodeaction($id, $p);
echo $s64, PHP_EOL;

$r=urldecodeaction($s64);

if ($r) {
	list($actionid, $timestamp, $param)=$r;
}

echo 'id=', $actionid, PHP_EOL;
echo 'timestamp=', date('Y-m-d H:i:s', $timestamp), PHP_EOL;
echo 'mail=', $param, PHP_EOL;

$id=255;
$s64=urlencodeaction($id, $p);
echo $s64, PHP_EOL;

$r=urldecodeaction($s64);

if ($r) {
	list($actionid, $timestamp)=$r;
}

echo 'id=', $actionid, PHP_EOL;
echo 'timestamp=', date('Y-m-d H:i:s', $timestamp), PHP_EOL;
