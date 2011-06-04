<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function cloud_id($cloud) {
	// a cloud_id is actually a thread_id
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
			$cloud=$n['node_cloud'];
			if ($cloud) {
				$node_id=$n['node_id'];
				$lang=$n['node_locale'];
				// build a list with all the different tags
				preg_match_all('/(\S+)/', $cloud, $wlist);
				$tags = array_unique($wlist[0]);

				// add all the tags in the node
				foreach ($tags as $w) {
					cloud_add_node_tag($lang, $node_id, $w);
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

function cloud_list_tags($lang, $cloud_id) {
	$sqllang=db_sql_arg($lang, false);

	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');
	$tabthreadnode=db_prefix_table('thread_node');

	$sql = "SELECT t.tag_id, ti.node_id, t.name AS tag_name, COUNT(*) AS tag_count FROM $tabtagindex ti JOIN $tabthreadnode tn ON tn.thread_id=$cloud_id AND tn.node_id=ti.node_id JOIN $tabtag t ON t.tag_id=ti.tag_id AND t.locale=$sqllang GROUP BY ti.tag_id ORDER BY tag_count DESC, tag_name";

	$r = db_query($sql);

	return $r;
}

function cloud_search($lang, $cloud_id, $taglist) {
	$ntags = count($taglist);
	if ( $ntags == 0) {
		return false;
	}

	$taglist = array_slice($taglist, 0, 10);	// limit to 10 different tags

	$sqllang=db_sql_arg($lang, false);

	$namelist=array();
	foreach ($taglist as $tag) {
		$sqlname=db_sql_arg($tag, true);
		$namelist[]='t.name='.$sqlname;
	}
	$where = "t.locale=$sqllang AND " . ($ntags > 1 ? '('.implode(' OR ', $namelist).')' : $namelist[0]);

	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');
	$tabthreadnode=db_prefix_table('thread_node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql = "SELECT nl.node_id, nl.name, nl.title, nl.abstract, nl.cloud, COUNT(nl.node_id) / $ntags AS pertinence FROM $tabtag t JOIN $tabtagindex ti ON ti.tag_id=t.tag_id JOIN $tabthreadnode tn ON tn.thread_id=$cloud_id AND tn.node_id=ti.node_id JOIN $tabnodelocale nl ON nl.node_id=ti.node_id AND nl.locale=t.locale WHERE $where GROUP BY nl.node_id ORDER BY pertinence DESC, tn.number";

	$r = db_query($sql);

	return $r;
}

function cloud_match($lang, $cloud_id, $s, $dlimit=0, $closest=true) {
	preg_match_all('/(\S+)/', $s, $r);
	$wordlist = array_unique($r[0]);
	$nwords=count($wordlist);
	if ( $nwords == 0) {
		return false;
	}

	$sqllang=db_sql_arg($lang, false);

	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');
	$tabthreadnode=db_prefix_table('thread_node');
	$tabnodelocale=db_prefix_table('node_locale');

	$sql="SELECT DISTINCT t.tag_id, t.name AS tag_name FROM $tabtag t JOIN $tabtagindex ti ON ti.tag_id=t.tag_id JOIN $tabthreadnode tn ON tn.thread_id=$cloud_id AND tn.node_id=ti.node_id WHERE t.locale=$sqllang";
	$r = db_query($sql);

	if (!$r) {
		return false;
	}

	$alltags=array();
	foreach ($r as $row) {
		$alltags[]=$row['tag_name'];
	}

	$wordlist = array_slice($wordlist, 0, 10);	// limit to 10 different tags

	$taglist=array();
	foreach ($wordlist as $w) {
		$r=wmatch($w, $alltags, $dlimit, $closest);
		if ($r) {
			$taglist=array_merge($taglist, $r);
		}
	}

	return $taglist;
}

function cloud_list_node_tags($lang, $node_id) {
	$sqllang=db_sql_arg($lang, false);

	// list tag_id, tag_name ordered by tag_name for all the tags which are in lang and associated to node_id
	$tabtag=db_prefix_table('tag');
	$tabtagindex=db_prefix_table('tag_index');

	$sql = "SELECT t.tag_id, t.name AS tag_name FROM $tabtagindex ti JOIN $tabtag t ON t.tag_id=ti.tag_id AND t.locale=$sqllang WHERE ti.node_id=$node_id ORDER BY tag_name";

	$r = db_query($sql);

	return $r;
}

function cloud_tag_node($lang, $node_id, $s) {
	// build a list with all the different tags
	preg_match_all('/(\S+)/', $s, $r);
	$newtags = array_unique($r[0]);

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

	$sql="DELETE t FROM $tabtag t WHERE t.tag_id IN (SELECT t1.tag_id FROM $tabtagindex t1 JOIN $tabtagindex t2 ON t2.tag_id=t1.tag_id WHERE t1.node_id=$node_id GROUP BY t1.tag_id HAVING COUNT(t1.tag_id) = 1)";

	$r = db_delete($sql);

	if (!$r) {
		return false;
	}

	// dissociate the tags of the node
	$sql="DELETE FROM $tabtagindex WHERE node_id=$node_id";

	$r = db_delete($sql);

	return $r;
}

