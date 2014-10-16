<?php
define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require_once 'qrencode.php';

$s='http://www.izend.org';
$size=10;
$quality='Q';
$fg='#333333';
$bg='#ffffc0';
$margin=0;

$png=qrencode($s, $size, $quality, $fg, $bg, $margin);

if ($png) {
	file_put_contents(ROOT_DIR . DIRECTORY_SEPARATOR . 'qr.png', $png);
}

