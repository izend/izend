<?php
define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require_once 'qrdecode.php';

$r=qrdecode('logos/siteqr.png');

if ($r) {
	echo $r, PHP_EOL;
}
