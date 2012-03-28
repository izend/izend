<?php
define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require_once 'dump.php';

require_once 'validateurl.php';

$testcases = array(
	array('HTTP://www.Example.com/',                     'http://www.example.com/'),
	array('http://www.example.com/a%c2%b1b',             'http://www.example.com/a%C2%B1b'),
	array('http://www.example.com/%7Eusername/',         'http://www.example.com/~username/'),
	array('http://www.example.com',                      'http://www.example.com/'),
	array('http://www.example.com:80/bar.html',          'http://www.example.com/bar.html'),
	array('http://www.example.com/../a/b/../c/./d.html', 'http://www.example.com/a/c/d.html'),
	array('eXAMPLE://a/./b/../b/%63/%7bfoo%7d',          'example://a/b/c/%7Bfoo%7D'),
	array('http://www.yahoo.com/%a1',                    'http://www.yahoo.com/%A1'),
	array('http://fancysite.nl/links/doit.pl?id=2029',   'http://fancysite.nl/links/doit.pl?id=2029'),
	array('http://example.com/index.html#fragment',      'http://example.com/index.html#fragment'),
	array('HtTp://User:Pass@www.ExAmPle.com:80/Blah',    'http://User:Pass@www.example.com/Blah'),
	array('http://example.com:81/index.html',            'http://example.com:81/index.html'),
	array('https://example.com:443',                     'https://example.com/'),
);

foreach ($testcases as $tc) {
	list($url, $normurl) = $tc;
	$newurl = normalize_url($url);
	echo $url, ' => ', $newurl;
	if ($newurl != $normurl) {
		echo ' != ', $normurl;
	}
	echo PHP_EOL;
}

