<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    19
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'identicon.php';
require_once 'newpassword.php';
require_once 'validatepassword.php';
require_once 'validatedbname.php';
require_once 'validatehostname.php';
require_once 'validateipaddress.php';
require_once 'tokenid.php';
require_once 'strlogo.php';

define('DB_INC', 'db.inc');
define('CONFIG_INC', 'config.inc');
define('ALIASES_INC', 'aliases.inc');
define('INIT_DIRNAME', 'init');
define('CONFIG_DIRNAME', 'includes');
define('INIT_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . INIT_DIRNAME);
define('CONFIG_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . CONFIG_DIRNAME);

define('SITELOGO_PNG', 'sitelogo.png');
define('LOGOS_DIRNAME', 'logos');
define('LOGOS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . LOGOS_DIRNAME);

define('AVATARS_DIRNAME', 'avatars');
define('AVATAR_SIZE', 24);
define('AVATARS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . AVATARS_DIRNAME);

define('LOG_DIRNAME', 'log');
define('LOG_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . LOG_DIRNAME);

define('SITEMAP_XML', 'sitemap.xml');

function configure($lang) {
	global $system_languages;
	global $base_url;

	$writable_files=array(
						CONFIG_DIRNAME . DIRECTORY_SEPARATOR . DB_INC,
						CONFIG_DIRNAME . DIRECTORY_SEPARATOR . CONFIG_INC,
						CONFIG_DIRNAME . DIRECTORY_SEPARATOR . ALIASES_INC,
						LOGOS_DIRNAME . DIRECTORY_SEPARATOR . SITELOGO_PNG,
						SITEMAP_XML,
						AVATARS_DIRNAME,
						LOG_DIRNAME,
						);
	$bad_write_permission=false;

	foreach ($writable_files as $fname) {
		$fpath = ROOT_DIR . DIRECTORY_SEPARATOR . $fname;
		clearstatcache(true, $fpath);
		if (!is_writable($fpath)) {
			if (!is_array($bad_write_permission)) {
				$bad_write_permission=array();
			}
			$bad_write_permission[]=$fname;
		}
	}

	$token=false;
	if (isset($_POST['configure_token'])) {
		$token=readarg($_POST['configure_token']);
	}

	$action='init';
	if (isset($_POST['configure_configure'])) {
		$action='configure';
	}

	$sitename=$webmaster='';
	$content_languages=false;
	$default_language=false;
	$db_flag=false;
	$db_reuse=false;
	$db_host='localhost';
	$db_admin_user=$db_admin_password='';
	$db_name=$db_user=$db_password=$db_prefix='';
	$site_admin_user=$site_admin_password='';

	switch($action) {
		case 'init':
			$sitename='izendnew.org';
			$webmaster='webmaster@izendnew.org';
			$content_languages=array($lang);
			$default_language=$lang;
			$db_flag=true;
			$db_reuse=false;
			$db_name='izendnew';
			$db_user='izendnew';
			$db_password=newpassword(8);
			$db_prefix='izendnew_';
			break;

		case 'configure':
			if (isset($_POST['configure_sitename'])) {
				$sitename=readarg($_POST['configure_sitename']);
			}
			if (isset($_POST['configure_webmaster'])) {
				$webmaster=readarg($_POST['configure_webmaster']);
			}
			if (isset($_POST['configure_content_languages'])) {
				$content_languages=readarg($_POST['configure_content_languages']);
			}
			if (isset($_POST['configure_default_language'])) {
				$default_language=readarg($_POST['configure_default_language']);
			}
			if (isset($_POST['configure_db_flag'])) {
				$db_flag=readarg($_POST['configure_db_flag']) == 'yes' ? true : false;
			}
			if (isset($_POST['configure_db_reuse'])) {
				$db_reuse=readarg($_POST['configure_db_reuse']) == 'yes' ? true : false;
			}
			if (isset($_POST['configure_db_admin_user'])) {
				$db_admin_user=readarg($_POST['configure_db_admin_user']);
			}
			if (isset($_POST['configure_db_admin_password'])) {
				$db_admin_password=readarg($_POST['configure_db_admin_password']);
			}
			if (isset($_POST['configure_db_name'])) {
				$db_name=readarg($_POST['configure_db_name']);
			}
			if (isset($_POST['configure_db_host'])) {
				$db_host=readarg($_POST['configure_db_host']);
			}
			if (isset($_POST['configure_db_user'])) {
				$db_user=readarg($_POST['configure_db_user']);
			}
			if (isset($_POST['configure_db_password'])) {
				$db_password=readarg($_POST['configure_db_password']);
			}
			if (isset($_POST['configure_db_prefix'])) {
				$db_prefix=readarg($_POST['configure_db_prefix']);
			}
			if (isset($_POST['configure_site_admin_user'])) {
				$site_admin_user=readarg($_POST['configure_site_admin_user']);
			}
			if (isset($_POST['configure_site_admin_password'])) {
				$site_admin_password=readarg($_POST['configure_site_admin_password']);
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_sitename=false;
	$missing_webmaster=false;

	$missing_content_languages=false;
	$bad_content_languages=false;
	$missing_default_language=false;
	$bad_default_language=false;

	$missing_db_admin_user=false;
	$missing_db_admin_password=false;

	$missing_db_name=false;
	$bad_db_name=false;

	$bad_db_prefix=false;

	$missing_db_host=false;
	$bad_db_host=false;

	$missing_db_user=false;
	$bad_db_user=false;
	$missing_db_password=false;
	$weak_db_password=false;

	$missing_site_admin_user=false;
	$bad_site_admin_user=false;
	$missing_site_admin_password=false;
	$weak_site_admin_password=false;

	$db_error=false;
	$file_error=false;
	$internal_error=false;

	switch($action) {
		case 'configure':
			if (!isset($_SESSION['configure_token']) or $token != $_SESSION['configure_token']) {
				$bad_token=true;
			}
			if (empty($sitename)) {
				$missing_sitename=true;
			}
			if (empty($webmaster)) {
				$missing_webmaster=true;
			}
			if (empty($content_languages)) {
				$missing_content_languages=true;
			}
			else if (!is_array($content_languages)) {
				$bad_content_languages=true;
			}
			else {
				foreach ($content_languages as $clang) {
					if (!in_array($clang, $system_languages)) {
						$bad_content_languages=true;
						break;
					}
				}
				if (empty($default_language)) {
					$default_language=$content_languages[0];
				}
				else if (!in_array($default_language, $content_languages)) {
					$bad_default_language=true;
				}
			}

			if ($db_flag) {
				if (empty($db_name)) {
					$missing_db_name=true;
				}
				else if (!$db_reuse and !validate_db_name($db_name)) {
					$bad_db_name=true;
				}
				if (!empty($db_prefix) and !validate_db_name($db_prefix)) {
					$bad_db_prefix=true;
				}
				if (!$db_reuse) {
					if (empty($db_admin_user)) {
						$missing_db_admin_user=true;
					}
					if (empty($db_admin_password)) {
						$missing_db_admin_password=true;
					}
				}

				if (empty($db_host)) {
					$missing_db_host=true;
				}
				else if (!(validate_host_name($db_host) or validate_ip_address($db_host))) {
					$bad_db_host=true;
				}
				if (empty($db_user)) {
					$missing_db_user=true;
				}
				else if (!$db_reuse and !validate_db_name($db_user)) {
					$bad_db_user=true;
				}
				if (empty($db_password)) {
					$missing_db_password=true;
				}
				else if (!$db_reuse and !validate_password($db_password)) {
					$weak_db_password=true;
				}
				if (empty($site_admin_user)) {
					$missing_site_admin_user=true;
				}
				else if (!validate_db_name($site_admin_user)) {
					$bad_site_admin_user=true;
				}
				if (empty($site_admin_password)) {
					$missing_site_admin_password=true;
				}
				else if (!validate_password($site_admin_password)) {
					$weak_site_admin_password=true;
				}
			}
			break;
		default:
			break;
	}

	switch($action) {
		case 'configure':
			if ($bad_token or $bad_write_permission or $missing_sitename or $missing_webmaster or $missing_content_languages or $bad_default_language or $missing_db_admin_user or $missing_db_admin_password or $missing_db_name or $bad_db_name or $missing_db_host or $bad_db_host or $missing_db_user or $bad_db_user or $missing_db_password or $weak_db_password or $missing_site_admin_user or $bad_site_admin_user or $missing_site_admin_password or $weak_site_admin_password) {
				break;
			}

			$site_admin_mail=$site_admin_user . '@' . $sitename;

			$languages=array($default_language);
			foreach ($content_languages as $clang) {
				if ($clang != $default_language) {
					$languages[]=$clang;
				}
			}

			if ($db_flag) {
				if (!$db_reuse) {
					if (!create_db($db_admin_user, $db_admin_password, 'localhost', $db_name, $db_user, $db_password)) {
						$db_error=mysql_error();
						break;
					}
				}

				if (!init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language)) {
					$db_error=mysql_error();
					break;
				}

				$img=identicon($site_admin_user, AVATAR_SIZE);
				@imagepng($img, AVATARS_DIR . DIRECTORY_SEPARATOR . $site_admin_user . '.png');

				$db_inc = build_db_inc($db_host, $db_name, $db_user, $db_password, $db_prefix);
				$config_inc = build_config_inc($sitename, $webmaster, $site_admin_user, 1, 'home', 'page', $languages);
				$features=array('captcha', 'avatar', 'rssfeed', 'home', 'contact', 'user', 'nobody', 'account', 'password', 'newuser', 'search', 'suggest', 'download', 'admin', 'adminuser', 'page', 'editpage', 'folder', 'folderedit', 'story', 'storyedit', 'book', 'bookedit', 'thread', 'threadedit', 'node', 'editnode', 'donation', 'paypalreturn', 'paypalcancel');
				$aliases_inc = build_aliases_inc($features, $languages);
			}
			else {
				$db_inc = build_db_inc(false, false, false, false, false);
				$config_inc = build_config_inc($sitename, $webmaster, $site_admin_user, false, 'homepage', 'anypage', $languages);
				$features=array('captcha', 'avatar', 'rssfeed', 'homepage', 'contact', 'donation', 'paypalreturn', 'paypalcancel');
				$aliases_inc = build_aliases_inc($features, $languages);
			}

			if (!$db_inc or !$config_inc or !$aliases_inc) {
				$internal_error=true;
				break;
			}

			if (!@file_put_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . DB_INC, array('<?php', $db_inc))) {
				$file_error=true;
				break;
			}
			if (!@file_put_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . CONFIG_INC, array('<?php', $config_inc))) {
				$file_error=true;
				break;
			}
			if (!@file_put_contents(CONFIG_DIR . DIRECTORY_SEPARATOR . ALIASES_INC, array("<?php", $aliases_inc))) {
				$file_error=true;
				break;
			}

			$sitemap_xml = build_sitemap_xml($sitename, $languages);
			@file_put_contents(ROOT_DIR . DIRECTORY_SEPARATOR . SITEMAP_XML, array('<?xml version="1.0" encoding="UTF-8"?>', $sitemap_xml));

			$logo = strlogo($sitename);
			@imagepng($logo, LOGOS_DIR . DIRECTORY_SEPARATOR . SITELOGO_PNG, 9, PNG_ALL_FILTERS);
			imagedestroy($logo);

			session_reopen();
			reload($base_url);

			return false;

		default:
			break;
	}

	$_SESSION['configure_token'] = $token = token_id();

	$errors = compact('bad_write_permission', 'missing_sitename', 'missing_webmaster', 'missing_content_languages', 'bad_default_language', 'missing_db_admin_user', 'missing_db_admin_password', 'missing_db_name', 'bad_db_name', 'missing_db_host', 'bad_db_host', 'bad_db_prefix', 'missing_db_user', 'bad_db_user', 'missing_db_password', 'weak_db_password', 'missing_site_admin_user', 'bad_site_admin_user', 'missing_site_admin_password', 'weak_site_admin_password');

	$output = view('configure', $lang, compact('token', 'sitename', 'webmaster', 'db_error', 'file_error', 'internal_error', 'content_languages', 'default_language', 'db_flag', 'db_reuse', 'db_admin_user', 'db_admin_password', 'db_name', 'db_host', 'db_prefix', 'db_user', 'db_password', 'site_admin_user', 'site_admin_password', 'errors'));

	return $output;
}

function build_db_inc($db_host, $db_name, $db_user, $db_password, $db_prefix) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . DB_INC, compact('db_host', 'db_name', 'db_user', 'db_password', 'db_prefix'));
}

function build_config_inc($sitename, $webmaster, $username, $root_node, $home_action, $default_action, $languages) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . CONFIG_INC, compact('sitename', 'webmaster', 'username', 'root_node', 'home_action', 'default_action', 'languages'));
}

function build_aliases_inc($features, $languages) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . ALIASES_INC, compact('features', 'languages'));
}

function build_sitemap_xml($sitename, $languages) {
	$date=date('Y-n-j');
	return render(INIT_DIR . DIRECTORY_SEPARATOR . SITEMAP_XML, compact('sitename', 'languages', 'date'));
}

function recover_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user) {
	$db_conn=@mysql_connect($db_host, $db_admin_user, $db_admin_password);
	if (!$db_conn) {
		return false;
	}

	$sql="DELETE FROM mysql.`user` WHERE `user`.`Host` = '$db_host' AND `user`.`User` = '$db_user'";
	@mysql_query($sql, $db_conn);

	$sql="DELETE FROM mysql.`db` WHERE `db`.`Host` = '$db_host' AND `db`.`Db` = '$db_name' AND `db`.`User` = '$db_user'";
	@mysql_query($sql, $db_conn);

	$sql="DROP DATABASE `$db_name`";
	@mysql_query($sql, $db_conn);

	return true;
}

function create_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user, $db_password) {
	$db_conn=@mysql_connect($db_host, $db_admin_user, $db_admin_password);
	if (!$db_conn) {
		return false;
	}

	$sql="CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO mysql.`user` (`Host`, `User`, `Password`)
VALUES ('$db_host', '$db_user', PASSWORD('$db_password'));
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO mysql.`db` (`Host`, `Db`, `User`, `Select_priv`, `Insert_priv`, `Update_priv`, `Delete_priv`, `Create_priv`, `Drop_priv`)
VALUES ('$db_host', '$db_name', '$db_user', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	if (!@mysql_query("FLUSH PRIVILEGES", $db_conn)) {
		return false;
	}

	if (!@mysql_close($db_conn)) {
		return false;
	}

	return true;
}

function init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language) {
	$db_conn=@mysql_connect($db_host, $db_user, $db_password);
	if (!$db_conn) {
		return false;
	}

	if (!@mysql_select_db($db_name, $db_conn)) {
		return false;
	}

	if (!@mysql_query("SET NAMES 'utf8'", $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `created` datetime NOT NULL,
  `edited` datetime NOT NULL,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `NODE` (`node_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_download` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `name` varchar(50) DEFAULT NULL,
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_file` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `path` varchar(200) DEFAULT NULL,
  `start` int(5) unsigned NOT NULL DEFAULT '0',
  `end` int(5) unsigned NOT NULL DEFAULT '0',
  `format` varchar(20) DEFAULT NULL,
  `lineno` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_infile` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_longtail` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `file` varchar(200) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  `icons` tinyint(1) NOT NULL DEFAULT '0',
  `skin` varchar(200) DEFAULT NULL,
  `controlbar` enum('none','bottom','top','over') NOT NULL DEFAULT 'none',
  `duration` int(5) unsigned NOT NULL DEFAULT '0',
  `autostart` tinyint(1) NOT NULL DEFAULT '0',
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_text` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `text` text,
  `eval` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `nocomment` tinyint(1) NOT NULL DEFAULT '0',
  `nomorecomment` tinyint(1) NOT NULL DEFAULT '0',
  `ilike` tinyint(1) NOT NULL DEFAULT '1',
  `tweet` tinyint(1) NOT NULL DEFAULT '1',
  `plusone` tinyint(1) NOT NULL DEFAULT '1',
  `linkedin` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node_locale` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NULL default NULL,
  `abstract` text,
  `cloud` text,
  PRIMARY KEY (`node_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node_content` (
  `node_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL,
  `content_type` enum('text','file','download','infile','longtail') CHARACTER SET ascii NOT NULL DEFAULT 'text',
  `number` int(3) unsigned NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`,`content_id`,`content_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '1',
  `thread_type` enum('thread','folder','story','book') NOT NULL DEFAULT 'thread',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `nosearch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nocloud` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `nocomment` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `nomorecomment` tinyint(1) NOT NULL DEFAULT '0',
  `ilike` tinyint(1) NOT NULL DEFAULT '1',
  `tweet` tinyint(1) NOT NULL DEFAULT '1',
  `plusone` tinyint(1) NOT NULL DEFAULT '1',
  `linkedin` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`thread_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_locale` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NULL default NULL,
  `abstract` text,
  `cloud` text,
  PRIMARY KEY (`thread_id`,`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_node` (
  `thread_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `number` int(4) unsigned NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`thread_id`,`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_list` (
  `thread_id` int(10) unsigned NOT NULL,
  `number` int(4) unsigned NOT NULL,
  PRIMARY KEY (`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`tag_id`,`locale`),
  UNIQUE KEY `locale` (`locale`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}tag_index` (
  `tag_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `password` varchar(32) NOT NULL,
  `newpassword` varchar(32) DEFAULT NULL,
  `mail` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accessed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `logged` int(10) unsigned NOT NULL DEFAULT '0',
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `mail` (`mail`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}user_role` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}registry` (
  `name` varchar(100) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
CREATE TABLE `${db_prefix}track` (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varchar(15) NOT NULL,
  `request_uri` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`track_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}role` (`role_id`, `name`) VALUES
(1, 'administrator'),
(2, 'writer'),
(3, 'reader'),
(4, 'moderator');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}user` (`user_id`, `name`, `password`, `mail`, `created`, `locale`, `active`, `banned`) VALUES
(1, '$site_admin_user', MD5('$site_admin_password'), '$site_admin_mail', NOW(), '$default_language', 1, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}user_role` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}node` (`node_id`, `user_id`, `created`, `modified`, `nocomment`, `nomorecomment`, `ilike`, `tweet`, `plusone`, `linkedin`) VALUES
(1, 1, NOW(), NOW(), 1, 1, 1, 1, 1, 1),
(2, 1, NOW(), NOW(), 1, 1, 0, 0, 0, 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}node_locale` (`node_id`, `locale`, `name`, `title`, `abstract`, `cloud`) VALUES
(1, 'fr', 'bienvenue', 'Bienvenue', NULL, NULL),
(1, 'en', 'welcome', 'Welcome', NULL, NULL),
(2, 'fr', 'documentation', 'Documentation', 'Manuel de l''utilisateur.', 'documentation'),
(2, 'en', 'documentation', 'Documentation', 'User''s manual.', 'documentation');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}node_content` (`node_id`, `content_id`, `content_type`, `number`) VALUES
(1, 1, 'infile', 1),
(1, 1, 'text', 2),
(2, 2, 'text', 1);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_text` (`content_id`, `locale`, `text`, `eval`) VALUES
(1, 'fr', '<p>Votre site <b>iZend</b> est maintenant opérationnel.</p>\r\n<p class="readmore">Lisez la <a href="/fr/documentation">documentation</a>.</p>', 0),
(1, 'en', '<p>Your <b>iZend</b> site is now operational.</p>\r\n<p class="readmore">Read the <a href="/en/documentation">documentation</a>.</p>', 0),
(2, 'fr', '<p class="readmore">Consultez la <a href="http://www.izend.org/fr/documentation">documentation en ligne</a>.</p>', 0),
(2, 'en', '<p class="readmore">Read the <a href="http://www.izend.org/en/documentation">on-line documentation</a>.</p>', 0);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_infile` (`content_id`, `locale`, `path`) VALUES
(1, 'fr', 'views/fr/social.phtml'),
(1, 'en', 'views/en/social.phtml');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}tag` (`tag_id`, `locale`, `name`) VALUES
(1, 'fr', 'documentation'),
(2, 'en', 'documentation');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}tag_index` (`tag_id`, `node_id`) VALUES
(1, 2),
(2, 2);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread` (`thread_id`, `user_id`, `thread_type`, `created`, `modified`, `nosearch`, `nocloud`, `nocomment`, `nomorecomment`, `ilike`, `tweet`, `plusone`, `linkedin`) VALUES
(1, 1, 'folder', NOW(), NOW(), 0, 0, 0, 0, 1, 1, 1, 1);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_locale` (`thread_id`, `locale`, `name`, `title`) VALUES
(1, 'fr', 'contenu', 'Contenu'),
(1, 'en', 'content', 'Content');
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_node` (`thread_id`, `node_id`, `number`) VALUES
(1, 1, 1),
(1, 2, 2);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_list` (`thread_id`, `number`) VALUES
(1, 1);
_SEP_;
	if (!@mysql_query($sql, $db_conn)) {
		return false;
	}

	if (!@mysql_close($db_conn)) {
		return false;
	}

	return true;
}

