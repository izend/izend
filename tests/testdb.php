<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

define('USAGE', 'php -f %s mysql|pgsql init|model');

$models=array('init', 'user', 'node', 'thread', 'cloud', 'newsletter', 'registry', 'rss');

function abort($msg, $code=1) {
	echo $msg, PHP_EOL;
	exit($code);
}

function usage() {
	global $argv;

	abort(sprintf(USAGE, basename($argv[0])), 1);
}

if (!($argc == 3)) {
	usage();
}

$scheme=$argv[1];

if (!in_array($scheme, array('mysql', 'pgsql'))) {
	usage();
}

$model=$argv[2];

if (!in_array($model, $models)) {
	usage();
}

define('ROOT_DIR', dirname(__FILE__));

set_include_path(ROOT_DIR . PATH_SEPARATOR . get_include_path());
set_include_path(ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . PATH_SEPARATOR . get_include_path());
set_include_path(ROOT_DIR . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());

require_once 'dump.php';

include 'testdb' . $model . '.php';
