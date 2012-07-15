<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userhasrole.php';

function footer($lang, $components=false) {
	$languages=false;

	$contact_page=$user_page=$nobody_page=$account_page=$admin_page=false;

	$is_identified = user_is_identified();
	$is_admin = user_has_role('administrator');

	$nobody_page=$is_identified ? url('nobody', $lang) : false;

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'languages':
					if ($param) {
						$languages = build('languages', $lang, $param);
					}
					break;
				case 'contact':
					if ($param) {
						$contact_page=url('contact', $lang);
					}
					break;
				case 'account':
					if ($param) {
						if ($is_identified) {
							if (!$is_admin) {
								$account_page=url('account', $lang);
							}
						}
						else {
							$user_page=url('user', $lang);
						}
					}
					break;
				case 'admin':
					if ($param) {
						$admin_page=$is_admin ? url('admin', $lang) : false;
					}
					break;
				default:
					break;
			}
		}
	}

	$output = view('footer', $lang, compact('languages', 'contact_page', 'user_page', 'nobody_page', 'account_page', 'admin_page'));

	return $output;
}

