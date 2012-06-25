<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function rssfeed($lang) {
	global $rss_thread;

	$itemlist = array();

	if ($rss_thread) {
		$sqllang=db_sql_arg($lang, false);

		$tabthreadnode=db_prefix_table('thread_node');
		$tabnode=db_prefix_table('node');
		$tabnodelocale=db_prefix_table('node_locale');
		$tabnodecontent=db_prefix_table('node_content');
		$tabcontenttext=db_prefix_table('content_text');

		$where="tn.thread_id=$rss_thread AND tn.ignored=0";

		$sql="SELECT nl.name AS node_name, nl.title AS node_title, UNIX_TIMESTAMP(n.created) AS node_created, ct.text AS content_text FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang LEFT JOIN $tabnodecontent nc ON nc.node_id=n.node_id AND nc.content_type='text' AND nc.ignored=0 LEFT JOIN $tabcontenttext ct ON ct.content_id=nc.content_id AND ct.locale=nl.locale WHERE $where ORDER BY tn.number";

		$r = db_query($sql);

		if ($r) {
			foreach ($r as $node) {
				extract($node);
				$title = $node_title;
				$uri = $lang . '/' . $node_name;
				$created = $node_created;
				$description = strip_tags($content_text);
				$itemlist[] = compact('title', 'uri', 'created', 'description');
			}
		}
	}

	$description = translate('description', $lang);

	$output = view('rssfeed', false, compact('lang', 'description', 'itemlist'));

	return $output;
}
