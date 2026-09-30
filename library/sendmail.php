<?php

/**
 *
 * @copyright  2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function sendmail($to, $subject, $body, $headers, $sender) {
	// add the -f option so the SMTP MAIL FROM address can be used by SPF authentication
	if (preg_match('/<([^>]+)>/', $sender, $m)) {
		$sender = $m[1];
	}

	return @mail($to, $subject, $body, $headers, '-f' . $sender);
}
