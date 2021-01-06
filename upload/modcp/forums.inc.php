<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: forums.inc.php 13172 2008-03-28 07:30:43Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$forumupdate = $listupdate = false;

$op = !in_array($op , array('editforum', 'recommend')) ? 'editforum' : $op;

if($fid && $forum['ismoderator']) {

	if($op == 'editforum') {

		$alloweditrules = $adminid == 1 || $forum['alloweditrules'] ? true : false;

		if(submitcheck('editsubmit')) {

			$forumupdate = true;
			$forum['description'] = dhtmlspecialchars($descnew);
			$forum['rules'] = $alloweditrules ? dhtmlspecialchars($rulesnew) : addslashes($forum['rules']);
			$db->query("UPDATE {$tablepre}forumfields SET description='$forum[description]', rules='$forum[rules]' WHERE fid='$fid'");
		}

	} elseif($op == 'recommend') {

		$useradd = $adminid == 3 ? "AND moderatorid='$discuz_uid'" : '';

		$ordernew = !empty($ordernew) && is_array($ordernew) ? $ordernew : array();

		if(submitcheck('editsubmit')) {
			if($ids = implodeids($delete)) {
				$listupdate = true;
				$db->query("DELETE FROM {$tablepre}forumrecommend WHERE fid='$fid' AND tid IN($ids) $useradd");
			}
		}

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$threadcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}forumrecommend WHERE fid='$fid' $useradd");
		$multipage = multi($threadcount, $tpp, $page, "$cpscript?action=$action&fid=$fid&page=$page");

		$threadlist = array();
		$query = $db->query("SELECT f.*, m.username as moderator
				FROM {$tablepre}forumrecommend f
				LEFT JOIN {$tablepre}members m ON f.moderatorid=m.uid
				WHERE f.fid='$fid' $useradd LIMIT $start_limit,$tpp");
		while($thread = $db->fetch_array($query)) {
			$thread['author'] =$thread['authorid'] ? "<a href=\"space.php?uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>" : 'Guest';
			$thread['moderator'] = $thread['moderator'] ? "<a href=\"space.php?uid=$thread[moderatorid]\" target=\"_blank\">$thread[moderator]</a>" : 'System';
			$thread['expiration'] = $thread['expiration'] ? gmdate("$dateformat $timeformat", $thread['expiration'] + ($timeoffset * 3600)) : '';
			if(isset($ordernew[$thread['tid']]) && $ordernew[$thread['tid']] != $thread['displayorder']) {
				$listupdate = true;
				$thread['displayorder'] = intval($ordernew[$thread['tid']]);
				$db->query("UPDATE {$tablepre}forumrecommend SET displayorder='$thread[displayorder]' WHERE fid='$fid' AND tid='$thread[tid]' $useradd", "UNBUFFERED");
			}
			$threadlist[]  = $thread;
		}

	}
}