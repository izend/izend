<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    14
 * @link       http://www.izend.org
 */

require_once 'strflat.php';

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

function user_create($name, $password, $mail, $locale='en', $role_names=false) {
	$seed=substr(md5(uniqid()), 1, 8);
	$password=$password ? md5($seed . $password) : '';

	$sqlname=db_sql_arg($name, true, true);
	$sqlmail=db_sql_arg($mail, true, true);
	$sqllocale=db_sql_arg($locale);
	$sqlpassword=db_sql_arg($password);
	$sqlseed=db_sql_arg($seed);

	$tabuser=db_prefix_table('user');

	$sql="INSERT IGNORE $tabuser (name, password, seed, mail, created, locale) VALUES ($sqlname, $sqlpassword, $sqlseed, $sqlmail, NOW(), $sqllocale)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$user_id = db_insert_id();

	if ($role_names) {
		user_set_role($user_id, $role_names);
	}

	if ($name) {
		user_create_avatar($name);
	}

	return $user_id;
}

function user_delete($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT name FROM $tabuser WHERE user_id=$user_id LIMIT 1";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	extract($r[0]);	/* name */

	if ($name) {
		user_delete_avatar($name);
	}

	$tabuserrole=db_prefix_table('user_role');

	$sql="DELETE u, ur FROM $tabuser u LEFT JOIN $tabuserrole ur ON ur.user_id=u.user_id WHERE u.user_id=$user_id";

	$r = db_delete($sql);

	return $r;
}

function user_get($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT name AS user_name, password AS user_password, newpassword AS user_newpassword, seed AS user_seed, mail AS user_mail, website AS user_website, logged AS user_logged, UNIX_TIMESTAMP(created) AS user_created, UNIX_TIMESTAMP(modified) AS user_modified, UNIX_TIMESTAMP(accessed) AS user_accessed, locale AS user_locale, active AS user_active, banned AS user_banned FROM $tabuser WHERE user_id=$user_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function user_set($user_id, $name, $mail, $website, $locale) {
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

	$sqlname=db_sql_arg($name, true, true);
	$sqlmail=db_sql_arg($mail, true, true);
	$sqlwebsite=db_sql_arg($website, true, true);
	$sqllocale=db_sql_arg($locale);

	$sql="UPDATE $tabuser SET name=$sqlname, mail=$sqlmail, website=$sqlwebsite, locale=$sqllocale, modified=NOW() WHERE user_id=$user_id LIMIT 1";

	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	if ($name != $oldname) {
		user_delete_avatar($oldname);
		if ($name) {
			user_create_avatar($name);
		}
	}

	return true;
}

function user_set_status($user_id, $active, $banned) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$sqlactive=$active ? 1 : 0;
	$sqlbanned=$banned ? 1 : 0;

	$tabuser=db_prefix_table('user');

	$sql="UPDATE $tabuser SET active=$sqlactive, banned=$sqlbanned WHERE user_id=$user_id LIMIT 1";

	$r = db_update($sql);

	return $r;
}

function user_set_newpassword($user_id, $newpassword) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$r = user_get($user_id);

	if (!$r) {
		return false;
	}

	extract($r);	/* user_seed */

	$newpassword=$newpassword ? md5($user_seed . $newpassword) : '';

	$sqlnewpassword=db_sql_arg($newpassword);

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

function user_set_role($user_id, $role_names) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuserrole=db_prefix_table('user_role');

	$sql="DELETE FROM $tabuserrole WHERE user_id=$user_id";

	$r = db_delete($sql);

	if ($role_names) {
		$sqlrolenames=db_sql_arg($role_names, true);
		if (is_array($sqlrolenames)) {
			$sqlrolenames=implode(',', $sqlrolenames);
		}

		$tabrole=db_prefix_table('role');

		$sql="INSERT IGNORE $tabuserrole (user_id, role_id) SELECT $user_id, r.role_id FROM $tabrole r WHERE r.name IN ($sqlrolenames)";

		$r = db_insert($sql);
	}

	return true;
}

function user_find($login) {
	$sqllogin=db_sql_arg($login, true);

	$tabuser=db_prefix_table('user');

	$sql="SELECT user_id FROM $tabuser WHERE name=$sqllogin OR mail=$sqllogin LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0]['user_id'] : false;
}

function user_login($login, $password, $quick=false) {
	global $login_lifetime;

	$user_id = user_find($login);

	if (!$user_id) {
		return false;
	}

	$r = user_get($user_id);

	if (!$r) {
		return false;
	}

	extract($r);	/* user_name user_password user_newpassword user_seed user_mail user_website user_created user_modified user_accessed user_locale user_active user_banned */

	if (!$user_active or $user_banned) {
		return false;
	}

	$password=md5($user_seed . $password);

	if ( ! ($password == $user_password or $password == $user_newpassword) ) {
		return false;
	}

	if ($quick) {
		return $user_id;
	}

	$user = array();
	$user['id'] = $user_id;
	$user['name'] = $user_name;
	$user['mail'] = $user_mail;
	$user['website'] = $user_website;
	$user['locale'] = $user_locale;
	$user['logged'] = (int)$user_logged;
	$user['created'] = (int)$user_created;
	$user['modified'] = (int)$user_modified;
	$user['accessed'] = (int)$user_accessed;

	if ($login_lifetime) {
		$user['lasttime'] = time();
	}

	$r = user_get_role($user_id);

	$user['role'] = $r;

	$tabuser=db_prefix_table('user');

	if ($user_newpassword) {
		if ($password == $user_newpassword) {	// use the new one
			$sql="UPDATE $tabuser SET logged=logged+1, accessed=NOW(), password='$user_newpassword', newpassword=NULL, modified=NOW() WHERE user_id=$user_id LIMIT 1";
		}
		else {									// keep the old one
			$sql="UPDATE $tabuser SET logged=logged+1, accessed=NOW(), newpassword=NULL WHERE user_id=$user_id LIMIT 1";
		}
	}
	else {										// just keep track
		$sql="UPDATE $tabuser SET logged=logged+1, accessed=NOW() WHERE user_id=$user_id LIMIT 1";
	}

	db_update($sql);

	return $user;
}

function user_check_name($name, $user_id=false) {
	$sqlname=db_sql_arg($name, true);

	$where = "name=$sqlname";
	if ($user_id) {
		$where .= " AND user_id != $user_id";
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
		$where .= " AND user_id != $user_id";
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT user_id FROM $tabuser WHERE $where LIMIT 1";

	$r = db_query($sql);

	return $r ? false : true;
}

function user_search($s, $pagesize=false, $page=1) {
	if (!is_numeric($pagesize) or $pagesize < 1) {
		$pagesize=false;
	}
	else if (!is_numeric($page) or $page < 1) {
		$page=1;
	}

	preg_match_all('/(\S+)/', $s, $r);
	$termlist = array_unique($r[0]);

	$sqltermlist=array();
	foreach ($termlist as $term) {
		$s=strflat($term);
		if (preg_match('/\w{3}/', $s)) {
			$sqltermlist[]=db_sql_arg("%$s%", false);
		}
	}

	$where='';
	if ($sqltermlist) {
		$fields=array('name', 'mail');

		$orlist=array();
		foreach ($sqltermlist as $sqlterm) {
			foreach ($fields as $f) {
				$orlist[]=$f . ' LIKE ' . $sqlterm;
			}
		}
		$where='WHERE ' . implode(' OR ', $orlist);
	}

	$tabuser=db_prefix_table('user');

	$count=$limit=false;
	if ($pagesize) {
		$sql="SELECT COUNT(*) AS count FROM $tabuser $where";

		$r = db_query($sql);
		if (!$r) {
			return false;
		}
		$count=$r[0]['count'];

		$limit=($page - 1) * $pagesize . ', ' . $pagesize;
	}

	$sql="SELECT user_id, name AS user_name, mail AS user_mail, website AS user_website, active AS user_active, banned AS user_banned, logged AS user_logged, UNIX_TIMESTAMP(accessed) AS user_accessed FROM $tabuser $where ORDER by user_id";

	if ($limit) {
		$sql .= " LIMIT $limit";
	}

	$r = db_query($sql);
	if (!$r) {
		return false;
	}

	if (!$count) {
		$count=count($r);
	}

	return array($count, $r);
}

