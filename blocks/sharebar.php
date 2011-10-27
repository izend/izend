<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function sharebar($lang, $components=false) {
	$ilike=$tweetit=$plusone=false;

	extract($components);

	$mode='bar';

	if ($ilike) {
		$ilike=view('ilike', $lang, compact('mode'));
	}
	if ($tweetit) {
		$tweetit=view('tweetit', $lang, compact('mode'));
	}
	if ($plusone) {
		$plusone=view('plusone', $lang, compact('mode'));
	}

	$output = view('sharebar', false, compact('ilike', 'tweetit', 'plusone'));

	return $output;
}

