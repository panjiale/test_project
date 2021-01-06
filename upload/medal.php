<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: medal.php 13130 2008-03-26 07:18:35Z monkey $
*/

require_once './include/common.inc.php';

if(!$discuz_uid) {
	showmessage('not_loggedin', NULL, 'HALTED');
}

if(empty($action)) {

	$medallist = array();
	$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available='1' ORDER BY displayorder");
	while($medal = $db->fetch_array($query)) {
		$medal['permission'] = formulaperm($medal['permission'], 2);
		$medallist[] = $medal;
	}

} elseif($action == 'log') {

	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_medals.php';

	$logstotalnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}medallog WHERE uid='$discuz_uid'");
	$multipage = multi($logstotalnum, $tpp, $page, "medal.php?action=log");

	$query = $db->query("SELECT me.*, m.image FROM {$tablepre}medallog me
		LEFT JOIN {$tablepre}medals m USING (medalid)
		WHERE me.uid='$discuz_uid' ORDER BY me.dateline DESC LIMIT $start_limit,$tpp");
	$medallogs = array();
	while($medallog = $db->fetch_array($query)) {
		$medallog['name'] = $_DCACHE['medals'][$medallog['medalid']]['name'];
		$medallog['dateline'] = gmdate("$dateformat $timeformat", $medallog['dateline'] + $timeoffset * 3600);
		$medallog['expiration'] = !empty($medallog['expiration']) ? gmdate("$dateformat $timeformat", $medallog['expiration'] + $timeoffset * 3600) : '';
		$medallogs[] = $medallog;
	}

} elseif($action == 'apply') {
	$medalid = intval($medalid);
	$formulamessage = '';
	$medal = $db->fetch_first("SELECT * FROM {$tablepre}medals WHERE medalid='$medalid'");
	if(!$medal['type']) {
		showmessage('medal_required_invalid');
	}
	formulaperm($medal['permission'], 1) && $medal['permission'] = formulaperm($medal['permission'], 2);

	if(submitcheck('medalsubmit')) {
		$medaldetail = $db->fetch_first("SELECT medalid FROM {$tablepre}medallog WHERE uid='$discuz_uid' AND medalid='$medalid' AND type!='3'");
		if($medaldetail['medalid']) {
			showmessage('medal_apply_existence', 'medal.php');
		} else {
			$expiration = empty($medal['expiration'])? 0 : $timestamp + $medal['expiration'] * 86400;
			$db->query("INSERT INTO {$tablepre}medallog (uid, medalid, type, dateline, expiration, status) VALUES ('$discuz_uid', '$medalid', '2', '$timestamp', '$expiration', '0')");
		}
		showmessage('medal_apply_succeed', 'medal.php');
	}
} else {
	showmessage('undefined_action', NULL, 'HALTED');
}

include template('medal');
?>