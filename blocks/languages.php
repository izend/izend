<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function languages($lang, $action, $arg=false) {
	global $supported_languages;

	$fr_page=($lang != 'fr' && in_array('fr', $supported_languages)) ? url($action, 'fr', $arg) : false;
	$en_page=($lang != 'en' && in_array('en', $supported_languages)) ? url($action, 'en', $arg) : false;

	$output = view('languages', false, compact('fr_page', 'en_page'));

	return $output;
}

