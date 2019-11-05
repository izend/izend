<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    30
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function thread_list($lang, $type=false, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="SELECT t.thread_id, t.thread_type, tloc.name AS thread_name, tloc.title AS thread_title, tloc.abstract AS thread_abstract, tloc.cloud AS thread_cloud, t.number AS thread_number FROM $tabthread t $join $tabthreadlocale tloc ON tloc.thread_id=t.thread_id AND tloc.locale=$sqllang";

	if ($type) {
		$sqltype=db_sql_arg($type, false);
		$sql .= " WHERE t.thread_type=$sqltype";
	}

	$sql .= ' ORDER BY t.number';

	$r = db_query($sql);

	return $r;
}

function thread_id($thread) {
	if (is_numeric($thread)) {
		$tabthread=db_prefix_table('thread');

		$sql="SELECT thread_id FROM $tabthread WHERE thread_id=$thread";
	}
	else {
		$sqlname=db_sql_arg($thread, true);

		$tabthreadlocale=db_prefix_table('thread_locale');

		$sql="SELECT thread_id FROM $tabthreadlocale WHERE name=$sqlname";
	}

	$r = db_query($sql);

	return $r ? $r[0]['thread_id'] : false;
}

function thread_node_id($thread_id, $node, $lang=false) {
	if (!$node) {
		$tabthreadnode=db_prefix_table('thread_node');

		$sql="SELECT tn.node_id AS node_id FROM $tabthreadnode tn WHERE tn.thread_id=$thread_id AND tn.ignored=FALSE ORDER BY tn.number LIMIT 1";
	}
	else if (is_numeric($node)) {
		$tabthreadnode=db_prefix_table('thread_node');

		$sql="SELECT tn.node_id AS node_id FROM $tabthreadnode tn WHERE tn.thread_id=$thread_id AND tn.node_id=$node LIMIT 1";
	}
	else {
		$sqlname=db_sql_arg($node, true);
		$sqllang=db_sql_arg($lang, false);

		$tabthreadnode=db_prefix_table('thread_node');
		$tabnodelocale=db_prefix_table('node_locale');

		$sql="SELECT tn.node_id AS node_id FROM $tabthreadnode tn JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang WHERE tn.thread_id=$thread_id AND nl.name=$sqlname LIMIT 1";
	}

	$r = db_query($sql);

	return $r ? $r[0]['node_id'] : false;
}

function thread_type($thread_id) {
	$tabthread=db_prefix_table('thread');

	$sql="SELECT thread_type FROM $tabthread WHERE thread_id=$thread_id";

	$r = db_query($sql);

	return $r ? $r[0]['thread_type'] : false;
}

function thread_user_id($thread_id) {
	$tabthread=db_prefix_table('thread');

	$sql="SELECT user_id FROM $tabthread WHERE thread_id=$thread_id";

	$r = db_query($sql);

	return $r ? $r[0]['user_id'] : false;
}

function thread_create($lang, $user_id, $thread_name, $thread_title, $thread_type='thread', $thread_number=0) {
	if (!$thread_type) {
		$thread_type='thread';
	}

	$tabthread=db_prefix_table('thread');

	$sql="SELECT COUNT(*)+1 AS n FROM $tabthread";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$n = $r[0]['n'];

	if ($thread_number < 1 or $thread_number > $n) {
		$thread_number = $n;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($thread_name, true);
	$sqltitle=db_sql_arg($thread_title, true, true);
	$sqltype=db_sql_arg($thread_type, false);

	$sql="INSERT INTO $tabthread (user_id, thread_type, created, modified, number) VALUES ($user_id, $sqltype, NOW(), NOW(), $thread_number)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	$thread_id = db_insert_id($tabthread . '_thread_id_seq');

	if ($thread_number != $n) {
		$sql="UPDATE $tabthread SET number=number+1 WHERE number >= $thread_number AND thread_id!=$thread_id";

		db_update($sql);

		if ($r === false) {
			return false;
		}
	}

	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="INSERT INTO $tabthreadlocale (thread_id, locale, name, title) VALUES ($thread_id, $sqllang, $sqlname, $sqltitle)";

	$r = db_insert($sql);

	if (!$r) {
		$sql="DELETE FROM $tabthread WHERE thread_id=$thread_id";

		$r = db_delete($sql);

		return false;
	}

	return compact('thread_id', 'thread_number');
}

function thread_delete($thread_id) {
	$tabthread=db_prefix_table('thread');

	$sql="SELECT number FROM $tabthread WHERE thread_id=$thread_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	$number=$r[0]['number'];

	$tabthreadnode=db_prefix_table('thread_node');

	$sql="SELECT node_id FROM $tabthreadnode WHERE thread_id=$thread_id";

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

	$sql="DELETE FROM $tabthreadnode WHERE thread_id=$thread_id";

	db_delete($sql);

	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="DELETE FROM $tabthreadlocale WHERE thread_id=$thread_id";

	db_delete($sql);

	$sql="DELETE FROM $tabthread WHERE thread_id=$thread_id";

	db_delete($sql);

	$sql="UPDATE $tabthread SET number=number-1 WHERE number > $number";

	db_update($sql);

	$tabvote=db_prefix_table('vote');

	$sql="DELETE FROM $tabvote WHERE content_id=$thread_id AND content_type='thread'";

	db_delete($sql);

	return true;
}

function thread_get($lang, $thread_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="SELECT tloc.name AS thread_name, tloc.title AS thread_title, tloc.abstract AS thread_abstract, tloc.cloud AS thread_cloud, tloc.image AS thread_image, t.user_id AS thread_user_id, t.thread_type AS thread_type, UNIX_TIMESTAMP(t.created) AS thread_created, UNIX_TIMESTAMP(t.modified) AS thread_modified, t.visits AS thread_visits, t.nosearch AS thread_nosearch, t.nocloud AS thread_nocloud, t.nocomment AS thread_nocomment, t.nomorecomment AS thread_nomorecomment, t.novote AS thread_novote, t.nomorevote AS thread_nomorevote, t.ilike AS thread_ilike, t.tweet AS thread_tweet, t.linkedin AS thread_linkedin, t.pinit AS thread_pinit, t.whatsapp AS thread_whatsapp FROM $tabthread t $join $tabthreadlocale tloc ON tloc.thread_id=t.thread_id AND tloc.locale=$sqllang WHERE t.thread_id=$thread_id";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_set($lang, $thread_id, $thread_name, $thread_title, $thread_type, $thread_abstract, $thread_cloud, $thread_image, $thread_visits, $thread_nosearch, $thread_nocloud, $thread_nocomment, $thread_nomorecomment, $thread_novote, $thread_nomorevote, $thread_ilike, $thread_tweet, $thread_linkedin, $thread_pinit, $thread_whatsapp) {
	$sqltype=db_sql_arg($thread_type, false);
	$sqlvisits=$thread_visits ? 'TRUE' : 'FALSE';
	$sqlnosearch=$thread_nosearch ? 'TRUE' : 'FALSE';
	$sqlnocloud=$thread_nocloud ? 'TRUE' : 'FALSE';
	$sqlnocomment=$thread_nocomment ? 'TRUE' : 'FALSE';
	$sqlnomorecomment=$thread_nocomment ? 'TRUE' : ($thread_nomorecomment ? 'TRUE' : 'FALSE');
	$sqlnovote=$thread_novote ? 'TRUE' : 'FALSE';
	$sqlnomorevote=$thread_novote ? 'TRUE' : ($thread_nomorevote ? 'TRUE' : 'FALSE');
	$sqlilike=$thread_ilike ? 'TRUE' : 'FALSE';
	$sqltweet=$thread_tweet ? 'TRUE' : 'FALSE';
	$sqllinkedin=$thread_linkedin ? 'TRUE' : 'FALSE';
	$sqlpinit=$thread_pinit ? 'TRUE' : 'FALSE';
	$sqlwhatsapp=$thread_whatsapp ? 'TRUE' : 'FALSE';

	$tabthread=db_prefix_table('thread');

	$sql="UPDATE $tabthread SET thread_type=$sqltype, modified=NOW(), visits=$sqlvisits, nosearch=$sqlnosearch, nocloud=$sqlnocloud, nocomment=$sqlnocomment, nomorecomment=$sqlnomorecomment, novote=$sqlnovote, nomorevote=$sqlnomorevote, \"ilike\"=$sqlilike, tweet=$sqltweet, linkedin=$sqllinkedin, pinit=$sqlpinit, whatsapp=$sqlwhatsapp WHERE thread_id=$thread_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($thread_name, true);
	$sqltitle=db_sql_arg($thread_title, true, true);
	$sqlabstract=db_sql_arg($thread_abstract, true, true);
	$sqlcloud=db_sql_arg($thread_cloud, true, true);
	$sqlimage=db_sql_arg($thread_image, true, true);

	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="INSERT INTO $tabthreadlocale (thread_id, locale, name, title, abstract, cloud, image) SELECT $thread_id, $sqllang, $sqlname, $sqltitle, $sqlabstract, $sqlcloud, $sqlimage WHERE NOT EXISTS (SELECT thread_id FROM $tabthreadlocale WHERE thread_id=$thread_id AND locale=$sqllang)";

	$r = db_insert($sql);

	if ($r) {
		return true;
	}

	$sql="UPDATE $tabthreadlocale SET name=$sqlname, title=$sqltitle, abstract=$sqlabstract, cloud=$sqlcloud, image=$sqlimage WHERE thread_id=$thread_id AND locale=$sqllang";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function thread_set_number($thread_id, $number) {
	$tabthread=db_prefix_table('thread');

	$sql="UPDATE $tabthread SET number=$number WHERE thread_id=$thread_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function thread_get_contents($lang, $thread_id, $strict=true) {
	$sqllang=db_sql_arg($lang, false);

	$join = $strict ? 'JOIN' : 'LEFT JOIN';

	$where="WHERE tn.thread_id=$thread_id";
	if ($strict) {
		$where .= ' AND tn.ignored=FALSE';
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
		$where .= ' AND tn.ignored=FALSE';
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
		$where .= ' AND tn.ignored=FALSE';
	}

	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.node_id AS prev_node_id, nl.name AS prev_node_name, nl.title AS prev_node_title, tn.number AS prev_node_number FROM $tabthreadnode tn $join $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang $where ORDER BY tn.number DESC LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_create_node($lang, $user_id, $thread_id, $node_name, $node_title, $node_number=0) {
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

	$r = node_create($lang, $user_id, $node_name, $node_title);

	if (!$r) {
		return false;
	}
	extract($r);	/* node_id */

	if ($node_number != $n) {
		$sql="UPDATE $tabthreadnode SET number=number+1 WHERE thread_id=$thread_id AND number >= $node_number";

		db_update($sql);

		if ($r === false) {
			return false;
		}
	}

	$sql="INSERT INTO $tabthreadnode (thread_id, node_id, number) VALUES ($thread_id, $node_id, $node_number)";

	$r = db_insert($sql);

	if (!$r) {
		return false;
	}

	return compact('node_id', 'node_number');
}

function thread_delete_node($thread_id, $node_id) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="SELECT number FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}
	$number=$r[0]['number'];

	$sql="DELETE FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id";

	$r = db_delete($sql);

	if ($r === false) {
		return false;
	}

	$sql="UPDATE $tabthreadnode SET number=number-1 WHERE thread_id=$thread_id AND number > $number";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	$sql="SELECT COUNT(*) AS count FROM $tabthreadnode WHERE node_id=$node_id";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$count=$r[0]['count'];

	if ($count > 0) {
		return true;
	}

	$r = node_delete($node_id);

	if ($r === false) {
		return false;
	}

	return true;
}

function thread_get_node($lang, $thread_id, $node_id) {
	$sqllang=db_sql_arg($lang, false);

	$tabthreadnode=db_prefix_table('thread_node');
	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.number AS node_number, tn.ignored AS node_ignored, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, nl.name AS node_name, nl.title AS node_title, nl.abstract AS node_abstract, nl.cloud AS node_cloud, nl.image AS node_image, n.user_id AS node_user_id, n.visits AS node_visits, n.nocomment AS node_nocomment, n.nomorecomment AS node_nomorecomment, n.novote AS node_novote, n.nomorevote AS node_nomorevote, n.ilike AS node_ilike, n.tweet AS node_tweet, n.linkedin AS node_linkedin, n.pinit AS node_pinit, n.whatsapp AS node_whatsapp FROM $tabthreadnode tn JOIN $tabnode n ON n.node_id=tn.node_id JOIN $tabnodelocale nl ON nl.node_id=tn.node_id AND nl.locale=$sqllang WHERE tn.thread_id=$thread_id AND tn.node_id=$node_id";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function thread_set_node($lang, $thread_id, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_image, $node_visits, $node_nocomment, $node_nomorecomment, $node_novote, $node_nomorevote, $node_ilike, $node_tweet, $node_linkedin, $node_pinit, $node_whatsapp) {
	return node_set($lang, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_image, $node_visits, $node_nocomment, $node_nomorecomment, $node_novote, $node_nomorevote, $node_ilike, $node_tweet, $node_linkedin, $node_pinit, $node_whatsapp);
}

function thread_copy_node($lang, $user_id, $thread_id, $node_id, $node_name, $node_title, $node_number=0) {
	$np = thread_create_node($lang, $user_id, $thread_id, $node_name, $node_title, $node_number);
	if (!$np) {
		return false;
	}
	extract($np);   /* node_id node_number */

	$node_contents = node_get_contents($lang, $node_id);

	if (!$node_contents) {
		return $np;
	}

	foreach ($node_contents as &$c) {
		$content_type=$c['content_type'];
		$content_number=$c['content_number'];

		$r = node_create_content($lang, $node_id, $content_type, $content_number);

		if (!$r) {
			return false;
		}

		extract($r);    // content_id content_number

		$c['content_id']=$content_id;
		$c['content_number']=$content_number;
	}

	$r = node_set_contents($lang, $node_id, $node_contents);

	if (!$r) {
		return false;
	}

	return $np;
}

function thread_get_node_number($thread_id, $node_id) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="SELECT number FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id";

	$r = db_query($sql);

	return $r ? $r[0]['number'] : false;
}

function thread_set_node_number($thread_id, $node_id, $number) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="UPDATE $tabthreadnode SET number=$number WHERE thread_id=$thread_id AND node_id=$node_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

function thread_get_node_ignored($thread_id, $node_id) {
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="SELECT ignored FROM $tabthreadnode WHERE thread_id=$thread_id AND node_id=$node_id";

	$r = db_query($sql);

	return $r ? $r[0]['ignored'] : false;
}

function thread_set_node_ignored($thread_id, $node_id, $ignored) {
	$sqlignored=$ignored ? 'TRUE' : 'FALSE';

	$tabthreadnode=db_prefix_table('thread_node');

	$sql="UPDATE $tabthreadnode SET ignored=$sqlignored WHERE thread_id=$thread_id AND node_id=$node_id";

	$r = db_update($sql);

	if ($r === false) {
		return false;
	}

	return true;
}

