<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    34
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

function node_id($node) {
	if (!is_numeric($node)) {
		return false;
	}

	$tabnode=db_prefix_table('node');

	$sql="SELECT node_id FROM $tabnode WHERE node_id=$node";

	$r = db_query($sql);

	return $r ? $r[0]['node_id'] : false;
}

function node_user_id($node_id) {
	$tabnode=db_prefix_table('node');

	$sql="SELECT user_id FROM $tabnode WHERE node_id=$node_id";

	$r = db_query($sql);

	return $r ? $r[0]['user_id'] : false;
}

function node_create($lang, $user_id, $node_name, $node_title) {
	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($node_name, true);
	$sqltitle=db_sql_arg($node_title, true, true);

	$tabnode=db_prefix_table('node');

	$sql="INSERT INTO $tabnode (user_id, created, modified) VALUES ($user_id, NOW(), NOW())";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$node_id = db_insert_id($tabnode . '_node_id_seq');

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="INSERT INTO $tabnodelocale (node_id, locale, name, title) VALUES ($node_id, $sqllang, $sqlname, $sqltitle)";

	$r = db_insert($sql);

	if (!$r) {
		$sql="DELETE FROM $tabnode WHERE node_id=$node_id";

		db_delete($sql);

		return false;
	}

	return $r ? compact('node_id') : false;
}

function node_get($lang, $node_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT nloc.name AS node_name, nloc.title AS node_title, nloc.abstract AS node_abstract, nloc.cloud AS node_cloud, nloc.image AS node_image, n.user_id AS node_user_id, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, n.visits AS node_visits, n.nocomment AS node_nocomment, n.nomorecomment AS node_nomorecomment, n.novote AS node_novote, n.nomorevote AS node_nomorevote, n.ilike AS node_ilike, n.tweet AS node_tweet, n.plusone AS node_plusone, n.linkedin AS node_linkedin, n.pinit AS node_pinit, n.whatsapp AS node_whatsapp FROM $tabnode n $join $tabnodelocale nloc ON nloc.node_id=n.node_id AND nloc.locale=$sqllang WHERE n.node_id=$node_id";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function node_set($lang, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_image, $node_visits, $node_nocomment, $node_nomorecomment, $node_novote, $node_nomorevote, $node_ilike, $node_tweet, $node_plusone, $node_linkedin, $node_pinit, $node_whatsapp) {
	$sqlvisits=$node_visits ? 'TRUE' : 'FALSE';
	$sqlnocomment=$node_nocomment ? 'TRUE' : 'FALSE';
	$sqlnomorecomment=$node_nocomment ? 'TRUE' : ($node_nomorecomment ? 'TRUE' : 'FALSE');
	$sqlnovote=$node_novote ? 'TRUE' : 'FALSE';
	$sqlnomorevote=$node_novote ? 'TRUE' : ($node_nomorevote ? 'TRUE' : 'FALSE');
	$sqlilike=$node_ilike ? 'TRUE' : 'FALSE';
	$sqltweet=$node_tweet ? 'TRUE' : 'FALSE';
	$sqlplusone=$node_plusone ? 'TRUE' : 'FALSE';
	$sqllinkedin=$node_linkedin ? 'TRUE' : 'FALSE';
	$sqlpinit=$node_pinit ? 'TRUE' : 'FALSE';
	$sqlwhatsapp=$node_whatsapp ? 'TRUE' : 'FALSE';

	$tabnode=db_prefix_table('node');

	$sql="UPDATE $tabnode SET modified=NOW(), visits=$sqlvisits, nocomment=$sqlnocomment, nomorecomment=$sqlnomorecomment, novote=$sqlnovote, nomorevote=$sqlnomorevote, \"ilike\"=$sqlilike, tweet=$sqltweet, plusone=$sqlplusone, linkedin=$sqllinkedin, pinit=$sqlpinit, whatsapp=$sqlwhatsapp WHERE node_id=$node_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($node_name, true);
	$sqltitle=db_sql_arg($node_title, true, true);
	$sqlabstract=db_sql_arg($node_abstract, true, true);
	$sqlcloud=db_sql_arg($node_cloud, true, true);
	$sqlimage=db_sql_arg($node_image, true, true);

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="INSERT INTO $tabnodelocale (node_id, locale, name, title, abstract, cloud, image) SELECT * FROM (SELECT $node_id AS node_id, $sqllang AS locale, $sqlname AS name, $sqltitle AS title, $sqlabstract AS abstract, $sqlcloud AS cloud, $sqlimage AS image) s WHERE NOT EXISTS (SELECT node_id FROM $tabnodelocale WHERE node_id=$node_id AND locale=$sqllang)";

	$r = db_insert($sql);

	if ($r === false) {
		return false;
	}

	if ($r === 0) {
		$sql="UPDATE $tabnodelocale SET name=$sqlname, title=$sqltitle, abstract=$sqlabstract, cloud=$sqlcloud, image=$sqlimage WHERE node_id=$node_id AND locale=$sqllang";

		$r = db_update($sql);

		if ($r === false) {
			return false;
		}
	}

	cloud_tag_node($lang, $node_id, $node_cloud);

	return true;
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
			$in=implode(',', $id);

			$tabcontent=db_prefix_table('content_' . $ctype);

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

	cloud_untag_node($node_id);

	return true;
}

function node_get_contents($lang, $node_id) {
	global $supported_contents, $contents_model;

	$sqllang=db_sql_arg($lang, false);

	$tabnodecontent=db_prefix_table('node_content');

	$union=array();
	foreach ($contents_model as $content_type => $content_props) {
		if (!in_array($content_type, $supported_contents)) {
			continue;
		}
		$tabcontenttype=db_prefix_table('content_' . $content_type);
		$select=array('nc.content_id AS content_id', 'nc.content_type AS content_type', 'nc.number AS content_number', 'nc.ignored AS content_ignored');
		foreach ($contents_model as $type => $fields) {
			$prefix="content_$type";
			foreach ($fields as $fname => $props) {
				if (!empty($props['ignored'])) {
					continue;
				}
				if ($content_type == $type) {
					$select[] = "ncc.$fname AS ${prefix}_$fname";
				}
				else {
					if (!empty($props['null'])) {
						$value = 'NULL';
					}
					else {
						switch ($props['type']) {
							case 'string':
								$value = isset($props['default']) ? db_sql_arg($props['default']) : "''";
								break;
							case 'enum':
								global $db_scheme;

								$value = isset($props['default']) ? db_sql_arg($props['default']) : "''";

								switch ($db_scheme) {
									case 'pgsql':
										$value = "CAST($value AS " . db_prefix_table("type_${prefix}_$fname") . ')';
										break;
									case 'mysql':
									default:
										break;
								}
								break;
							case 'number':
								$value = isset($props['default']) ? $props['default'] : '0';
								break;
							case 'boolean':
								$value = isset($props['default']) ? ($props['default'] ? 'TRUE' : 'FALSE') : 'FALSE';
								break;
							default:
								$value = "''";
								break;
						}
					}
					$select[] = "$value AS ${prefix}_$fname";
				}
			}
		}
		$select=implode(', ', $select);
		$join="$tabcontenttype ncc ON ncc.content_id=nc.content_id AND ncc.locale=$sqllang";
		$union[]="SELECT $select FROM $tabnodecontent nc LEFT JOIN $join WHERE nc.node_id=$node_id AND nc.content_type='$content_type'";
	}
	$union=implode(' UNION ', $union);

	$sql="SELECT * FROM ($union) nc ORDER BY nc.content_number";

	$r = db_query($sql);

	return $r;
}

function node_set_contents($lang, $node_id, $node_contents) {
	global $supported_contents, $contents_model;

	$tabnodecontent=db_prefix_table('node_content');

	$wlist=array();
	foreach ($node_contents as $c) {
		$content_id=$c['content_id'];
		$content_type=$c['content_type'];

		if (!in_array($content_type, $supported_contents)) {
			return false;
		}

		$sqltype=db_sql_arg($content_type, false);

		$wlist[]="content_id=$content_id AND content_type=$sqltype";
	}
	$where = implode(' OR ', $wlist);

	$sql="SELECT COUNT(*) AS count FROM $tabnodecontent WHERE node_id=$node_id AND ($where)";

	$r = db_query($sql);

	if ($r === false) {
		return false;
	}

	if ($r[0]['count'] != count($node_contents)) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);

	$tabnode=db_prefix_table('node');

	$sql="UPDATE $tabnode SET modified=NOW() WHERE node_id=$node_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	$number = 1;

	foreach ($node_contents as $c) {
		$content_id=$c['content_id'];
		$content_type=$c['content_type'];
		$content_ignored=$c['content_ignored'];

		$sqltype=db_sql_arg($content_type, false);
		$sqlignored=$content_ignored ? 'TRUE' : 'FALSE';

		$sql="UPDATE $tabnodecontent SET number=$number, ignored=$sqlignored WHERE node_id=$node_id AND content_id=$content_id AND content_type=$sqltype";

		$r = db_update($sql);

		if ($r === false) {
			return false;
		}

		$tabcontenttype=db_prefix_table('content_' . $content_type);
		$data=array('content_id' => $content_id, 'locale' => $sqllang);
		foreach ($contents_model[$content_type] as $fname => $props) {
			if (!empty($props['ignored'])) {
				continue;
			}
			$fieldname = "content_${content_type}_$fname";
			$val=isset($c[$fieldname]) ? $c[$fieldname] : false;
			switch ($props['type']) {
				case 'number':
					if (!is_numeric($val)) {
						continue 2;
					}
					$sqlval=$val;
					break;
				case 'boolean':
					$sqlval=$val ? 'TRUE' : 'FALSE';
					break;
				case 'string':
				case 'enum':
				default:
					$sqlval=db_sql_arg($val, true, !empty($props['null']));
					break;
			}
			$data[$fname]=$sqlval;
		}

		$fields=array();
		$as=array();
		foreach ($data as $f => $v) {
			$fields[]="\"$f\"";
			$as[]="$v AS \"$f\"";
		}
		$fields=implode(', ', $fields);
		$as=implode(', ', $as);

		$sql="INSERT INTO $tabcontenttype ($fields) SELECT * FROM (SELECT $as) s WHERE EXISTS (SELECT $content_id FROM $tabcontenttype WHERE content_id=$content_id) AND NOT EXISTS (SELECT $content_id FROM $tabcontenttype WHERE content_id=$content_id AND locale=$sqllang)";

		$r = db_insert($sql);

		if ($r === false) {
			return false;
		}

		if ($r === 0) {
			$set=array();
			foreach ($data as $f => $v) {
				$set[]="\"$f\"=$v";
			}
			$set=implode(', ', $set);

			$sql="UPDATE $tabcontenttype SET $set WHERE content_id=$content_id AND locale=$sqllang";

			$r = db_update($sql);

			if ($r === false) {
				return false;
			}
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

	$sql="INSERT INTO $tabcontenttype (locale) VALUES ($sqllang)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$content_id = db_insert_id($tabcontenttype . '_content_id_seq');

	if ($content_number != $n) {
		$sql="UPDATE $tabnodecontent SET number=number+1 WHERE node_id=$node_id AND number >= $content_number";

		db_update($sql);

		if ($r === false) {
			return false;
		}
	}

	$sqltype=db_sql_arg($content_type, false);

	$sql="INSERT INTO $tabnodecontent (node_id, content_id, content_type, number) VALUES ($node_id, $content_id, $sqltype, $content_number)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	return compact('content_id', 'content_number');
}

function node_delete_content($node_id, $content_id, $content_type) {
	global $supported_contents;

	if (!in_array($content_type, $supported_contents)) {
		return false;
	}

	$sqltype=db_sql_arg($content_type, false);

	$tabnodecontent=db_prefix_table('node_content');

	$sql="SELECT number FROM $tabnodecontent WHERE node_id=$node_id AND content_id=$content_id AND content_type=$sqltype";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	$number=$r[0]['number'];

	$sql="DELETE FROM $tabnodecontent WHERE node_id=$node_id AND content_id=$content_id AND content_type=$sqltype";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	$sql="UPDATE $tabnodecontent SET number=number-1 WHERE node_id=$node_id AND number > $number";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	$tabcontenttype=db_prefix_table('content_' . $content_type);

	$sql="DELETE FROM $tabcontenttype WHERE content_id=$content_id";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function node_add_comment($node_id, $user_id, $user_mail, $ip_address, $text, $locale, $confirmed=true) {
	$sqltext=db_sql_arg($text, true);
	$sqllocale=db_sql_arg($locale, false);
	$sqlipaddress=db_sql_arg($ip_address, false);
	$sqlmail=db_sql_arg($user_mail, true, true);
	$sqlconfirmed=$confirmed ? 'TRUE' : 'FALSE';

	$tabcomment=db_prefix_table('comment');

	$sql="INSERT INTO $tabcomment (node_id, locale, created, edited, user_id, user_mail, ip_address, text, confirmed) VALUES ($node_id, $sqllocale, NOW(), NOW(), $user_id, $sqlmail, INET_ATON($sqlipaddress), $sqltext, $sqlconfirmed)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$comment_id = db_insert_id($tabcomment . '_comment_id_seq');

	return $comment_id;
}

function node_delete_comment($node_id, $comment_id) {
	$tabcomment=db_prefix_table('comment');

	$sql="DELETE FROM $tabcomment WHERE node_id=$node_id AND comment_id=$comment_id";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function node_set_comment($node_id, $comment_id, $text, $locale) {
	$sqltext=db_sql_arg($text, true);
	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');

	$sql="UPDATE $tabcomment SET text=$sqltext, edited=NOW() WHERE node_id=$node_id AND comment_id=$comment_id AND locale=$sqllocale";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function node_get_comment($node_id, $comment_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');

	$sql="SELECT UNIX_TIMESTAMP(created) AS comment_created, UNIX_TIMESTAMP(edited) AS comment_edited, user_id AS comment_user_id, user_mail AS comment_user_mail, INET_NTOA(ip_address) AS comment_ip_address, text AS comment_text, confirmed AS comment_confirmed FROM $tabcomment WHERE node_id=$node_id AND comment_id=$comment_id AND locale=$sqllocale";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function node_get_all_comments($node_id, $locale, $published=true, $included=false) {
	if (!is_numeric($node_id)) {
		return false;
	}

	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');
	$tabuser=db_prefix_table('user');
	$tabuserinfo=db_prefix_table('user_info');

	$where="c.node_id=$node_id AND c.locale=$sqllocale";

	if ($published) {
		$where .= ' AND c.confirmed=TRUE';

		if ($included) {
			$where .= ' OR c.comment_id IN (' . implode(',', $included) . ')';
		}
	}

	$sql="SELECT c.comment_id, c.text AS comment_text, u.user_id AS comment_user_id, u.name AS comment_user_name, u.mail AS comment_user_mail, u.website AS comment_user_website, ui.lastname AS comment_user_lastname, ui.firstname AS comment_user_firstname, UNIX_TIMESTAMP(c.created) AS comment_created, UNIX_TIMESTAMP(c.edited) AS comment_edited, c.confirmed AS comment_confirmed FROM $tabcomment c LEFT JOIN $tabuser u ON u.user_id=c.user_id LEFT JOIN $tabuserinfo ui ON ui.user_id=c.user_id WHERE $where ORDER BY comment_created";

	$r = db_query($sql);

	return $r;
}

function node_confirm_comment($node_id, $comment_id, $locale) {
	$sqllocale=db_sql_arg($locale, false);

	$tabcomment=db_prefix_table('comment');

	$sql="UPDATE $tabcomment SET confirmed=TRUE WHERE node_id=$node_id AND comment_id=$comment_id AND locale=$sqllocale";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function node_get_visits($node_id, $locale=false) {
	if (!is_numeric($node_id)) {
		return false;
	}

	$where = "node_id=$node_id";

	if ($locale) {
		$sqllocale=db_sql_arg($locale, false);

		$where .= " AND locale=$sqllocale";
	}

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT SUM(visited) AS node_visited FROM $tabnodelocale WHERE $where";

	$r = db_query($sql);

	return $r ? $r[0]['node_visited'] : 0;
}

function node_add_visit($node_id, $locale) {
	if (!is_numeric($node_id)) {
		return false;
	}

	$sqllocale=db_sql_arg($locale, false);

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="UPDATE $tabnodelocale SET visited=visited+1 WHERE node_id=$node_id AND locale=$sqllocale";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}
