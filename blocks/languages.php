<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function languages($lang, $action) {
	global $supported_languages;

	$fr_page=($lang != 'fr' && in_array('fr', $supported_languages)) ? url($action, 'fr') : false;
	$en_page=($lang != 'en' && in_array('en', $supported_languages)) ? url($action, 'en') : false;

	$output = view('languages', false, compact('fr_page', 'en_page'));

	return $output;
}

