<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

global $base_url, $base_path, $base_root;

global $sitename, $webmaster, $mailer, $blackmaillist, $blacknamelist;

$sitename = 'izend.org';
$webmaster = 'izend@izend.org';
$mailer = 'izend';
$blackmaillist = array('izend@izend.org', 'webmaster@izend.org', 'keymaster@izend.org', 'izend@izend.org', 'webmaster@izend.org', 'keymaster@izend.org');
$blacknamelist = array('izend', 'izend', 'webmaster', 'keymaster');

global $logoascii;

$logoascii='';

global $signature;

$signature='izend.org - http://www.izend.org';

global $log_dir;

$log_dir = ROOT_DIR . DIRECTORY_SEPARATOR . 'log';

global $closing_time, $opening_time;

$closing_time=0;	// mktime(13, 0);
$opening_time=0;	// $closing_time+30*60;

global $system_languages;

$system_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_languages;

$supported_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_threads;

$supported_threads=array('thread', 'folder', 'story', 'book');	// 'thread', 'folder', 'story', 'book'

global $search_distance, $search_closest;

$search_distance=2;		// 0 - exact match, 1 - close, 2 - not so close, etc.
$search_closest=true;	// true - best matches, false - all matches

global $track_db, $track_log;
global $track_visitor, $track_visitor_agent;

$track_db=false;
$track_log=false;
$track_visitor=true;
$track_visitor_agent=true;

global $root_node;

$root_node = false;	// home page node, e.g. 1 or false

global $home_action, $default_action, $default_folder;

$home_action = 'install';		// 'home' or 'install'
$default_action = 'install';	// 'page' or 'install' or false
$default_folder = 1;

