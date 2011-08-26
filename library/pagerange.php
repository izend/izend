<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function page_range($page, $npages, $offset=1) {
	$range=2*$offset+1;

	if ($npages <= $range+$offset) {
		$pagenums=range(1, $npages);
	}
	else if (($page - $offset) <= 2) {
		$pagenums=array_merge(range(1, $range+1), array($npages));
	}
	else if (($page + $offset) >= ($npages-1)) {
		$pagenums=array_merge(array(1), range($npages-$range, $npages));
	}
	else {
		$pagenums=array_merge(array(1), range($page-$offset, $page+$offset), array($npages));
	}

	return $pagenums;
}
