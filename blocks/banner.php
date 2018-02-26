<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    8
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userhasrole.php';

function banner($lang, $components=false) {
	global $home_action, $cookieconsent, $cookieconsentauto;

	$is_identified = user_is_identified();
	$is_admin = user_has_role('administrator');
	$is_writer = user_has_role('writer');

	$consent=false;
	if ($cookieconsent and ! ($is_admin or $is_writer)) {
		if (!isset($_COOKIE['cookieconsent'])) {
			$confirmcookieconsent=!$cookieconsentauto;
			if ($cookieconsentauto) {
				setcookie('cookieconsent', true, time()+60*60*24*365, '/');
			}
			$consent=view('consent', $lang, compact('confirmcookieconsent'));
		}
	}

	$home_page=url($home_action, $lang);
	$logo = view('logo', $lang, compact('home_page'));

	$menu=$languages=$headline=$search=$donate=false;

	$contact_page=$user_page=$nobody_page=$account_page=$edit_page=$view_page=$validate_page=$admin_page=false;

	if ($is_identified) {
		$nobody_page=url('nobody', $lang);
	}

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'account':
					if ($param) {
						if ($is_identified) {
							$account_page=url('account', $lang);
						}
						else {
							$user_page=url('user', $lang);
						}
					}
					break;
				case 'contact':
					if ($param) {
						$contact_page=url('contact', $lang);
					}
					break;
				case 'languages':
					if ($param) {
						if (is_array($param)) {
							list($action, $arg)=$param;
						}
						else {
							$action=$param;
							$arg=false;
						}
						$languages = build('languages', $lang, $action, $arg);
					}
					break;
				case 'donate':
					if ($param) {
						$donate = build('donate', $lang);
					}
					break;
				case 'headline':
					if ($param) {
						$headline = view('headline', false, $param);
					}
					break;
				case 'search':
					if ($param) {
						$search = view('searchinput', $lang, $param);
					}
					break;
				case 'edit':
					if ($param) {
						if ($is_writer) {
							$edit_page=$param;
						}
					}
					break;
				case 'view':
					if ($param) {
						if ($is_writer) {
							$view_page=$param;
						}
					}
					break;
				case 'validate':
					if ($param) {
						if ($is_writer) {
							$validate_page=$param;
						}
					}
					break;
				case 'admin':
					if ($param) {
						if ($is_admin) {
							$admin_page=url('admin', $lang);
						}
					}
					break;
				default:
					break;
			}
		}
	}

	$menu = view('bannermenu', $lang, compact('user_page', 'nobody_page', 'account_page', 'contact_page', 'edit_page', 'view_page', 'validate_page', 'admin_page'));

	$output = view('banner', false, compact('consent', 'logo', 'menu', 'languages', 'headline', 'search', 'donate'));

	return $output;
}

