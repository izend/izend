<?php

/**
 *
 * @copyright  2012-2013 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'userhasrole.php';
require_once 'validatemail.php';
require_once 'models/thread.inc';
require_once 'models/newsletter.inc';

function newsletterpage($lang, $newsletter, $page) {
	global $with_toolbar;

	$newsletter_id = thread_id($newsletter);
	if (!$newsletter_id) {
		return run('error/notfound', $lang);
	}

	$page_id = thread_node_id($newsletter_id, $page, $lang);
	if (!$page_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $newsletter_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch thread_nocomment thread_nomorecomment */

	$newsletter_name = $thread_name;
	$newsletter_title = $thread_title;
	$newsletter_nocloud = $thread_nocloud;
	$newsletter_nosearch = $thread_nosearch;

	$r = thread_get_node($lang, $newsletter_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud */

	if ($node_ignored) {
		return run('error/notfound', $lang);
	}

	$page_name=$node_name;
	$page_title=$node_title;
	$page_abstract=$node_abstract;
	$page_cloud=$node_cloud;
	$page_modified=$node_modified;

	if ($newsletter_title and $page_title) {
		head('title', $newsletter_title . ' - ' . $page_title );
	}
	else if ($page_title) {
		head('title', $page_title );
	}
	else if ($newsletter_title) {
		head('title', $newsletter_title );
	}
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$message_title=$message_html=$message_text=false;

	$r = newsletter_get_message($newsletter_id, $page_id, $lang);

	if ($r) {
		list($message_title, $message_html, $message_text)=$r;
	}

	$postnews=false;

	$with_mail=false;

	$mailto=false;

	$missing_mail=false;
	$bad_mail=false;

	$email_sent=false;

	if ($message_title and ($message_html or $message_text)) {
		global $webmaster;

		$mailto=$webmaster;

		$with_mail=true;

		if (isset($_POST['newsletterpage_send'])) {
			if (isset($_POST['newsletterpage_mailto'])) {
				$mailto=strtolower(strflat(readarg($_POST['newsletterpage_mailto'])));

				if (!$mailto) {
					$missing_mail=true;
				}
				else if (!validate_mail($mailto)) {
					$bad_mail=true;
				}
			}

			if (!($missing_mail or $bad_mail)) {
				require_once 'emailhtml.php';

				$cssfile=ROOT_DIR . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'newsletter.css';
				$css=@file_get_contents($cssfile);

				$r = emailhtml($message_text, $message_html, $css, $mailto, $message_title);

				if ($r) {
					$email_sent=true;
				}
			}
		}

		$postnews=build('postnews', $lang, $newsletter_id, $page_id);
	}

	$prev_page_label=$prev_page_url=false;
	$r=thread_node_prev($lang, $newsletter_id, $page_id);
	if ($r) {
		extract($r);	/* prev_node_id prev_node_name prev_node_title prev_node_number */
		$prev_page_label = $prev_node_title ? $prev_node_title : $prev_node_number;
		$prev_page_url=url('newsletter', $lang) . '/'. ($prev_node_name ? $prev_node_name : $prev_node_id);
	}

	$next_page_label=$next_page_url=false;
	$r=thread_node_next($lang, $newsletter_id, $page_id);
	if ($r) {
		extract($r);	/* next_node_id next_node_name next_node_title next_node_number */
		$next_page_label = $next_node_title ? $next_node_title : $next_node_number;
		$next_page_url=url('newsletter', $lang) . '/'. ($next_node_name ? $next_node_name : $next_node_id);
	}

	$content = view('newsletterpage', $lang, compact('page_id', 'page_title', 'page_modified', 'message_title', 'message_text', 'message_html', 'prev_page_url', 'prev_page_label', 'next_page_url', 'next_page_label', 'postnews', 'with_mail', 'mailto', 'missing_mail', 'bad_mail', 'email_sent'));

	$search=false;
	if (!$newsletter_nosearch) {
		$search_text='';
		$search_url= url('search', $lang, $newsletter_name);
		$suggest_url= url('suggest', $lang, $newsletter_name);
		$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	}

	$cloud=false;
	if (!$newsletter_nocloud) {
		$cloud_url= url('search', $lang, $newsletter_name);
		$byname=$bycount=$index=true;
		$cloud = build('cloud', $lang, $cloud_url, $newsletter_id, false, 15, compact('byname', 'bycount', 'index'));
	}

	$headline_text=$newsletter_title ? $newsletter_title : $newsletter_id;
	$headline_url=url('newsletter', $lang);
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	$search=!$newsletter_nosearch ? compact('search_url', 'search_text', 'suggest_url') : false;
	$edit=user_has_role('writer') ? url('newsletteredit', $_SESSION['user']['locale']) . '/'. $newsletter_id . '/' . $page_id . '?' . 'clang=' . $lang : false;
	$validate=url('newsletter', $lang) . '/' . $page_name;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline', 'search') : compact('headline', 'edit', 'validate', 'search'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$output = layout('standard', compact('toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

