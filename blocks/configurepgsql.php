<?php

/**
 *
 * @copyright  2014-2023 izend.org
 * @version    11
 * @link       http://www.izend.org
 */

function create_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user, $db_password) {
	$dsn = "pgsql:host=$db_host";

	try {
		$db_conn = new PDO($dsn, $db_admin_user, $db_admin_password);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		$sql="CREATE ROLE \"$db_user\" WITH LOGIN PASSWORD '$db_password'";
		$db_conn->exec($sql);

		$sql="CREATE DATABASE \"$db_name\" WITH OWNER \"$db_user\" TEMPLATE template0 ENCODING 'UTF8'";
		$db_conn->exec($sql);
	}
	catch (PDOException $e) {
		throw($e);
	}

	$db_conn=null;

	return true;
}

function recover_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user) {
	$dsn = "pgsql:host=$db_host";

	try {
		$db_conn = new PDO($dsn, $db_admin_user, $db_admin_password);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		$sql="DROP DATABASE \"$db_name\"";
		$db_conn->exec($sql);

		$sql="DROP ROLE \"$db_user\"";
		$db_conn->exec($sql);
	}
	catch (PDOException $e) {
	}

	$db_conn=null;

	return true;
}

function init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language) {
	$dsn = "pgsql:host=$db_host;dbname=$db_name";

	try {
		$db_conn = new PDO($dsn, $db_user, $db_password);
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->exec("SET NAMES 'utf8'");

		$db_conn->exec("CREATE OR REPLACE FUNCTION FROM_UNIXTIME(integer) RETURNS timestamp AS 'SELECT TO_TIMESTAMP($1)::timestamp AS result;' LANGUAGE 'SQL';");
		$db_conn->exec("CREATE OR REPLACE FUNCTION UNIX_TIMESTAMP() RETURNS bigint AS 'SELECT EXTRACT(EPOCH FROM CURRENT_TIMESTAMP(0))::bigint AS result;' LANGUAGE 'SQL';");
		$db_conn->exec("CREATE OR REPLACE FUNCTION UNIX_TIMESTAMP(timestamp with time zone) RETURNS bigint AS 'SELECT EXTRACT(EPOCH FROM $1)::bigint AS result;' LANGUAGE 'SQL';");
		$db_conn->exec("CREATE OR REPLACE FUNCTION UNIX_TIMESTAMP(timestamp without time zone) RETURNS bigint AS 'SELECT EXTRACT(EPOCH FROM $1)::bigint AS result;' LANGUAGE 'SQL';");
		$db_conn->exec("CREATE OR REPLACE FUNCTION INET_ATON(inet) RETURNS bigint AS 'SELECT INETMI($1,''0.0.0.0'');' LANGUAGE 'SQL';");
		$db_conn->exec("CREATE OR REPLACE FUNCTION INET_NTOA(bigint) RETURNS inet AS 'SELECT ''0.0.0.0''::inet+$1;' LANGUAGE 'SQL';");
		$db_conn->exec("CREATE OR REPLACE FUNCTION STRFLAT(text) RETURNS text AS 'SELECT TRANSLATE($1, ''ŠšŽžÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝŸÞàáâãäåæçèéêëìíîïñòóôõöøùúûüýÿþƒ'', ''SsZzAAAAAAACEEEEIIIINOOOOOOUUUUYYBaaaaaaaceeeeiiiinoooooouuuuyybf'');' LANGUAGE 'SQL';");

		$sql= <<<_SEP_
CREATE TYPE "${db_prefix}type_locale" AS ENUM('en','fr');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE FUNCTION ${db_prefix}type_locale(unknown) RETURNS ${db_prefix}type_locale AS 'SELECT $1::text::${db_prefix}type_locale;' LANGUAGE 'SQL';
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE CAST (unknown as ${db_prefix}type_locale) WITH FUNCTION ${db_prefix}type_locale(unknown) AS ASSIGNMENT;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}comment" (
  "comment_id" SERIAL,
  "node_id" integer NOT NULL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "created" timestamp NOT NULL,
  "edited" timestamp NOT NULL,
  "user_id" integer NOT NULL DEFAULT '0',
  "user_mail" varchar(100) DEFAULT NULL,
  "ip_address" bigint NOT NULL,
  "text" text NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY ("comment_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE INDEX "${db_prefix}comment_index_node" ON "${db_prefix}comment" ("node_id", "locale");
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}content_download" (
  "content_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "name" varchar(50) DEFAULT NULL,
  "path" varchar(200) DEFAULT NULL,
  PRIMARY KEY ("content_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}content_file" (
  "content_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "path" varchar(200) DEFAULT NULL,
  "start" integer NOT NULL DEFAULT '0',
  "end" integer NOT NULL DEFAULT '0',
  "format" varchar(20) DEFAULT NULL,
  "lineno" boolean NOT NULL DEFAULT '1',
  PRIMARY KEY ("content_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

			$sql= <<<_SEP_
CREATE TABLE "${db_prefix}content_infile" (
  "content_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "path" varchar(200) DEFAULT NULL,
  PRIMARY KEY ("content_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TYPE "${db_prefix}type_content_longtail_controlbar" AS ENUM('none','bottom','top','over');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE FUNCTION ${db_prefix}type_content_longtail_controlbar(unknown) RETURNS ${db_prefix}type_content_longtail_controlbar AS 'SELECT $1::text::${db_prefix}type_content_longtail_controlbar;' LANGUAGE 'SQL';
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE CAST (unknown as ${db_prefix}type_content_longtail_controlbar) WITH FUNCTION ${db_prefix}type_content_longtail_controlbar(unknown) AS ASSIGNMENT;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}content_longtail" (
  "content_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "file" varchar(200) DEFAULT NULL,
  "image" varchar(200) DEFAULT NULL,
  "width" integer NOT NULL DEFAULT '0',
  "height" integer NOT NULL DEFAULT '0',
  "icons" boolean NOT NULL DEFAULT '0',
  "skin" varchar(200) DEFAULT NULL,
  "controlbar" ${db_prefix}type_content_longtail_controlbar NOT NULL DEFAULT 'none',
  "duration" integer NOT NULL DEFAULT '0',
  "autostart" boolean NOT NULL DEFAULT '0',
  "repeat" boolean NOT NULL DEFAULT '0',
  PRIMARY KEY ("content_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

			$sql= <<<_SEP_
CREATE TABLE "${db_prefix}content_text" (
  "content_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "text" text,
  "eval" boolean NOT NULL DEFAULT '0',
  PRIMARY KEY ("content_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TYPE "${db_prefix}type_content_youtube_theme" AS ENUM('light','dark');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE FUNCTION ${db_prefix}type_content_youtube_theme(unknown) RETURNS ${db_prefix}type_content_youtube_theme AS 'SELECT $1::text::${db_prefix}type_content_youtube_theme;' LANGUAGE 'SQL';
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE CAST (unknown as ${db_prefix}type_content_youtube_theme) WITH FUNCTION ${db_prefix}type_content_youtube_theme(unknown) AS ASSIGNMENT;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}content_youtube" (
  "content_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "id" varchar(20) DEFAULT NULL,
  "width" integer NOT NULL DEFAULT '0',
  "height" integer NOT NULL DEFAULT '0',
  "miniature" varchar(200) DEFAULT NULL,
  "title" varchar(200) DEFAULT NULL,
  "autoplay" boolean NOT NULL DEFAULT '0',
  "controls" boolean NOT NULL DEFAULT '0',
  "fs" boolean NOT NULL DEFAULT '0',
  "theme" ${db_prefix}type_content_youtube_theme NOT NULL DEFAULT 'dark',
  "rel" boolean NOT NULL DEFAULT '0',
  PRIMARY KEY ("content_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}newsletter_post" (
  "thread_id" integer NOT NULL,
  "node_id" integer NOT NULL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "scheduled" timestamp NOT NULL,
  "mailed" timestamp DEFAULT NULL,
  PRIMARY KEY ("thread_id","node_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}newsletter_user" (
  "mail" varchar(100) NOT NULL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "created" timestamp NOT NULL,
  PRIMARY KEY ("mail")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE INDEX "${db_prefix}newsletter_user_locale" ON "${db_prefix}newsletter_user" ("locale");
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}node" (
  "node_id" SERIAL,
  "user_id" integer NOT NULL,
  "created" timestamp NOT NULL,
  "modified" timestamp NOT NULL,
  "visits" boolean NOT NULL DEFAULT '1',
  "nocomment" boolean NOT NULL DEFAULT '0',
  "nomorecomment" boolean NOT NULL DEFAULT '0',
  "novote" boolean NOT NULL DEFAULT '0',
  "nomorevote" boolean NOT NULL DEFAULT '0',
  "ilike" boolean NOT NULL DEFAULT '1',
  "tweet" boolean NOT NULL DEFAULT '1',
  "linkedin" boolean NOT NULL DEFAULT '1',
  "pinit" boolean NOT NULL DEFAULT '0',
  "whatsapp" boolean NOT NULL DEFAULT '0',
  PRIMARY KEY ("node_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}node_locale" (
  "node_id" integer NOT NULL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "name" varchar(100) NOT NULL,
  "title" varchar(100) NULL default NULL,
  "abstract" text,
  "cloud" text,
  "image" varchar(200) DEFAULT NULL,
  "visited" integer NOT NULL DEFAULT '0',
  PRIMARY KEY ("node_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TYPE "${db_prefix}type_content_type" AS ENUM('text','file','download','infile','youtube','longtail');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE FUNCTION ${db_prefix}type_content_type(unknown) RETURNS ${db_prefix}type_content_type AS 'SELECT $1::text::${db_prefix}type_content_type;' LANGUAGE 'SQL';
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE CAST (unknown as ${db_prefix}type_content_type) WITH FUNCTION ${db_prefix}type_content_type(unknown) AS ASSIGNMENT;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}node_content" (
  "node_id" integer NOT NULL,
  "content_id" integer NOT NULL,
  "content_type" ${db_prefix}type_content_type NOT NULL DEFAULT 'text',
  "number" integer NOT NULL,
  "ignored" boolean NOT NULL DEFAULT '0',
  PRIMARY KEY ("node_id","content_id","content_type")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TYPE "${db_prefix}type_thread_thread_type" AS ENUM('thread','folder','story','book','rss','newsletter');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE FUNCTION ${db_prefix}type_thread_thread_type(unknown) RETURNS ${db_prefix}type_thread_thread_type AS 'SELECT $1::text::${db_prefix}type_thread_thread_type;' LANGUAGE 'SQL';
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE CAST (unknown as ${db_prefix}type_thread_thread_type) WITH FUNCTION ${db_prefix}type_thread_thread_type(unknown) AS ASSIGNMENT;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}thread" (
  "thread_id" SERIAL,
  "user_id" integer NOT NULL DEFAULT '1',
  "thread_type" ${db_prefix}type_thread_thread_type NOT NULL DEFAULT 'thread',
  "created" timestamp NOT NULL,
  "modified" timestamp NOT NULL,
  "number" integer NOT NULL,
  "visits" boolean NOT NULL DEFAULT '1',
  "nosearch" boolean NOT NULL DEFAULT '0',
  "nocloud" boolean NOT NULL DEFAULT '0',
  "nocomment" boolean NOT NULL DEFAULT '0',
  "nomorecomment" boolean NOT NULL DEFAULT '0',
  "novote" boolean NOT NULL DEFAULT '0',
  "nomorevote" boolean NOT NULL DEFAULT '0',
  "ilike" boolean NOT NULL DEFAULT '1',
  "tweet" boolean NOT NULL DEFAULT '1',
  "linkedin" boolean NOT NULL DEFAULT '1',
  "pinit" boolean NOT NULL DEFAULT '1',
  "whatsapp" boolean NOT NULL DEFAULT '1',
  PRIMARY KEY ("thread_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}thread_locale" (
  "thread_id" integer NOT NULL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "name" varchar(100) NOT NULL,
  "title" varchar(100) DEFAULT NULL,
  "abstract" text,
  "cloud" text,
  "image" varchar(200) DEFAULT NULL,
  PRIMARY KEY ("thread_id","locale")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}thread_node" (
  "thread_id" integer NOT NULL,
  "node_id" integer NOT NULL,
  "number" integer NOT NULL,
  "ignored" boolean NOT NULL DEFAULT '0',
  PRIMARY KEY ("thread_id","node_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}tag" (
  "tag_id" SERIAL,
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "name" varchar(100) NOT NULL,
  PRIMARY KEY ("tag_id","locale"),
  UNIQUE ("locale","name")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}tag_index" (
  "tag_id" integer NOT NULL,
  "node_id" integer NOT NULL,
  PRIMARY KEY ("tag_id","node_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}user" (
  "user_id" SERIAL,
  "name" varchar(40) DEFAULT NULL,
  "password" char(32) NOT NULL,
  "newpassword" char(32) DEFAULT NULL,
  "seed" char(8) NOT NULL,
  "mail" varchar(100) DEFAULT NULL,
  "website" varchar(100) DEFAULT NULL,
  "timezone" varchar(100) DEFAULT NULL,
  "created" timestamp NOT NULL,
  "modified" timestamp DEFAULT NULL,
  "accessed" timestamp DEFAULT NULL,
  "logged" integer NOT NULL DEFAULT '0',
  "locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "active" boolean NOT NULL DEFAULT '1',
  "banned" boolean NOT NULL DEFAULT '0',
  "confirmed" boolean NOT NULL DEFAULT '1',
  PRIMARY KEY ("user_id"),
  UNIQUE ("name"),
  UNIQUE ("mail")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE IF NOT EXISTS "${db_prefix}user_info" (
  "user_id" integer NOT NULL,
  "lastname" varchar(100) DEFAULT NULL,
  "firstname" varchar(100) DEFAULT NULL,
  "help" boolean NOT NULL DEFAULT '1',
  PRIMARY KEY ("user_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}role" (
  "role_id" SERIAL,
  "name" varchar(40) NOT NULL,
  PRIMARY KEY ("role_id"),
  UNIQUE ("name")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}user_role" (
  "user_id" integer NOT NULL,
  "role_id" integer NOT NULL,
  PRIMARY KEY ("user_id","role_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE INDEX "${db_prefix}user_role_role" ON "${db_prefix}user_role" ("role_id");
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}registry" (
  "name" varchar(100) NOT NULL,
  "value" text NOT NULL,
  PRIMARY KEY ("name")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}track" (
  "track_id" SERIAL,
  "time_stamp" timestamp NOT NULL DEFAULT NOW(),
  "ip_address" bigint NOT NULL,
  "request_uri" varchar(255) NOT NULL,
  "user_agent" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("track_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TYPE "${db_prefix}type_vote_content_type" AS ENUM('node','thread','comment');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE FUNCTION ${db_prefix}type_vote_content_type(unknown) RETURNS ${db_prefix}type_vote_content_type AS 'SELECT $1::text::${db_prefix}type_vote_content_type;' LANGUAGE 'SQL';
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE CAST (unknown as ${db_prefix}type_vote_content_type) WITH FUNCTION ${db_prefix}type_vote_content_type(unknown) AS ASSIGNMENT;
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
CREATE TABLE "${db_prefix}vote" (
  "vote_id" SERIAL,
  "content_id" integer NOT NULL,
  "content_type" ${db_prefix}type_vote_content_type NOT NULL DEFAULT 'node',
  "content_locale" ${db_prefix}type_locale NOT NULL DEFAULT '$default_language',
  "created" timestamp NOT NULL,
  "user_id" integer NOT NULL DEFAULT '0',
  "ip_address" bigint NOT NULL,
  "value" integer NOT NULL DEFAULT '1',
  PRIMARY KEY ("vote_id"),
  UNIQUE ("content_id","content_type","content_locale","ip_address","user_id")
);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}role" ("role_id", "name") VALUES
(1, 'administrator'),
(2, 'writer'),
(3, 'reader'),
(4, 'moderator'),
(5, 'member');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}role_role_id_seq', (SELECT MAX("role_id") FROM "${db_prefix}role"));
_SEP_;
		$db_conn->exec($sql);

		$seed=substr(md5(uniqid()), 1, 8);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}user" ("user_id", "name", "password", "seed", "mail", "created", "locale", "active", "banned", "confirmed") VALUES
(1, '$site_admin_user', MD5(CONCAT('$seed', '$site_admin_password')), '$seed', '$site_admin_mail', NOW(), '$default_language', '1', '0', '1');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}user_user_id_seq', (SELECT MAX("user_id") FROM "${db_prefix}user"));
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}user_role" ("user_id", "role_id") VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}node" ("node_id", "user_id", "created", "modified", "visits", "nocomment", "nomorecomment", "novote", "nomorevote", "ilike", "tweet", "linkedin", "pinit", "whatsapp") VALUES
(1, 1, NOW(), NOW(), '0', '1', '1', '1', '1', '1', '1', '1', '0', '0'),
(2, 1, NOW(), NOW(), '1', '1', '1', '1', '1', '0', '0', '0', '0', '0');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}node_node_id_seq', (SELECT MAX("node_id") FROM "${db_prefix}node"));
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}node_locale" ("node_id", "locale", "name", "title", "abstract", "cloud") VALUES
(1, 'fr', 'bienvenue', 'Bienvenue', NULL, NULL),
(1, 'en', 'welcome', 'Welcome', NULL, NULL),
(2, 'fr', 'documentation', 'Documentation', 'Manuel de l''utilisateur.', 'documentation'),
(2, 'en', 'documentation', 'Documentation', 'User''s manual.', 'documentation');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}node_content" ("node_id", "content_id", "content_type", "number") VALUES
(1, 1, 'infile', 1),
(1, 1, 'text', 2),
(1, 2, 'infile', 3),
(2, 2, 'text', 1);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}content_text" ("content_id", "locale", "text", "eval") VALUES
(1, 'fr', '<p>Votre site <b>iZend</b> est maintenant opérationnel.</p>\r\n<p class="readmore">Lisez la <a href="/fr/documentation">documentation</a>.</p>\r\n<p>Validé avec\r\n<span class="btn_browser" id="browser_firefox" title="Firefox">Firefox</span>,\r\n<span class="btn_browser" id="browser_chrome" title="Chrome">Chrome</span>,\r\n<span class="btn_browser" id="browser_safari" title="Safari">Safari</span>,\r\n<span class="btn_browser" id="browser_opera" title="Opera">Opera</span>\r\net\r\n<span class="nowrap"><span class="btn_browser" id="browser_edge" title="Edge">Edge</span>.</span></p>', '0'),
(1, 'en', '<p>Your <b>iZend</b> site is now operational.</p>\r\n<p class="readmore">Read the <a href="/en/documentation">documentation</a>.</p>\r\n<p>Validated with <span class="btn_browser" id="browser_firefox" title="Firefox">Firefox</span>,\r\n<span class="btn_browser" id="browser_chrome" title="Chrome">Chrome</span>,\r\n<span class="btn_browser" id="browser_safari" title="Safari">Safari</span>,\r\n<span class="btn_browser" id="browser_opera" title="Opera">Opera</span>\r\nand\r\n<span class="nowrap"><span class="btn_browser" id="browser_edge" title="Edge">Edge</span>.</span></p>', '0'),
(2, 'fr', '<p class="readmore">Consultez la <a href="http://www.izend.org">documentation en ligne</a>.</p>', '0'),
(2, 'en', '<p class="readmore">Read the <a href="http://www.izend.org">on-line documentation</a>.</p>', '0');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}content_text_content_id_seq', (SELECT MAX("content_id") FROM "${db_prefix}content_text"));
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}content_infile" ("content_id", "locale", "path") VALUES
(1, 'fr', 'views/fr/social.phtml'),
(1, 'en', 'views/en/social.phtml'),
(2, 'fr', 'views/fr/link.phtml'),
(2, 'en', 'views/en/link.phtml');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}content_infile_content_id_seq', (SELECT MAX("content_id") FROM "${db_prefix}content_infile"));
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}tag" ("tag_id", "locale", "name") VALUES
(1, 'fr', 'documentation'),
(2, 'en', 'documentation');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}tag_tag_id_seq', (SELECT MAX("tag_id") FROM "${db_prefix}tag"));
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}tag_index" ("tag_id", "node_id") VALUES
(1, 2),
(2, 2);
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}thread" ("thread_id", "user_id", "thread_type", "created", "modified", "number", "visits", "nosearch", "nocloud", "nocomment", "nomorecomment", "novote", "nomorevote", "ilike", "tweet", "linkedin", "pinit", "whatsapp") VALUES
(1, 1, 'folder', NOW(), NOW(), '1', '1', '0', '0', '0', '0', '0', '0', '1', '1', '1', '0', '0');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
SELECT setval('${db_prefix}thread_thread_id_seq', (SELECT MAX("thread_id") FROM "${db_prefix}thread"));
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}thread_locale" ("thread_id", "locale", "name", "title") VALUES
(1, 'fr', 'contenu', 'Contenu'),
(1, 'en', 'content', 'Content');
_SEP_;
		$db_conn->exec($sql);

		$sql= <<<_SEP_
INSERT INTO "${db_prefix}thread_node" ("thread_id", "node_id", "number") VALUES
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
