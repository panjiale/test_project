<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: home.inc.php 13017 2008-03-23 13:09:58Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


if($op == 'addnote' && submitcheck('submit')) {
	$newaccess[$adminid] = 1;
	$newaccess = bindec(intval($newaccess[1]).intval($newaccess[2]).intval($newaccess[3]));
	$newexpiration = $timestamp + (intval($newexpiration) > 0 ? intval($newexpiration) : 30) * 86400;
	$newmessage = nl2br(dhtmlspecialchars(trim($newmessage)));
	if($newmessage != '') {
		$db->query("INSERT INTO {$tablepre}adminnotes (admin, access, adminid, dateline, expiration, message)
			VALUES ('$discuz_user', '$newaccess', '$adminid', '$timestamp', '$newexpiration', '$newmessage')");
	}
}

if($op == 'delete' && submitcheck('submit')) {
	if(is_array($delete) && $deleteids = implodeids($delete)) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id IN($deleteids) AND ($adminid=1 OR admin='$discuz_user')");
	}
}

switch($adminid) {
	case 1: $access = '1,2,3,4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}

$notelist = array();
$query = $db->query("SELECT * FROM {$tablepre}adminnotes WHERE access IN ($access) ORDER BY dateline DESC");
while($note = $db->fetch_array($query)) {
	if($note['expiration'] < $timestamp) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id='$note[id]'");
	} else {
		$note['expiration'] = ceil(($note['expiration'] - $note['dateline']) / 86400);
		$note['dateline'] = gmdate("$dateformat $timeformat", $note['dateline'] + $timeoffset * 3600);
		$note['checkbox'] = '<input class="checkbox" type="checkbox" name="delete[]" '.($note['admin'] == $discuz_userss || $adminid == 1 ? "value=\"$note[id]\"" : 'disabled').'>';
		$note['admin'] = '<a href="space.php?username='.rawurlencode($note['admin']).'" target="_blank">'.$note['admin'].'</a>';
		$notelist[] = $note;
	}
}

$reportnum = $modpostnum =$modthreadnum = $modforumnum = 0;
$modforumnum = count($modforums['list']);
if($modforumnum) {
	$reportnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}reportlog WHERE fid IN($modforums[fids]) AND status='1'");
	$modpostnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE invisible='-2' AND first='0' and fid IN($modforums[fids])");
	$modthreadnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE fid IN($modforums[fids]) AND displayorder='-2'");
}
