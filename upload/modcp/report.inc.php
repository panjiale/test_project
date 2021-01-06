<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: report.inc.php 12932 2008-03-17 07:01:21Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$reportlist = $logids = array();

if(empty($fid) || $forum['type'] == 'group' || !$forum['ismoderator'] ) {
	return false;
}

if((submitcheck('deletesubmit') || submitcheck('marksubmit')) && $logids = implodeids($ids)) {

	if($op == 'delete') {
		$db->query("DELETE FROM {$tablepre}reportlog WHERE id IN ($logids) AND fid='$fid'", 'UNBUFFERED');
	}

	if($op == 'mark') {
		$db->query("UPDATE {$tablepre}reportlog SET status=0 WHERE id IN ($logids) AND fid='$fid'", 'UNBUFFERED');
	}

	if($forum['modworks'] && !$db->result_first("SELECT COUNT(*) FROM {$tablepre}reportlog WHERE fid='$fid' AND status=1")) {
		$db->query("UPDATE {$tablepre}forums SET modworks='0' WHERE fid='$fid'", 'UNBUFFERED');
	}

}

$page = max(1, intval($page));
$ppp = 10;
$reportlist = array('pagelink' => '', 'data' => array());

if($num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}reportlog WHERE fid='$fid'")) {

	$page = $page > ceil($num / $ppp) ? ceil($num / $ppp) : $page;
	$start_limit = ($page - 1) * $ppp;
	$reportlist['pagelink'] = multi($num, $ppp, $page, "modcp.php?fid=$fid&action=report");

	$query = $db->query("SELECT r.*, p.tid, p.message, p.author, p.authorid, t.subject FROM {$tablepre}reportlog r
				LEFT JOIN {$tablepre}posts p ON p.pid=r.pid
				LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
				WHERE r.fid='$fid' ORDER BY r.dateline DESC LIMIT $start_limit, $ppp");
	while($report = $db->fetch_array($query)) {
		$report['dateline'] = gmdate("$dateformat $timeformat", $report['dateline'] + $timeoffset * 3600);
		$report['subject'] = cutstr($report['subject'], 30);
		$report['author'] = $report['author'] != '' ? "<a href=\"space.php?uid=$report[authorid]\" target=\"_blank\">$report[author]</a>" : 'Guest';
		$report['username'] = "<a href=\"space.php?uid=$report[uid]\" target=\"_blank\">$report[username]</a>";
		$reportlist['data'][] = $report;
	}
}

?>