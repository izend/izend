<?php

/**
 *
 * @copyright  2011-2014 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require_once 'dump.php';

@include 'db.inc';
$db_debug = false;

$br = (php_sapi_name() == 'cli') ? '' : '</br>';

if (isset($db_url) && $db_url == 'mysql://username:password@localhost/databasename') {
	$db_url = false;
}

if (!$db_url) {
	echo 'db_url?', $br, PHP_EOL;
	exit( 1 );
}

require_once 'pdo.php';
db_connect($db_url);

require 'models/cloud.inc';

$tabthread=db_prefix_table('thread');
$sql="SELECT thread_id FROM $tabthread";
$r = db_query($sql);

if ($r) {
	$tabtagindex=db_prefix_table('tag_index');
	db_exec("TRUNCATE TABLE $tabtagindex");
	$tabtag=db_prefix_table('tag');
	db_exec("TRUNCATE TABLE $tabtag");

	foreach ($r as $t) {
		$thread_id=$t['thread_id'];
		echo $thread_id, $br, PHP_EOL;
//		cloud_delete($thread_id);
		cloud_create($thread_id);
	}
}

