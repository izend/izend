<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

define('AVATAR_SIZE', 24);
define('AVATARS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'avatars');

function user_create_avatar($name) {
	require_once 'identicon.php';

	$img=identicon($name, AVATAR_SIZE);

	$r = imagepng($img, AVATARS_DIR . DIRECTORY_SEPARATOR . $name . '.png');

	return $r;
}

function user_delete_avatar($name) {
	@unlink(AVATARS_DIR . DIRECTORY_SEPARATOR . $name . '.png');
}

function user_id($user) {
	if (!is_numeric($user)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT user_id FROM $tabuser WHERE user_id=$user LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0]['user_id'] : false;
}

function user_create($name, $password, $mail, $locale='fr') {
	$sqlname=db_sql_arg($name, true);
	$sqlmail=db_sql_arg($mail, true);
	$sqllocale=db_sql_arg($locale);

	$sqlpassword=$password ? '\'' . md5($password) . '\'' : NULL;

	$tabuser=db_prefix_table('user');

	$sql="INSERT IGNORE $tabuser (name, password, mail, created, locale) VALUES ($sqlname, $sqlpassword, $sqlmail, NOw(), $sqllocale)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$user_id = db_insert_id();

	user_create_avatar($name);

	return $user_id;
}


function user_get($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT name AS user_name, password AS user_password, newpassword AS user_newpassword, mail AS user_mail, UNIX_TIMESTAMP(created) AS user_created, UNIX_TIMESTAMP(modified) AS user_modified, UNIX_TIMESTAMP(accessed) AS user_accessed, locale AS user_locale, active AS user_active, banned AS user_banned FROM $tabuser WHERE user_id=$user_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function user_set($user_id, $name, $mail, $locale) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT name AS oldname FROM $tabuser WHERE user_id=$user_id LIMIT 1";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	extract($r[0]);	/* oldname */

	$sqlname=db_sql_arg($name, true);
	$sqlmail=db_sql_arg($mail, true);
	$sqllocale=db_sql_arg($locale);

	$sql="UPDATE $tabuser SET name=$sqlname, mail=$sqlmail, locale=$sqllocale, modified=NOW() WHERE user_id=$user_id LIMIT 1";

	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	if ($name != $oldname) {
		user_delete_avatar($oldname);
		user_create_avatar($name);
	}

	return true;
}

function user_set_newpassword($user_id, $password) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$sqlnewpassword=$password ? '\'' . md5($password) . '\'' : NULL;

	$tabuser=db_prefix_table('user');

	$sql="UPDATE $tabuser SET newpassword=$sqlnewpassword WHERE user_id=$user_id LIMIT 1";

	$r = db_update($sql);

	return $r;
}

function user_get_role($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabrole=db_prefix_table('role');
	$tabuserrole=db_prefix_table('user_role');

	$sql="SELECT r.name AS role_name FROM $tabuserrole ur JOIN $tabrole r ON r.role_id=ur.role_id WHERE ur.user_id=$user_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$role=array();

	foreach ($r as $v) {
		$role[] = $v['role_name'];
	}

	return $role;
}

function user_find($login) {
	$sqllogin=db_sql_arg($login, true);

	$tabuser=db_prefix_table('user');

	$sql="SELECT user_id FROM $tabuser WHERE name=$sqllogin OR mail=$sqllogin LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0]['user_id'] : false;
}

function user_login($login, $password) {
	$user_id = user_find($login);

	if (!$user_id) {
		return false;
	}

	$r = user_get($user_id);

	if (!$r) {
		return false;
	}

	extract($r);	/* user_name user_password user_newpassword user_mail user_created user_modified user_accessed user_locale user_active user_banned */

	if (!$user_active or $user_banned) {
		return false;
	}

	$password=md5($password);

	if ( ! ($password == $user_password or $password == $user_newpassword) ) {
		return false;
	}

	$now=time();

	$user = array();
	$user['id'] = $user_id;
	$user['name'] = $user_name;
	$user['password'] = $user_password;
	$user['mail'] = $user_mail;
	$user['locale'] = $user_locale;
	$user['created'] = (int)$user_created;
	$user['modified'] = (int)$user_modified;
	$user['accessed'] = $now;

	$r = user_get_role($user_id);

	$user['role'] = $r;

	$tabuser=db_prefix_table('user');

	if ($user_newpassword) {
		if ($password == $user_newpassword) {	// use the new one
			$sql="UPDATE $tabuser SET accessed=FROM_UNIXTIME($now), password='$user_newpassword', newpassword=NULL, modified=NOW() WHERE user_id=$user_id LIMIT 1";
		}
		else {									// keep the old one
			$sql="UPDATE $tabuser SET accessed=FROM_UNIXTIME($now), newpassword=NULL WHERE user_id=$user_id LIMIT 1";
		}
	}
	else {										// just keep track
		$sql="UPDATE $tabuser SET accessed=FROM_UNIXTIME($now) WHERE user_id=$user_id LIMIT 1";
	}

	db_update($sql);

	return $user;
}

function user_check_name($name, $user_id=false) {
	$sqlname=db_sql_arg($name, true);

	$where = "name=$sqlname";
	if ($user_id) {
		$where .= "AND user_id != $user_id";
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT user_id FROM $tabuser WHERE $where LIMIT 1";

	$r = db_query($sql);

	return $r ? false : true;
}

function user_check_mail($mail, $user_id=false) {
	$sqlmail=db_sql_arg($mail, true);

	$where = "mail=$sqlmail";
	if ($user_id) {
		$where .= "AND user_id != $user_id";
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT user_id FROM $tabuser WHERE $where LIMIT 1";

	$r = db_query($sql);

	return $r ? false : true;
}


