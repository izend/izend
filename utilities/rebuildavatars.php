<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
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

require_once 'db.php';
db_connect($db_url);

require 'models/user.inc';

$tabuser=db_prefix_table('user');
$sql="SELECT name FROM $tabuser";
$r = db_query($sql);

if ($r) {
	foreach ($r as $u) {
		$name=$u['name'];
		user_create_avatar($name);
	}
}

