<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function nodecontent($lang, $node_id) {
	$contents = array();
	$r = node_get_contents($lang, $node_id);

	if ($r) {
		require_once 'prettyfile.php';

		foreach ($r as $c) {	/* content_id content_number content_ignored content_type (content_text content_eval | content_file content_start content_end content_format | content_download content_path | content_infile | content_longtail_file content_longtail_image content_longtail_width content_longtail_height content_longtail_skin content_longtail_icons content_longtail_duration content_longtail_autostart content_longtail_repeat) */
			if ($c['content_ignored'])
				continue;
			$type=$c['content_type'];
			switch($type) {
				case 'text':
					$s = $c['content_text'];
					if (!empty($s)) {
						$eval = $c['content_eval'] == 1 ? true : false;
						if ($eval) {
							require_once 'seval.php';
							$s = seval($s);
						}
						$text = $s;
						$contents[] = compact('type', 'text');
					}
					break;
				case 'file':
					$path=$c['content_file'];
					$start = $c['content_start'];
					$end = $c['content_end'];
					$format=$c['content_format'];
					$lineno=$c['content_lineno'] == 1 ? true : false;
					$file = pretty_file($path, $format, $start, $end, $lineno);
					if (!empty($file)) {
						head('stylesheet', 'geshi'.'/'.$format, 'screen');
						$contents[] = compact('type', 'file', 'start', 'end', 'format', 'lineno');
					}
					break;
				case 'download':
					$file=$c['content_download'];
					if ($file) {
						$download_url = url('download', $lang) . '/' . $node_id . '/' . urlencode($file);
						$contents[] = compact('type', 'file', 'download_url');
					}
					break;
				case 'infile':
					$infile=$c['content_infile'];
					if ($infile) {
						$contents[] = compact('type', 'infile');
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
					if (!empty($file)) {
						head('javascript', 'swfobject');
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

