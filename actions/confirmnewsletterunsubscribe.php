<?php

/**
 *
 * @copyright  2014-2018 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'models/newsletter.inc';

function confirmnewsletterunsubscribe($lang, $arglist) {
	head('title', translate('newsletter:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	list($timestamp, $mail)=$arglist;

	$bad_mail=false;
	$bad_time=false;

	if (!newsletter_get_user($mail)) {
		$bad_mail=true;
	}

	if (time() - $timestamp > 3600) {
		$bad_time=true;
	}

	$subscribe_page=$unsubscribe_page=false;

	$internal_error=false;
	$contact_page=false;

	if ($bad_mail) {
		$subscribe_page=url('newslettersubscribe', $lang);
	}
	else if ($bad_time) {
		$unsubscribe_page=url('newsletterunsubscribe', $lang);
	}

	else {
		$r = newsletter_delete_user($mail);

		if (!$r) {
			$internal_error=true;
		}
		else {
			require_once 'serveripaddress.php';
			require_once 'emailme.php';

			global $sitename;

			$ip=server_ip_address();
			$timestamp=strftime('%Y-%m-%d %H:%M:%S', time());
			$subject = 'unsubscribe' . '@' . $sitename;
			$msg = $ip . ' ' . $timestamp . ' ' . $lang . ' ' . $mail;
			@emailme($subject, $msg);

			$subscribe_page=url('newslettersubscribe', $lang);
		}
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}

	$errors = compact('bad_mail', 'bad_time', 'internal_error', 'contact_page');

	$content = view('confirmnewsletterunsubscribe', $lang, compact('mail', 'subscribe_page', 'unsubscribe_page', 'errors'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

