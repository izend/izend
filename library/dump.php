<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function dump($var, $label=null, $echo=true) {
	$label = ($label===null) ? '' : rtrim($label) . '=';

	ob_start();
	var_dump($var);
	$output = ob_get_clean();

	$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
	if (PHP_SAPI == 'cli') {
		$output = PHP_EOL . $label . $output . PHP_EOL;
	}
	else {
		$output = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

		$output = '<pre class="dump"><code>' . $label . $output . '</code></pre>' . PHP_EOL;
	}

	if ($echo) {
		echo $output;
	}

	return $output;
}

