<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'registry.php';

function cron_run() {
	$semaphore = registry_get('cron_semaphore', FALSE);

	if ($semaphore) {
		if (time() - $semaphore > 3600) {
			registry_del('cron_lock');
		}
	}
	else {
		registry_set('cron_lock', time());

		registry_set('cron_last', time());
		registry_del('cron_lock');

		return true;
	}
}

function cron_cleanup() {
	if (registry_get('cron_lock', false)) {
	    registry_del('cron_lock');
 	}
}

