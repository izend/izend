<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    18
 * @link       http://www.izend.org
 */

require_once 'ismobile.php';

global $base_url, $base_path, $base_root;

global $request_path, $request_query;

global $sitename, $webmaster, $mailer, $blackmaillist, $blacknamelist;

$sitename = 'izend.org';
$webmaster = 'izend@izend.org';
$mailer = 'iZend';
$blackmaillist = array('izend@izend.org', 'webmaster@izend.org', 'keymaster@izend.org');
$blacknamelist = array('izend', 'webmaster', 'keymaster');

global $blackfilelist;

$blackfilelist = array('.htaccess', 'index.html', 'index.phtml', 'index.php');

global $logoascii;

$logoascii='';

global $signature;

$signature='izend.org - http://www.izend.org';

global $socializing, $facebookid;

$socializing='inline';	// is_mobile() ? 'inline' : 'bar';	// false, 'inline', 'bar'
$facebookid = false;

global $log_dir;

$log_dir = ROOT_DIR . DIRECTORY_SEPARATOR . 'log';

global $closing_time, $opening_time;

$closing_time=0;	// true, mktime(13, 0)
$opening_time=0;	// $closing_time+30*60

global $system_languages;

$system_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_languages;

$supported_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_currencies;

$supported_currencies=array('EUR', 'USD', 'GBP');      // 'EUR', 'USD', 'GBP'

global $supported_roles;

$supported_roles=array('administrator', 'writer', 'reader', 'moderator');

global $supported_threads;

$supported_threads=array('thread', 'folder', 'story', 'book');	// 'thread', 'folder', 'story', 'book'

global $contents_model, $supported_contents;

$contents_model = array(
	'text'		=>	array('text' => array('type' => 'string', 'null' => true), 'eval' => array('type' => 'boolean', 'default' => false)),
	'infile'	=>	array('path' => array('type' => 'string', 'null' => true)),
	'download'	=>	array('name' => array('type' => 'string', 'null' => true), 'path' => array('type' => 'string', 'null' => true)),
	'file'		=>	array('path' => array('type' => 'string', 'null' => true), 'start' => array('type' => 'number', 'default' => 0), 'end' => array('type' => 'number', 'default' => 0), 'format' => array('type' => 'string', 'null' => true), 'lineno' => array('type' => 'boolean', 'default' => true)),
	'longtail'	=>	array('file' => array('type' => 'string', 'null' => true), 'image' => array('type' => 'string', 'null' => true), 'width' => array('type' => 'number', 'default' => 0), 'height' => array('type' => 'number', 'default' => 0), 'icons' => array('type' => 'boolean'), 'skin' => array('type' => 'string', 'null' => true), 'controlbar' => array('type' => 'string', 'default' => 'none'), 'duration' => array('type' => 'number', 'default' => 0), 'autostart' => array('type' => 'boolean'), 'repeat' => array('type' => 'boolean'))
);

$supported_contents=array('text', 'infile', 'download', 'file', 'longtail');	// 'text', 'infile', 'download', 'file', 'longtail'

global $with_toolbar;

$with_toolbar=is_mobile() ? false : true;

global $search_distance, $search_closest;

$search_distance=2;		// 0 - exact match, 1 - close, 2 - not so close, etc.
$search_closest=true;	// true - best matches, false - all matches

global $session_name;
$session_name=false;	// see bootstrap

global $login_lifetime, $login_failed_delay;
$login_lifetime=false;	// false or in seconds
$login_failed_delay=1;	// false or in seconds

global $track_db, $track_log;
global $track_visitor, $track_visitor_agent;

$track_db=false;
$track_log=false;		// true, file name or false
$track_visitor=false;
$track_visitor_agent=false;

global $root_node;

$root_node = false;	// home page node, e.g. 1 or false

global $home_action, $default_action, $default_folder;

$home_action = 'install';		// 'home' or 'install'
$default_action = 'install';	// 'page' or 'install' or false
$default_folder = 1;			// can be an array

