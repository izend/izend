<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function newsletter_get_message($newsletter_id, $page_id, $lang) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnodelocale=db_prefix_table('node_locale');
	$tabnodecontent=db_prefix_table('node_content');
	$tabcontenttext=db_prefix_table('content_text');

	$where="tn.thread_id=$newsletter_id AND tn.node_id=$page_id AND tn.ignored=0";

	$sql="SELECT nl.title AS node_title, ct.text AS content_text_text, ct.eval AS content_text_eval FROM $tabthreadnode tn JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang LEFT JOIN $tabnodecontent nc ON nc.node_id=tn.node_id AND nc.content_type='text' AND nc.ignored=0 LEFT JOIN $tabcontenttext ct ON ct.content_id=nc.content_id AND ct.locale=$sqllang WHERE $where ORDER BY tn.number LIMIT 2";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$msg=array();

	for ($i=0; $i < 2; $i++) {
		$c = isset($r[$i]) ? $r[$i] : false;
		$s = $c ? $c['content_text_text'] : false;
		if ($s) {
			$eval = $c['content_text_eval'] == 1 ? true : false;
			if ($eval) {
				require_once 'seval.php';
				$s = seval($s);
			}
		}
		switch ($i) {
			case 0:
				$msg['newsletter_message_title']=$c['node_title'];
				$msg['newsletter_message_html']=$s;
				break;
			case 1:
				if ($i==1) {
					$s=strip_tags($s);
				}
				$msg['newsletter_message_text']=$s;
				break;
		}
	}

	return $msg;
}

function newsletter_schedule_post($newsletter_id, $page_id, $locale, $scheduled) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="INSERT $tabnewsletterpost (thread_id, node_id, locale, scheduled) VALUES ($newsletter_id, $page_id, $sqllocale, FROM_UNIXTIME($scheduled)) ON DUPLICATE KEY UPDATE thread_id=VALUES(thread_id), node_id=VALUES(node_id), locale=VALUES(locale), scheduled=VALUES(scheduled)";

	$r = db_insert($sql);

	return $r;
}

function newsletter_cancel_post($newsletter_id, $page_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="DELETE FROM $tabnewsletterpost WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale LIMIT 1";

	$r = db_delete($sql);

	return $r;
}

function newsletter_get_post($newsletter_id, $page_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="SELECT UNIX_TIMESTAMP(scheduled) AS newsletter_post_scheduled, UNIX_TIMESTAMP(mailed) AS newsletter_post_mailed FROM $tabnewsletterpost WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function newsletter_count_users($locale=false) {
	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="SELECT COUNT(*) AS count FROM $tabnewsletteruser";

	if ($locale) {
		$sqllocale=db_sql_arg($locale, false);

		$sql .= " WHERE locale=$sqllocale";
	}

	$r = db_query($sql);

	return $r ? $r[0]['count'] : false;
}

function newsletter_add_user($mail, $locale) {
	$sqlmail=db_sql_arg($mail, true, true);
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="INSERT IGNORE $tabnewsletteruser SET mail=$sqlmail, locale=$sqllocale, created=NOW()";

	$r = db_insert($sql);

	return $r;
}

function newsletter_remove_user($mail) {
	$sqlmail=db_sql_arg($mail, true, true);

	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="DELETE FROM $tabnewsletteruser WHERE mail=$sqlmail LIMIT 1";

	$r = db_delete($sql);

	return $r;
}
