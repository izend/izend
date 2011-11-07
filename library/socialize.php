<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function socialize($lang, $components=false) {
	global $socializing;

	$besocial=$sharebar=false;

	switch ($socializing) {
		case 'inline':
			$besocial=build('besocial', $lang, $components);
			break;
		case 'bar':
			$sharebar=build('sharebar', $lang, $components);
			break;
		default:
			break;
	}

	return array($besocial, $sharebar);
}

