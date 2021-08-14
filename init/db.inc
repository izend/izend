

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

global $db_url, $db_prefix, $db_debug;

<?php if ($db_user): ?>
$db_url = '<?php echo $db_type; ?>://<?php echo $db_user; ?><?php if ($db_password) { echo ':', urlencode($db_password); } ?>@<?php echo $db_host ? $db_host : 'localhost'; ?>/<?php echo $db_name; ?>';
$db_prefix = <?php echo $db_prefix ? "'$db_prefix'" : 'false'; ?>;
<?php else: ?>
$db_url = false;
$db_prefix = false;
<?php endif; ?>
$db_debug = false;
