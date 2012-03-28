<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version	1
 * @link	   http://www.izend.org
 */

function validate_url($url) {
	return filter_var($url, FILTER_VALIDATE_URL);
}

function normalize_url($url) {
	$purl = @parse_url($url);

	if (!$purl) {
		return false;
	}

	$scheme=$host=$port=$user=$pass=$path=$query=$fragment=false;

	extract($purl);

	if ($scheme) {
		$scheme = strtolower($scheme);
	}

	if ($host) {
		$host = strtolower($host);
	}

	if ($port and $port == getservbyname($scheme, 'tcp')) {
		$port = false;
	}

	foreach (array('user', 'pass', 'host', 'path') as $p) {
		if ($$p) {
			$$p = preg_replace( '/%[0-9a-f]{2}/ie', 'strtoupper("\0")', $$p );
		}
	}

	if ($path) {
		$path = _restore_allowed_chars(_remove_dot_segments($path));
	}

	if ($host && !$path) {
		$path = '/';
	}

	$newurl = $scheme . '://';

	if ($host) {
		if ($user) {
			$newurl .= $user;
			if ($pass) {
				$newurl .= ':' . $pass;
			}
			$newurl .= '@';
		}

		$newurl .= $host;

		if ($port) {
			$newurl .= ':' . $port;
		}
	}

	$newurl .= $path;

	if ($query) {
		$newurl .= '?' . $query;
	}

	if ($fragment) {
		$newurl .= '#' . $fragment;
	}

	return $newurl;
}

function _remove_dot_segments($path) {
	$newpath = '';

	$watchdog = 100;

	while ($path && $watchdog-- > 0) {
		if (substr($path, 0, 2) == './') {
			$path = substr($path, 2);
		}
		elseif (substr($path, 0, 3) == '../') {
			$path = substr($path, 3);
		}
		elseif (substr($path, 0, 3) == '/./' || $path == '/.') {
			$path = '/' . substr($path, 3);
		}
		elseif (substr($path, 0, 4) == '/../' || $path == '/..') {
			$path   = '/' . substr($path, 4);
			$i	  = strrpos($newpath, '/');
			$newpath = $i === false ? '' : substr($newpath, 0, $i);
		}
		elseif ($path == '.' || $path == '..') {
			$path = '';
		}
		else {
			$i = strpos($path, '/');
			if ($i === 0) {
				$i = strpos($path, '/', 1);
			}
			if ($i === false) {
				$i = strlen($path);
			}
			$newpath .= substr($path, 0, $i);
			$path = substr($path, $i);
		}
	}

	return $newpath;
}

function _restore_allowed_chars($s) {
	$from = array(
		'%41', '%42', '%43', '%44', '%45', '%46', '%47', '%48', '%49', '%4A', '%4B', '%4C', '%4D', '%4E', '%4F', '%50', '%51', '%52', '%53', '%54', '%55', '%56', '%57', '%58', '%59', '%5A',
		'%61', '%62', '%63', '%64', '%65', '%66', '%67', '%68', '%69', '%6A', '%6B', '%6C', '%6D', '%6E', '%6F', '%70', '%71', '%72', '%73', '%74', '%75', '%76', '%77', '%78', '%79', '%7A',
		'%30', '%31', '%32', '%33', '%34', '%35', '%36', '%37', '%38', '%39',
		'%2D', '%2E', '%5F', '%7E'
	);

	$to = array(
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'-', '.', '_', '~'
	);

	return str_replace($from, $to, $s);
}
