<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userhasrole.php';

function toolbar($lang, $components=false) {
	$nobody_page=$edit_page=$view_page=$validate_page=$admin_page=false;

	$is_identified = user_is_identified();
	$is_admin = user_has_role('administrator');
	$is_writer = user_has_role('writer');

	if ($components) {
		foreach ($components as $v => $param) {
			switch ($v) {
				case 'logout':
					if ($param) {
						if ($is_identified) {
							$nobody_page=url('nobody', $lang);
						}
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

	$output = view('toolbar', $lang, compact('nobody_page', 'edit_page', 'view_page', 'validate_page', 'admin_page'));

	return $output;
}

