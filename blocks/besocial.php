<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function besocial($lang, $components=false) {
	$ilike=$tweetit=$plusone=false;

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'ilike':
					if ($param) {
						$ilike=view('ilike', $lang);
					}
					break;
				case 'tweetit':
					if ($param) {
						$tweetit=view('tweetit', $lang);
					}
					break;
				case 'plusone':
					if ($param) {
						$plusone=view('plusone', $lang);
					}
					break;
				default:
					break;
			}
		}
	}

	$output = view('besocial', false, compact('ilike', 'tweetit', 'plusone'));

	return $output;
}

