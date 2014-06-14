<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'blocks/configure' . $scheme . '.php';

$db_admin_user=$scheme == 'mysql' ? 'root' : 'postgres';
$db_admin_password='root';
$db_host='localhost';
$db_name='test';
$db_user='test';
$db_password='test';

recover_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user);

create_db($db_admin_user, $db_admin_password, $db_host, $db_name, $db_user, $db_password);

$db_prefix='test_';

$site_admin_user='foobar';
$site_admin_password='f00bar';
$site_admin_mail='foobar@izend.org';
$default_language='fr';

$msecs = microtime(true);

init_db($db_host, $db_name, $db_user, $db_password, $db_prefix, $site_admin_user, $site_admin_password, $site_admin_mail, $default_language);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
