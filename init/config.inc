

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    10
 * @link       http://www.izend.org
 */
<?php
if (!$sitename) {
	$sitename = 'localhost';
}
if (!$webmaster) {
	$webmaster = 'webmaster@localhost.net';
}
?>

global $base_url, $base_path, $base_root;

global $request_path, $request_query;

global $sitename, $webmaster, $mailer, $blackmaillist, $blacknamelist;

$sitename = '<?php echo $sitename; ?>';
$webmaster = '<?php echo $webmaster; ?>';
$mailer = 'iZend';
$blackmaillist=array($webmaster);
$blacknamelist=array('<?php echo $username; ?>');

global $logoascii;

$logoascii='';

global $signature;

$signature='<?php echo $sitename; ?> - http://www.<?php echo $sitename; ?>';

global $socializing, $facebookid;

$socializing='inline';	// false, 'bar', 'inline'
$facebookid = false;

global $log_dir;

$log_dir = ROOT_DIR . DIRECTORY_SEPARATOR . 'log';

global $closing_time, $opening_time;

$closing_time=0;	// mktime(13, 0);
$opening_time=0;	// $closing_time+30*60;

global $system_languages;

$system_languages=array('en', 'fr');	// 'en', 'fr'

global $supported_languages;

$supported_languages=array(<?php for ($i=0; $i<count($languages); $i++) { echo $i == 0 ? "'$languages[$i]'" : ", '$languages[$i]'"; } ?>);	// 'en', 'fr'

global $supported_threads;

$supported_threads=array('thread', 'folder', 'story', 'book');	// 'thread', 'folder', 'story', 'book'

global $contents_model, $supported_contents;

$contents_model = array(
	'text'		=>	array('text' => array('type' => 'string', 'null' => true), 'eval' => array('type' => 'boolean', 'default' => false)),
	'infile'	=>	array('path' => array('type' => 'string', 'null' => true)),
	'download'	=>	array('name' => array('type' => 'string', 'null' => true), 'path' => array('type' => 'string', 'null' => true)),
	'file'		=>	array('path' => array('type' => 'string', 'null' => true), 'start' => array('type' => 'number', 'default' => 0), 'end' => array('type' => 'number', 'default' => 0), 'format' => array('type' => 'string', 'null' => true), 'lineno' => array('type' => 'boolean', 'default' => true)),
	'longtail'	=>	array('file' => array('type' => 'string', 'null' => true), 'image' => array('type' => 'string', 'null' => true), 'width' => array('type' => 'number', 'default' => 0), 'height' => array('type' => 'number', 'default' => 0), 'icons' => array('type' => 'boolean'), 'skin' => array('type' => 'string', 'null' => true), 'duration' => array('type' => 'number', 'default' => 0), 'autostart' => array('type' => 'boolean'), 'repeat' => array('type' => 'boolean'))
);

$supported_contents=array('text', 'infile', 'download', 'file', 'longtail');	// 'text', 'infile', 'download', 'file', 'longtail'

global $with_toolbar;

$with_toolbar=false;

global $search_distance, $search_closest;

$search_distance=2;		// 0 - exact match, 1 - close, 2 - not so close, etc.
$search_closest=true;	// true - best matches, false - all matches

global $session_name, $login_lifetime;
$session_name=false;	// see bootstrap
$login_lifetime=false;	// false or in seconds

global $track_db, $track_log;
global $track_visitor, $track_visitor_agent;

$track_db=false;
$track_log=false;
$track_visitor=false;
$track_visitor_agent=false;

global $root_node;

$root_node = <?php echo $root_node ? $root_node : 'false'; ?>;	// home page node, e.g. 1 or false

global $home_action, $default_action, $default_folder;

$home_action = <?php echo "'$home_action'"; ?>;		// 'home' or 'homepage'
$default_action = <?php echo $default_action ? "'$default_action'" : 'false'; ?>;	// 'page' or 'anypage' or false
$default_folder = 1;			// can be an array

