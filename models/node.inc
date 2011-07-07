<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
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

function node_create($lang, $node_name, $node_title) {
	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($node_name, true);
	$sqltitle=db_sql_arg($node_title, true);

	$tabnode=db_prefix_table('node');

	$sql="INSERT $tabnode SET created=NOW(), modified=created";

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

	$sql="SELECT nloc.name AS node_name, nloc.title AS node_title, nloc.abstract AS node_abstract, nloc.cloud AS node_cloud, n.user_id AS node_user, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, n.nocomment AS node_nocomment, n.nomorecomment AS node_nomorecomment FROM $tabnode n $join $tabnodelocale nloc ON nloc.node_id=n.node_id AND nloc.locale=$sqllang WHERE n.node_id=$node_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function node_set($lang, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_nocomment, $node_nomorecomment) {
	$sqlnocomment=$node_nocomment ? 1 : 0;
	$sqlnomorecomment=$node_nocomment ? 1 : ($node_nomorecomment ? 1 : 0);

	$tabnode=db_prefix_table('node');

	$sql="UPDATE $tabnode SET modified=NOW(), nocomment=$sqlnocomment, nomorecomment=$sqlnomorecomment WHERE node_id=$node_id LIMIT 1";
	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($node_name, true);
	$sqltitle=db_sql_arg($node_title, true);
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
	$tabcontenttext=db_prefix_table('content_text');
	$tabcontentfile=db_prefix_table('content_file');
	$tabcontentdownload=db_prefix_table('content_download');
	$tabcontentinfile=db_prefix_table('content_infile');
	$tabcontentmedia=db_prefix_table('content_media');

	$sql="DELETE n, nloc, nc, ct, cf, cd, cin, cm FROM $tabnode n LEFT JOIN $tabnodelocale nloc ON nloc.node_id=n.node_id LEFT JOIN $tabnodecontent nc ON nc.node_id=n.node_id LEFT JOIN $tabcontenttext ct ON ct.content_id=nc.content_id LEFT JOIN $tabcontentfile cf ON cf.content_id=nc.content_id LEFT JOIN $tabcontentdownload cd ON cd.content_id=nc.content_id LEFT JOIN $tabcontentinfile cin ON cin.content_id=nc.content_id LEFT JOIN $tabcontentmedia cm ON cm.content_id=nc.content_id WHERE n.node_id=$node_id";

	$r = db_delete($sql);

	return $r;
}

function node_get_contents($lang, $node_id) {
	$sqllang=db_sql_arg($lang, false);

	$tabnodecontent=db_prefix_table('node_content');
	$tabcontenttext=db_prefix_table('content_text');
	$tabcontentfile=db_prefix_table('content_file');
	$tabcontentdownload=db_prefix_table('content_download');
	$tabcontentinfile=db_prefix_table('content_infile');
	$tabcontentmedia=db_prefix_table('content_media');

	$sql="SELECT nc.content_id AS content_id, nc.content_type AS content_type, nc.number AS content_number, nc.ignored AS content_ignored, ct.text AS content_text, ct.eval AS content_eval, cf.path AS content_file, cf.start AS content_start, cf.end AS content_end, cf.format AS content_format, cf.lineno AS content_lineno, cd.name AS content_download, cd.path AS content_path, cin.path AS content_infile, cm.file AS content_media_file, cm.image AS content_media_image, cm.width AS content_media_width, cm.height AS content_media_height, cm.icons AS content_media_icons, cm.skin AS content_media_skin, cm.duration AS content_media_duration, cm.autostart AS content_media_autostart, cm.repeat AS content_media_repeat FROM $tabnodecontent nc LEFT JOIN $tabcontenttext ct ON nc.content_type='text' AND ct.content_id=nc.content_id AND ct.locale=$sqllang LEFT JOIN $tabcontentfile cf ON nc.content_type='file' AND cf.content_id=nc.content_id AND cf.locale=$sqllang LEFT JOIN $tabcontentdownload cd ON nc.content_type='download' AND cd.content_id=nc.content_id AND cd.locale=$sqllang LEFT JOIN $tabcontentinfile cin ON nc.content_type='infile' AND cin.content_id=nc.content_id AND cin.locale=$sqllang LEFT JOIN $tabcontentmedia cm ON nc.content_type='media' AND cm.content_id=nc.content_id AND cm.locale=$sqllang WHERE nc.node_id=$node_id ORDER BY nc.number";

	$r = db_query($sql);

	return $r;
}

function node_set_contents($lang, $node_id, $node_contents) {
	$sqllang=db_sql_arg($lang, false);

	$tabnode=db_prefix_table('node');

	$sql="UPDATE $tabnode SET modified=NOW() WHERE node_id=$node_id LIMIT 1";

	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	$number = 1;

	$tabnodecontent=db_prefix_table('node_content');
	$tabcontenttext=db_prefix_table('content_text');
	$tabcontentfile=db_prefix_table('content_file');
	$tabcontentdownload=db_prefix_table('content_download');
	$tabcontentinfile=db_prefix_table('content_infile');
	$tabcontentmedia=db_prefix_table('content_media');

	foreach ($node_contents as $c) {
		$content_ignored=false;
		$content_text='';
		$content_eval=false;
		$content_download=$content_path=false;
		$content_file=$content_format=false;
		$content_start=$content_end=0;
		$content_lineno=false;
		$content_infile=false;
		$content_media_file=$content_media_image=false;
		$content_media_width=$content_media_height=0;
		$content_media_skin=false;
		$content_media_icons=false;
		$content_media_duration=0;
		$content_media_autostart=$content_media_repeat=false;

		extract($c);	/* content_id content_number content_type (content_text content_eval | content_file content_start content_end content_format content_lineno | content_download content_path | content_infile | content_media_file content_media_image content_media_width content_media_height content_media_skin content_media_icons content_media_duration content_media_autostart content_media_repeat) ignored */
		$sqltype=db_sql_arg($content_type, false);
		$sqlignored=$content_ignored ? 1 : 0;

		$sql="UPDATE $tabnodecontent SET number=$number, ignored=$sqlignored WHERE node_id=$node_id AND content_id=$content_id AND content_type=$sqltype";
		$r = db_update($sql);

		if (!$r) {
			return false;
		}

		switch($content_type) {
			case 'text':
				$sqltext=db_sql_arg($content_text, true, true);
				$sqleval=$content_eval ? 1 : 0;
				$sql="INSERT $tabcontenttext (content_id, locale, text, eval) VALUES ($content_id, $sqllang, $sqltext, $sqleval) ON DUPLICATE KEY UPDATE text=VALUES(text), eval=VALUES(eval)";
				break;
			case 'file':
				$sqlpath=db_sql_arg($content_file, true);
				$sqlformat=db_sql_arg($content_format, true, true);
				$sqlstart=$content_start > 0 ? $content_start : 0;
				$sqlend=$content_end > 0 ? $content_end : 0;
				$sqllineno=$content_lineno ? 1 : 0;
				$sql="INSERT $tabcontentfile SET content_id=$content_id, locale=$sqllang, path=$sqlpath, start=$sqlstart, end=$sqlend, format=$sqlformat, lineno=$sqllineno ON DUPLICATE KEY UPDATE path=VALUES(path), start=VALUES(start), end=VALUES(end), format=VALUES(format), lineno=VALUES(lineno)";
				break;
			case 'download':
				$sqlname=db_sql_arg($content_download, true);
				$sqlpath=db_sql_arg($content_path, true, true);
				$sql="INSERT $tabcontentdownload SET content_id=$content_id, locale=$sqllang, name=$sqlname, path=$sqlpath ON DUPLICATE KEY UPDATE name=VALUES(name), path=VALUES(path)";
				break;
			case 'infile':
				$sqlpath=db_sql_arg($content_infile, true, true);
				$sql="INSERT $tabcontentinfile SET content_id=$content_id, locale=$sqllang, path=$sqlpath ON DUPLICATE KEY UPDATE path=VALUES(path)";
				break;
			case 'media':
				$sqlfile=db_sql_arg($content_media_file, true, true);
				$sqlimage=db_sql_arg($content_media_image, true, true);
				$sqlwidth=$content_media_width > 0 ? $content_media_width : 0;
				$sqlheight=$content_media_height > 0 ? $content_media_height : 0;
				$sqlskin=db_sql_arg($content_media_skin, true, true);
				$sqlicons=$content_media_image ? ($content_media_icons ? 1 : 0) : 0;
				$sqlduration=$content_media_duration > 0 ? $content_media_duration : 0;
				$sqlautostart=$content_media_autostart ? 1 : 0;
				$sqlrepeat=$content_media_repeat ? 1 : 0;

				$sql="INSERT $tabcontentmedia (content_id, locale, file, image, width, height, skin, icons, duration, autostart, `repeat`) VALUES ($content_id, $sqllang, $sqlfile, $sqlimage, $sqlwidth, $sqlheight, $sqlskin, $sqlicons, $sqlduration, $sqlautostart, $sqlrepeat) ON DUPLICATE KEY UPDATE file=VALUES(file), image=VALUES(image), width=VALUES(width), height=VALUES(height), skin=VALUES(skin), icons=VALUES(icons), duration=VALUES(duration), autostart=VALUES(autostart), `repeat`=VALUES(`repeat`)";
				break;
		}

		$r = db_update($sql);

		if (!$r) {
			return false;
		}

		$number++;
	}

	return true;
}

function node_create_content($lang, $node_id, $content_type, $content_number=0) {
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

	switch($content_type) {
		case 'text':
		case 'file':
		case 'download':
		case 'infile':
		case 'media':
			$tabcontenttype=db_prefix_table("content_$content_type");
			break;
		default:
			return false;
	}

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
	$sqltype=db_sql_arg($content_type, false);

	switch($content_type) {
		case 'text':
		case 'file':
		case 'download':
		case 'infile':
		case 'media':
			$tabnodecontent=db_prefix_table('node_content');
			$tabcontenttype=db_prefix_table("content_$content_type");

			$sql="DELETE nc, ct FROM $tabnodecontent nc JOIN $tabcontenttype ct ON ct.content_id=nc.content_id WHERE nc.node_id=$node_id AND nc.content_id=$content_id AND nc.content_type=$sqltype";
			break;
		default:
			return false;
	}

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

function node_get_content_download_path($lang, $node_id, $name) {
	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($name, true);

	$tabnodecontent=db_prefix_table('node_content');
	$tabcontentdownload=db_prefix_table('content_download');

	$sql="SELECT cd.path FROM $tabnodecontent nc JOIN $tabcontentdownload cd ON nc.content_type='download' AND cd.content_id=nc.content_id AND cd.locale=$sqllang WHERE nc.node_id=$node_id AND cd.name=$sqlname LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0]['path'] : false;
}

function node_add_comment($node_id, $user_id, $text, $locale) {
	$sqltext=db_sql_arg($text, true);
	$sqllocale=db_sql_arg($locale);

	$ipaddress=client_ip_address();
	$sqlipaddress=db_sql_arg($ipaddress);

	$tabcomment=db_prefix_table('comment');

	$sql="INSERT $tabcomment (node_id, locale, created, user_id, ip_address, text) VALUES ($node_id, $sqllocale, NOW(), $user_id, $sqlipaddress, $sqltext)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$comment_id = db_insert_id();

	return $comment_id;
}

function node_get_comments($node_id, $locale) {
	if (!is_numeric($node_id)) {
		return false;
	}

	$sqllocale=db_sql_arg($locale);

	$tabcomment=db_prefix_table('comment');
	$tabuser=db_prefix_table('user');

	$sql="SELECT c.comment_id, c.text AS comment_text, u.name AS comment_name, u.mail AS comment_mail, UNIX_TIMESTAMP(c.created) AS comment_created FROM $tabcomment c JOIN $tabuser u ON u.user_id=c.user_id WHERE c.node_id=$node_id AND c.locale=$sqllocale ORDER BY comment_created";

	$r = db_query($sql);

	return $r;
}

