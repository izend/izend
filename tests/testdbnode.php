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

require_once 'models/node.inc';

require_once 'config.inc';

$msecs = microtime(true);

$r=node_id(1);
dump($r);

$lang='en';
$user_id=1;
$node_name='testnode';
$node_title='Test Node';

$r=node_create($lang, $user_id, $node_name, $node_title);
dump($r);

extract($r);	// node_id;

$r=node_get('en', $node_id);
dump($r);

$r=node_get('fr', $node_id, false);
dump($r);

$lang='en';
$node_name='testnode';
$node_title='Test node';
$node_abstract='The test node.';
$node_cloud='test node';
$node_image='/files/images/testnode.png';

$r=node_set('en', $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_image, false, false, false, false, true, true, true, true, true);
dump($r);

$r=node_get($lang, $node_id);
dump($r);

$content_type='text';

$r=node_create_content($lang, $node_id, $content_type);
dump($r);

$r['content_type']=$content_type;
$r['content_ignored']=false;
$content_text=$r;

$content_type='download';

$r=node_create_content($lang, $node_id, $content_type, 1);
dump($r);

$r['content_type']=$content_type;
$r['content_ignored']=false;
$content_download=$r;

$r=node_get_contents($lang, $node_id);
dump($r);

$content_type='file';

$r=node_create_content($lang, $node_id, $content_type, 2);
dump($r);

$r['content_type']=$content_type;
$r['content_ignored']=false;
$content_file=$r;

$r=node_get_contents($lang, $node_id);
dump($r);

$content_type='infile';

$r=node_create_content($lang, $node_id, $content_type);
dump($r);

$r['content_type']=$content_type;
$r['content_ignored']=false;
$content_infile=$r;

$r=node_get_contents($lang, $node_id);
dump($r);

$content_text['content_ignored']=false;
$content_text['content_text_text']='<p>Some \'text\' which is "very" interesting! ;)</p>';
$content_text['content_text_eval']=false;

$lang='en';

$r=node_set_contents($lang, $node_id, array($content_download, $content_file, $content_text, $content_infile));
dump($r);

$content_text['content_ignored']=true;
$content_text['content_text_text']='<p>Un peu de \'texte\' qui est "très" intéressant ! ;)</p>';
$content_text['content_text_eval']=true;

$lang='fr';

$r=node_set_contents($lang, $node_id, array($content_download, $content_file, $content_text, $content_infile));
dump($r);

$r=node_get_contents($lang, $node_id);
dump($r);

$content_id=$content_file['content_id'];
$content_type=$content_file['content_type'];
$r=node_delete_content($node_id, $content_id, $content_type);
dump($r);

$r=node_get_contents($lang, $node_id);
dump($r);

$text='Rien à dire de spécial LOL !';
$locale='fr';
$r=node_add_comment($node_id, 1, '192.168.1.2', $text, $locale);
dump($r);

$comment_id=$r;

$r=node_get_comment($node_id, $comment_id, $locale);
dump($r);

$text='Toujours rien à dire d\'intéressant LOL LOL !!!';
$r=node_set_comment($node_id, $comment_id, $text, $locale);

$r=node_get_comment($node_id, $comment_id, $locale);
dump($r);

$r=node_get_all_comments($node_id, $locale);
dump($r);

$r=node_delete_comment($node_id, $comment_id);
dump($r);

$r=node_delete($node_id);
dump($r);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
