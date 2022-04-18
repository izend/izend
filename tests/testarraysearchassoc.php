<?php
define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');

require_once 'arraysearchassoc.php';
require_once 'arrayget.php';
require_once 'arrayput.php';

$test_array = array(
	'a' => array(
		'aa' => true,
		'ab' => array(
			'aaa' => array(
				'one' => 1,
				'two' => 2,
				'three' => 3,
				'four' => 4
			),
			'four' => 4,
			'five' => 5
		),
		'six' => 6,
	),
	'seven' => 7
);

$test_data = array(
	array('one', 1),
	array('two', 2),
	array('three', 3),
	array('four', 4),
	array('five', 5),
	array('six', 6),
	array('seven', 7),
	array('zero', 0),
	array('one', 0),
);

foreach ($test_data as $d) {
	$r = array_search_assoc($test_array, $d[0], $d[1]);

	echo $d[0] . ' => ' . $d[1] . ' ? ', $r ? implode('/', $r) . ' => ' . array_get($test_array, $r) : 'null', PHP_EOL;
}

foreach ($test_data as $d) {
	$r = array_search_assoc($test_array, $d[0], $d[1]);

	if ($r) {
		array_put($test_array, $r, $d[0]);
	}
}

array_put($test_array, array('a', 'ab', 'aaa', 'zero'), 'zero');

print_r($test_array);

