<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

require_once 'dump.php';

require_once 'bootstrap.php';

bootstrap();

require_once 'engine.php';

dispatch($supported_languages);	// see config.inc
