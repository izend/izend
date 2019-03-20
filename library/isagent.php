<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function is_agent($s, $agent=false) {
	if (!$agent) {
		require_once 'useragent.php';

		$agent=user_agent();
	}

	return $agent and preg_match('/'. $s . '/i', $agent);
}

function is_ipad($agent=false) {
	return is_agent('ipad', $agent);
}

function is_iphone($agent=false) {
	return is_agent('iphone', $agent);
}

function is_android($agent=false) {
	return is_agent('android', $agent);
}

function is_facebook($agent=false) {
	return is_agent('facebook', $agent);
}

function is_google($agent=false) {
	return is_agent('google', $agent);
}

function is_opengraph($agent=false) {
	$bots = array(
		'facebookexternalhit',
		'facebot',
		'google',
		'twitterbot',
		'linkedinbot',
		'pinterest',
		'whatsapp',
	);

	return is_agent(implode('|', $bots), $agent);
}

function is_bot($agent=false){
	$bots = array(
		'googlebot',
		'bingbot',
		'yahoo! slurp',
		'baiduspider',
		'yandexbot',
	);

	return is_agent(implode('|', $bots), $agent);
}
