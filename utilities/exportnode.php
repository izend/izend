<?php

/**
 *
 * @copyright  2016-2021 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require 'config.inc';

@include 'db.inc';
$db_debug = false;

if (isset($db_url) && $db_url == 'mysql://username:password@localhost/databasename') {
	$db_url = false;
}

if (!$db_url) {
	echo 'db_url?', PHP_EOL;
	exit( 1 );
}

define('USAGE', 'php %s node [lang]');

function abort($msg, $code=1) {
	echo $msg, PHP_EOL;
	exit($code);
}

function usage() {
	global $argv;

	abort(sprintf(USAGE, basename($argv[0])), 1);
}

$lang=$node_id=false;

switch ($argc) {
	case 3:
		$lang=$argv[2];
		/* fall thru */;
	case 2:
		$node_id=$argv[1];
		break;

	default:
		usage();
}

if (! (is_numeric($node_id) and $node_id > 0) ){
	abort($node_id . '?');
}
if ($lang and !in_array($lang, $supported_languages)) {
	abort($lang . '?');
}

$languages=$lang ? array($lang) : $supported_languages;

require_once 'pdo.php';

db_connect($db_url);

require_once 'models/node.inc';

$node=false;

foreach ($languages as $lang) {
	$r = node_get($lang, $node_id);
	if (!$r) {
		abort($node_id . '?');
	}
	extract($r); /* node_number node_name node_title node_abstract node_cloud node_image node_visits node_nocomment node_nomorecomment node_novote node_nomorevote node_ilike node_tweet node_linkedin node_pinit node_whatsapp */

	if (!$node) {
		$node=array(compact('node_visits', 'node_nocomment', 'node_nomorecomment', 'node_novote', 'node_nomorevote', 'node_ilike', 'node_tweet', 'node_linkedin', 'node_pinit', 'node_whatsapp'));
	}
	$node[$lang]=compact('node_name', 'node_title', 'node_abstract', 'node_cloud', 'node_image');
}

$node_contents=array();

foreach ($languages as $lang) {
	$r = node_get_contents($lang, $node_id);
	if (!$r) {
		continue;
	}

	foreach ($r as $c) {
		$content_type=$c['content_type'];
		$content_ignored=$c['content_ignored'];
		$content_number=$c['content_number'];

		if (!isset($node_contents[$content_number])) {
			$node_contents[$content_number]=array(compact('content_type', 'content_ignored'));
		}
		$content=&$node_contents[$content_number];

		$prefix="content_$content_type";

		$content[$lang]=array();

		foreach ($c as $fname => $val) {
			if (strpos($fname, $prefix) === 0) {
				$content[$lang][$fname]=$val;
			}
		}
	}
}

echo serialize(array($node, $node_contents)), PHP_EOL;

exit( 0 );
