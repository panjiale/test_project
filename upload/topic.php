<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: topic.php 12679 2008-03-05 09:44:29Z liuqiang $
*/

require_once './include/common.inc.php';

$randnum = !empty($qihoo['relate']['webnum']) ? rand(1, 1000) : '';
$statsdata = $statsdata ? dhtmlspecialchars($statsdata) : '';

if($url && $randnum) {

	$url = dhtmlspecialchars($url);
	$md5 = dhtmlspecialchars($md5);
	$fid = substr($statsdata, 0, strpos($statsdata, '||'));

} else {

	if(empty($keyword)) {
		showmessage('undefined_action');
	}
	
	$tpp = intval($tpp);
	$page = max(1, intval($page));
	$start = ($page - 1) * $tpp;
	
	$site = site();
	$length = intval($length);
	$stype = empty($stype) ? 0 : 'title';
	$relate = in_array($relate, array('score', 'pdate', 'rdate')) ? $relate : 'score';
	
	$keyword = dhtmlspecialchars(stripslashes($keyword));
	$topic = $topic ? dhtmlspecialchars(stripslashes($topic)) : $keyword;

}

include template('topic');

?>