<?php

/**
 *
 * @copyright  2012-2014 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(ROOT_DIR . PATH_SEPARATOR . get_include_path());
set_include_path(ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . PATH_SEPARATOR . get_include_path());
set_include_path(ROOT_DIR . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());

global $db_url;

@include 'settings.inc';
@include 'config.inc';
@include 'db.inc';

if ($db_url == 'mysql://username:password@localhost/databasename') {
	$db_url = false;
}

if ($db_url) {
	require_once 'pdo.php';

	db_connect($db_url);

	require_once 'cron.php';

	cron_run();
}
