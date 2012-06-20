<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    13
 * @link       http://www.izend.org
 */

function node_id($node) {
	if (!is_numeric($node)) {
		return false;
	}

	$tabnode=db_prefix_table('node');

	$sql="SELECT node_id FROM $tabnode WHERE node_id=$node LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0]['node_id'] : false;
}

function node_create($lang, $user_id, $node_name, $node_title) {
	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($node_name, true);
	$sqltitle=db_sql_arg($node_title, true, true);

	$tabnode=db_prefix_table('node');

	$sql="INSERT $tabnode SET user_id=$user_id, created=NOW(), modified=created";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$node_id = db_insert_id();

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="INSERT $tabnodelocale SET node_id=$node_id, locale=$sqllang, name=$sqlname, title=$sqltitle";

	$r = db_insert($sql);

	if (!$r) {
		$sql="DELETE FROM $tabnode WHERE node_id=$node_id LIMIT 1";

		$r = db_delete($sql);

		return false;
	}

	$node_id = db_insert_id();

	return $r ? compact('node_id') : false;
}

function node_get($lang, $node_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT nloc.name AS node_name, nloc.title AS node_title, nloc.abstract AS node_abstract, nloc.cloud AS node_cloud, n.user_id AS node_user, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, n.nocomment AS node_nocomment, n.nomorecomment AS node_nomorecomment, n.novote AS node_novote, n.nomorevote AS node_nomorevote, n.ilike AS node_ilike, n.tweet AS node_tweet, n.plusone AS node_plusone, n.linkedin AS node_linkedin FROM $tabnode n $join $tabnodelocale nloc ON nloc.node_id=n.node_id AND nloc.locale=$sqllang WHERE n.node_id=$node_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function node_set($lang, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_nocomment, $node_nomorecomment, $node_novote, $node_nomorevote, $node_ilike, $node_tweet, $node_plusone, $node_linkedin) {
	$sqlnocomment=$node_nocomment ? 1 : 0;
	$sqlnomorecomment=$node_nocomment ? 1 : ($node_nomorecomment ? 1 : 0);
	$sqlnovote=$node_novote ? 1 : 0;
	$sqlnomorevote=$node_novote ? 1 : ($node_nomorevote ? 1 : 0);
	$sqlilike=$node_ilike ? 1 : 0;
	$sqltweet=$node_tweet ? 1 : 0;
	$sqlplusone=$node_plusone ? 1 : 0;
	$sqllinkedin=$node_linkedin ? 1 : 0;

	$tabnode=db_prefix_table('node');

	$sql="UPDATE $tabnode SET modified=NOW(), nocomment=$sqlnocomment, nomorecomment=$sqlnomorecomment, novote=$sqlnovote, nomorevote=$sqlnomorevote, ilike=$sqlilike, tweet=$sqltweet, plusone=$sqlplusone, linkedin=$sqllinkedin WHERE node_id=$node_id LIMIT 1";

	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($node_name, true);
	$sqltitle=db_sql_arg($node_title, true, true);
	$sqlabstract=db_sql_arg($node_abstract, true, true);
	$sqlcloud=db_sql_arg($node_cloud, true, true);

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="INSERT $tabnodelocale SET node_id=$node_id, locale=$sqllang, name=$sqlname, title=$sqltitle, abstract=$sqlabstract, cloud=$sqlcloud ON DUPLICATE KEY UPDATE node_id=LAST_INSERT_ID(node_id), locale=VALUES(locale), name=VALUES(name), title=VALUES(title), abstract=VALUES(abstract), cloud=VALUES(cloud)";

	$r = db_insert($sql);

	return $r;
}

function node_delete($node_id) {
	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');
	$tabnodecontent=db_prefix_table('node_content');

	$sql="DELETE FROM $tabnode WHERE node_id=$node_id";
	$r = db_delete($sql);
	if (!$r) {
		return false;
	}

	$sql="DELETE FROM $tabnodelocale WHERE node_id=$node_id";
	db_delete($sql);

	$sql="SELECT content_id, content_type FROM $tabnodecontent WHERE node_id=$node_id";
	$r = db_query($sql);

	if ($r) {
		$cdata=array();
		foreach ($r as $c) {
			extract($c); /* content_id, content_type */
			if (!isset($cdata[$content_type])) {
				$cdata[$content_type]=array();
			}
			$cdata[$content_type][]=$content_id;
		}

		foreach ($cdata as $ctype => $id) {
			$tabcontent=db_prefix_table('content_' . $ctype);
			$in=implode(',', $id);
			$sql="DELETE FROM $tabcontent WHERE content_id IN ($in)";
			db_delete($sql);
		}

		$sql="DELETE FROM $tabnodecontent WHERE node_id=$node_id";
		db_delete($sql);
	}

	$tabcomment=db_prefix_table('comment');
	$sql="DELETE FROM $tabcomment WHERE node_id=$node_id";
	db_delete($sql);

	$tabvote=db_prefix_table('vote');
	$sql="DELETE FROM $tabvote WHERE content_id=$node_id AND content_type='node'";
	db_delete($sql);

	return true;
}

function node_get_contents($lang, $node_id) {
	global $contents_model;

	$sqllang=db_sql_arg($lang, false);

	$tabnodecontent=db_prefix_table('node_content');

	$select=array('nc.content_id AS content_id', 'nc.content_type AS content_type', 'nc.number AS content_number', 'nc.ignored AS content_ignored');
	$join=array();
	$n=1;
	foreach ($contents_model as $content_type => $fields) {
		$tabcontenttype=db_prefix_table('content_' . $content_type);
		$alias="nc$n";
		$join[]="LEFT JOIN $tabcontenttype $alias ON nc.content_type='$content_type' AND $alias.content_id=nc.content_id AND $alias.locale=$sqllang";
		foreach ($fields as $fname => $props) {
			$select[]="$alias.`$fname` AS content_${content_type}_$fname";
		}
		$n++;
	}

	$select=implode(', ', $select);
	$join=implode(' ', $join);

	$sql="SELECT $select FROM $tabnodecontent nc $join WHERE nc.node_id=$node_id ORDER BY nc.number";

	$r = db_query($sql);

	return $r;
}

function node_set_contents($lang, $node_id, $node_contents) {
	global $contents_model;

	$sqllang=db_sql_arg($lang, false);

	$tabnode=db_prefix_table('node');

	$sql="UPDATE $tabnode SET modified=NOW() WHERE node_id=$node_id LIMIT 1";

	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	$number = 1;

	$tabnodecontent=db_prefix_table('node_content');

	foreach ($node_contents as $c) {
		$content_id=$c['content_id'];
		$content_type=$c['content_type'];
		$content_ignored=$c['content_ignored'];

		$sqltype=db_sql_arg($content_type, false);
		$sqlignored=$content_ignored ? 1 : 0;

		$sql="UPDATE $tabnodecontent SET number=$number, ignored=$sqlignored WHERE node_id=$node_id AND content_id=$content_id AND content_type=$sqltype";
		$r = db_update($sql);

		if (!$r) {
			return false;
		}

		$tabcontenttype=db_prefix_table('content_' . $content_type);
		$set=array("content_id=$content_id", "locale=$sqllang");
		$dup=array();
		foreach ($contents_model[$content_type] as $fname => $props) {
			$fieldname = "content_${content_type}_$fname";
			$val=isset($c[$fieldname]) ? $c[$fieldname] : false;
			switch ($props['type']) {
				case 'number':
					$sqlval=$val ? $val : 0;
					break;
				case 'boolean':
					$sqlval=$val ? 1 : 0;
					break;
				case 'string':
				default:
					$sqlval=db_sql_arg($val, true, isset($props['null']));
					break;
			}
			$set[]="`$fname`=$sqlval";
			$dup[]="`$fname`=VALUES(`$fname`)";
		}
		$set=implode(', ', $set);
		$dup=implode(', ', $dup);

		$sql="INSERT $tabcontenttype SET $set ON DUPLICATE KEY UPDATE $dup";

		$r = db_update($sql);

		if (!$r) {
			return false;
		}

		$number++;
	}

	return true;
}

function node_create_content($lang, $node_id, $content_type, $content_number=0) {
	global $supported_contents;

	if (!in_array($content_type, $supported_contents)) {
		return false;
	}

	$tabnodecontent=db_prefix_table('node_content');

	$sql="SELECT COUNT(*)+1 AS n FROM $tabnodecontent WHERE node_id=$node_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$n = $r[0]['n'];

	if ($content_number < 1 or $content_number > $n) {
		$content_number = $n;
	}

	$sqllang=db_sql_arg($lang, false);

	$tabcontenttype=db_prefix_table('content_' . $content_type);

	$sql="INSERT $tabcontenttype SET locale=$sqllang";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$content_id = db_insert_id();

	if ($content_number != $n) {
		$sql="UPDATE $tabnodecontent SET number=number+1 WHERE node_id=$node_id AND number >= $content_number ORDER BY number";

		db_update($sql);
	}

	$sqltype=db_sql_arg($content_type, false);

	$sql="INSERT $tabnodecontent SET node_id=$node_id, content_id=$content_id, content_type=$sqltype, number=$content_number";

	$r = db_insert($sql);

	return $r ? compact('content_id', 'content_number') : false;
}

function node_delete_content($node_id, $content_id, $content_type) {
	global $supported_contents;

	if (!in_array($content_type, $supported_contents)) {
		return false;
	}

	$sqltype=db_sql_arg($content_type, false);

	$tabnodecontent=db_prefix_table('node_content');
	$tabcontenttype=db_prefix_table('content_' . $content_type);

	$sql="DELETE nc, ct FROM $tabnodecontent nc JOIN $tabcontenttype ct ON ct.content_id=nc.content_id WHERE nc.node_id=$node_id AND nc.content_id=$content_id AND nc.content_type=$sqltype";

	$r = db_delete($sql);

	if (!$r) {
		return false;
	}

	$sql="SET @n=0";
	db_update($sql);
	$sql="UPDATE $tabnodecontent SET number=(@n:=@n+1) WHERE node_id=$node_id ORDER BY number";
	db_update($sql);

	return true;
}

function node_add_comment($node_id, $user_id, $ip_address, $text, $locale) {
	$sqltext=db_sql_arg($text, true);
	$sqllocale=db_sql_arg($locale, false);
	$sqlipaddress=db_sql_arg($ip_address, false);

	$tabcomment=db_prefix_table('comment');

	$sql="INSERT $tabcomment (node_id, locale, created, edited, user_id, ip_address, text) VALUES ($node_id, $sqllocale, NOW(), created, $user_id, INET_ATON($sqlipaddress), $sqltext)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$comment_id = db_insert_id();

	return $comment_id;
}

function node_delete_comment($node_id, $comment_id) {
	$tabcomment=db_prefix_table('comment');

	$sql="DELETE FROM $tabcomment WHERE node_id=$node_id AND comment_id=$comment_id";

	$r = db_delete($sql);

	return $r;
}

function node_set_comment($node_id, $comment_id, $text, $locale) {
	$sqltext=db_sql_arg($text, true);
	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');

	$sql="UPDATE $tabcomment SET text=$sqltext, edited=NOW() WHERE node_id=$node_id AND comment_id=$comment_id AND locale=$sqllocale LIMIT 1";

	$r = db_update($sql);

	return $r;
}

function node_get_comment($node_id, $comment_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');

	$sql="SELECT UNIX_TIMESTAMP(created) AS comment_created, UNIX_TIMESTAMP(c.edited) AS comment_edited, user_id AS comment_user_id, INET_NTOA(ip_address) AS comment_ip_address, text AS comment_text FROM $tabcomment WHERE node_id=$node_id AND comment_id=$comment_id AND locale=$sqllocale LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function node_get_all_comments($node_id, $locale) {
	if (!is_numeric($node_id)) {
		return false;
	}

	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');
	$tabuser=db_prefix_table('user');

	$sql="SELECT c.comment_id, c.text AS comment_text, u.user_id AS comment_user_id, u.name AS comment_user_name, u.mail AS comment_user_mail, u.website AS comment_user_website, UNIX_TIMESTAMP(c.created) AS comment_created, UNIX_TIMESTAMP(c.edited) AS comment_edited FROM $tabcomment c JOIN $tabuser u ON u.user_id=c.user_id WHERE c.node_id=$node_id AND c.locale=$sqllocale ORDER BY comment_created";

	$r = db_query($sql);

	return $r;
}

