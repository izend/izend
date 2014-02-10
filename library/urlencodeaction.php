<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'urlencrypt.php';

function urlencodeaction($id, $param=false) {
	global $sitekey;

	$data=pack('N', time()) . pack('C', $id);

	if ($param) {
		$data .= serialize($param);
	}

	$digest=md5($data, true);
	$sdata=$digest.$data;

	$s64=urlencrypt($sdata, $sitekey);

	return $s64;
}

function urldecodeaction($s64) {
	global $sitekey;

	$sdata=urldecrypt($s64, $sitekey);

	if (!$sdata) {
		return false;
	}

	$digest=substr($sdata, 0, 16);

	$data=substr($sdata, 16);

	if ($digest != md5($data, true)) {
		return false;
	}

	$r=unpack('N', substr($data, 0, 4));
	if (!$r) {
		return false;
	}
	$timestamp = $r[1];

	$r=unpack('C', substr($data, 4, 1));
	if (!$r) {
		return false;
	}
	$actionid = $r[1];

	$param=false;
	if (strlen($data) > 5) {
		$param=@unserialize(substr($data, 5));
		if (!$param) {
			return false;
		}
	}

	return array($actionid, $timestamp, $param);
}
