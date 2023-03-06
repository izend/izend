<?php

/**
 *
 * @copyright  2010-2023 izend.org
 * @version    63
 * @link       http://www.izend.org
 */

global $base_url, $base_path, $base_root;

global $request_path, $request_query;

global $sitename, $webmaster, $mailer, $blackmaillist, $blacknamelist;

$sitename = 'izend.org';
$webmaster = 'izend@izend.org';
$mailer = 'iZend';
$blackmaillist = array('izend@izend.org', 'webmaster@izend.org', 'keymaster@izend.org');
$blacknamelist = array('izend', 'webmaster', 'keymaster');

global $sitekey;

$sitekey=false;	// 32 random bytes - see openssl_random_pseudo_bytes

global $siteshot;

$siteshot=false;	// false or pathname

global $blackfilelist;

$blackfilelist = array('.htaccess', 'index.html', 'index.phtml', 'index.php');

global $logoascii;

$logoascii='';

global $signature;

$signature='izend.org - http://www.izend.org';

global $socializing, $socializingmode;

$socializing='inline';	// false, 'inline', 'bar', 'either', 'both'
$socializingmode='lite';	// 'standard', 'lite', 'awesome'

global $cookieconsent, $cookieconsentauto;

$cookieconsent=false;
$cookieconsentauto=true;

global $facebookjssdk, $facebookapiversion, $facebookid, $facebooksecret;

$facebookjssdk=false;	// false

$facebookapiversion='v9.0';

$facebookid=false;
$facebooksecret=false;

global $pinterestassets;

$pinterestassets=false;	// false

global $googleapiid, $googleapisid;

$googleapiid=false;		// client key
$googleapisid=false;	// server key

global $googleanalyticsid;

$googleanalyticsid=false;

global $googleanalyticspropertyid;

$googleanalyticspropertyid=false;

global $googleanalyticsaccount, $googleanalyticskeyfile;

$googleanalyticsaccount = false;
$googleanalyticskeyfile = false;	// ROOT_DIR . DIRECTORY_SEPARATOR . 'google-api-*.p12';

global $googlecredentials;

$googlecredentials=false;			// ROOT_DIR . DIRECTORY_SEPARATOR . 'google-credentials-*.json';

global $googleanalyticspageview;

$googleanalyticspageview=false;

global $linkedinapiid;

$linkedinapiid=false;

global $author;

$author=false;

global $log_dir;

$log_dir = ROOT_DIR . DIRECTORY_SEPARATOR . 'log';

global $closing_time, $opening_time;

$closing_time=false;	// true, mktime(13, 0)
$opening_time=false;	// $closing_time+30*60

global $system_languages;

$system_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_languages;

$supported_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_currencies;

$supported_currencies=array('EUR', 'USD', 'GBP');	// 'EUR', 'USD', 'GBP'

global $supported_roles;

$supported_roles=array('administrator', 'writer', 'reader', 'moderator');

global $supported_threads;

$supported_threads=array('thread', 'folder', 'story', 'book', 'rss', 'newsletter');	// 'thread', 'folder', 'story', 'book', 'rss', 'newsletter'

global $limited_languages;

$limited_languages=false;

global $contents_model, $supported_contents, $limited_contents;

$contents_model = array(
	'text'		=>	array('text' => array('type' => 'string', 'null' => true), 'eval' => array('type' => 'boolean', 'default' => false)),
	'infile'	=>	array('path' => array('type' => 'string', 'null' => true)),
	'download'	=>	array('name' => array('type' => 'string', 'null' => true), 'path' => array('type' => 'string', 'null' => true)),
	'file'		=>	array('path' => array('type' => 'string', 'null' => true), 'start' => array('type' => 'number', 'default' => 0), 'end' => array('type' => 'number', 'default' => 0), 'format' => array('type' => 'string', 'null' => true), 'lineno' => array('type' => 'boolean', 'default' => true)),
	'youtube'	=>	array('id' => array('type' => 'string', 'null' => true), 'width' => array('type' => 'number', 'default' => 0), 'height' => array('type' => 'number', 'default' => 0), 'miniature' => array('type' => 'string', 'null' => true), 'title' => array('type' => 'string', 'null' => true), 'autoplay' => array('type' => 'boolean'), 'controls' => array('type' => 'boolean'), 'fs' => array('type' => 'boolean'), 'rel' => array('type' => 'boolean'), 'theme' => array('type' => 'enum', 'default' => 'dark')),
	'longtail'	=>	array('file' => array('type' => 'string', 'null' => true), 'image' => array('type' => 'string', 'null' => true), 'width' => array('type' => 'number', 'default' => 0), 'height' => array('type' => 'number', 'default' => 0), 'icons' => array('type' => 'boolean'), 'skin' => array('type' => 'string', 'null' => true), 'controlbar' => array('type' => 'enum', 'default' => 'none'), 'duration' => array('type' => 'number', 'default' => 0), 'autostart' => array('type' => 'boolean'), 'repeat' => array('type' => 'boolean')),
);

$supported_contents=array('text', 'infile', 'download', 'file', 'youtube', 'longtail');	// 'text', 'infile', 'download', 'file', 'youtube', 'longtail'

$limited_contents=array('rss' => array('text'), 'newsletter' => array('text'));

global $with_toolbar;

require_once 'ismobile.php';

$with_toolbar=is_mobile() ? false : true;

global $search_default, $search_all, $search_distance, $search_closest, $search_pertinence, $search_cloud;

$search_default=false;	// false or cloud_id
$search_all=true;		// true, false, 'cloud'
$search_distance=2;		// 0 - exact match, 1 - close, 2 - not so close, etc.
$search_closest=true;	// true - best matches, false - all matches
$search_pertinence=0;	// 0 - at least one tag, 1 - all tags
$search_cloud=30;		// just a few or not too many keywords

global $read_access;

$read_access=false;		// false or array('role' => thread_id, ...) - thread_id can be an array

global $session_name;

$session_name=false;	// see bootstrap

global $login_verified;

$login_verified=false;	// false or array of roles

global $login_lifetime, $login_failed_delay;

$login_lifetime=false;	// false or in seconds
$login_failed_delay=1;	// false or in seconds

global $track_db, $track_log;
global $track_visitor, $track_visitor_agent;
global $track_agent_blacklist;

$track_db=false;
$track_log=false;		// true, file name or false
$track_visitor=false;
$track_visitor_agent=false;
$track_agent_blacklist=false;	// false or array of agent signatures

global $root_node;

$root_node = false;	// home page node, e.g. 1 or false

global $home_action, $default_action, $default_folder;

$home_action = 'install';		// 'home' or 'install'
$default_action = 'install';	// 'page' or 'install' or false
$default_folder = 1;			// can be an array

global $content_folder;

$content_folder = false;		// can be an array

global $rss_thread, $rss_description;

$rss_thread = false;			// can be an array
$rss_description = 'abstract';	// 'content or 'abstract'

global $newsletter_thread;

$newsletter_thread = false;		// see cron.php

global $theme;

$theme = false;
