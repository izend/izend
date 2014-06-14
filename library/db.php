<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

$db_conn=false;

function db_connect($url) {
	global $db_conn;

	$url = parse_url($url);

	$url['user'] = urldecode($url['user']);
	$url['pass'] = isset($url['pass']) ? urldecode($url['pass']) : '';
	$url['host'] = urldecode($url['host']);
	$url['path'] = urldecode($url['path']);
	if (isset($url['port'])) {
		$url['host'] = $url['host'] .':'. $url['port'];
	}

	$db_conn = @mysql_pconnect($url['host'], $url['user'], $url['pass']) or trigger_error(mysql_error(), E_USER_ERROR);
	@mysql_select_db(substr($url['path'], 1), $db_conn) or trigger_error(mysql_error(), E_USER_ERROR);

	@mysql_query("SET NAMES 'utf8'", $db_conn) or trigger_error(mysql_error(), E_USER_ERROR);

	return $db_conn;
}

function db_query($sql) {
	$result = _db_sql_query($sql);
	$found = mysql_num_rows($result);

	$r = false;

	if ($found) {
		$r = array();
		while ($row = mysql_fetch_assoc($result)) {
			if (get_magic_quotes_runtime()) {
				foreach ($row as $k => &$v) {
					$v = stripslashes($v);
				}
			}
			$r[] = $row;
		}
	}

	mysql_free_result($result);

	return $r;
}

function db_insert($sql) {
	$r = _db_sql_query($sql);

	return $r;
}

function db_update($sql) {
	$r = _db_sql_query($sql);

	return $r;
}

function db_delete($sql) {
	$r = _db_sql_query($sql);

	return $r;
}

function db_insert_id() {
	$r = mysql_insert_id();

	return $r;
}

function db_sql_arg($s, $escape=true, $optional=false) {
	global $db_conn;

	if ($s === false or $s === '') {
		return $optional ? 'NULL' : "''";
	}

	return '\'' . ($escape ? mysql_real_escape_string($s, $db_conn) : $s) . '\'';	// MUST be connected!
}

function db_prefix_table($table) {
	global $db_prefix;

	return $db_prefix ? $db_prefix . $table : $table;
}

function _db_sql_query($sql) {
	global $db_debug;
	global $db_conn;

	if ($db_debug) {
		dump($sql);
	}

	$r = mysql_query($sql, $db_conn) or die(mysql_error());

	return $r;
}

