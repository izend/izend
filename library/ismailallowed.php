<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function is_mail_allowed($mail) {
	global $blackmaillist;

	return !in_array($mail, $blackmaillist);
}

