<?php

/**
 *
 * @copyright  2011-2022 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'models/user.inc';

function usersearch($lang) {
	$with_name=true;
	$with_website=true;

	$action='init';
	if (isset($_POST['usersearch_search']) or isset($_GET['q'])) {
		$action='search';
	}

	$pagesize=20;
	$page=1;

	$what=false;

	switch($action) {
		case 'search':
			if (isset($_POST['usersearch_what'])) {
				$what=readarg($_POST['usersearch_what']);
			}
			else if (isset($_GET['q'])) {
				$what=readarg($_GET['q']);
				if (isset($_GET['p'])) {
					$page=intval(readarg($_GET['p']));
					if ($page < 1) {
						$page=1;
					}
				}
			}

			break;
		default:
			break;
	}

	$count=0;
	$result=false;

	switch($action) {
		case 'search':
			$r = user_search($what, $pagesize, $page);

			if (!$r) {
				break;
			}

			list($count, $result) = $r;

			$edit_url = url('adminuser', $lang);
			foreach ($result as &$r) {
				$r['edit'] = $edit_url . '/' . $r['user_id'];
			}

			break;
		default:
			break;
	}

	$admin_page=url('admin', $lang);

	$output = view('usersearch', $lang, compact('what', 'page', 'pagesize', 'count', 'result', 'with_name', 'with_website', 'admin_page'));

	return $output;
}

