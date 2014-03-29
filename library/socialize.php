<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function socialize($lang, $components=false) {
	global $socializing;

	$besocial=$sharebar=false;

	switch ($socializing) {
		case 'either':
			$besocial=build('besocial', $lang, $components, true);
			$sharebar=build('sharebar', $lang, $components);
			break;
		case 'both':
			$besocial=build('besocial', $lang, $components);
			$sharebar=build('sharebar', $lang, $components);
			break;
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

