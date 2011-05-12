<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function book($lang, $arglist=false) {
	$book=$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$book=$arglist[0];
		}
		if (isset($arglist[1])) {
			$page=$arglist[1];
		}
	}

	if (!$book) {
		return run('error/notfound', $lang);
	}

	if (!$page) {
		require_once 'actions/booksummary.php';

		return booksummary($lang, $book);

	}

	require_once 'actions/bookpage.php';

	return bookpage($lang, $book, $page);
}

