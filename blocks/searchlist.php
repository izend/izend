<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'inarrayi.php';

function searchlist($lang, $searchtext, $cloud_id, $cloud_name, $cloud_action, $taglist) {
	preg_match_all('/(\S+)/', $searchtext, $r);
	$wordlist = $r[0];

	$linklist=array();
	$cloud_url = url($cloud_action, $lang) . '/'. $cloud_name;
	foreach ($taglist as $tag) {
		extract($tag);	/* page_id, name, title, abstract, cloud, pertinence */
		$link_title=$title;
		$link_description=strip_tags($abstract);
		$link_cloud=array();
		preg_match_all('/(\S+)/', $cloud, $r);
		foreach ($r[0] as $word) {
			$w=htmlentities($word, ENT_COMPAT, 'UTF-8');
			$link_cloud[]=in_arrayi($word, $wordlist) ? "<span class=\"tag\">$w</span>" : $w;
		}
		$link_cloud=implode(' ', $link_cloud);
		$link_url=$cloud_url . '/' . $name;
		$linklist[]=compact('link_title', 'link_url', 'link_description', 'link_cloud');
	}

	$output = view('searchlist', false, compact('linklist'));

	return $output;
}

