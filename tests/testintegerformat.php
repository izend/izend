<?php
define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');


require_once 'dump.php';

require_once 'countformat.php';

foreach (array(0, 1, 999, 1000, 9999, 10000, 10001, 10100, 10399, 10499, 10500, 10900, 10999, 999999, 1000000, -1) as $n) {
	echo $n, ' => ', count_format($n), PHP_EOL;
}

