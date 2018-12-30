<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function strurl($url) {
	if (is_string($url)) {
		$url=parse_url($s);
	}

	$scheme = isset($url['scheme']) ? $url['scheme'] . '://' : '';
	$host = isset($url['host']) ? $url['host'] : '';
	$port = isset($url['port']) ? ':' . rawurlencode($url['port']) : '';
	$user = isset($url['user']) ? rawurlencode($url['user']) : '';
	$pass = isset($url['pass']) ? ':' . rawurlencode($url['pass']) : '';
	$pass = ($user || $pass) ? "$pass@" : '';
	$path = isset($url['path']) ? implode('/', array_map('rawurlencode', explode('/', $url['path']))) : '';
	$query = isset($url['query']) ?  '?' . implode('&', array_map(function($arg) { list($k, $v)=explode('=', $arg); return urlencode($k).'='.urlencode($v); }, explode('&', $url['query']))) : '';
	$fragment = isset($url['fragment']) ? '#' . urlencode($url['fragment']) : '';

	return "$scheme$user$pass$host$port$path$query$fragment";
}
