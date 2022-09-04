<?php

/**
 *
 * @copyright  2022 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_uuid($id) {
	return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id);
}
