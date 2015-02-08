<?php

/**
 *
 * @copyright  2014-2015 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

$db_conn=false;
$db_scheme=false;

function db_connect($url, $persistent=true) {
	global $db_conn, $db_scheme;

	$url = parse_url($url);

	$scheme = $url['scheme'];
	$host = urldecode($url['host']);
	if (isset($url['port'])) {
		$host = $host . ':' . $url['port'];
	}
	$user = urldecode($url['user']);
	$pass = isset($url['pass']) ? urldecode($url['pass']) : '';
	$path = urldecode($url['path']);
	if ($path[0] == '/') {
		$path = substr($path, 1);
	}

	$dsn = "$scheme:host=$host;dbname=$path";
	$options = array(PDO::ATTR_PERSISTENT => $persistent ? true : false);

	try {
		$db_conn = new PDO($dsn, $user, $pass, $options);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		if ($scheme == 'mysql') {
			$db_conn->exec("SET SQL_MODE='ANSI_QUOTES'");
		}

		$db_scheme=$scheme;
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}

	return $db_conn;
}

function db_close() {
	global $db_conn;

	$db_conn=null;
}

function db_version() {
	global $db_conn;

	$r = $db_conn->getAttribute(PDO::ATTR_SERVER_VERSION);

	return $r;
}

function db_query($sql) {
	global $db_debug;
	global $db_conn;

	if ($db_debug) {
		dump($sql);
	}

	try {
		$r = $db_conn->query($sql);
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}

	$rows = $r->fetchAll(PDO::FETCH_ASSOC);

	if (!$rows) {
		return false;
	}

	if (get_magic_quotes_runtime()) {
		foreach ($rows as $row) {
			foreach ($row as $k => &$v) {
				$v = stripslashes($v);
			}
		}
	}

	return $rows;
}

function db_insert($sql) {
	return _db_sql_exec($sql);
}

function db_update($sql) {
	return _db_sql_exec($sql);
}

function db_delete($sql) {
	return _db_sql_exec($sql);
}

function db_exec($sql) {
	return _db_sql_exec($sql);
}

function db_insert_id($id=null) {
	global $db_conn;

	$r = $db_conn->lastInsertId($id);

	return $r;
}

function db_sql_arg($s, $escape=true, $optional=false) {
	global $db_conn;

	if ($s === false or $s === '') {
		return $optional ? 'NULL' : "''";
	}

	return $escape ? $db_conn->quote($s) : "'$s'";
}

function db_prefix_table($table) {
	global $db_prefix;

	return $db_prefix ? $db_prefix . $table : $table;
}

function _db_sql_exec($sql) {
	global $db_debug;
	global $db_conn;

	if ($db_debug) {
		dump($sql);
	}

	try {
		$r = $db_conn->exec($sql);
	}
	catch (PDOException $e) {
		die($e->getMessage());
	}

	return $r;
}
