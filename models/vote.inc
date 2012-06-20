<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function vote_get_total_count($type, $id, $locale=false) {
	if (!is_numeric($id)) {
		return false;
	}

	$sqltype=db_sql_arg($type, false);

	$where = "content_type=$sqltype AND content_id=$id";

	if ($locale) {
		$sqllocale=db_sql_arg($locale, false);

		$where .= " AND content_locale=$sqllocale";
	}

	$tabvote=db_prefix_table('vote');

	$sql="SELECT COUNT(*) AS vote_count, SUM(v.value) as vote_total FROM $tabvote v WHERE $where";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function vote_plusone($type, $id, $locale, $ip_address, $user_id=false) {
	if (!is_numeric($id)) {
		return false;
	}

	$sqltype=db_sql_arg($type, false);
	$sqllocale=db_sql_arg($locale, false);
	$sqlipaddress=db_sql_arg($ip_address, false);

	$tabvote=db_prefix_table('vote');

	$where = "content_type=$sqltype AND content_id=$id AND content_locale=$sqllocale AND " . ($user_id ? "user_id=$user_id" : "ip_address=INET_ATON($sqlipaddress)");

	$sql="SELECT user_id FROM $tabvote WHERE $where LIMIT 1";

	$r = db_query($sql);

	if ($r) {
		return true;
	}

	if (!$user_id) {
		$user_id=0;
	}

	$sql="INSERT IGNORE $tabvote (content_id, content_type, content_locale, user_id, ip_address, created, value) VALUES ($id, $sqltype, $sqllocale, $user_id, INET_ATON($sqlipaddress), NOW(), 1)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	return true;
}
