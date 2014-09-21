<?php

/**
 *
 * @copyright  2013-2014 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'tokenid.php';
require_once 'models/newsletter.inc';

function postnews($lang, $newsletter_id, $page_id) {
	$postdate=$scheduled=$mailed=false;

	$r = newsletter_get_post($newsletter_id, $page_id, $lang);

	if ($r) {
		extract($r);	// newsletter_post_scheduled, newsletter_post_mailed

		$scheduled=$newsletter_post_scheduled;
		$mailed=$newsletter_post_mailed;
	}

	if ($mailed) {
		return view('postnews', $lang, compact('mailed'));
	}

	$action='init';
	if (isset($_POST['postnews_post']) and !$scheduled) {
		$action='post';
	}
	else if (isset($_POST['postnews_cancel']) and $scheduled and !$mailed) {
		$action='cancel';
	}

	$hmin=8;
	$hmax=18;

	$token=false;

	$date=false;
	$hour=$hmin;
	$minute=0;

	switch($action) {
		case 'init':
			break;

		case 'post':
			if (isset($_POST['postnews_date'])) {
				$date=readarg($_POST['postnews_date']);
			}
			if (isset($_POST['postnews_hour'])) {
				$hour=readarg($_POST['postnews_hour']);
			}
			if (isset($_POST['postnews_minute'])) {
				$minute=readarg($_POST['postnews_minute']);
			}
			if (isset($_POST['postnews_token'])) {
				$token=readarg($_POST['postnews_token']);
			}
			break;

		case 'cancel':
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_date=false;
	$bad_date=false;

	$internal_error=false;

	switch($action) {
		case 'post':
			if (!isset($_SESSION['postnews_token']) or $token != $_SESSION['postnews_token']) {
				$bad_token=true;
			}

			if (!is_numeric($hour)) {
				$hour=$hmin;
			}
			else if ($hour < $hmin) {
				$hour=$hmin;
			}
			else if ($hour >= $hmax) {
				$hour=$hmax;
				$minute=0;
			}

			if (!is_numeric($minute)) {
				$minute=0;
			}
			else if ($minute < 0) {
				$minute=0;
			}
			else if ($minute > 59) {
				$minute=59;
			}

			if (!$date) {
				$missing_date=true;
			}
			else if (!preg_match('#^([0-9]{4})([/-])([0-9]{2})\2([0-9]{2})$#', $date, $d)) {
				$bad_date=true;
			}
			else if (!checkdate($d[3], $d[4], $d[1])) {
				$bad_date=true;
			}

			if ($missing_date or $bad_date) {
				break;
			}

			$postdate=mktime($hour, $minute, 0, $d[3], $d[4], $d[1]);

			if ($postdate < mktime($hmin, 0, 0)) {
				$bad_date=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'post':
			if ($bad_token or $missing_date or $bad_date) {
				break;
			}

			$r = newsletter_schedule_post($newsletter_id, $page_id, $lang, $postdate);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$scheduled=$postdate;

			break;

		case 'cancel':
			$r = newsletter_cancel_post($newsletter_id, $page_id, $lang);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$scheduled=false;

			break;

		default:
			break;
	}

	if (!$scheduled) {
		$postdate=mktime($hour, $minute, 0);
		if (time() > mktime($hmax+1, 0, 0)) {
			$postdate=strtotime('+1 day', $postdate);
		}
	}

	$_SESSION['postnews_token'] = $token = token_id();

	$errors = compact('missing_date', 'bad_date', 'internal_error');

	$output = view('postnews', $lang, compact('token', 'scheduled', 'mailed', 'hmin', 'hmax', 'postdate', 'errors'));

	return $output;
}

