<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'pdo.php';

$db_url=$scheme . '://test:test@localhost/test';
$db_prefix='test_';
$db_debug=true;

db_connect($db_url);

require_once 'registry.php';

$msecs = microtime(true);

$now=time();

$semaphore = registry_get('cron_lock', false);

if ($semaphore) {
	dump($now - $semaphore);
}

registry_set('cron_last', $now);

registry_set('cron_lock', $now);

registry_delete('cron_lock');

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
