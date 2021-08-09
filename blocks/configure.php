<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    56
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'identicon.php';
require_once 'newdbpassword.php';
require_once 'validatedbname.php';
require_once 'validatedbpassword.php';
require_once 'validatehostname.php';
require_once 'validateipaddress.php';
require_once 'validatepassword.php';
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

define('TMP_DIRNAME', 'tmp');

define('PHPQRCODECACHE_DIRNAME', 'phpqrcode' . DIRECTORY_SEPARATOR . 'cache');

define('SITEMAP_XML', 'sitemap.xml');
define('ROBOTS_TXT', 'robots.txt');

define('HTACCESS', 'htaccess');
define('DOT_HTACCESS', '.htaccess');

function configure($lang) {
	global $system_languages;

	$writable_files=array(
		DOT_HTACCESS,
		CONFIG_DIRNAME . DIRECTORY_SEPARATOR . DB_INC,
		CONFIG_DIRNAME . DIRECTORY_SEPARATOR . CONFIG_INC,
		CONFIG_DIRNAME . DIRECTORY_SEPARATOR . ALIASES_INC,
		LOGOS_DIRNAME . DIRECTORY_SEPARATOR . SITELOGO_PNG,
		SITEMAP_XML,
		ROBOTS_TXT,
		AVATARS_DIRNAME,
		LOG_DIRNAME,
		PHPQRCODECACHE_DIRNAME,
		TMP_DIRNAME,
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
	$db_type='mysql';
	$db_reuse=false;
	$db_host='localhost';
	$db_admin_user=$db_admin_password='';
	$db_name=$db_user=$db_password=$db_prefix='';
	$site_admin_user=$site_admin_password='';

	switch($action) {
		case 'init':
			$sitename='mysite.net';
			$webmaster='webmaster@mysite.net';
			$content_languages=array($lang);
			$default_language=$lang;
			$db_flag=true;
			$db_reuse=false;
			$db_name='mysite';
			$db_user='mysite';
			$db_prefix='mysite_';

			$db_password=newdbpassword();

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
			if (isset($_POST['configure_db_type'])) {
				$db_type=readarg($_POST['configure_db_type']);
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

	$bad_db_type=false;

	$missing_db_name=false;
	$bad_db_name=false;

	$bad_db_prefix=false;

	$missing_db_host=false;
	$bad_db_host=false;

	$missing_db_user=false;
	$bad_db_user=false;
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
				if (empty($db_type) or !in_array($db_type, array('mysql', 'pgsql'))) {
					$bad_db_type=true;
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
				if (!$db_reuse and empty($db_password)) {
					$db_password=newdbpassword();
				}
				else if (!$db_reuse and !validate_db_password($db_password)) {
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
			if ($bad_token or $bad_write_permission or $missing_sitename or $missing_webmaster or $missing_content_languages or $bad_default_language or $missing_db_admin_user or $missing_db_admin_password or $missing_db_name or $bad_db_name or $bad_db_type or $missing_db_host or $bad_db_host or $missing_db_user or $bad_db_user or $weak_db_password or $missing_site_admin_user or $bad_site_admin_user or $missing_site_admin_password or $weak_site_admin_password) {
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
				switch ($db_type) {
					case 'pgsql':
						require_once 'configurepgsql.php';
						break;
					case 'mysql':
					default:
						require_once 'configuremysql.php';
						break;
				}
				if (!$db_reuse) {
					try {
						create_db($db_admin_user, $db_admin_password, 'localhost', $db_name, $db_user, $db_password);
					}
					catch (PDOException $e) {
						$db_error=$e->getMessage();
						break;
					}
				}

				try {
					init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language);
				}
				catch (PDOException $e) {
					$db_error=$e->getMessage();
					break;
				}

				$img=identicon($site_admin_user, AVATAR_SIZE);
				@imagepng($img, AVATARS_DIR . DIRECTORY_SEPARATOR . $site_admin_user . '.png');

				$db_inc = build_db_inc($db_host, $db_name, $db_user, $db_password, $db_prefix, $db_type);
				$config_inc = build_config_inc($sitename, $webmaster, $site_admin_user, 1, 'home', 'page', $languages);
				$features=array('captcha', 'avatar', 'rssfeed', 'home', 'contact', 'user', 'nobody', 'account', 'password', 'newuser', 'search', 'suggest', 'download', 'admin', 'adminuser', 'fileupload', 'pagecontent', 'pagevisit', 'page', 'editpage', 'folder', 'folderedit', 'story', 'storyedit', 'book', 'bookedit', 'newsletter', 'newsletteredit', 'newslettersubscribe', 'newsletterunsubscribe', 'traffic', 'thread', 'threadedit', 'node', 'editnode', 'donation', 'paypalreturn', 'paypalcancel', 'paylinereturn', 'paylinecancel', 'sslverifyclient', 'saction');
				$aliases_inc = build_aliases_inc($features, $languages);
			}
			else {
				$db_inc = build_db_inc(false, false, false, false, false, false);
				$config_inc = build_config_inc($sitename, $webmaster, $site_admin_user, false, 'homepage', 'anypage', $languages);
				$features=array('captcha', 'avatar', 'rssfeed', 'homepage', 'contact', 'donation', 'paypalreturn', 'paypalcancel', 'paylinereturn', 'paylinecancel', 'sslverifyclient', 'saction');
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

			$htaccess = build_htaccess($sitename);
			@file_put_contents(ROOT_DIR . DIRECTORY_SEPARATOR . DOT_HTACCESS, $htaccess);

			$sitemap_xml = build_sitemap_xml($sitename, $languages);
			@file_put_contents(ROOT_DIR . DIRECTORY_SEPARATOR . SITEMAP_XML, array('<?xml version="1.0" encoding="UTF-8"?>', "\n", $sitemap_xml));

			$robots_txt = build_robots_txt($sitename, $languages);
			@file_put_contents(ROOT_DIR . DIRECTORY_SEPARATOR . ROBOTS_TXT, $robots_txt);

			$logo = strlogo($sitename);
			@imagepng($logo, LOGOS_DIR . DIRECTORY_SEPARATOR . SITELOGO_PNG, 9, PNG_ALL_FILTERS);
			imagedestroy($logo);

			session_reopen();

			$output = view('configureok', $lang);

			return $output;

		default:
			break;
	}

	$_SESSION['configure_token'] = $token = token_id();

	$errors = compact('bad_write_permission', 'missing_sitename', 'missing_webmaster', 'missing_content_languages', 'bad_default_language', 'missing_db_admin_user', 'missing_db_admin_password', 'bad_db_type', 'missing_db_name', 'bad_db_name', 'missing_db_host', 'bad_db_host', 'bad_db_prefix', 'missing_db_user', 'bad_db_user', 'weak_db_password', 'missing_site_admin_user', 'bad_site_admin_user', 'missing_site_admin_password', 'weak_site_admin_password');

	$output = view('configure', $lang, compact('token', 'sitename', 'webmaster', 'db_error', 'file_error', 'internal_error', 'content_languages', 'default_language', 'db_flag', 'db_type', 'db_reuse', 'db_admin_user', 'db_admin_password', 'db_name', 'db_host', 'db_prefix', 'db_user', 'db_password', 'site_admin_user', 'site_admin_password', 'errors'));

	return $output;
}

function build_db_inc($db_host, $db_name, $db_user, $db_password, $db_prefix, $db_type) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . DB_INC, compact('db_host', 'db_name', 'db_user', 'db_password', 'db_prefix', 'db_type'));
}

function build_config_inc($sitename, $webmaster, $username, $root_node, $home_action, $default_action, $languages) {
	$sitekey=function_exists('openssl_random_pseudo_bytes') ? bin2hex(openssl_random_pseudo_bytes(32)) : false;

	return render(INIT_DIR . DIRECTORY_SEPARATOR . CONFIG_INC, compact('sitename', 'webmaster', 'username', 'root_node', 'home_action', 'default_action', 'languages', 'sitekey'));
}

function build_aliases_inc($features, $languages) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . ALIASES_INC, compact('features', 'languages'));
}

function build_sitemap_xml($sitename, $languages) {
	$date=date('Y-m-d');

	return render(INIT_DIR . DIRECTORY_SEPARATOR . SITEMAP_XML, compact('sitename', 'languages', 'date'));
}

function build_robots_txt($sitename, $languages) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . ROBOTS_TXT, compact('sitename', 'languages'));
}

function build_htaccess($sitename) {
	return render(INIT_DIR . DIRECTORY_SEPARATOR . HTACCESS, compact('sitename'));
}
