<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'registry.php';

define('CRON_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'cron');

function cron_run() {
	$files=glob(CRON_DIR . DIRECTORY_SEPARATOR . '*.php');

	if (!$files) {
		return true;
	}

	$now=time();

	$semaphore = registry_get('cron_lock', false);

	if ($semaphore) {
		if ($now - $semaphore < 3600) {
			return false;
		}
	}

	registry_set('cron_last', $now);
	registry_set('cron_lock', $now);

	foreach ($files as $f) {
		include $f;
	}

	registry_delete('cron_lock');

	return true;
}

function cron_cleanup() {
    registry_delete('cron_lock');
}

