<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

require_once 'readarg.php';
require_once 'strtofname.php';

function nodecontenteditor($lang, $clang, $node_id) {
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
				$id=$_POST['content_id'];	// DON'T readarg!
			}
			if (isset($_POST['content_p'])) {
				$p=$_POST['content_p'];		// DON'T readarg!
			}

			if ($id and $p and is_array($id) and is_array($p) and count($id) == count($p)) {
				$fieldgroups = array(
								'text'		=> array('content_ignored', 'content_text', 'content_eval'),
								'file'		=> array('content_ignored', 'content_file', 'content_start', 'content_end', 'content_format', 'content_lineno'),
								'download'	=> array('content_ignored', 'content_download', 'content_path'),
								'infile'	=> array('content_ignored', 'content_infile'),
								'media'		=> array('content_ignored', 'content_media_file', 'content_media_image', 'content_media_width', 'content_media_height', 'content_media_skin', 'content_media_icons', 'content_media_duration', 'content_media_autostart', 'content_media_repeat'),
								);

				$node_contents=array();

				foreach ($fieldgroups as $type => $fields) {
					foreach ($fields as $fieldname) {
						if (isset($_POST[$fieldname]) and is_array($_POST[$fieldname])) {
							foreach ($_POST[$fieldname] as $i => $value) {
								$v=readarg($value, true, false);	// trim but DON'T strip!
								switch ($fieldname) {
									case 'content_text':
										/* DON'T strip_tag! */
										break;
									case 'content_file':
									case 'content_format':
									case 'content_path':
									case 'content_download':
									case 'content_infile':
									case 'content_media_file':
									case 'content_media_image':
									case 'content_media_skin':
										$v=strip_tags($v);
										break;
									case 'content_ignored':
									case 'content_eval':
									case 'content_lineno':
									case 'content_media_icons':
									case 'content_media_autostart':
									case 'content_media_repeat':
										$v=$v=='on' ? true : false;
										break;
								}
								if (!isset($node_contents[$i])) {
									$node_contents[$i] = array('content_id' => $id[$i], 'content_pos' => $p[$i], 'content_type' => $type, $fieldname => $v);
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

	$missing_content_number=false;
	$bad_content_number=false;

	switch($action) {
		case 'create':
			if (empty($new_content_type)) {
				$missing_new_content_type = true;
			}
			else if (!in_array($new_content_type, array('text', 'file', 'download', 'infile', 'media'))) {
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
			foreach ($node_contents as $c) {
				extract($c);

				if (empty($content_pos)) {
					$missing_content_number = true;
				}
				else if (!is_numeric($content_pos)) {
					$bad_content_number = true;
				}
				else if ($content_pos < 1 or $content_pos > count($node_contents)) {
					$bad_content_number = true;
				}
			}
			break;

		default:
			break;
	}

	switch($action) {
		case 'modify':
			if ($missing_content_number or $bad_content_number) {
				break;
			}

			if ($p) {
				array_multisort(range(1, count($p)), SORT_NUMERIC, $p);
				array_multisort($p, SORT_NUMERIC, $node_contents);
			}

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

			$fields=array('content_id', 'content_type', 'content_ignored', 'content_text', 'content_eval', 'content_file', 'content_start', 'content_end', 'content_format', 'content_lineno', 'content_download', 'content_path', 'content_infile', 'content_media_file', 'content_media_image', 'content_media_width', 'content_media_height', 'content_media_skin', 'content_media_icons', 'content_media_duration', 'content_media_autostart', 'content_media_repeat', 'content_thread', 'content_node', 'content_pos');

			$content_id = $nc['content_id'];
			$content_pos = $nc['content_number'];
			$content_type = $new_content_type;
			$content_text=false;
			$content_eval=false;
			$content_download=$content_path=false;
			$content_file=$content_format=false;
			$content_start=$content_end=0;
			$content_lineno=false;
			$content_infile=false;
			$content_media_file=$content_media_image=false;
			$content_media_width=$content_media_height=0;
			$content_media_skin=false;
			$content_media_icons=false;
			$content_media_duration=0;
			$content_media_autostart=$content_media_repeat=false;

			$content_thread=$content_node=false;
			$content_ignored=false;

			if ($node_contents) {
				foreach ($node_contents as &$c) {
					if ($c['content_pos'] >= $content_pos) {
						$c['content_pos']++;
					}
				}
				array_splice($node_contents, $content_pos-1, 0, array(compact($fields)));
			}
			else {
				$content_pos=1;
				$node_contents=array($content_pos => compact($fields));
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

	$errors = compact('missing_new_content_type', 'bad_new_content_type', 'bad_new_content_number', 'missing_old_content_number', 'bad_old_content_number', 'missing_content_number', 'bad_content_number');

	$output = view('editing/nodecontenteditor', $lang, compact('clang', 'new_content_type', 'new_content_number', 'old_content_number', 'node_contents', 'errors'));

	return $output;
}

