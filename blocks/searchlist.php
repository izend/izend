<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function searchlist($lang, $rsearch, $taglist) {
	global $default_folder;
	global $base_path;

	$linklist=array();
	foreach ($rsearch as $r) {
		extract($r);	/* thread_id, thread_name, thread_title, thread_type, node_name, node_title, node_abstract, node_cloud, pertinence */
		$link_title=$node_title;
		$link_description=$node_abstract;
		$link_cloud=array();
		preg_match_all('/(\S+)/', $node_cloud, $r);
		foreach ($r[0] as $tag) {
			$w=htmlspecialchars($tag, ENT_COMPAT, 'UTF-8');
			$link_cloud[]=in_array($tag, $taglist) ? "<span class=\"tag\">$w</span>" : $w;
		}
		$link_cloud=implode(' ', $link_cloud);
		if ((is_array($default_folder) and in_array($thread_id, $default_folder)) or $thread_id == $default_folder) {
			$thread_url=$base_path . '/' . $lang;
		}
		else {
			$thread_url=url($thread_type, $lang) . '/'. $thread_name;
		}
		$link_url=$thread_url . '/' . $node_name;
		if (!isset($linklist[$thread_id])) {
			$content=array(compact('link_title', 'link_url', 'link_description', 'link_cloud'));
			$linklist[$thread_id]=compact('thread_title', 'content');
		}
		else {
			$linklist[$thread_id]['content'][]=compact('link_title', 'link_url', 'link_description', 'link_cloud');
		}
	}

	$output = view('searchlist', false, compact('linklist'));

	return $output;
}

