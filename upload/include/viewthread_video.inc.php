<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_video.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'./api/video.php';

$videolist = array();
$vautoplay = $videosource = '';
$query = $db->query("SELECT vid, vtitle, vautoplay, vtime, vview, vthumb FROM {$tablepre}videos WHERE tid='$tid' ORDER BY displayorder");
while($videoinfo = $db->fetch_array($query)) {
	if($videoinfo['vthumb']) {
		$videosource = 1;
	} else {
		$videoinfo['vthumb'] =  VideoClient_Util::getThumbUrl($videoinfo['vid'], 'small');
	}
	$videoinfo['vtime'] = $videoinfo['vtime'] ? sprintf("%02d", intval($videoinfo['vtime'] / 60)).':'.sprintf("%02d", intval($videoinfo['vtime'] % 60)) : '';
	$videolist[] = $videoinfo;
}

if(!empty($videolist)) {
	$videocount = count($videolist);
	if(!empty($vid)) {
		foreach($videolist as $video) {
			if($video['vid'] == $vid) {
				$vid = dhtmlspecialchars($video['vid']);
				$vautoplay = $autoplay ? intval($autoplay) :$video['vautoplay'];
				break;
			}
		}
	} else {
		$vid = $videolist[0]['vid'];
		$vautoplay = $autoplay ? intval($autoplay) : $videolist[0]['vautoplay'];
	}

	if(!$videosource) {
		$client = new VideoClient_Util($appid, $siteid, $sitekey);
		$videoshow = $client->createPlayer(array(), array('ivid' => $vid, 'site' => $boardurl, 'auto' => $vautoplay));
	} else {
		$playurl = "http://union.bokecc.com/flash/discuz2/player.swf?siteid=$vsiteid&vid=$vid&tid=$tid&pid=$pid&autoStart=$vautoplay&referer=".urlencode($boardurl."viewthread.php?tid=$tid");
		$videoshow = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="object_flash_player" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" height="373" width="438">'.
			'<param name="movie" value='.$playurl.'>'.
			'<param name="quality" value="high">'.
			'<param name="allowScriptAccess" value="always">'.
			'<param name="allowFullScreen" value="true">'.
			'<embed src='.$playurl.' allowScriptAccess="always" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowfullscreen="true" height="373" width="438"></object>';
	}

	$query = $db->query("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
		m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
		m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, mf.nickname, mf.site,
		mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.customstatus, mf.spacename $fieldsadd
		FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		WHERE p.tid='$tid' AND first=1 LIMIT 1");

	$post = $db->fetch_array($query);
	$pid = $post['pid'];
	$postlist[$post['pid']] = viewthread_procpost($post);

	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $postlist, $showimages);
	}

	viewthread_parsetags();

	$post = $postlist[$post['pid']];

	include template('viewthread_video');
	exit;
} else {
	$db->query("UPDATE {$tablepre}threads SET special=0 WHERE tid='$tid'", 'UNBUFFERED');
	dheader("Location: {$boardurl}viewthread.php?tid=$tid");
}

?>