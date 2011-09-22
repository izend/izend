<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';
require_once 'models/cloud.inc';

function thread_list($lang, $type, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');
	$tabthreadlist=db_prefix_table('thread_list');

	$sql="SELECT tl.thread_id, tloc.name AS thread_name, tloc.title AS thread_title, tloc.abstract AS thread_abstract, tloc.cloud AS thread_cloud, tl.number AS thread_number FROM $tabthreadlist tl JOIN $tabthread t ON t.thread_id=tl.thread_id $join $tabthreadlocale tloc ON tloc.thread_id=tl.thread_id AND tloc.locale=$sqllang";

	if ($type) {
		$sqltype=db_sql_arg($type, false);
		$sql .= " WHERE thread_type=$sqltype";
	}

	$sql .= ' ORDER BY tl.number';

	$r = db_query($sql);

	return $r;
}

function thread_id($thread) {
	if (is_numeric($thread)) {
		$tabthread=db_prefix_table('thread');

		$sql="SELECT thread_id FROM $tabthread WHERE thread_id=$thread LIMIT 1";
	}
	else {
		$sqlname=db_sql_arg($thread, true);

		$tabthreadlocale=db_prefix_table('thread_locale');

		$sql="SELECT thread_id FROM $tabthreadlocale WHERE name=$sqlname LIMIT 1";
	}

	$r = db_query($sql);

	return $r ? $r[0]['thread_id'] : false;
}

function thread_node_id($thread_id, $node) {
	if (!$node) {
		$tabthreadnode=db_prefix_table('thread_node');

		$sql="SELECT tn.node_id AS node_id FROM $tabthreadnode tn WHERE tn.thread_id=$thread_id AND tn.ignored=0 ORDER BY tn.number LIMIT 1";
	}
	else if (is_numeric($node)) {
		$tabthreadnode=db_prefix_table('thread_node');

		$sql="SELECT tn.node_id AS node_id FROM $tabthreadnode tn WHERE tn.thread_id=$thread_id AND tn.node_id=$node LIMIT 1";
	}
	else {
		$sqlname = db_sql_arg($node, true);

		$tabthreadnode=db_prefix_table('thread_node');
		$tabnodelocale=db_prefix_table('node_locale');

		$sql="SELECT tn.node_id AS node_id FROM $tabthreadnode tn JOIN $tabnodelocale nl ON nl.node_id=tn.node_id WHERE tn.thread_id=$thread_id AND nl.name=$sqlname LIMIT 1";
	}

	$r = db_query($sql);

	return $r ? $r[0]['node_id'] : false;
}

function thread_next_number() {
	$tabthreadlist=db_prefix_table('thread_list');

	$sql="SELECT IFNULL(MAX(number),0)+1 AS number FROM $tabthreadlist";

	$r = db_query($sql);

	return $r ? $r[0]['number'] : false;
}

function thread_create($lang, $thread_name, $thread_title, $thread_type='thread', $thread_number=false) {
	if (!$thread_type) {
		$thread_type='thread';
	}

	if (!$thread_number) {
		$n = thread_next_number();

		if (!$n) {
			return false;
		}
		$thread_number = $n;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($thread_name, true);
	$sqltitle=db_sql_arg($thread_title, true, true);
	$sqltype=db_sql_arg($thread_type, false);

	$tabthread=db_prefix_table('thread');

	$sql="INSERT $tabthread SET thread_type=$sqltype, created=NOW(), modified=created";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$thread_id = db_insert_id();

	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="INSERT $tabthreadlocale SET thread_id=$thread_id, locale=$sqllang, name=$sqlname, title=$sqltitle";

	$r = db_insert($sql);

	if (!$r) {
		$sql="DELETE FROM $tabthread WHERE thread_id=$thread_id LIMIT 1";

		$r = db_delete($sql);

		return false;
	}

	$tabthreadlist=db_prefix_table('thread_list');

	$sql="INSERT $tabthreadlist SET thread_id=$thread_id, number=$thread_number";

	$r = db_insert($sql);

	if (!$r) {
		$sql="DELETE FROM $tabthread WHERE thread_id=$thread_id LIMIT 1";

		$r = db_delete($sql);

		$sql="DELETE FROM $tabthreadlocale WHERE thread_id=$thread_id LIMIT 1";

		$r = db_delete($sql);

		return false;
	}

	return compact('thread_id', 'thread_number');
}

function thread_delete($thread_id) {
	cloud_delete($thread_id);

	$tabthreadnode=db_prefix_table('thread_node');

	$sql="SELECT tn.node_id FROM $tabthreadnode tn WHERE tn.thread_id=$thread_id";

	$r = db_query($sql);

	if ($r) {
		foreach ($r as $tn) {
			$node_id=$tn['node_id'];

			$sql="SELECT COUNT(*) AS count FROM $tabthreadnode WHERE node_id=$node_id";

			$r = db_query($sql);

			if  ($r && $r[0]['count'] == 1) {
				$r = node_delete($node_id);
			}
		}
	}

	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');
	$tabthreadlist=db_prefix_table('thread_list');

	$sql="DELETE tl, t, tloc, tn FROM $tabthreadlist tl JOIN $tabthread t ON t.thread_id=tl.thread_id JOIN $tabthreadlocale tloc on tloc.thread_id=t.thread_id LEFT JOIN $tabthreadnode tn ON tn.thread_id=tl.thread_id WHERE tl.thread_id=$thread_id";

	$r = db_delete($sql);

	return $r;
}

function thread_get($lang, $thread_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="SELECT tloc.name AS thread_name, tloc.title AS thread_title, tloc.abstract AS thread_abstract, tloc.cloud AS thread_cloud, t.thread_type AS thread_type, UNIX_TIMESTAMP(t.created) AS thread_created, UNIX_TIMESTAMP(t.modified) AS thread_modified, t.nosearch AS thread_nosearch, t.nocloud AS thread_nocloud, t.nocomment AS thread_nocomment, t.nomorecomment AS thread_nomorecomment FROM $tabthread t $join $tabthreadlocale tloc ON tloc.thread_id=t.thread_id AND tloc.locale=$sqllang WHERE t.thread_id=$thread_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_set($lang, $thread_id, $thread_name, $thread_title, $thread_type, $thread_abstract, $thread_cloud, $thread_nosearch, $thread_nocloud, $thread_nocomment, $thread_nomorecomment) {
	$sqltype=db_sql_arg($thread_type, false);
	$sqlnosearch=$thread_nosearch ? 1 : 0;
	$sqlnocloud=$thread_nocloud ? 1 : 0;
	$sqlnocomment=$thread_nocomment ? 1 : 0;
	$sqlnomorecomment=$thread_nocomment ? 1 : ($thread_nomorecomment ? 1 : 0);

	$tabthread=db_prefix_table('thread');

	$sql="UPDATE $tabthread SET thread_type=$sqltype, modified=NOW(), nosearch=$sqlnosearch, nocloud=$sqlnocloud, nocomment=$sqlnocomment, nomorecomment=$sqlnomorecomment WHERE thread_id=$thread_id LIMIT 1";
	$r = db_update($sql);

	if (!$r) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($thread_name, true);
	$sqltitle=db_sql_arg($thread_title, true, true);
	$sqlabstract=db_sql_arg($thread_abstract, true, true);
	$sqlcloud=db_sql_arg($thread_cloud, true, true);

	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="INSERT $tabthreadlocale (thread_id, locale, name, title, abstract, cloud) VALUES ($thread_id, $sqllang, $sqlname, $sqltitle, $sqlabstract, $sqlcloud) ON DUPLICATE KEY UPDATE thread_id=LAST_INSERT_ID(thread_id), locale=VALUES(locale), name=VALUES(name), title=VALUES(title), abstract=VALUES(abstract), cloud=VALUES(cloud)";

	$r = db_insert($sql);

	return $r;
}

function thread_set_number($thread_id, $number) {
	$tabthreadlist=db_prefix_table('thread_list');

	$sql="UPDATE $tabthreadlist SET number=$number WHERE thread_id=$thread_id LIMIT 1";

	$r = db_update($sql);

	return $r;
}

function thread_get_contents($lang, $thread_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$where="WHERE tn.thread_id=$thread_id";
	if ($strict) {
		$where .= ' AND tn.ignored=0';
	}

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.node_id, tn.number AS node_number, tn.ignored AS node_ignored, nl.name AS node_name, nl.title AS node_title, nl.abstract AS node_abstract, nl.cloud AS node_cloud FROM $tabthreadnode tn $join $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang $where ORDER BY tn.number";

	$r = db_query($sql);

	return $r;
}

function thread_node_next($lang, $thread_id, $node_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$where = "WHERE tn.thread_id=$thread_id AND tn.number > (SELECT number FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id)";
	if ($strict) {
		$where .= ' AND tn.ignored=0';
	}

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.node_id AS next_node_id, nl.name AS next_node_name, nl.title AS next_node_title, tn.number AS next_node_number FROM $tabthreadnode tn $join $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang $where ORDER BY tn.number ASC LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_node_prev($lang, $thread_id, $node_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$where = "WHERE tn.thread_id=$thread_id AND tn.number < (SELECT number FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id)";
	if ($strict) {
		$where .= ' AND tn.ignored=0';
	}

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.node_id AS prev_node_id, nl.name AS prev_node_name, nl.title AS prev_node_title, tn.number AS prev_node_number FROM $tabthreadnode tn $join $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang $where ORDER BY tn.number DESC LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_create_node($lang, $thread_id, $node_name, $node_title, $node_number=0) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="SELECT COUNT(*)+1 AS n FROM $tabthreadnode WHERE thread_id=$thread_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$n = $r[0]['n'];

	if ($node_number < 1 or $node_number > $n) {
		$node_number = $n;
	}

	$r = node_create($lang, $node_name, $node_title);

	if (!$r) {
		return false;
	}
	extract($r);	/* node_id */

	if ($node_number != $n) {
		$sql="UPDATE $tabthreadnode SET number=number+1 WHERE thread_id=$thread_id AND number >= $node_number ORDER BY number";

		db_update($sql);
	}

	$sql="INSERT $tabthreadnode SET thread_id=$thread_id, node_id=$node_id, number=$node_number";

	$r = db_insert($sql);

	return $r ? compact('node_id', 'node_number') : false;
}

function thread_delete_node($thread_id, $node_id) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="DELETE FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id LIMIT 1";

	$r = db_delete($sql);

	if (!$r) {
		return false;
	}

	$sql="SET @n=0";
	db_update($sql);
	$sql="UPDATE $tabthreadnode SET number=(@n:=@n+1) WHERE thread_id=$thread_id ORDER BY number";
	db_update($sql);

	$sql="SELECT COUNT(*) AS count FROM $tabthreadnode WHERE node_id=$node_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	if ($r[0]['count'] != 0) {
		return true;
	}

	$r = node_delete($node_id);
	if (!$r) {
		return false;
	}

	cloud_untag_node($node_id);

	return true;
}

function thread_get_node($lang, $thread_id, $node_id) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.number AS node_number, tn.ignored AS node_ignored, nl.name AS node_name, nl.title AS node_title, nl.abstract AS node_abstract, nl.cloud AS node_cloud, n.nocomment AS node_nocomment, n.nomorecomment AS node_nomorecomment FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang WHERE tn.thread_id=$thread_id AND tn.node_id=$node_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_set_node($lang, $thread_id, $node_id, $node_name, $node_title, $node_abstract, $node_cloud) {
	$r = node_set($lang, $node_id, $node_name, $node_title, $node_abstract, $node_cloud);

	if (!$r) {
		return false;
	}

	cloud_tag_node($lang, $node_id, $node_cloud);

	return true;
}

function thread_set_node_number($thread_id, $node_id, $number) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="UPDATE $tabthreadnode SET number=$number WHERE thread_id=$thread_id AND node_id=$node_id LIMIT 1";

	$r = db_update($sql);

	return $r;
}

function thread_set_node_ignored($thread_id, $node_id, $ignored) {
	$sqlignored=$ignored ? 1 : 0;

	$tabthreadnode=db_prefix_table('thread_node');

	$sql="UPDATE $tabthreadnode SET ignored=$sqlignored WHERE thread_id=$thread_id AND node_id=$node_id LIMIT 1";

	$r = db_update($sql);

	return $r;
}

