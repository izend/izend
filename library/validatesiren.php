<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_siren($siren) {
	return preg_match('/^\d{9}$/', $siren) and checksum_siren($siren);
}

function validate_siret($siret, $siren=false) {
	return preg_match('/^\d{14}$/', $siret) and checksum_siren($siret) and (!$siren or strncmp($siren, $siret, strlen($siren)) == 0);
}

function validate_kbis($kbis, $siren=false) {
	return preg_match('/(^\d{9})\s+/', $kbis, $r) and validate_siren($r[1], $siren);
}

function checksum_siren($num) {
	$len=strlen($num);
	if (!($len == 9 or $len == 14)) {
		return false;
	}

	$sum=0;
	for ($i=0; $i < $len; $i++) {
		if ($i%2==0) {
			$sum += $num[$i];
		}
		else {
			$n=2*$num[$i];
			$sum += $n > 9 ? 1 + ($n - 10) : $n;
		}
	}

	return ($sum % 10) == 0;
}

