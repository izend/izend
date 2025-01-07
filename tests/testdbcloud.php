<?php

/**
 *
 * @copyright  2014-2025 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'pdo.php';

$db_url=$scheme . '://test:test@localhost/test';
$db_prefix='test_';
$db_debug=true;

db_connect($db_url);

require_once 'models/cloud.inc';

$msecs = microtime(true);

$rss_thread=1;

$thread_id=1;
$cloud_id=$thread_id;

$r=cloud_delete($cloud_id);
dump($r);

$r=cloud_create($thread_id);
dump($r);

$lang='en';

$r=cloud_get($lang, $cloud_id);
dump($r);

$r=cloud_list_tags($lang, false, false);
dump($r);

$r=cloud_list_tags($lang, $cloud_id, false, false);
dump($r);

$r=cloud_list_tags($lang, $cloud_id, false, true, true);
dump($r);

$node_id=2;

$r=cloud_list_tags($lang, $cloud_id, $node_id);
dump($r);

$r=cloud_list_tags($lang, $cloud_id, $node_id, true, false, false);
dump($r);

$taglist=array('documentation', 'foobar');

$r=cloud_search($lang, $cloud_id, false, $taglist);
dump($r);

$s='documentation';

$r=cloud_match($lang, $cloud_id, $s);
dump($r);

$s='Documentations#2';

$r=cloud_match($lang, $cloud_id, $s);
dump($r);

$term='docu';

$r=cloud_suggest($lang, $cloud_id, $term);
dump($r);

$s='documentation foobar barfoo';

$r=cloud_tag_node($lang, $node_id, $s);
dump($r);

$r=cloud_list_node_tags($lang, $node_id);
dump($r);

$s='documentation';

$r=cloud_tag_node($lang, $node_id, $s);
dump($r);

$r=cloud_list_node_tags($lang, $node_id);
dump($r);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
