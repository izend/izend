<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'strflat.php';

function strtofname($s, $strict=false) {
	/* remove accents */
	$s = strflat($s);

	/* lower case */
	$s = strtolower($s);

	/* keep letters, digits, underscores and dashes replacing others by a dash */
	$s = preg_replace('#[^a-z0-9_-]#', '-', $s);

	/* replace consecutive dashes by one */
	$s = preg_replace('/[-]+/', '-', $s);

	/* remove a dash at the beginning or at the end */
	$s = preg_replace('/^-|-$/', '', $s);

	if (!$strict) {
		return $s;
	}

	/* remove words which are too short */
	$r = array();
	foreach (explode('-', $s) as $w)	{
		if (strlen($w) > 2) {
			$r[] = $w;
		}
	}

	return implode('-', $r);
}

