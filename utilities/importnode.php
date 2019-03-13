<?php

/**
 *
 * @copyright  2016-2019 izend.org
 * @version    2
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

define('USAGE', 'php %s thread infile [lang]');

function abort($msg, $code=1) {
	echo $msg, PHP_EOL;
	exit($code);
}

function usage() {
	global $argv;

	abort(sprintf(USAGE, basename($argv[0])), 1);
}

$lang=$thread_id=$infile=false;
$user_id=1;

switch ($argc) {
	case 4:
		$lang=$argv[3];
		/* fall thru */
	case 3:
		$thread_id=$argv[1];
		$infile=$argv[2];
		break;

	default:
		usage();
}

if (! (is_numeric($thread_id) and $thread_id > 0) ){
	abort($thread_id . '?');
}

if ($lang and !in_array($lang, $supported_languages)) {
	abort($lang . '?');
}

$s=@file_get_contents($infile);
if (!$s) {
	abort($infile . '?');
}

$data=@unserialize($s);

if (! (is_array($data) and count($data) == 2)) {
	abort($infile . '?');
}

list($node, $contents)=$data;

$languages=$lang ? array($lang) : $supported_languages;

require_once 'pdo.php';

db_connect($db_url);

require_once 'models/thread.inc';

if (!thread_id($thread_id)) {
	abort($thread_id . '?');
}

$node_id=false;

foreach ($languages as $lang) {
	if (isset($node[$lang])) {
		extract($node[$lang]); /* node_name node_title node_abstract node_cloud node_image */

		if (!$node_id) {
			extract($node[0]); /* node_ignored node_visits node_nocomment node_nomorecomment node_ilike node_tweet node_plusone node_linkedin node_pinit node_whatsapp */
			$r = thread_create_node($lang, $user_id, $thread_id, $node_name, $node_title);
			if (!$r) {
				abort($node_name . '?');
			}
			extract($r); /* node_id node_number */
		}

		$r = thread_set_node($lang, $thread_id, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_image, $node_visits, $node_nocomment, $node_nomorecomment, $node_novote, $node_nomorevote, $node_ilike, $node_tweet, $node_plusone, $node_linkedin, $node_pinit, $node_whatsapp);
		if (!$r) {
			abort($node_name . '?');
		}
	}
}

$node_contents=array();

foreach ($contents as $c) {
	$content_id=false;

	foreach ($languages as $lang) {
		if (isset($c[$lang])) {
			if (!$content_id) {
				extract($c[0]); /* content_type content_ignored */
				$r = node_create_content($lang, $node_id, $content_type);
				if (!$r) {
					abort($content_number . '?');
				}
				extract($r); /* content_id content_number */
			}

			if (!isset($node_contents[$lang])) {
				$node_contents[$lang]=array();
			}

			$node_contents[$lang][]=array_merge(compact('content_id', 'content_type', 'content_ignored'), $c[$lang]);
		}
	}
}

foreach ($node_contents as $lang => $c) {
	$r = node_set_contents($lang, $node_id, $c);

	if (!$r) {
		abort($node_id . '?');
	}
}

exit( 0 );
