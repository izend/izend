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

require_once 'models/thread.inc';

require_once 'config.inc';

$msecs = microtime(true);

$id=1;
$r=thread_id($id);
dump($r);

$id='content';

$r=thread_id($id);
dump($r);

$thread_id=$r;

$lang='en';

$r=thread_list($lang);
dump($r);

$type='folder';

$r=thread_list($lang, $type);
dump($r);

$r=thread_get($lang, $thread_id);
dump($r);

$r=thread_get($lang, $thread_id, false);
dump($r);

$r=thread_node_id($thread_id, false);
dump($r);

$id=1;
$r=thread_node_id($thread_id, $id);
dump($r);

$id='welcome';

$r=thread_node_id($thread_id, $id, $lang);
dump($r);

$user_id=1;
$thread_name='testthread';
$thread_title='Test Thread';
$thread_type='folder';

$r=thread_create($lang, $user_id, $thread_name, $thread_title, $thread_type);
dump($r);

extract($r);	// thread_id thread_number

$thread_title='Test thread';
$thread_abstract='The test thread.';
$thread_cloud='test node';
$thread_image='/files/images/testthread.png';

$r=thread_set($lang, $thread_id, $thread_name, $thread_title, $thread_type, $thread_abstract, $thread_cloud, $thread_image, false, false, false, false, false, false, true, true, true, true, true);
dump($r);

$r=thread_get($lang, $thread_id);
dump($r);

$thread_name='testanotherthread';
$thread_title='Test Another Thread';

$r=thread_create($lang, $user_id, $thread_name, $thread_title, false, 2);
dump($r);

$another_thread_id=$r['thread_id'];

$r=thread_get($lang, $another_thread_id, false);
dump($r);

$r=thread_delete($another_thread_id);
dump($r);

$node_name='testnode';
$node_title='Test node';

$r=thread_create_node($lang, $user_id, $thread_id, $node_name, $node_title);
dump($r);

extract($r);	// node_id node_number

$another_node_name='anothertestnode';
$another_node_title='Another test node';

$r=thread_create_node($lang, $user_id, $thread_id, $another_node_name, $another_node_title, 1);
dump($r);

$another_node_id=$r['node_id'];

$node_name='testnode';
$node_title='Test node';
$node_abstract='The test node.';
$node_cloud='test node';
$node_image='/files/images/testnode.png';

$r=thread_set_node($lang, $thread_id, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_image, false, false, false, false, true, true, true, true, true);
dump($r);

$r=thread_get_node($lang, $thread_id, $node_id);
dump($r);

$r=thread_node_id($thread_id, false);
dump($r);

$r=thread_node_id($thread_id, $node_id);
dump($r);

$r=thread_node_id($thread_id, $node_name, $lang);
dump($r);

$r=thread_node_next($lang, $thread_id, $another_node_id);
dump($r);

$r=thread_node_prev($lang, $thread_id, $node_id);
dump($r);

$r=thread_delete_node($thread_id, $another_node_id);
dump($r);

$r=thread_delete($thread_id);
dump($r);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
