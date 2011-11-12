<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'models/node.inc';

function nodecontenteditor($lang, $clang, $node_id) {
	global $contents_model, $supported_contents;

	$action='init';
	if (isset($_POST['content_modify'])) {
		$action='modify';
	}
	else if (isset($_POST['content_create'])) {
		$action='create';
	}
	else if (isset($_POST['content_delete'])) {
		$action='delete';
	}

	$new_content_type=$new_content_number=false;
	$old_content_number=false;

	$node_contents = false;
	$id=false;
	$p=false;

	switch($action) {
		case 'init':
		case 'reset':
			$r = node_get_contents($clang, $node_id);
			if ($r) {
				$pos=1;
				$node_contents = array();
				foreach ($r as $c) {
					$c['content_pos'] = $c['content_number'];
					$node_contents[$pos] = $c;
					$pos++;
				}
			}
			break;
		case 'modify':
		case 'create':
		case 'delete':
			if (isset($_POST['content_new_type'])) {
				$new_content_type=readarg($_POST['content_new_type']);
			}
			if (isset($_POST['content_new_number'])) {
				$new_content_number=readarg($_POST['content_new_number']);
			}
			if (isset($_POST['content_old_number'])) {
				$old_content_number=readarg($_POST['content_old_number']);
			}
			if (isset($_POST['content_id'])) {
				$id=$_POST['content_id'];				// DON'T readarg!
			}
			if (isset($_POST['content_p'])) {
				$p=$_POST['content_p'];					// DON'T readarg!
			}
			if (isset($_POST['content_ignored'])) {
				$ignored=$_POST['content_ignored'];		// DON'T readarg!
			}

			if ($id and $p and is_array($id) and is_array($p) and count($id) == count($p)) {
				$node_contents=array();

				foreach ($contents_model as $type => $fields) {
					foreach ($fields as $fname => $props) {
						$fieldname="content_${type}_$fname";
						if (isset($_POST[$fieldname]) and is_array($_POST[$fieldname])) {
							foreach ($_POST[$fieldname] as $i => $value) {
								$v=readarg($value, true, false);	// trim but DON'T strip_tags!
								if (!isset($node_contents[$i])) {
									$content_ignored = isset($ignored[$i]) && $ignored[$i] == 'on';
									$node_contents[$i] = array('content_id' => $id[$i], 'content_pos' => $p[$i], 'content_ignored' => $content_ignored, 'content_type' => $type, $fieldname => $v);
								}
								else {
									$node_contents[$i][$fieldname] = $v;
								}
							}
						}
					}
				}

				if ($node_contents) {
					ksort($node_contents);
				}
			}
			break;
		default:
			break;
	}

	$missing_new_content_type=false;
	$bad_new_content_type=false;
	$bad_new_content_number=false;

	$missing_old_content_number=false;
	$bad_old_content_number=false;

	switch($action) {
		case 'create':
			if (empty($new_content_type)) {
				$missing_new_content_type = true;
			}
			else if (!in_array($new_content_type, $supported_contents)) {
				$bad_new_content_type = true;
			}
			if (empty($new_content_number)) {
				$new_content_number = false;
			}
			else if (!is_numeric($new_content_number)) {
				$bad_new_content_number = true;
			}
			else if ($new_content_number < 1) {
				$bad_new_content_number = true;
			}
			else if ($new_content_number > count($node_contents)) {
				$new_content_number = false;
			}
			break;

		case 'delete':
			if (empty($old_content_number)) {
				$missing_old_content_number = true;
			}
			else if (!is_numeric($old_content_number)) {
				$bad_old_content_number = true;
			}
			else if ($old_content_number < 1 or $old_content_number > count($node_contents)) {
				$bad_old_content_number = true;
			}
			break;

		case 'modify':
			break;

		default:
			break;
	}

	switch($action) {
		case 'modify':
			if (!$p) {
				break;
			}

			$neworder=range(1, count($p));
			array_multisort($p, SORT_NUMERIC, $neworder);

			$number=1;
			$nc=array();
			foreach ($neworder as $i) {
				$c = &$node_contents[$i];
				$c['content_pos']=$number;

				$nc[$number++] = $c;
			}
			$node_contents = $nc;

			$r = node_set_contents($clang, $node_id, $node_contents);

			if (!$r) {
				break;
			}

			break;

		case 'create':
			if ($missing_new_content_type or $bad_new_content_type or $bad_new_content_number) {
				break;
			}

			$nc = node_create_content($clang, $node_id, $new_content_type, $new_content_number);
			if (!$nc) {
				break;
			}

			$content_id = $nc['content_id'];
			$content_pos = $nc['content_number'];
			$content_type = $new_content_type;
			$content_ignored=false;

			$fields=compact('content_pos', 'content_id', 'content_type', 'content_ignored');

			foreach ($contents_model[$content_type] as $fname => $props) {
				$fieldname = "content_${content_type}_$fname";
				$fields[$fieldname]=isset($props['default']) ? $props['default'] : false;
			}

			if ($node_contents) {
				foreach ($node_contents as &$c) {
					if ($c['content_pos'] >= $content_pos) {
						$c['content_pos']++;
					}
				}
				array_splice($node_contents, $content_pos-1, 0, array($fields));
			}
			else {
				$content_pos=1;
				$node_contents=array($content_pos => $fields);
			}

			if ($new_content_number) {
				$new_content_number++;
			}

			break;

		case 'delete':
			if ($missing_old_content_number or $bad_old_content_number) {
				break;
			}

			$c = $node_contents[$old_content_number];
			$content_id = $c['content_id'];
			$content_type = $c['content_type'];

			$r = node_delete_content($node_id, $content_id, $content_type);

			if (!$r) {
				break;
			}

			unset($node_contents[$old_content_number]);
			$node_contents = array_values($node_contents);

			foreach ($node_contents as &$c) {
				if ($c['content_pos'] >= $old_content_number) {
					$c['content_pos']--;
				}
			}

			$old_content_number = false;

			break;

		default:
			break;
	}

	$errors = compact('missing_new_content_type', 'bad_new_content_type', 'bad_new_content_number', 'missing_old_content_number', 'bad_old_content_number');

	$output = view('editing/nodecontenteditor', $lang, compact('clang', 'supported_contents', 'new_content_type', 'new_content_number', 'old_content_number', 'node_contents', 'errors'));

	return $output;
}

