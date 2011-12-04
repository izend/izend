

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

global $db_url, $db_prefix, $db_debug;

<?php if ($db_user): ?>
$db_url = 'mysql://<?php echo $db_user; ?><?php if ($db_password) { echo ':', $db_password; } ?>@<?php echo $db_host ? $db_host : 'localhost'; ?>/<?php echo $db_name; ?>';
<?php else: ?>
$db_url = 'mysql://username:password@localhost/databasename';
<?php endif; ?>
$db_prefix = <?php echo $db_prefix ? "'$db_prefix'" : 'false'; ?>;
$db_debug = false;

