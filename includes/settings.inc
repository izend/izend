<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

ini_set('arg_separator.output',		'&amp;');
ini_set('magic_quotes_gpc',			0);
ini_set('magic_quotes_runtime',		0);
ini_set('magic_quotes_sybase',		0);
ini_set('register_globals',			0);
ini_set('session.cache_expire',		180);
ini_set('session.cache_limiter',	'none');
ini_set('session.cookie_httponly',	1);
ini_set('session.cookie_lifetime',	0);
ini_set('session.cookie_samesite',	'None');	// 'None; Secure'
ini_set('session.gc_maxlifetime',	1440);
ini_set('session.name',				'');
ini_set('session.save_handler',		'files');
ini_set('session.use_cookies',		1);
ini_set('session.use_only_cookies',	1);
ini_set('session.use_trans_sid',	0);
ini_set('session.use_strict_mode',	1);
ini_set('url_rewriter.tags',		'');
ini_set('mail.add_x_header',		0);

ini_set('error_reporting',			E_ALL);
ini_set('display_errors',			1);
ini_set('log_errors',				0);
