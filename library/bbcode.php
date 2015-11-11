<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'geshi.php';

function bbcode($s) {
	static $bbcode = array(
		'#\[br\]#is'					=> '<br />',
//		'#\[(h[4-6])\](.+?)\[/\1\]#is'	=> '<\1>\2</\1>',
		'#\[b\](.+?)\[/b\]#is'			=> '<b>\1</b>',
		'#\[i\](.+?)\[/i\]#is'			=> '<i>\1</i>',
		'#\[u\](.+?)\[/u\]#is'			=> '<u>\1</u>',
		'#\[s\](.+?)\[/s\]#is'			=> '<s>\1</s>',
		'#\[p\](.+?)\[/p\]#is'			=> '<p>\1</p>',
		'#\[pre\](.+?)\[/pre\]#is'		=> '<pre>\1</pre>',
		'#\[quote\](.+?)\[/quote\]#is'	=> '<blockquote>\1</blockquote>',
//		'#\[small\](.+?)\[/small\]#is'	=> '<span class="smaller">\1</span>',
//		'#\[big\](.+?)\[/big\]#is'		=> '<span class="larger">\1</span>',
	);

	$s = preg_replace_callback('#\[code([^\]]*?)\](.*?)\[/code\]#is', function ($m) { return '[code' . $m[1] . ']' . bbcode_protect($m[2]) . '[/code]'; }, $s);

	$s = htmlspecialchars($s, ENT_COMPAT, 'UTF-8');

	$s = preg_replace(array_keys($bbcode), array_values($bbcode), $s);

	$bbcode_cb = array(
		'#\[(url)\=(.+?)\](.*?)\[/\1\]#is'		=> function($m) { return filter_var($m[2], FILTER_VALIDATE_URL) ? '<a href="' . $m[2] . '" target="_blank">' . $m[3] . '</a>' : $m[0]; },
		'#\[(url)](.*?)\[/\1\]#is'				=> function($m) { return filter_var($m[2], FILTER_VALIDATE_URL) ? '<a href="' . $m[2] . '" target="_blank">' . $m[2] . '</a>' : $m[0]; },
		'#\[(e?mail)\=(.+?)\](.*?)\[/\1\]#is'	=> function($m) { return filter_var($m[2], FILTER_VALIDATE_EMAIL) ? '<a href="mailto:' . $m[2] .'">' . $m[3] . '</a>' : $m[0]; },
		'#\[(e?mail)\](.*?)\[/\1\]#is'			=> function($m) { return filter_var($m[2], FILTER_VALIDATE_EMAIL) ? '<a href="mailto:' . $m[2] .'">' . $m[2] . '</a>' : $m[0]; },
		'#\[code\=(.+?)\](.+?)\[/code\]#is'		=> function($m) { return bbcode_highlite($m[2], $m[1]); },
		'#\[code\](.+?)\[/code\]#is'			=> function($m) { return bbcode_highlite($m[1]); },
	);

	foreach ($bbcode_cb as $regexp => $f) {
		$s = preg_replace_callback($regexp, $f, $s);
	}

	return $s;
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
