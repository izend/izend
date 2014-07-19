<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    8
 * @link       http://www.izend.org
 */

function searchlist($lang, $rsearch, $taglist) {
	global $default_folder, $newsletter_thread, $rss_thread;
	global $base_path;

	$linklist=array();
	foreach ($rsearch as $r) {
		extract($r);	/* thread_id, thread_name, thread_title, thread_type, node_name, node_title, node_abstract, node_cloud, pertinence */
		$link_title=$node_title;
		$link_description=$node_abstract;
		$link_cloud=array();
		$wordlist = preg_split('/\s+/', $node_cloud, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($wordlist as $w) {
			$tag=htmlspecialchars($w, ENT_COMPAT, 'UTF-8');
			$link_cloud[]=in_array($w, $taglist) ? "<span class=\"tag\">$tag</span>" : $tag;
		}
		$link_cloud=implode(' ', $link_cloud);

		if ($thread_type == 'folder' and ((is_array($default_folder) and in_array($thread_id, $default_folder)) or $thread_id == $default_folder)) {
			$thread_url=$base_path . '/' . $lang;
		}
		else if ($thread_type == 'newsletter' and $thread_id == $newsletter_thread) {
			$thread_url=url('newsletter', $lang);
		}
		else if ($thread_type == 'rss' and $thread_id == $rss_thread) {
			$thread_url=url('rssfeed', $lang);
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

