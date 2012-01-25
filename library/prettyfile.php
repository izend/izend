<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'geshi.php';

function read_file($file, $startline=0, $endline=0) {
	$lines = @file($file);

	if ($lines === false) {
		return false;
	}

	if ($startline or $endline) {
		$offset=$startline ? $startline-1 : 0;

		if ($endline) {
			$length=$startline ? $endline - $startline + 1 : $endline;
			$lines = array_slice($lines, $offset, $length);
		}
		else {
			$lines = array_slice($lines, $offset);
		}
	}

	$s=implode('', $lines);

	$s=rtrim($s);

	if (get_magic_quotes_runtime()) {
		$s = stripslashes($s);
	}

	return $s;
}

function pretty_file($file, $language, $startline=0, $endline=0, $number=false) {
	if (!$file) {
		return false;
	}

	$s=read_file($file, $startline, $endline);

	if (!$s) {
		return false;
	}

	if (!$language) {
		return $s;
	}

	$output = false;

	switch ($language) {
		case 'plain':
			$s = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $s);
			$s = htmlentities($s, ENT_COMPAT, 'UTF-8');

			$output = '<pre class="plain">' . PHP_EOL . $s . '</pre>' . PHP_EOL;
			break;
		default:
			$geshi = new GeSHi($s, $language);
			$geshi->enable_classes(true);
			$geshi->set_header_type(GESHI_HEADER_DIV);
			if ($number) {
				$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
				$geshi->start_line_numbers_at($startline > 0 ? $startline : 1);
			}
			$geshi->enable_keyword_links(false);
			$geshi->set_tab_width(4);

//			echo '<pre>' . PHP_EOL .$geshi->get_stylesheet( ). '</pre>' . PHP_EOL;

			$output = $geshi->parse_code();

			if ($geshi->error()) {
				return false;
			}
	}

	return $output;
}

