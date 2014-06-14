<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function registry_get($name, $default=false) {
	$sqlname=db_sql_arg($name, false);

	$tabregistry=db_prefix_table('registry');

	$sql="SELECT value FROM $tabregistry WHERE name=$sqlname";

	$r = db_query($sql);

	return $r ? unserialize($r[0]['value']) : $default;
}

function registry_set($name, $value) {
	$sqlname=db_sql_arg($name, false);
	$sqlvalue=db_sql_arg(serialize($value), true);

	$tabregistry=db_prefix_table('registry');

	$sql="INSERT INTO $tabregistry (name, value) SELECT * FROM (SELECT $sqlname AS name, $sqlvalue AS value) s WHERE NOT EXISTS (SELECT name FROM $tabregistry WHERE name=$sqlname)";

	$r = db_insert($sql);

	if ($r) {
		return true;
	}

	$sql="UPDATE $tabregistry SET name=$sqlname, value=$sqlvalue WHERE name=$sqlname";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function registry_delete($name) {
	$sqlname=db_sql_arg($name, false);

	$tabregistry=db_prefix_table('registry');

	$sql="DELETE FROM $tabregistry WHERE name=$sqlname";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	return true;
}
