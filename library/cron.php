<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'dirlist.php';
require_once 'registry.php';

define('CRON_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'cron');

function cron_run() {
	$now=time();

	$semaphore = registry_get('cron_semaphore', false);

	if ($semaphore) {
		if ($now - $semaphore < 3600) {
			return false;
		}
	}

	registry_set('cron_last', $now);

	registry_set('cron_lock', $now);

	foreach (dirlist(CRON_DIR) as $file) {
		include $file;
	}

	registry_delete('cron_lock');

	return true;
}

function cron_cleanup() {
    registry_delete('cron_lock');
}

