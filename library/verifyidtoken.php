<?php

/**
 *
 * @copyright  2023 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'vendor/autoload.php';

function verifyidtoken($credential, $client_id=false) {
	try {
		$client = new Google_Client(['client_id' => $client_id]);

		return $client->verifyIdToken($credential);
	}
	catch(Exception $e) {
	}

	return false;
}

