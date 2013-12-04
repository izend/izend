<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function languages($lang, $action, $arg=false) {
	global $supported_languages;

	if (count($supported_languages) < 2) {
		return false;
	}

	$fr_page=($lang != 'fr' && in_array('fr', $supported_languages)) ? url($action, 'fr', $arg) : false;
	$en_page=($lang != 'en' && in_array('en', $supported_languages)) ? url($action, 'en', $arg) : false;
	$de_page=($lang != 'de' && in_array('de', $supported_languages)) ? url($action, 'de', $arg) : false;
	$it_page=($lang != 'it' && in_array('it', $supported_languages)) ? url($action, 'it', $arg) : false;
	$es_page=($lang != 'es' && in_array('es', $supported_languages)) ? url($action, 'es', $arg) : false;
	$ru_page=($lang != 'ru' && in_array('ru', $supported_languages)) ? url($action, 'ru', $arg) : false;

	$output = view('languages', false, compact('fr_page', 'en_page', 'de_page', 'it_page', 'es_page', 'ru_page'));

	return $output;
}

