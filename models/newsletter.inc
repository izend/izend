<?php

/**
 *
 * @copyright  2012-2014 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

function newsletter_post() {
	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="SELECT thread_id AS newsletter_id, node_id AS page_id, locale FROM $tabnewsletterpost WHERE mailed IS NULL AND NOW() >= scheduled";

	$r = db_query($sql);

	if (!$r) {
		return true;
	}

	$newslist=array();

	foreach ($r as $post) {
		extract($post);	// newsletter_id, page_id, locale

		$r = newsletter_get_message($newsletter_id, $page_id, $locale);

		if ($r) {
			list($message_title, $message_html, $message_text)=$r;

			if ($message_title and ($message_html or $message_text)) {
				$newslist[]=array('newsletter_id', 'page_id', 'locale', 'message_title', 'message_text', 'message_html');
			}
		}
	}

	require_once 'emailhtml.php';

	$cssfile=ROOT_DIR . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'newsletter.css';
	$css=@file_get_contents($cssfile);

	$mailinglist=array();

	foreach ($newslist as $news) {
		extract($news);	// newsletter_id, page_id, locale, message_title, message_text, message_html

		if (!isset($mailinglist[$locale])) {
			$mailinglist[$locale]=newsletter_mailinglist($locale);
		}

		if (!$mailinglist[$locale]) {
			continue;
		}

		foreach ($mailinglist[$locale] as $to) {
			$r = emailhtml($message_text, $message_html, $css, $to, $message_title);

			if (!$r) {
				break;
			}
		}

		if (!$r) {
			break;
		}

		$sqllocale=db_sql_arg($locale, false);

		$sql="UPDATE $tabnewsletterpost SET mailed=NOW() WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale";

		$r = db_update($sql);

		if ($r === false) {
			return false;
		}
	}

	return true;
}

function newsletter_get_message($newsletter_id, $page_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnodelocale=db_prefix_table('node_locale');
	$tabnodecontent=db_prefix_table('node_content');
	$tabcontenttext=db_prefix_table('content_text');

	$where="tn.thread_id=$newsletter_id AND tn.node_id=$page_id AND tn.ignored=FALSE";

	$sql="SELECT nl.title AS node_title, ct.text AS content_text_text, ct.eval AS content_text_eval FROM $tabthreadnode tn JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllocale LEFT JOIN $tabnodecontent nc ON nc.node_id=tn.node_id AND nc.content_type='text' AND nc.ignored=FALSE LEFT JOIN $tabcontenttext ct ON ct.content_id=nc.content_id AND ct.locale=$sqllocale WHERE $where ORDER BY tn.number";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$title=$html=$text=false;

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
				$title=$c['node_title'];
				$html=$s;
				break;
			case 1:
				if ($i==1) {
					$s=strip_tags($s);
				}
				$text=$s;
				break;
		}
	}

	return array($title, $html, $text);
}

function newsletter_schedule_post($newsletter_id, $page_id, $locale, $scheduled) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="INSERT INTO $tabnewsletterpost (thread_id, node_id, locale, scheduled) SELECT * FROM (SELECT $newsletter_id AS thread_id, $page_id AS node_id, $sqllocale AS locale, FROM_UNIXTIME($scheduled) AS scheduled) s WHERE NOT EXISTS (SELECT node_id FROM $tabnewsletterpost WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale)";

	$r = db_insert($sql);

	if ($r) {
		return true;
	}

	$sql="UPDATE $tabnewsletterpost SET thread_id=$newsletter_id, node_id=$page_id, locale=$sqllocale, scheduled=FROM_UNIXTIME($scheduled) WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function newsletter_cancel_post($newsletter_id, $page_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="DELETE FROM $tabnewsletterpost WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale";

	$r = db_delete($sql);

	if (!$r) {
		return false;
	}

	return true;
}

function newsletter_get_post($newsletter_id, $page_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletterpost=db_prefix_table('newsletter_post');

	$sql="SELECT UNIX_TIMESTAMP(scheduled) AS newsletter_post_scheduled, UNIX_TIMESTAMP(mailed) AS newsletter_post_mailed FROM $tabnewsletterpost WHERE thread_id=$newsletter_id AND node_id=$page_id AND locale=$sqllocale";

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

function newsletter_create_user($mail, $locale) {
	$sqlmail=db_sql_arg($mail);
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="INSERT INTO $tabnewsletteruser (mail, locale, created) SELECT * FROM (SELECT $sqlmail AS mail, $sqllocale AS locale, NOW() AS created) s WHERE NOT EXISTS (SELECT mail FROM $tabnewsletteruser WHERE mail=$sqlmail)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	return true;
}

function newsletter_delete_user($mail) {
	$sqlmail=db_sql_arg($mail);

	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="DELETE FROM $tabnewsletteruser WHERE mail=$sqlmail";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function newsletter_get_user($mail) {
	$sqlmail=db_sql_arg($mail);

	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="SELECT mail AS newsletter_user_mail, locale AS newsletter_user_locale FROM $tabnewsletteruser WHERE mail=$sqlmail";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function newsletter_mailinglist($locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabnewsletteruser=db_prefix_table('newsletter_user');

	$sql="SELECT mail FROM $tabnewsletteruser WHERE locale=$sqllocale";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$mailinglist=array();

	foreach ($r as $u) {
		$mailinglist[]=$u['mail'];
	}

	return $mailinglist;
}
