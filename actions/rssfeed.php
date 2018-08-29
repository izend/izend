<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'filemimetype.php';

function rssfeed($lang) {
	global $rss_thread, $rss_description;

	$feed_content = $rss_description == 'content';

	$itemlist = array();

	if ($rss_thread) {
		$sqllang=db_sql_arg($lang, false);

		$tabthreadnode=db_prefix_table('thread_node');
		$tabnode=db_prefix_table('node');
		$tabnodelocale=db_prefix_table('node_locale');

		$where=(is_array($rss_thread) ? 'tn.thread_id IN (' . implode(',', $rss_thread) . ')' : "tn.thread_id=$rss_thread") . ' AND tn.ignored=FALSE';

		if ($feed_content) {
			$tabnodecontent=db_prefix_table('node_content');
			$tabcontenttext=db_prefix_table('content_text');

			$sql="SELECT nl.name AS node_name, nl.title AS node_title, UNIX_TIMESTAMP(n.created) AS node_created, nl.image AS node_image, ct.text AS content_text FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang LEFT JOIN $tabnodecontent nc ON nc.node_id=n.node_id AND nc.content_type='text' AND nc.ignored=FALSE LEFT JOIN $tabcontenttext ct ON ct.content_id=nc.content_id AND ct.locale=nl.locale WHERE $where ORDER BY tn.number";
		}
		else {
			$sql="SELECT nl.name AS node_name, nl.title AS node_title, UNIX_TIMESTAMP(n.created) AS node_created, nl.image AS node_image, nl.abstract AS node_abstract FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang WHERE $where ORDER BY n.created DESC";
		}

		$r = db_query($sql);

		if ($r) {
			foreach ($r as $node) {
				extract($node);
				$title = $node_title;
				$uri = false;	// "/$lang/$node_name";
				$created = $node_created;
				$description = preg_replace('/(\r\n|\r|\n)+/', ' - ', preg_replace('/[\t ]+/', ' ', strip_tags($feed_content ? $content_text : $node_abstract)));
				$image_uri = false;	// $node_image;
				$image_size = $image_uri ? filesize(ROOT_DIR . $image_uri) : 0;
				$image_type = $image_uri ? file_mime_type(ROOT_DIR . $image_uri) : false;
				$itemlist[] = compact('title', 'uri', 'created', 'description', 'image_uri', 'image_size', 'image_type');
			}
		}
	}

	$description = translate('description', $lang);

	$output = view('rssfeed', false, compact('lang', 'description', 'itemlist'));

	return $output;
}
