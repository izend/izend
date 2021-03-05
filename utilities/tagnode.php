<?php

/**
 *
 * @copyright  2021 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require 'config.inc';

require_once 'dump.php';

function trace($var, $label=null) {
	return dump($var, $label);
}

@include 'db.inc';

$db_debug = false;

if (isset($db_url) && $db_url == 'mysql://username:password@localhost/databasename') {
	$db_url = false;
}

if (!$db_url) {
	echo 'db_url?', PHP_EOL;
	exit( 1 );
}

define('USAGE', 'php %s node_id infile lang');

function abort($msg, $code=1) {
	echo $msg, PHP_EOL;
	exit($code);
}

function usage() {
	global $argv;

	abort(sprintf(USAGE, basename($argv[0])), 1);
}

$lang=$node_id=$infile=false;

switch ($argc) {
	case 4:
		$node_id=$argv[1];
		$infile=$argv[2];
		$lang=$argv[3];
		break;

	default:
		usage();
}

if (! (is_numeric($node_id) and $node_id > 0) ){
	abort($node_id . '?');
}

if (!in_array($lang, $supported_languages)) {
	abort($lang . '?');
}

$wlist=@file($infile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!$wlist) {
	abort($infile . '?');
}

$wlist=array_unique($wlist);

setlocale(LC_COLLATE | LC_CTYPE, $lang);

sort($wlist, SORT_LOCALE_STRING);

$cloud=implode(' ', $wlist);

require_once 'pdo.php';

db_connect($db_url);

require_once 'models/node.inc';

$sqllang=db_sql_arg($lang, false);
$sqlcloud=db_sql_arg($cloud, true, true);

$tabnodelocale=db_prefix_table('node_locale');

$sql="UPDATE $tabnodelocale SET cloud=$sqlcloud WHERE node_id=$node_id AND locale=$sqllang";

$r = db_update($sql);

if ($r === false) {
	abort($node_id . '?');
}

cloud_tag_node($lang, $node_id, $cloud);
