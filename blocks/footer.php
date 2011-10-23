<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userhasrole.php';

function footer($lang, $components=false) {
	$contact_page=$user_page=$admin_page=false;

	$is_identified = user_is_identified();
	$is_admin = user_has_role('administrator');

	$nobody_page=$is_identified ? url('nobody', $lang) : false;

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'contact':
					if ($param) {
						$contact_page=url('contact', $lang);
					}
					break;
				case 'account':
					if ($param) {
						$user_page=$is_identified ? false : url('user', $lang);
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

	$output = view('footer', $lang, compact('contact_page', 'user_page', 'nobody_page', 'admin_page'));

	return $output;
}

