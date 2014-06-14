<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'pdo.php';

$db_url=$scheme . '://test:test@localhost/test';
$db_prefix='test_';
$db_debug=true;

db_connect($db_url);

$msecs = microtime(true);

$rss_thread=1;

$lang='fr';

$sqllang=db_sql_arg($lang, false);

$tabthreadnode=db_prefix_table('thread_node');
$tabnode=db_prefix_table('node');
$tabnodelocale=db_prefix_table('node_locale');
$tabnodecontent=db_prefix_table('node_content');
$tabcontenttext=db_prefix_table('content_text');

$where="tn.thread_id=$rss_thread AND tn.ignored=FALSE";

$sql="SELECT nl.name AS node_name, nl.title AS node_title, UNIX_TIMESTAMP(n.created) AS node_created, ct.text AS content_text FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang LEFT JOIN $tabnodecontent nc ON nc.node_id=n.node_id AND nc.content_type='text' AND nc.ignored=FALSE LEFT JOIN $tabcontenttext ct ON ct.content_id=nc.content_id AND ct.locale=nl.locale WHERE $where ORDER BY tn.number";

$r = db_query($sql);

dump($r);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
