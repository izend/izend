<?php

/**
 *
 * @copyright  2010-2016 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(ROOT_DIR . PATH_SEPARATOR . get_include_path());
set_include_path(ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . PATH_SEPARATOR . get_include_path());
set_include_path(ROOT_DIR . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());

require_once 'bootstrap.php';

bootstrap();

require_once 'engine.php';

dispatch($supported_languages);	// see config.inc

