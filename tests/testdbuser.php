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

require_once 'models/user.inc';

$msecs = microtime(true);

$name='barfoo';
$password='barf00';
$mail='';
$locale='fr';
$website=false;

$r=user_create_avatar($name);
dump($r);

$r=user_delete_avatar($name);
dump($r);

$r=user_create($name, $password, $mail, $locale, $website);
dump($r);

$user_id=user_find($name);
dump($user_id);

if (!$user_id) {
	exit;
}

$r=user_id($user_id);
dump($r);

$r=user_get($user_id);
dump($r);

$mail='barfoo@izend.org';
$website='www.izend.org';

$r=user_set($user_id, $name, $mail, $website, $locale);
dump($r);

$r=user_get($user_id);
dump($r);

$lastname='iZend';
$firstname='BarFoo';

$r = user_set_info($user_id, $lastname, $firstname);
dump($r);

$firstname='Bar-Foo';

$r = user_set_info($user_id, $lastname, $firstname);
dump($r);

$r=user_get_info($user_id);
dump($r);

$r=user_set_status($user_id, true, false);
dump($r);

$r=user_get_role($user_id);
dump($r);

$role='writer';

$r=user_set_role($user_id, $role);
dump($r);

$r=user_get_role($user_id);
dump($r);

$role=array('writer', 'moderator');

$r=user_set_role($user_id, $role);
dump($r);

$r=user_get_role($user_id);
dump($r);

$login=$name;

$r=user_login($login, $password);
dump($r);

$newpassword='f00bar';

$r=user_set_newpassword($user_id, $newpassword);
dump($r);

$login=$mail;

$r=user_login($login, $newpassword);
dump($r);

$r=user_check_name($name);
dump($r);

$r=user_check_name($name, $user_id);
dump($r);

$r=user_check_mail($mail);
dump($r);

$r=user_check_mail($mail, $user_id);
dump($r);

$r=user_search('foo bar');
dump($r);
$r=user_search('foo', 1);
dump($r);
$r=user_search(false, 1, 2);
dump($r);

$r=user_delete($user_id);
dump($r);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
