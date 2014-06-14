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

require_once 'models/newsletter.inc';

$msecs = microtime(true);

$mail='foobar@izend.org';
$locale='fr';

$r=newsletter_create_user($mail, $locale);
dump($r);

$locale='en';

$r=newsletter_create_user($mail, $locale);
dump($r);

$r=newsletter_get_user($mail);
dump($r);

$r=newsletter_count_users('en');
dump($r);

$r=newsletter_count_users();
dump($r);

$r=newsletter_mailinglist('fr');
dump($r);

//$r=newsletter_schedule_post($newsletter_id, $page_id, $locale, $scheduled);
//dump($r);

$r=newsletter_post();
dump($r);

$r=newsletter_delete_user($mail);
dump($r);

echo sprintf('%.4f', microtime(true) - $msecs), PHP_EOL;
