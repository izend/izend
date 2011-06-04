<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function searchlist($lang, $searchtext, $cloud_id, $cloud_name, $cloud_action, $rsearch, $taglist) {
	$linklist=array();
	$cloud_url = url($cloud_action, $lang) . '/'. $cloud_name;
	foreach ($rsearch as $r) {
		extract($r);	/* page_id, name, title, abstract, cloud, pertinence */
		$link_title=$title;
		$link_description=strip_tags($abstract);
		$link_cloud=array();
		preg_match_all('/(\S+)/', $cloud, $r);
		foreach ($r[0] as $tag) {
			$w=htmlentities($tag, ENT_COMPAT, 'UTF-8');
			$link_cloud[]=in_array($tag, $taglist) ? "<span class=\"tag\">$w</span>" : $w;
		}
		$link_cloud=implode(' ', $link_cloud);
		$link_url=$cloud_url . '/' . $name;
		$linklist[]=compact('link_title', 'link_url', 'link_description', 'link_cloud');
	}

	$output = view('searchlist', false, compact('linklist'));

	return $output;
}

