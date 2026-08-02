<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_user_agent($agent) {
	return is_string($agent) and !preg_match('/[\x00-\x1F\x7F]/', $agent);
}
