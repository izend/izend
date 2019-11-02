<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    8
 * @link       http://www.izend.org
 */

function sharebar($lang, $components=false, $sharemode=false) {
	$ilike=$tweetit=$linkedin=$pinit=$whatsapp=false;

	extract($components);	/* ilike tweetit linkedin pinit whatsapp */

	$mode=$sharemode == 'standard' ? 'bar' : $sharemode;

	if ($whatsapp) {
		require_once 'ismobile.php';

		if (!is_mobile()) {
			$whatsapp=false;
		}
	}

	if ($ilike) {
		$ilike=view('ilike', $lang, compact('mode'));
	}
	if ($tweetit) {
		$tweet_text=false;
		if (is_array($tweetit)) {
			extract($tweetit);	/* tweet_text */
		}
		$tweet_text=preg_replace('/\s+/', ' ', trim($tweet_text));
		$tweetit=view('tweetit', $lang, compact('mode', 'tweet_text'));
	}
	if ($linkedin) {
		$linkedin=view('linkedin', $lang, compact('mode'));
	}
	if ($pinit) {
		$pinit_text=$pinit_image=false;
		if (is_array($pinit)) {
			extract($pinit);	/* pinit_text pinit_image */
		}
		$pinit=view('pinit', $lang, compact('mode', 'pinit_text', 'pinit_image'));
	}
	if ($whatsapp) {
		$whatsapp=view('whatsapp', $lang, compact('mode'));
	}

	$output = view('sharebar', false, compact('sharemode', 'ilike', 'tweetit', 'linkedin', 'pinit', 'whatsapp'));

	return $output;
}

