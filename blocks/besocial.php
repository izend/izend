<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function besocial($lang, $components=false) {
	$ilike=$tweetit=$plusone=false;

	extract($components);	/* ilike, tweetit, plusone */

	$mode='inline';

	if ($ilike) {
		$ilike=view('ilike', $lang, compact('mode'));
	}
	if ($tweetit) {
		$tweet_text=false;
		if (is_array($tweetit)) {
			extract($tweetit);	/* tweet_text */
		}
		$tweetit=view('tweetit', $lang, compact('mode', 'tweet_text'));
	}
	if ($plusone) {
		$plusone=view('plusone', $lang, compact('mode'));
	}

	$output = view('besocial', false, compact('ilike', 'tweetit', 'plusone'));

	return $output;
}

