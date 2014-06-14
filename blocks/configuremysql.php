<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function create_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user, $db_password) {
	$dsn = "mysql:host=$db_host";

	try {
		$db_conn = new PDO($dsn, $db_admin_user, $db_admin_password);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		$sql="CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO mysql.`user` (`Host`, `User`, `Password`, `ssl_cipher`, `x509_issuer`, `x509_subject`)
VALUES ('$db_host', '$db_user', PASSWORD('$db_password'), '', '', '');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO mysql.`db` (`Host`, `Db`, `User`, `Select_priv`, `Insert_priv`, `Update_priv`, `Delete_priv`, `Create_priv`)
VALUES ('$db_host', '$db_name', '$db_user', 'Y', 'Y', 'Y', 'Y', 'Y');
_SEP_;
		$db_conn->exec($sql);

		$sql="FLUSH PRIVILEGES";
		$db_conn->exec($sql);
	}
	catch (PDOException $e) {
		throw($e);
	}

	$db_conn=null;

	return true;
}

function recover_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user) {
	$dsn = "mysql:host=$db_host";

	try {
		$db_conn = new PDO($dsn, $db_admin_user, $db_admin_password);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		$sql="DELETE FROM mysql.`user` WHERE `user`.`Host` = '$db_host' AND `user`.`User` = '$db_user'";
		$db_conn->exec($sql);

		$sql="DELETE FROM mysql.`db` WHERE `db`.`Host` = '$db_host' AND `db`.`Db` = '$db_name' AND `db`.`User` = '$db_user'";
		$db_conn->exec($sql);

		$sql="DROP DATABASE `$db_name`";
		$db_conn->exec($sql);
	}
	catch (PDOException $e) {
	}

	$db_conn=null;

	return true;
}

function init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language) {
	$dsn = "mysql:host=$db_host;dbname=$db_name";

	try {
		$db_conn = new PDO($dsn, $db_user, $db_password);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `created` datetime NOT NULL,
  `edited` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `node` (`node_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_download` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `name` varchar(50) DEFAULT NULL,
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`content_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

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
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

			$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_infile` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`content_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

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
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

			$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_text` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `text` text,
  `eval` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

			$sql= <<<_SEP_
CREATE TABLE `${db_prefix}content_youtube` (
  `content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  `miniature` VARCHAR(200) DEFAULT NULL,
  `title` VARCHAR(200) DEFAULT NULL,
  `autoplay` tinyint(1) NOT NULL DEFAULT '0',
  `controls` tinyint(1) NOT NULL DEFAULT '0',
  `fs` tinyint(1) NOT NULL DEFAULT '0',
  `theme` enum('light','dark') NOT NULL DEFAULT 'dark',
  `rel` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}newsletter_post` (
  `thread_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `locale` enum('fr','en') NOT NULL DEFAULT '$default_language',
  `scheduled` datetime NOT NULL,
  `mailed` datetime DEFAULT NULL,
  PRIMARY KEY (`thread_id`,`node_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}newsletter_user` (
  `mail` varchar(100) NOT NULL,
  `locale` enum('fr','en') NOT NULL DEFAULT '$default_language',
  `created` datetime NOT NULL,
  PRIMARY KEY (`mail`),
  KEY `locale` (`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `nocomment` tinyint(1) NOT NULL DEFAULT '0',
  `nomorecomment` tinyint(1) NOT NULL DEFAULT '0',
  `novote` tinyint(1) NOT NULL DEFAULT '0',
  `nomorevote` tinyint(1) NOT NULL DEFAULT '0',
  `ilike` tinyint(1) NOT NULL DEFAULT '1',
  `tweet` tinyint(1) NOT NULL DEFAULT '1',
  `plusone` tinyint(1) NOT NULL DEFAULT '1',
  `linkedin` tinyint(1) NOT NULL DEFAULT '1',
  `pinit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node_locale` (
  `node_id` int(10) unsigned NOT NULL,
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NULL default NULL,
  `abstract` text,
  `cloud` text,
  `image` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`node_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}node_content` (
  `node_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL,
  `content_type` enum('text','file','download','infile','youtube','longtail') NOT NULL DEFAULT 'text',
  `number` int(3) unsigned NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`,`content_id`,`content_type`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `thread_type` enum('thread','folder','story','book','rss','newsletter') NOT NULL DEFAULT 'thread',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `number` int(4) unsigned NOT NULL,
  `nosearch` tinyint(1) NOT NULL DEFAULT '0',
  `nocloud` tinyint(1) NOT NULL DEFAULT '0',
  `nocomment` tinyint(1) NOT NULL DEFAULT '0',
  `nomorecomment` tinyint(1) NOT NULL DEFAULT '0',
  `novote` tinyint(1) NOT NULL DEFAULT '0',
  `nomorevote` tinyint(1) NOT NULL DEFAULT '0',
  `ilike` tinyint(1) NOT NULL DEFAULT '1',
  `tweet` tinyint(1) NOT NULL DEFAULT '1',
  `plusone` tinyint(1) NOT NULL DEFAULT '1',
  `linkedin` tinyint(1) NOT NULL DEFAULT '1',
  `pinit` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`thread_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_locale` (
  `thread_id` int(10) unsigned NOT NULL,
  `locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `abstract` text,
  `cloud` text,
  `image` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`thread_id`,`locale`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}thread_node` (
  `thread_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `number` int(4) unsigned NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`thread_id`,`node_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`tag_id`,`locale`),
  UNIQUE KEY `locale` (`locale`,`name`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}tag_index` (
  `tag_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`node_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `password` char(32) CHARACTER SET ascii NOT NULL,
  `newpassword` char(32) CHARACTER SET ascii DEFAULT NULL,
  `seed` char(8) CHARACTER SET ascii NOT NULL,
  `mail` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `accessed` datetime DEFAULT NULL,
  `logged` int(10) unsigned NOT NULL DEFAULT '0',
  `locale` enum('en','fr') NOT NULL DEFAULT '$default_language',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `mail` (`mail`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE IF NOT EXISTS `${db_prefix}user_info` (
  `user_id` int(10) unsigned NOT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}user_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role` (`role_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}registry` (
  `name` varchar(100) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}track` (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` int(10) unsigned NOT NULL,
  `request_uri` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`track_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE `${db_prefix}vote` (
  `vote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `content_type` enum('node','thread','comment') NOT NULL DEFAULT 'node',
  `content_locale` enum('fr','en') NOT NULL DEFAULT 'fr',
  `created` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` int(10) unsigned NOT NULL,
  `value` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `content` (`content_id`,`content_type`,`content_locale`,`ip_address`,`user_id`)
) DEFAULT CHARSET=utf8;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}role` (`role_id`, `name`) VALUES
(1, 'administrator'),
(2, 'writer'),
(3, 'reader'),
(4, 'moderator'),
(5, 'member');
_SEP_;
		$db_conn->exec($sql);

		$seed=substr(md5(uniqid()), 1, 8);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}user` (`user_id`, `name`, `password`, `seed`, `mail`, `created`, `locale`, `active`, `banned`) VALUES
(1, '$site_admin_user', MD5(CONCAT('$seed', '$site_admin_password')), '$seed', '$site_admin_mail', NOW(), '$default_language', '1', '0');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}user_role` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}node` (`node_id`, `user_id`, `created`, `modified`, `nocomment`, `nomorecomment`, `novote`, `nomorevote`, `ilike`, `tweet`, `plusone`, `linkedin`) VALUES
(1, 1, NOW(), NOW(), '1', '1', '1', '1', '1', '1', '1', '1'),
(2, 1, NOW(), NOW(), '1', '1', '1', '1', '0', '0', '0', '0');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}node_locale` (`node_id`, `locale`, `name`, `title`, `abstract`, `cloud`) VALUES
(1, 'fr', 'bienvenue', 'Bienvenue', NULL, NULL),
(1, 'en', 'welcome', 'Welcome', NULL, NULL),
(2, 'fr', 'documentation', 'Documentation', 'Manuel de l''utilisateur.', 'documentation'),
(2, 'en', 'documentation', 'Documentation', 'User''s manual.', 'documentation');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}node_content` (`node_id`, `content_id`, `content_type`, `number`) VALUES
(1, 1, 'infile', 1),
(1, 1, 'text', 2),
(1, 2, 'infile', 3),
(2, 2, 'text', 1);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_text` (`content_id`, `locale`, `text`, `eval`) VALUES
(1, 'fr', '<p>Votre site <b>iZend</b> est maintenant opérationnel.</p>\r\n<p class="readmore">Lisez la <a href="/fr/documentation">documentation</a>.</p>\r\n<p>Validé avec\r\n<span class="btn_browser" id="browser_firefox" title="Firefox">Firefox</span>,\r\n<span class="btn_browser" id="browser_chrome" title="Chrome">Chrome</span>,\r\n<span class="btn_browser" id="browser_safari" title="Safari">Safari</span>,\r\n<span class="btn_browser" id="browser_opera" title="Opera">Opera</span>\r\net\r\n<span class="nowrap"><span class="btn_browser" id="browser_ie" title="Internet Explorer">Internet Explorer</span>.</span></p>', '0'),
(1, 'en', '<p>Your <b>iZend</b> site is now operational.</p>\r\n<p class="readmore">Read the <a href="/en/documentation">documentation</a>.</p>\r\n<p>Validated with <span class="btn_browser" id="browser_firefox" title="Firefox">Firefox</span>,\r\n<span class="btn_browser" id="browser_chrome" title="Chrome">Chrome</span>,\r\n<span class="btn_browser" id="browser_safari" title="Safari">Safari</span>,\r\n<span class="btn_browser" id="browser_opera" title="Opera">Opera</span>\r\nand\r\n<span class="nowrap"><span class="btn_browser" id="browser_ie" title="Internet Explorer">Internet Explorer</span>.</span></p>', '0'),
(2, 'fr', '<p class="readmore">Consultez la <a href="http://www.izend.org/fr/documentation">documentation en ligne</a>.</p>', '0'),
(2, 'en', '<p class="readmore">Read the <a href="http://www.izend.org/en/documentation">on-line documentation</a>.</p>', '0');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}content_infile` (`content_id`, `locale`, `path`) VALUES
(1, 'fr', 'views/fr/social.phtml'),
(1, 'en', 'views/en/social.phtml'),
(2, 'fr', 'views/fr/link.phtml'),
(2, 'en', 'views/en/link.phtml');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}tag` (`tag_id`, `locale`, `name`) VALUES
(1, 'fr', 'documentation'),
(2, 'en', 'documentation');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}tag_index` (`tag_id`, `node_id`) VALUES
(1, 2),
(2, 2);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread` (`thread_id`, `user_id`, `thread_type`, `created`, `modified`, `number`, `nosearch`, `nocloud`, `nocomment`, `nomorecomment`, `novote`, `nomorevote`, `ilike`, `tweet`, `plusone`, `linkedin`) VALUES
(1, 1, 'folder', NOW(), NOW(), '1', '0', '0', '0', '0', '0', '0', '1', '1', '1', '1');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_locale` (`thread_id`, `locale`, `name`, `title`) VALUES
(1, 'fr', 'contenu', 'Contenu'),
(1, 'en', 'content', 'Content');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO `${db_prefix}thread_node` (`thread_id`, `node_id`, `number`) VALUES
(1, 1, 1),
(1, 2, 2);
_SEP_;
		$db_conn->exec($sql);
	}
	catch (PDOException $e) {
		throw($e);
	}

	$db_conn=null;

	return true;
}
