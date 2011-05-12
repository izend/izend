<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function registry_get($name, $default=false) {
	$sqlname=db_sql_arg($name, false);

	$tabregistry=db_prefix_table('registry');

	$r = db_query("SELECT value FROM $tabregistry WHERE name=$sqlname LIMIT 1");

	return $r ? unserialize($r[0]['value']) : $default;
}

function registry_set($name, $value) {
	$sqlname=db_sql_arg($name, false);
	$sqlvalue=db_sql_arg(serialize($value), true);

	$tabregistry=db_prefix_table('registry');

	db_insert("INSERT $tabregistry SET name=$sqlname, value=$sqlvalue ON DUPLICATE KEY UPDATE name=VALUES(name), value=VALUES(value)");
}

function registry_delete($name) {
	$sqlname=db_sql_arg($name, false);

	$tabregistry=db_prefix_table('registry');

	db_delete("DELETE FROM $tabregistry WHERE name=$sqlname LIMIT 1");
}

