

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

global $db_url, $db_prefix, $db_debug;

<?php if ($db_user): ?>
$db_url = '<?php echo $db_type; ?>://<?php echo $db_user; ?><?php if ($db_password) { echo ':', $db_password; } ?>@<?php echo $db_host ? $db_host : 'localhost'; ?>/<?php echo $db_name; ?>';
<?php else: ?>
$db_url = '<?php echo $db_type; ?>://username:password@localhost/databasename';
<?php endif; ?>
$db_prefix = <?php echo $db_prefix ? "'$db_prefix'" : 'false'; ?>;
$db_debug = false;

