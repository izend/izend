<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

define('CONTENTS_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'contents');

function content($lang, $content, $vars=false) {
	$file = $lang ? CONTENTS_DIR . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $content. '.phtml' : CONTENTS_DIR . DIRECTORY_SEPARATOR . $content.'.phtml';
	if (!is_file($file)) {
		return false;
	}
	$output = render($file, $vars);

	return $output;
}

