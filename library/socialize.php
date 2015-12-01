<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function socialize($lang, $components=false) {
	global $socializing, $socializingmode;

	$besocial=$sharebar=false;

	switch ($socializing) {
		case 'either':
			$besocial=build('besocial', $lang, $components, $socializingmode, true);
			$sharebar=build('sharebar', $lang, $components, $socializingmode);
			break;
		case 'both':
			$besocial=build('besocial', $lang, $components, $socializingmode);
			$sharebar=build('sharebar', $lang, $components, $socializingmode);
			break;
		case 'inline':
			$besocial=build('besocial', $lang, $components, $socializingmode);
			break;
		case 'bar':
			$sharebar=build('sharebar', $lang, $components, $socializingmode);
			break;
		default:
			break;
	}

	return array($besocial, $sharebar);
}

