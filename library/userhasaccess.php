<?php

/**
 *
 * @copyright  2021 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function user_can_read($thread_id) {
	global $read_access;

	if ($read_access && !user_has_role('administrator')) {
		foreach ($read_access as $role => $id) {
			if ((is_array($id) && in_array($thread_id, $id)) || $thread_id == $id) {
				return user_has_role($role);
			}
		}
	}

	return true;
}

function user_noread_list() {
	global $read_access;

	$id_list=array();

	if ($read_access && !user_has_role('administrator')) {
		foreach ($read_access as $role => $id) {
			if (!user_has_role($role)) {
				if (is_array($id)) {
					$id_list=array_merge($id_list, $id);
				}
				else {
					$id_list[] = $id;
				}
			}
		}
	}

	return $id_list ? array_unique($id_list) : false;
}

