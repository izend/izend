<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    18
 * @link       http://www.izend.org
 */

function cloud_id($cloud) {
	// a cloud_id is actually a thread_id
	require_once 'models/thread.inc';

	return thread_id($cloud);
}

function cloud_create($cloud_id) {
	// list the cloud of all the nodes in cloud_id
	$tabthreadnode=db_prefix_table('thread_node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT tn.node_id AS node_id, nl.locale AS node_locale, nl.cloud AS node_cloud FROM $tabthreadnode tn JOIN $tabnodelocale nl ON nl.node_id=tn.node_id WHERE tn.thread_id=$cloud_id";

	$r = db_query($sql);

	if ($r) {
		foreach ($r as $n) {
			$node_cloud=$n['node_cloud'];
			if ($node_cloud) {
				$node_id=$n['node_id'];
				$lang=$n['node_locale'];
				// build a list with all the different tags
				$taglist = array_unique(preg_split('/\s+/', $node_cloud, -1, PREG_SPLIT_NO_EMPTY));
				// add all the tags in the node
				foreach ($taglist as $tag) {
					cloud_add_node_tag($lang, $node_id, $tag);
				}
			}
		}
	}

	return true;
}

function cloud_delete($cloud_id) {
	// delete all the associations between tags and nodes in thread cloud_id
	$tabtagindex=db_prefix_table('tag_index');
	$tabthreadnode=db_prefix_table('thread_node');

	$sql="DELETE ti FROM $tabtagindex ti JOIN $tabthreadnode tn ON tn.node_id=ti.node_id WHERE tn.thread_id=$cloud_id";

	$r = db_delete($sql);

	// delete all the tags which are not used anymore
	$tabtag=db_prefix_table('tag');

	$sql="DELETE t FROM $tabtag t WHERE NOT EXISTS (SELECT * FROM $tabtagindex ti WHERE ti.tag_id=t.tag_id)";

	$r = db_delete($sql);

	return $r;
}

function cloud_get($lang, $cloud_id) {
	// list cloud_name, cloud_title, cloud_action of cloud_id in lang
	$sqllang=db_sql_arg($lang, false);

	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');

	$sql="SELECT tloc.name AS cloud_name, tloc.title AS cloud_title, t.thread_type AS cloud_action FROM $tabthread t JOIN $tabthreadlocale tloc ON tloc.thread_id=t.thread_id AND tloc.locale=$sqllang WHERE t.thread_id=$cloud_id LIMIT 1";

	$r = db_query($sql);

	return $r ? $r[0] : false;
}

function cloud_list_tags($lang, $cloud_id, $node_id, $byname=false, $bycount=false, $inclusive=true) {
	// list the tags in a thread or in all the threads which are optionally related to a node by count and/or by name skipping threads of type 'thread' and nodes which are ignored
	$sqllang=db_sql_arg($lang, false);

	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');
	$tabthreadnode=db_prefix_table('thread_node');
	$tabthread=db_prefix_table('thread');

	$join1 = "JOIN $tabthreadnode tn ON tn.node_id=ti.node_id AND tn.ignored=0";
	if ($cloud_id) {
		$join1 .= " AND tn.thread_id=$cloud_id";
	}

	$join2 = "JOIN $tabthread t ON t.thread_id=tn.thread_id AND t.thread_type!='thread'";
	if (!$cloud_id) {
		$join2 .= " AND t.thread_type!='rss' AND t.thread_type!='newsletter'";
	}

	$in=$having='';
	if ($node_id) {
		$in="WHERE ti.tag_id IN (SELECT ti.tag_id FROM $tabtagindex ti JOIN $tabtag tg ON tg.tag_id=ti.tag_id AND tg.locale=$sqllang WHERE ti.node_id=$node_id)";
		if (!$inclusive) {
			$having='HAVING tag_count > 1';
		}
	}

	$order='';
	if ($byname or $bycount) {
		$orderitems=array();
		if ($bycount) {
			$orderitems[]='tag_count DESC';
		}
		if ($byname) {
			$orderitems[]='tag_name COLLATE utf8_general_ci';
		}
		if ($orderitems) {
			$order = 'ORDER BY ' . implode(', ', $orderitems);
		}
	}

	$sql = "SELECT tg.tag_id, ti.node_id, tg.name AS tag_name, COUNT(*) AS tag_count FROM $tabtagindex ti $join1 $join2 JOIN $tabtag tg ON tg.tag_id=ti.tag_id AND tg.locale=$sqllang $in GROUP BY ti.tag_id $having $order";

	$r = db_query($sql);

	return $r;
}

function cloud_search($lang, $cloud_id, $taglist) {
	// return the nodes which are associated to a list of tags in a thread or in all the threads skipping threads of type 'thread' and nodes which are ignored
	$taglist=array_slice(array_unique($taglist), 0, 10);
	$ntags = count($taglist);
	if ($ntags == 0) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);

	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');
	$tabthread=db_prefix_table('thread');
	$tabthreadlocale=db_prefix_table('thread_locale');
	$tabthreadnode=db_prefix_table('thread_node');
	$tabnode=db_prefix_table('node');
	$tabnodelocale=db_prefix_table('node_locale');
	$tabuser=db_prefix_table('user');

	$join1 = "JOIN $tabthreadnode tn ON tn.node_id=ti.node_id AND tn.ignored=0";
	if ($cloud_id) {
		$join1 .= " AND tn.thread_id=$cloud_id";
	}

	$join2 = "JOIN $tabthread t ON t.thread_id=tn.thread_id AND t.thread_type!='thread'";
	if (!$cloud_id) {
		$join2 .= " AND t.thread_type!='rss' AND t.thread_type!='newsletter'";
	}

	$join3="JOIN $tabthreadlocale tl ON tl.thread_id=tn.thread_id AND tl.locale=$sqllang JOIN $tabnode n ON n.node_id=ti.node_id JOIN $tabnodelocale nl ON nl.node_id=ti.node_id AND nl.locale=tg.locale LEFT JOIN $tabuser u ON u.user_id=n.user_id";

	$namelist=array();
	foreach ($taglist as $tag) {
		$sqlname=db_sql_arg($tag, true);
		$namelist[]='tg.name='.$sqlname;
	}
	$where = "tg.locale=$sqllang AND " . ($ntags > 1 ? '('.implode(' OR ', $namelist).')' : $namelist[0]);

	$sql = "SELECT t.thread_id, tl.name AS thread_name, tl.title AS thread_title, t.thread_type, n.node_id, n.user_id, u.name AS user_name, u.website AS user_website, UNIX_TIMESTAMP(n.created) AS node_created, UNIX_TIMESTAMP(n.modified) AS node_modified, nl.name AS node_name, nl.title AS node_title, nl.abstract AS node_abstract, nl.cloud AS node_cloud, COUNT(nl.node_id) / $ntags AS pertinence FROM $tabtag tg JOIN $tabtagindex ti ON ti.tag_id=tg.tag_id $join1 $join2 $join3 WHERE $where GROUP BY tn.thread_id, tn.node_id ORDER BY pertinence DESC, t.number, tn.number";

	$r = db_query($sql);

	return $r;
}

function cloud_match($lang, $cloud_id, $s, $dlimit=0, $closest=true) {
	// return the tags which match a word in a thread or in all the threads skipping threads of type 'thread' and nodes which are ignored
	$wordlist=array_slice(array_unique(preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY)), 0, 10);
	$nwords=count($wordlist);
	if ($nwords == 0) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);

	$tabtag=db_prefix_table('tag');
	$tabthread=db_prefix_table('thread');
	$tabthreadnode=db_prefix_table('thread_node');
	$tabtagindex=db_prefix_table('tag_index');

	$join1 = "JOIN $tabthreadnode tn ON tn.node_id=ti.node_id AND tn.ignored=0";
	if ($cloud_id) {
		$join1 .= " AND tn.thread_id=$cloud_id";
	}

	$join2 = "JOIN $tabthread t ON t.thread_id=tn.thread_id AND t.thread_type!='thread'";
	if (!$cloud_id) {
		$join2 .= " AND t.thread_type!='rss' AND t.thread_type!='newsletter'";
	}

	$where = "tg.locale=$sqllang";

	$sql="SELECT DISTINCT tg.tag_id, tg.name AS tag_name FROM $tabtag tg JOIN $tabtagindex ti ON ti.tag_id=tg.tag_id $join1 $join2 WHERE $where";

	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	require_once 'wmatch.php';

	$alltags=array();
	foreach ($r as $row) {
		$alltags[]=$row['tag_name'];
	}

	$taglist=array();
	foreach ($wordlist as $w) {
		if (preg_match('/(^.+)#(\d)+(\*)?$/', $w, $r)) {
			$r=wmatch($r[1], $alltags, $r[2], !isset($r[3]));
		}
		else {
			$r=wmatch($w, $alltags, $dlimit, $closest);
		}
		if ($r) {
			$taglist=array_merge($taglist, $r);
		}
	}

	return $taglist;
}

function cloud_suggest($lang, $cloud_id, $term) {
	// return the tags which contain a term in a thread or in all the threads skipping threads of type 'thread' and nodes which are ignored
	$sqllang=db_sql_arg($lang, false);
	$sqlterm=db_sql_arg("%$term%", false);

	$tabtag=db_prefix_table('tag');
	$tabthread=db_prefix_table('thread');
	$tabthreadnode=db_prefix_table('thread_node');
	$tabtagindex=db_prefix_table('tag_index');

	$join1 = "JOIN $tabthreadnode tn ON tn.node_id=ti.node_id AND tn.ignored=0";
	if ($cloud_id) {
		$join1 .= " AND tn.thread_id=$cloud_id";
	}

	$join2 = "JOIN $tabthread t ON t.thread_id=tn.thread_id AND t.thread_type!='thread'";
	if (!$cloud_id) {
		$join2 .= " AND t.thread_type!='rss' AND t.thread_type!='newsletter'";
	}

	$where = "tg.locale=$sqllang AND tg.name COLLATE utf8_general_ci LIKE $sqlterm";

	$sql="SELECT DISTINCT tg.tag_id, tg.name AS tag_name FROM $tabtag tg JOIN $tabtagindex ti ON ti.tag_id=tg.tag_id $join1 $join2 WHERE $where ORDER BY tag_name";

	$r = db_query($sql);

	return $r;
}

function cloud_list_node_tags($lang, $node_id) {
	// return all the tags ordered by tag_name which are associated to a node for a specific language
	$sqllang=db_sql_arg($lang, false);

	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');

	$sql = "SELECT t.tag_id, t.name AS tag_name FROM $tabtagindex ti JOIN $tabtag t ON t.tag_id=ti.tag_id AND t.locale=$sqllang WHERE ti.node_id=$node_id ORDER BY tag_name COLLATE utf8_general_ci";

	$r = db_query($sql);

	return $r;
}

function cloud_tag_node($lang, $node_id, $s) {
	// build a list with all the different tags
	$newtags = array_unique(preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY));

	// build a list with all the current tags
	$oldtags = array();
	$r = cloud_list_node_tags($lang, $node_id);
	if ($r) {
		foreach ($r as $t) {
			$oldtags[]=$t['tag_name'];
		}
	}

	// remove all the tags which are not wanted anymore
	foreach (array_diff($oldtags, $newtags) as $w) {
		cloud_remove_node_tag($lang, $node_id, $w);
	}

	// add all the tags which are new
	foreach (array_diff($newtags, $oldtags) as $w) {
		cloud_add_node_tag($lang, $node_id, $w);
	}
}

function cloud_add_node_tag($lang, $node_id, $tag) {
	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($tag, true);

	// see if tag is already used
	$tabtag=db_prefix_table('tag');

	$sql="SELECT t.tag_id FROM $tabtag t WHERE t.locale=$sqllang AND t.name=$sqlname";

	$r = db_query($sql);

	$tag_id = $r ? $r[0]['tag_id'] : false;

	// create a new tag?
	if (!$tag_id) {
		$sql = "INSERT INTO $tabtag (locale, name) VALUES ($sqllang, $sqlname)";

		$r = db_insert($sql);

		if (!$r) {
			return false;
		}

		$tag_id = db_insert_id();
	}

	// associate the tag to the node
	$tabtagindex=db_prefix_table('tag_index');

	$sql = "INSERT IGNORE INTO $tabtagindex (tag_id, node_id) VALUES ($tag_id, $node_id)";

	$r = db_insert($sql);

	return $r;
}

function cloud_remove_node_tag($lang, $node_id, $tag) {
	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($tag, true);

	// disassociate the tag from the node
	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');

	$sql="DELETE ti FROM $tabtagindex ti JOIN $tabtag t ON t.locale=$sqllang AND t.name=$sqlname WHERE ti.tag_id=t.tag_id AND ti.node_id=$node_id";

	$r = db_delete($sql);

	if (!$r) {
		return false;
	}

	// delete the tag if it's not used anymore
	$sql="DELETE t FROM $tabtag t WHERE t.locale=$sqllang AND t.name=$sqlname AND NOT EXISTS (SELECT * FROM $tabtagindex ti WHERE ti.tag_id=t.tag_id)";

	$r = db_delete($sql);

	return $r;
}

function cloud_untag_node($node_id) {
	// delete all the tags associated to the node which are used only once
	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');

	$sql="SELECT t1.tag_id FROM $tabtagindex t1 JOIN $tabtagindex t2 ON t2.tag_id=t1.tag_id WHERE t1.node_id=$node_id GROUP BY t1.tag_id HAVING COUNT(t1.tag_id) = 1";
	$r = db_query($sql);

	if ($r) {
		$id=array();
		foreach ($r as $t) {
			$id[]=$t['tag_id'];
		}
		$in=implode(',', $id);
		$sql="DELETE FROM $tabtag WHERE tag_id IN ($in)";
		$r = db_delete($sql);

		if (!$r) {
			return false;
		}
	}

	// dissociate the tags of the node
	$sql="DELETE FROM $tabtagindex WHERE node_id=$node_id";

	$r = db_delete($sql);

	return $r;
}

