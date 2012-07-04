<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    11
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strtofname.php';
require_once 'userhasrole.php';
require_once 'userprofile.php';
require_once 'models/thread.inc';

function threadeditall($lang, $clang) {
	global $supported_threads, $with_toolbar;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$confirmed=false;

	$action='init';
	if (isset($_POST['thread_create'])) {
		$action='create';
	}
	else if (isset($_POST['thread_delete'])) {
		$action='delete';
	}
	else if (isset($_POST['thread_confirmdelete'])) {
		$action='delete';
		$confirmed=true;
	}
	else if (isset($_POST['threadlist_reorder'])) {
		$action='reorder';
	}

	$new_thread_name=$new_thread_title=$new_thread_type=$new_thread_number=false;
	$old_thread_number=false;

	$thread_list=false;
	$p=false;

	switch($action) {
		case 'init':
		case 'reset':
			break;
		case 'create':
		case 'delete':
		case 'reorder':
			if (isset($_POST['new_thread_title'])) {
				$new_thread_title=readarg($_POST['new_thread_title']);
			}
			if ($new_thread_title) {
				$new_thread_name = strtofname($new_thread_title);
			}
			if (isset($_POST['new_thread_number'])) {
				$new_thread_number=readarg($_POST['new_thread_number']);
			}
			if (isset($_POST['new_thread_type'])) {
				$new_thread_type=readarg($_POST['new_thread_type']);
			}
			if (isset($_POST['old_thread_number'])) {
				$old_thread_number=readarg($_POST['old_thread_number']);
			}
			if (isset($_POST['p'])) {
				$p=$_POST['p'];	// DON'T readarg!
			}
		default:
			break;
	}

	$r = thread_list($clang, false, false);

	if (count($p) != count($r)) {
		$p = false;
	}

	if ($r) {
		$pos=1;
		$thread_list = array();
		$thread_url = url('threadedit', $lang);
		foreach ($r as $b) {
			$b['thread_url'] = $thread_url  . '/' . $b['thread_id'];
			$b['pos'] = $p ? $p[$pos] : $pos;
			$thread_list[$pos] = $b;
			$pos++;
		}
	}

	$missing_new_thread_title=false;
	$missing_new_thread_name=false;
	$bad_new_thread_name=false;
	$missing_new_thread_type=false;
	$bad_new_thread_type=false;
	$bad_new_thread_number=false;

	$missing_old_thread_number=false;
	$bad_old_thread_number=false;

	switch($action) {
		case 'create':
			if (!$new_thread_title) {
				$missing_new_thread_title = true;
			}
			if (!$new_thread_name) {
				$missing_new_thread_name = true;
			}
			else if (!preg_match('#^[\w-]{2,}$#', $new_thread_name)) {
				$bad_new_thread_name = true;
			}
			if (!$new_thread_number) {
				$bad_new_thread_number = false;
			}
			else if (!is_numeric($new_thread_number)) {
				$bad_new_thread_number = true;
			}
			else if ($new_thread_number < 1 or $new_thread_number > count($thread_list) + 1) {
				$bad_new_thread_number = true;
			}
			if (!$new_thread_type) {
				$missing_new_thread_type = true;
			}
			else if (!in_array($new_thread_type, $supported_threads)) {
				$bad_new_thread_type = true;
			}
			break;

		case 'delete':
			if (!$old_thread_number) {
				$missing_old_thread_number = true;
			}
			else if (!is_numeric($old_thread_number)) {
				$bad_old_thread_number = true;
			}
			else if ($old_thread_number < 1 or $old_thread_number > count($thread_list)) {
				$bad_old_thread_number = true;
			}
			break;

		default:
			break;
	}

	$confirm_delete_thread=false;

	switch($action) {
		case 'create':
			if ($missing_new_thread_title or $missing_new_thread_name or $bad_new_thread_name or $bad_new_thread_number or $missing_new_thread_type or $bad_new_thread_type) {
				break;
			}

			$user_id=user_profile('id');
			$np = thread_create($clang, $user_id, $new_thread_name, $new_thread_title, $new_thread_type, $new_thread_number);
			if (!$np) {
				break;
			}

			extract($np);	/* thread_id thread_number */
			$thread_title = $new_thread_title;
			$thread_url = url('threadedit', $lang) . '/'. $thread_id;
			$pos = $thread_number;

			if ($thread_list) {
				foreach ($thread_list as &$tr) {
					if ($tr['thread_number'] >= $pos) {
						$tr['thread_number']++;
					}
					if ($tr['pos'] >= $pos) {
						$tr['pos']++;
					}
				}
				array_splice($thread_list, $pos-1, 0, array(compact('thread_id', 'thread_title', 'thread_number', 'thread_url', 'pos')));
				array_multisort(range(1, count($thread_list)), $thread_list);
			}
			else {
				$pos=1;
				$thread_list=array($pos => compact('thread_id', 'thread_title', 'thread_number', 'thread_url', 'pos'));
			}

			break;

		case 'delete':
			if ($missing_old_thread_number or $bad_old_thread_number) {
				break;
			}

			if (!$confirmed) {
				$confirm_delete_thread=true;
				break;
			}

			$thread_id = $thread_list[$old_thread_number]['thread_id'];

			$r = thread_delete($thread_id);

			if (!$r) {
				break;
			}

			unset($thread_list[$old_thread_number]);

			foreach ($thread_list as &$b) {
				if ($b['pos'] >= $old_thread_number) {
					$b['pos']--;
				}
			}

			$old_thread_number = false;

			break;

		case 'reorder':
			if (!$p) {
				break;
			}

			$neworder=range(1, count($p));
			array_multisort($p, SORT_NUMERIC, $neworder);

			$number=1;
			$nl=array();
			foreach ($neworder as $i) {
				$tr = &$thread_list[$i];
				if ($tr['thread_number'] != $number) {
					thread_set_number($tr['thread_id'], $number);
					$tr['thread_number'] = $number;
				}
				$tr['pos']=$number;

				$nl[$number++] = $tr;
			}
			$thread_list = $nl;

			break;

		default:
			break;
	}

	head('title', translate('threadall:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$site_title=translate('title', $lang);

	$view=url('thread', $clang) . '?' . 'slang=' . $lang;
	$validate=url('thread', $clang);

	$banner = build('banner', $lang, $with_toolbar ? compact('headline') : compact('headline', 'view', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('view', 'validate')) : false;

	$inlanguages=view('inlanguages', false, compact('clang'));

	$errors = compact('missing_new_thread_title', 'bad_new_thread_title', 'missing_new_thread_name', 'missing_new_thread_type', 'bad_new_thread_name', 'bad_new_thread_type', 'bad_new_thread_number', 'missing_old_thread_number', 'bad_old_thread_number');

	$content = view('editing/threadeditall', $lang, compact('clang', 'site_title', 'inlanguages', 'supported_threads', 'thread_list', 'new_thread_title', 'new_thread_type', 'new_thread_number', 'old_thread_number', 'confirm_delete_thread', 'errors'));

	$output = layout('editing', compact('toolbar', 'banner', 'content'));

	return $output;
}

