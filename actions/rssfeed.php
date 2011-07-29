<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function rssfeed($lang) {
	$description = translate('description', $lang);
	$itemlist = array();

	$output = view('rssfeed', false, compact('description', 'lang', 'itemlist'));

	return $output;
}
