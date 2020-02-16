<?php

/**
 *
 * @copyright  2010-2020 izend.org
 * @version    26
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
	return @unlink(AVATARS_DIR . DIRECTORY_SEPARATOR . $name . '.png');
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

function user_create($name, $password, $mail, $locale='en', $timezone=false, $website=false, $confirmed=true) {
	$seed=substr(md5(uniqid()), 1, 8);
	$password=$password ? md5($seed . $password) : '';

	$sqlname=db_sql_arg($name, true, true);
	$sqlmail=db_sql_arg($mail, true, true);
	$sqllocale=db_sql_arg($locale, false);
	$sqltimezone=db_sql_arg($timezone, true, true);
	$sqlwebsite=db_sql_arg($website, true, true);
	$sqlpassword=db_sql_arg($password, false);
	$sqlseed=db_sql_arg($seed, false);
	$sqlconfirmed=$confirmed ? 'TRUE' : 'FALSE';

	$wherenotexists=array();
	if ($name) {
		$wherenotexists[]="name=$sqlname";
	}
	if ($mail) {
		$wherenotexists[]="mail=$sqlmail";
	}
	$wherenotexists=implode(' OR ', $wherenotexists);

	$tabuser=db_prefix_table('user');

	$sql="INSERT INTO $tabuser (name, password, seed, mail, created, confirmed, locale, timezone, website) SELECT $sqlname, $sqlpassword, $sqlseed, $sqlmail, NOW(), $sqlconfirmed, $sqllocale, $sqltimezone, $sqlwebsite WHERE NOT EXISTS (SELECT user_id FROM $tabuser WHERE $wherenotexists)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$user_id = db_insert_id($tabuser . '_user_id_seq');

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

	$sql="SELECT name FROM $tabuser WHERE user_id=$user_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	extract($r[0]);	/* name */

	if ($name) {
		user_delete_avatar($name);
	}

	$tabuserinfo=db_prefix_table('user_info');

	$sql="DELETE FROM $tabuserinfo WHERE user_id=$user_id";

	$r = db_delete($sql);

	$tabuserrole=db_prefix_table('user_role');

	$sql="DELETE FROM $tabuserrole WHERE user_id=$user_id";

	$r = db_delete($sql);

	$sql="DELETE FROM $tabuser WHERE user_id=$user_id";

	$r = db_delete($sql);

	if (!$r) {
		return false;
	}

	return true;
}

function user_get($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT name AS user_name, password AS user_password, newpassword AS user_newpassword, seed AS user_seed, mail AS user_mail, timezone AS user_timezone, website AS user_website, logged AS user_logged, UNIX_TIMESTAMP(created) AS user_created, UNIX_TIMESTAMP(modified) AS user_modified, UNIX_TIMESTAMP(accessed) AS user_accessed, locale AS user_locale, active AS user_active, banned AS user_banned, confirmed AS user_confirmed FROM $tabuser WHERE user_id=$user_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function user_set($user_id, $name, $mail, $website, $locale, $timezone) {
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
	$sqllocale=db_sql_arg($locale, false);
	$sqltimezone=db_sql_arg($timezone, true, true);

	$sql="UPDATE $tabuser SET name=$sqlname, mail=$sqlmail, website=$sqlwebsite, locale=$sqllocale, timezone=$sqltimezone, modified=NOW() WHERE user_id=$user_id";

	$r = db_update($sql);

	if ($r === false) {
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

function user_get_info($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuserinfo=db_prefix_table('user_info');

	$sql="SELECT lastname AS user_lastname, firstname AS user_firstname FROM $tabuserinfo WHERE user_id=$user_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function user_set_info($user_id, $lastname, $firstname) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$sqllastname=db_sql_arg($lastname, true, true);
	$sqlfirstname=db_sql_arg($firstname, true, true);

	$tabuserinfo=db_prefix_table('user_info');

	$sql="INSERT INTO $tabuserinfo (user_id, lastname, firstname) SELECT $user_id, $sqllastname, $sqlfirstname WHERE NOT EXISTS (SELECT user_id FROM $tabuserinfo WHERE user_id=$user_id)";

	$r = db_insert($sql);

	if ($r) {
		return true;
	}

	$sql="UPDATE $tabuserinfo SET lastname=$sqllastname, firstname=$sqlfirstname WHERE user_id=$user_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function user_set_status($user_id, $active, $banned, $confirmed) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$sqlactive=$active ? 'TRUE' : 'FALSE';
	$sqlbanned=$banned ? 'TRUE' : 'FALSE';

	$sqlconfirmed=$confirmed ? 'TRUE' : 'FALSE';

	$tabuser=db_prefix_table('user');

	$sql="UPDATE $tabuser SET active=$sqlactive, banned=$sqlbanned, confirmed=$sqlconfirmed WHERE user_id=$user_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function user_set_newpassword($user_id, $newpassword) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT seed AS user_seed FROM $tabuser WHERE user_id=$user_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	extract($r[0]);		/* user_seed */

	$newpassword=$newpassword ? md5($user_seed . $newpassword) : '';

	$sqlnewpassword=db_sql_arg($newpassword, false);

	$tabuser=db_prefix_table('user');

	$sql="UPDATE $tabuser SET newpassword=$sqlnewpassword WHERE user_id=$user_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function user_verify_password($user_id, $password) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="SELECT password AS user_password, seed AS user_seed FROM $tabuser WHERE user_id=$user_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	extract($r[0]);		/* user_password user_seed */

	return $password ? $user_password == md5($user_seed . $password) : !$user_password;
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

function user_set_role($user_id, $role) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuserrole=db_prefix_table('user_role');

	$sql="DELETE FROM $tabuserrole WHERE user_id=$user_id";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	if ($role) {
		if (is_array($role)) {
			$sqlrole=array();
			foreach ($role as $r) {
				$sqlrole[]=db_sql_arg($r, true);
			}
			$sqlrole=implode(',', $sqlrole);
		}
		else {
			$sqlrole=db_sql_arg($role, true);
		}

		$tabrole=db_prefix_table('role');

		$sql="INSERT INTO $tabuserrole (user_id, role_id) SELECT $user_id, r.role_id FROM $tabrole r WHERE r.name IN ($sqlrole)";

		$r = db_insert($sql);

		if (!$r) {
			return false;
		}
	}

	return true;
}

function user_confirm($user_id) {
	if (!is_numeric($user_id)) {
		return false;
	}

	$tabuser=db_prefix_table('user');

	$sql="UPDATE $tabuser SET confirmed=TRUE WHERE user_id=$user_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
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
	global $login_lifetime, $login_failed_delay;

	$user_id = user_find($login);

	if (!$user_id)
		goto failed;

	$r = user_get($user_id);

	if (!$r)
		goto failed;

	extract($r);	/* user_name user_password user_newpassword user_seed user_mail user_timezone user_website user_created user_modified user_accessed user_locale user_active user_banned */

	if (!$user_active or $user_banned)
		goto failed;

	if ($password) {
		$password=md5($user_seed . $password);

		if ( ! ($password == $user_password or $password == $user_newpassword) )
			goto failed;
	}

	if ($quick) {
		return $user_id;
	}

	$user = array();
	$user['id'] = $user_id;
	$user['name'] = $user_name;
	$user['mail'] = $user_mail;
	$user['website'] = $user_website;
	$user['timezone'] = $user_timezone;
	$user['locale'] = $user_locale;
	$user['logged'] = (int)$user_logged;
	$user['created'] = (int)$user_created;
	$user['modified'] = (int)$user_modified;
	$user['accessed'] = (int)$user_accessed;
	$user['confirmed'] = (int)$user_confirmed;

	if ($user_timezone) {
		date_default_timezone_set($user_timezone);
	}

	if ($login_lifetime) {
		$user['lasttime'] = time();
	}

	$r = user_get_info($user_id);

	if ($r) {
		extract($r);	/* user_lastname, user_firstname */

		$user['lastname'] = $user_lastname;
		$user['firstname'] = $user_firstname;
	}

	$r = user_get_role($user_id);

	$user['role'] = $r;

	$tabuser=db_prefix_table('user');

	if ($password and $user_newpassword) {
		if ($password == $user_newpassword) {
			$sql="UPDATE $tabuser SET logged=logged+1, accessed=NOW(), password='$user_newpassword', newpassword=NULL, modified=NOW() WHERE user_id=$user_id";
		}
		else {
			$sql="UPDATE $tabuser SET logged=logged+1, accessed=NOW(), newpassword=NULL WHERE user_id=$user_id";
		}
	}
	else {
		$sql="UPDATE $tabuser SET logged=logged+1, accessed=NOW() WHERE user_id=$user_id";
	}

	$r = db_update($sql);

	if ($r === false)
		goto failed;

	return $user;

failed:
	if ($login_failed_delay) {
		sleep($login_failed_delay);
	}

	return false;
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

	return !$r ? true : false;
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

	return !$r ? true : false;
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

		$offset=($page - 1) * $pagesize;
		$limit=$pagesize;
	}

	$sql="SELECT user_id, name AS user_name, mail AS user_mail, website AS user_website, active AS user_active, banned AS user_banned, logged AS user_logged, UNIX_TIMESTAMP(accessed) AS user_accessed FROM $tabuser $where ORDER by user_id";

	if ($limit) {
		$sql .= " LIMIT $limit OFFSET $offset";
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

