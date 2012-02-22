<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function nodecontent($lang, $node_id) {
	$contents = array();
	$r = node_get_contents($lang, $node_id);

	if ($r) {
		foreach ($r as $c) {
			if ($c['content_ignored'])
				continue;
			$type=$c['content_type'];
			switch($type) {
				case 'text':
					$s = $c['content_text_text'];
					if (!empty($s)) {
						$eval = $c['content_text_eval'] == 1 ? true : false;
						if ($eval) {
							require_once 'seval.php';
							$s = seval($s);
						}
						$text = $s;
						$contents[] = compact('type', 'text');
					}
					break;
				case 'infile':
					$infile=$c['content_infile_path'];
					if ($infile) {
						$contents[] = compact('type', 'infile');
					}
					break;
				case 'download':
					$file=$c['content_download_name'];
					if ($file) {
						$download_url = url('download', $lang) . '/' . $node_id . '/' . urlencode($file);
						$contents[] = compact('type', 'file', 'download_url');
					}
					break;
				case 'file':
					$path=$c['content_file_path'];
					$start = $c['content_file_start'];
					$end = $c['content_file_end'];
					$format=$c['content_file_format'];
					$lineno=$c['content_file_lineno'] == 1 ? true : false;
					if ($path) {
						require_once 'prettyfile.php';
						$file = pretty_file($path, $format, $start, $end, $lineno);
						if ($file) {
							head('stylesheet', 'geshi'.'/'.$format, 'screen');
							$contents[] = compact('type', 'file', 'start', 'end', 'format', 'lineno');
						}
					}
					break;
				case 'longtail':
					$file=$c['content_longtail_file'];
					$image = $c['content_longtail_image'];
					$width=$c['content_longtail_width'];
					$height=$c['content_longtail_height'];
					$icons=$c['content_longtail_icons'];
					$skin=$c['content_longtail_skin'];
					$duration=$c['content_longtail_duration'];
					$autostart = $c['content_longtail_autostart'] == 1 ? true : false;
					$repeat = $c['content_longtail_repeat'] == 1 ? true : false;
					if ($file) {
						head('javascript', 'jwplayer');
						$contents[] = compact('type', 'file', 'image', 'width', 'height', 'icons', 'skin', 'duration', 'autostart', 'repeat');
					}
					break;
				default:
					break;
			}
		}
	}

	$output = view('nodecontent', false, compact('contents'));

	return $output;
}

