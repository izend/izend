<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'geshi.php';

function bbcode($s) {
	static $bbcode = array(
			'#\[br\]#is'							=> '<br />',
//			'#\[(h[4-6])\](.+?)\[/\1\]#is'			=> '<\1>\2</\1>',
			'#\[b\](.+?)\[/b\]#is'					=> '<b>\1</b>',
			'#\[i\](.+?)\[/i\]#is'					=> '<i>\1</i>',
			'#\[u\](.+?)\[/u\]#is'					=> '<u>\1</u>',
			'#\[s\](.+?)\[/s\]#is'					=> '<s>\1</s>',
			'#\[p\](.+?)\[/p\]#is'					=> '<p>\1</p>',
			'#\[pre\](.+?)\[/pre\]#is'				=> '<pre>\1</pre>',
			'#\[quote\](.+?)\[/quote\]#is'			=> '<blockquote>\1</blockquote>',
			'#\[(url)\=(.+?)\](.*?)\[/\1\]#ise'		=> "filter_var('\\2', FILTER_VALIDATE_URL) ? '<a href=\"\\2\" target=\"_blank\">\\3</a>' : '\\0'",
			'#\[(url)](.*?)\[/\1\]#ise'				=> "filter_var('\\2', FILTER_VALIDATE_URL) ? '<a href=\"\\2\" target=\"_blank\">\\2</a>' : '\\0'",
			'#\[(e?mail)\=(.+?)\](.*?)\[/\1\]#ise'	=> "filter_var('\\2', FILTER_VALIDATE_EMAIL) ? '<a href=\"mailto:\\2\">\\3</a>' : '\\0'",
			'#\[(e?mail)\](.*?)\[/\1\]#ise'			=> "filter_var('\\2', FILTER_VALIDATE_EMAIL) ? '<a href=\"mailto:\\2\">\\2</a>' : '\\0'",
			'#\[code\=(.+?)\](.+?)\[/code\]#ise'	=> "bbcode_highlite('\\2', '\\1')",
			'#\[code\](.+?)\[/code\]#ise'			=> "bbcode_highlite('\\1')",
//			'#\[small\](.+?)\[/small\]#is'			=> '<span class="smaller">\1</span>',
//			'#\[big\](.+?)\[/big\]#is'				=> '<span class="larger">\1</span>',
	);

	$s = preg_replace('#\[code([^\]]*?)\](.*?)\[/code\]#ise', "'[code\\1]'.bbcode_protect('\\2').'[/code]'", $s);

	$s = htmlspecialchars($s, ENT_COMPAT, 'UTF-8');

	return preg_replace(array_keys($bbcode), array_values($bbcode), $s);
}

function bbcode_protect($s) {
	return base64_encode(preg_replace('#\\\"#', '"', $s));
}

function bbcode_highlite($s, $language=false) {
	$s = base64_decode($s);

	if (!$language) {
		return '<code>' . htmlspecialchars($s, ENT_COMPAT, 'UTF-8') . '</code>';
	}

	$geshi = new GeSHi($s, $language);
	$geshi->enable_classes(true);
	$geshi->set_header_type(GESHI_HEADER_DIV);
	$geshi->enable_keyword_links(false);
	$geshi->set_tab_width(4);

	$output = $geshi->parse_code();

	if ($geshi->error()) {
		return false;
	}

	head('stylesheet', 'geshi/' . $language, 'screen');

	return '<div class="geshi">' . $output . '</div>';
}

