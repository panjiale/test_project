<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: request.func.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

function parse_request($requestdata, $cachefile, $mode, $specialfid = 0, $key = '') {
	global $_DCACHE;

	$requesttemplate = '';
	$requestcachelife = (isset($requestdata['cachelife']) && $requestdata['cachelife'] != '') ? $requestdata['cachelife'] : (isset($_DCACHE['settings']['jscachelife']) ? $_DCACHE['settings']['jscachelife'] : 1800);
	!empty($requestdata['jstemplate']) && $requesttemplate = stripslashes($requestdata['jstemplate']);
	get_magic_quotes_gpc() && $requesttemplate = stripslashes($requesttemplate);
	$mode && $requesttemplate = preg_replace("/\r\n|\n|\r/", '\n', $requesttemplate);
	$nocache = !empty($requestdata['nocache']) ? 1 : 0;
	$mode && $requestcharset = $requestdata['jscharset'];

	if(!in_array($requestdata['function'], array('custom', 'side'))) {
		$requesttemplatebody = '';
		if(preg_match("/\[node\](.+?)\[\/node\]/is", $requesttemplate, $node)) {
			$requesttemplatebody = $requesttemplate;
			$requesttemplate = $node[1];
		}

		$parsedata = updaterequest($requestdata, $requesttemplatebody, $requesttemplate, $specialfid, $mode, $key);
		if($mode) {
			$datalist = $writedata = jsprocdata($parsedata, $requestcharset);
		} else {
			$datalist = $writedata = $parsedata;
		}
		$writedata = addcslashes($writedata, '\\\'');
	} else {
		$customnocache = $nocache;
		$requestcachelife = (isset($requestdata['cachelife']) && $requestdata['cachelife'] != '') ? $requestdata['cachelife'] : (isset($_DCACHE['settings']['jscachelife']) ? $_DCACHE['settings']['jscachelife'] : 1800);
		$writedata = preg_match_all("/\[module\](.+?)\[\/module\]/is", $requesttemplate, $modulelist);
		$modulelist = array_unique($modulelist[1]);
		$writedata = $requesttemplate = str_replace('\"', '"', $requesttemplate);

		$nocache = TRUE;
		foreach($modulelist as $key) {
			$find = "/\[module\]".preg_quote($key)."\[\/module\]/is";
			if(!empty($_DCACHE['request'][$key]['url'])) {
				parse_str($_DCACHE['request'][$key]['url'], $requestdata);
				$function = isset($requestdata['function']) ? $requestdata['function'] : NULL;
				$requesttemplate = $requestdata['jstemplate'];
				!empty($requesttemplate) && $requesttemplate = stripslashes($requesttemplate);
				get_magic_quotes_gpc() && $requesttemplate = stripslashes($requesttemplate);
				$mode && $requesttemplate = preg_replace("/\r\n|\n|\r/", '\n', $requesttemplate);
				$requesttemplate = str_replace(array('\"', '\\\''), array('"', '\''), $requesttemplate);

				$requesttemplatebody = '';
				if(preg_match("/\[node\](.+?)\[\/node\]/is", $requesttemplate, $node)) {
					$requesttemplatebody = $requesttemplate;
					$requesttemplate = $node[1];
				}
				$parsedata = updaterequest($requestdata, $requesttemplatebody, $requesttemplate, $specialfid, $mode, $key);
				$writedata = preg_replace($find, $parsedata, $writedata);
			}
		}
		$nocache = $customnocache;

		if($mode) {
			$datalist = $writedata = jsprocdata($writedata, $requestcharset);
		} else {
			$datalist = $writedata;
		}
		$writedata = addcslashes($writedata, '\\\'');
	}

	if(!$nocache) {
		$writedata = "\$datalist = '".$writedata."';";
		writetorequestcache($cachefile, $requestcachelife, $writedata);
	}
	if($mode) {
		return $datalist;
	} else {
		$datalist = str_replace(array('\'', '{$GLOBALS[TID]}'), array('\\\'', '\'.$GLOBALS[\'tid\'].\''), $datalist);
		return eval("return '".$datalist."';");
	}
}

function updaterequest($requestdata, $requesttemplatebody, $requesttemplate, $specialfid, $mode, $key = '') {
	global $db, $tablepre, $timestamp, $boardurl, $dateformat, $timeformat, $rewritestatus, $uc, $_DCACHE;

	$function = $requestdata['function'];
	$fids = isset($requestdata['fids']) ? $requestdata['fids'] : NULL;
	$startrow = isset($requestdata['startrow']) ? intval($requestdata['startrow']) : 0;
	$items = isset($requestdata['items']) ? intval($requestdata['items']) : 10;
	$digest = isset($requestdata['digest']) ? intval($requestdata['digest']) : 0;
	$stick = isset($requestdata['stick']) ? intval($requestdata['stick']) : 0;
	$newwindow = isset($requestdata['newwindow']) ? $requestdata['newwindow'] : 1;
	$LinkTarget = $newwindow == 1 ? " target='_blank'" : ($newwindow == 2 ? " target='main'" : NULL);
	$sidestatus = !empty($requestdata['sidestatus']) ? 1 : 0;

	if($function == 'threads') {
		$orderby = isset($requestdata['orderby']) ? (in_array($requestdata['orderby'],array('lastpost','dateline','replies','views','hourviews','todayviews','weekviews','monthviews')) ? $requestdata['orderby'] : 'lastpost') : 'lastpost';
		$hours	 = isset($requestdata['hours']) ? intval($requestdata['hours']) : 0;
		$highlight = isset($requestdata['highlight']) ? $requestdata['highlight'] : 0;
		$picpre = isset($requestdata['picpre']) ? urldecode($requestdata['picpre']) : NULL;
		$maxlength = !empty($requestdata['maxlength']) ? intval($requestdata['maxlength']) : 50;
		$fnamelength = isset($requestdata['fnamelength']) ? intval($requestdata['fnamelength']) : 0;
		$recommend = !empty($requestdata['recommend']) ? 1 : 0;
		$tids = isset($requestdata['tids']) ? $requestdata['tids'] : NULL;
		$keyword = !empty($requestdata['keyword']) ? $requestdata['keyword'] : NULL;
		$typeids = isset($requestdata['typeids']) ? $requestdata['typeids'] : NULL;
		$special = isset($requestdata['special']) ? intval($requestdata['special']) : 0;
		$rewardstatus = isset($requestdata['rewardstatus']) ? intval($requestdata['rewardstatus']) : 0;
		$threadtype = isset($requestdata['threadtype']) ? intval($requestdata['threadtype']) : 0;
		$tag = !empty($requestdata['tag']) ? trim($requestdata['tag']) : NULL;
		$messagelength = !empty($requestdata['messagelength']) ? intval($requestdata['messagelength']) : 255;

		require DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

		$datalist = array();
		$threadtypeids = array();
		if($keyword) {
			if(preg_match("(AND|\+|&|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
				$andor = ' AND ';
				$keywordsrch = '1';
				$keyword = preg_replace("/( AND |&| )/is", "+", $keyword);
			} else {
				$andor = ' OR ';
				$keywordsrch = '0';
				$keyword = preg_replace("/( OR |\|)/is", "+", $keyword);
			}
			$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
			foreach(explode('+', $keyword) as $text) {
				$text = trim($text);
				if($text) {
					$keywordsrch .= $andor;
					$keywordsrch .= "t.subject LIKE '%$text%'";
				}
			}
			$keyword = " AND ($keywordsrch)";
		} else {
			$keyword = '';
		}
		$sql = ($specialfid && $sidestatus ? ' AND t.fid = '.$specialfid : ($fids ? ' AND t.fid IN (\''.str_replace('_', '\',\'', $fids).'\')' : ''))
			.$keyword
			.($tids ? ' AND t.tid IN (\''.str_replace('_', '\',\'', $tids).'\')' : '')
			.($typeids ? ' AND t.typeid IN (\''.str_replace('_', '\',\'', $typeids).'\')' : '')
			.(($special >= 0 && $special < 127) ? threadrange($special, 'special', 7) : '')
			.((($special & 8) && $rewardstatus) ? ($rewardstatus == 1 ? ' AND t.price < 0' : ' AND t.price > 0') : '')
			.(($digest > 0 && $digest < 15) ? threadrange($digest, 'digest') : '')
			.(($stick > 0 && $stick < 15) ? threadrange($stick, 'displayorder') : '');
		if(in_array($orderby, array('hourviews','todayviews','weekviews','monthviews'))) {
			$historytime = 0;
			switch($orderby) {
				case 'hourviews':
					$historytime = $timestamp - 3600 * $hours;
				break;
				case 'todayviews':
					$historytime = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp));
				break;
				case 'weekviews':
					$week = gmdate('w', $timestamp) - 1;
					$week = $week != -1 ? $week : 6;
					$historytime = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp) - $week, date('Y', $timestamp));
				break;
				case 'monthviews':
					$historytime = mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp));
				break;
			}
			$sql .= ' AND t.dateline>='.$historytime;
			$orderby = 'views';
		}
		$sqlfrom = strexists($requesttemplate, '{message}') ? ",p.message FROM `{$tablepre}threads` t LEFT JOIN `{$tablepre}posts` p ON p.tid=t.tid AND p.first='1'" : "FROM `{$tablepre}threads` t";
		if(strexists($requesttemplate, '{imgattach}')) {
			$sqlfrom = ",a.remote,a.attachment,a.thumb $sqlfrom INNER JOIN `{$tablepre}attachments` a ON a.tid=t.tid";
			$sql .= " AND a.isimage='1' AND a.readperm='0' AND a.price='0'";
			$attachurl = $_DCACHE['settings']['attachurl'];
			$attachurl = preg_match("/^((https?|ftps?):\/\/|www\.)/i", $attachurl) ? $attachurl : $boardurl.$attachurl;
		}
		if($recommend) {
			$sqlfrom .= " INNER JOIN `{$tablepre}forumrecommend` fc ON fc.tid=t.tid";
		}

		if($tag) {
			$tags = explode(' ', $tag);
			foreach($tags as $tagk => $tagv) {
				if(!preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $tagv)) {
					unset($tags[$tagk]);
				}
			}
			if($tags = implode("','", $tags)) {
				$sqlfrom .= " INNER JOIN `{$tablepre}threadtags` tag ON tag.tid=t.tid AND tag.tagname IN ('$tags')";
			}
		}
		$query = $db->query("SELECT t.tid,t.fid,t.readperm,t.author,t.authorid,t.subject,t.dateline,t.lastpost,t.lastposter,t.views,t.replies,t.highlight,t.digest,t.typeid
			$sqlfrom WHERE t.readperm='0'
			$sql
			AND t.displayorder>='0'
			AND t.fid>'0'
			$attachadd
			ORDER BY t.$orderby DESC
			LIMIT $startrow,$items;"
			);
		while($data = $db->fetch_array($query))	{
			$datalist[$data['tid']]['fid'] = $data['fid'];
			$datalist[$data['tid']]['fname'] = isset($_DCACHE['forums'][$data['fid']]['name']) ? str_replace('\'', '&nbsp;',addslashes($_DCACHE['forums'][$data['fid']]['name'])) : NULL;
			$datalist[$data['tid']]['fnamelength'] = strlen($datalist[$data['tid']]['fname']);
			$datalist[$data['tid']]['subject'] = isset($data['subject']) ? str_replace('\'', '&nbsp;',addslashes($data['subject'])) : NULL;
			$datalist[$data['tid']]['dateline'] = gmdate("$dateformat $timeformat",$data['dateline'] + $_DCACHE['settings']['timeoffset'] * 3600);
			$datalist[$data['tid']]['lastpost'] = gmdate("$dateformat $timeformat",$data['lastpost'] + $_DCACHE['settings']['timeoffset'] * 3600);
			$datalist[$data['tid']]['lastposter'] = $data['lastposter'];
			$datalist[$data['tid']]['authorid'] = $data['authorid'];
			$datalist[$data['tid']]['views'] = $data['views'];
			$datalist[$data['tid']]['replies'] = $data['replies'];
			$datalist[$data['tid']]['highlight'] = $data['highlight'];
			$datalist[$data['tid']]['message'] = str_replace(array('\'',"\n","\r"), array('&nbsp;','',''), addslashes(cutstr(dhtmlspecialchars(preg_replace("/(\[.+\])/s", '', strip_tags(nl2br($data['message'])))), $messagelength)));
			$datalist[$data['tid']]['imgattach'] = ($data['remote'] ? $_DCACHE['settings']['ftp']['attachurl'] : $attachurl)."/$data[attachment]".($_DCACHE['settings']['thumbstatus'] && $data['thumb'] ? '.thumb.jpg' : '');
			if($data['author']) {
				$datalist[$data['tid']]['author'] = $data['author'];
			} else {
				$datalist[$data['tid']]['author'] = 'Anonymous';
			}
			if($data['lastposter']) {
				$datalist[$data['tid']]['lastposter'] = $data['lastposter'];
			} else {
				$datalist[$data['tid']]['lastposter'] = 'Anonymous';
			}
			$datalist[$data['tid']]['typeid'] = $data['typeid'];
			$threadtypeids[] = $data['typeid'];
		}
		if($threadtype && $threadtypeids) {
			$typelist = array();
			$query = $db->query("SELECT typeid, name FROM {$tablepre}threadtypes WHERE typeid IN ('".implode('\',\'', $threadtypeids)."')");
			while($typearray = $db->fetch_array($query)) {
				$typelist[$typearray['typeid']] = $typearray['name'];
			}
			foreach($datalist AS $tid=>$value) {
				if($value['typeid'] && isset($typelist[$value['typeid']])) {
					$datalist[$tid]['subject'] = '['.$typelist[$value['typeid']].']'.$value['subject'];
				}
			}
		}
		$writedata = '';
		if(is_array($datalist)) {
			$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
			$prefix	= $picpre ? "<img src='$picpre' border='0' align='absmiddle'>" : NULL;
			$requesttemplate = !$requesttemplate ? '{prefix} {subject}<br />' : $requesttemplate;
			$order = 1;
			foreach($datalist AS $tid=>$value) {
				$SubjectStyles = '';
				if($highlight && $value['highlight']) {
					$string = sprintf('%02d', $value['highlight']);
					$stylestr = sprintf('%03b', $string[0]);
					$SubjectStyles .= " style='";
					$SubjectStyles .= $stylestr[0] ? 'font-weight: bold;' : NULL;
					$SubjectStyles .= $stylestr[1] ? 'font-style: italic;' : NULL;
					$SubjectStyles .= $stylestr[2] ? 'text-decoration: underline;' : NULL;
					$SubjectStyles .= $string[1] ? 'color: '.$colorarray[$string[1]] : NULL;
					$SubjectStyles .= "'";
				}
				$replace['{link}'] = $boardurl."viewthread.php?tid=$tid";
				$replace['{subject_nolink}'] = cutstr($value['subject'],($fnamelength ? ($maxlength - $value['fnamelength']) : $maxlength));
				$replace['{subject_full}'] = $value['subject'];
				$replace['{prefix}'] = $prefix;
				$replace['{forum}'] = "<a href='".$boardurl."forumdisplay.php?fid=$value[fid]'$LinkTarget>$value[fname]</a>";
				$replace['{dateline}'] = $value['dateline'];
				$replace['{subject}'] = "<a href='".$boardurl."viewthread.php?tid=$tid' title='$value[subject]'$SubjectStyles$LinkTarget>".$replace['{subject_nolink}']."</a>";
				$replace['{message}'] = $value['message'];
				$replace['{author}'] = "<a href='".$boardurl."space.php?uid=$value[authorid]'$LinkTarget>$value[author]</a>";
				$replace['{author_nolink}'] = $value['author'];
				$replace['{lastposter}'] = "<a href='".$boardurl."space.php?username=".rawurlencode($value['lastposter'])."'$LinkTarget>$value[lastposter]</a>";
				$replace['{lastposter_nolink}'] = $value['lastposter'];
				$replace['{lastpost}'] = $value['lastpost'];
				$replace['{views}'] = $value['views'];
				$replace['{replies}'] = $value['replies'];
				$replace['{imgattach}'] = $value['imgattach'];
				$replace['{order}'] = $order++;
				$writedata .= nodereplace($replace, $requesttemplate);
			}
		}
	} elseif($function == 'forums') {
		$fups	 = isset($requestdata['fups']) ? $requestdata['fups'] : NULL;
		$orderby = isset($requestdata['orderby']) ? (in_array($requestdata['orderby'],array('displayorder','threads','posts')) ? $requestdata['orderby'] : 'displayorder') : 'displayorder';
		$datalist = array();
		$query = $db->query("SELECT `fid`,`fup`,`name`,`status`,`threads`,`posts`,`todayposts`,`displayorder`,`type`
			FROM `{$tablepre}forums`
			WHERE `type`!='group'
			".($fups ? "AND `fup` IN ('".str_replace('_', '\',\'', $fups)."') " : "")."
			AND `status`='1'
			ORDER BY ".($orderby == 'displayorder' ? " `displayorder` ASC " : " `$orderby` DESC")."
			LIMIT $startrow,".($items > 0 ? $items : 65535).";"
			);
		while($data = $db->fetch_array($query)) {
			$datalist[$data['fid']]['name'] = str_replace('\'', '&nbsp;',addslashes($data['name']));
			$datalist[$data['fid']]['threads'] = $data['threads'];
			$datalist[$data['fid']]['posts'] = $data['posts'];
			$datalist[$data['fid']]['todayposts'] = $data['todayposts'];
		}
		$writedata = '';
		if(is_array($datalist)) {
			$requesttemplate = !$requesttemplate ? '{forumname}<br />' : $requesttemplate;
			$order = 1;
			foreach($datalist AS $fid=>$value) {
				$replace['{link}'] = $boardurl."forumdisplay.php?fid=$fid";
				$replace['{forumname_nolink}'] = $value['name'];
				$replace['{forumname}'] = "<a href='".$boardurl."forumdisplay.php?fid=$fid'$LinkTarget>$value[name]</a>";
				$replace['{threads}'] = $value['threads'];
				$replace['{posts}'] = $value['posts'];
				$replace['{todayposts}'] = $value['todayposts'];
				$replace['{order}'] = $order++;
				$writedata .= nodereplace($replace, $requesttemplate);
			}
		}
	} elseif($function == 'memberrank') {
		$orderby = isset($requestdata['orderby']) ? (in_array($requestdata['orderby'],array('credits','extcredits','posts','digestposts','regdate','hourposts','todayposts','weekposts','monthposts')) ? $requestdata['orderby'] : 'credits') : 'credits';
		$hours	 = isset($requestdata['hours']) ? intval($requestdata['hours']) : 0;
		$datalist = array();
		switch($orderby) {
			case 'credits':
				$sql = "SELECT m.`username`,m.`uid`,m.`credits` FROM `{$tablepre}members` m ORDER BY m.`credits` DESC";
			break;
			case 'extcredits':
				$requestdata['extcredit'] = intval($requestdata['extcredit']);
				$sql = "SELECT m.`username`,m.`uid`,m.`extcredits$requestdata[extcredit]` FROM `{$tablepre}members` m ORDER BY m.`extcredits$requestdata[extcredit]` DESC";
			break;
			case 'posts':
				$sql = "SELECT m.`username`,m.`uid`,m.`posts` FROM `{$tablepre}members` m ORDER BY m.`posts` DESC";
			break;
			case 'digestposts':
				$sql = "SELECT m.`username`,m.`uid`,m.`digestposts` FROM `{$tablepre}members` m ORDER BY m.`digestposts` DESC";
			break;
			case 'regdate':
				$sql = "SELECT m.`username`,m.`uid`,m.`regdate` FROM `{$tablepre}members` m ORDER BY m.`regdate` DESC";
			break;
			case 'hourposts';
				$historytime = $timestamp - 3600 * intval($hours);
				$sql = "SELECT DISTINCT(p.author) AS username,p.authorid AS uid,COUNT(p.pid) AS postnum FROM `{$tablepre}posts` p WHERE p.`dateline`>=$historytime AND p.`authorid`!='0' GROUP BY p.`author` ORDER BY `postnum` DESC";
			break;
			case 'todayposts':
				$historytime = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp));
				$sql = "SELECT DISTINCT(p.author) AS username,p.authorid AS uid,COUNT(p.pid) AS postnum FROM `{$tablepre}posts` p WHERE p.`dateline`>=$historytime AND p.`authorid`!='0' GROUP BY p.`author` ORDER BY `postnum` DESC";
			break;
			case 'weekposts':
				$week = gmdate('w', $timestamp) - 1;
				$week = $week != -1 ? $week : 6;
				$historytime = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp) - $week, date('Y', $timestamp));
				$sql = "SELECT DISTINCT(p.author) AS username,p.authorid AS uid,COUNT(p.pid) AS postnum FROM `{$tablepre}posts` p LEFT JOIN `{$tablepre}memberfields` mf ON mf.`uid` = p.`authorid` WHERE p.`dateline`>=$historytime AND p.`authorid`!='0' GROUP BY p.`author` ORDER BY `postnum` DESC";
			break;
			case 'monthposts':
				$historytime = mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp));
				$sql = "SELECT DISTINCT(p.author) AS username,p.authorid AS uid,COUNT(p.pid) AS postnum FROM `{$tablepre}posts` p LEFT JOIN `{$tablepre}memberfields` mf ON mf.`uid` = p.`authorid` WHERE p.`dateline`>=$historytime AND p.`authorid`!='0' GROUP BY p.`author` ORDER BY `postnum` DESC";
			break;
		}
		$query = $db->query($sql." LIMIT $startrow,$items;");
		while($data = $db->fetch_array($query,MYSQL_NUM)) {
			$data[2] = $orderby == 'regdate' ? gmdate($dateformat,$data[2] + $_DCACHE['settings']['timeoffset'] * 3600) : $data[2];
			$datalist[] = $data;
		}
		$writedata = '';
		if(is_array($datalist)) {
			$requesttemplate = !$requesttemplate ? '{regdate} {member} {value}<br />' : $requesttemplate;
			$order = 1;
			foreach($datalist AS $value) {
				$replace['{regdate}'] = $replace['{value}'] = '';
				if($orderby == 'regdate') {
					$replace['{regdate}'] = $value[2];
				} else {
					$replace['{value}'] = $value[2];
				}
				$replace['{uid}'] = $value[1];
				$replace['{member}'] = "<a href='".$boardurl."space.php?uid=$value[1]'$LinkTarget>$value[0]</a>";
				$replace['{avatar}'] = "<a href='".$boardurl."space.php?uid=$value[1]'$LinkTarget><img src='".discuz_uc_avatar($value[1])."' border=0 alt='' /></a>";
				$replace['{avatarsmall}'] = "<a href='".$boardurl."space.php?uid=$value[1]'$LinkTarget><img src='".discuz_uc_avatar($value[1], 'small')."' border=0 alt='' /></a>";
				$replace['{avatarbig}'] = "<a href='".$boardurl."space.php?uid=$value[1]'$LinkTarget><img src='".discuz_uc_avatar($value[1], 'big')."' border=0 alt='' /></a>";
				$replace['{order}'] = $order++;
				$writedata .= nodereplace($replace, $requesttemplate);
			}
		}
	} elseif($function == 'stats') {
		$info = isset($requestdata['info']) ? $requestdata['info'] : NULL;
		if(is_array($info)) {
			$language = $info;
			$info_index = '';
			$statsinfo = array();
			$statsinfo['forums'] = $statsinfo['threads'] = $statsinfo['posts'] = 0;
			$query = $db->query("SELECT `status`,`threads`,`posts`
					FROM `{$tablepre}forums` WHERE
					`status`='1';
					");
			while($forumlist = $db->fetch_array($query)) {
				$statsinfo['forums']++;
				$statsinfo['threads'] += $forumlist['threads'];
				$statsinfo['posts'] += $forumlist['posts'];
			}
			unset($info['forums'],$info['threads'],$info['posts']);
			foreach($info AS $index=>$value) {
				if($index == 'members') {
					$sql = "SELECT COUNT(*) FROM `{$tablepre}members`;";
				} elseif($index == 'online') {
					$sql = "SELECT COUNT(*) FROM `{$tablepre}sessions`;";
				} elseif($index == 'onlinemembers') {
					$sql = "SELECT COUNT(*) FROM `{$tablepre}sessions` WHERE `uid`>'0';";
				}
				if($index == 'members' || $index == 'online' || $index == 'onlinemembers') {
					$statsinfo[$index] = $db->result_first($sql);
				}
			}
			unset($index, $value);
			$writedata = '';
			$requesttemplate = !$requesttemplate ? '{name} {value}<br />' : $requesttemplate;
			foreach($language AS $index=>$value) {
				$replace['{name}'] = $value;
				$replace['{value}'] = $statsinfo[$index];
				$writedata .= str_replace(array_keys($replace), $replace, $requesttemplate);
			}
		}
	} elseif($function == 'images') {
		$maxwidth = isset($requestdata['maxwidth']) ? $requestdata['maxwidth'] : 0;
		$maxheight = isset($requestdata['maxheight']) ? $requestdata['maxheight'] : 0;

		require DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
		$datalist = array();
		$sql = ($specialfid && $sidestatus ? ' AND t.fid = '.$specialfid : ($fids ? ' AND t.fid IN (\''.str_replace('_', '\',\'', $fids).'\')' : ''))
			.(($digest > 0 && $digest < 15) ? threadrange($digest, 'digest') : '');
		$imagesql = !empty($requestdata['isimage']) ? "AND `attach`.`isimage`='1'" : '';
		$methodsql = !empty($requestdata['threadmethod']) ? 'GROUP BY `attach`.`tid`' : '';
		$hours = isset($requestdata['hours']) ? intval($requestdata['hours']) : 0;
		$orderby = isset($requestdata['orderby']) ? (in_array($requestdata['orderby'],array('dateline','downloads','hourdownloads','todaydownloads','weekdownloads','monthdownloads')) ? $requestdata['orderby'] : 'dateline') : 'dateline';
		$orderbysql = '';
		switch($orderby) {
			case 'dateline':
				$orderbysql = "ORDER BY `attach`.`dateline` DESC";
			break;
			case 'downloads':
				$orderbysql = "ORDER BY `attach`.`downloads` DESC";
			break;
			case 'hourdownloads';
				$historytime = $timestamp - 3600 * intval($hours);
				$orderbysql = "AND `attach`.`dateline`>=$historytime ORDER BY `attach`.`downloads` DESC";
			break;
			case 'todaydownloads':
				$historytime = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp));
				$orderbysql = "AND `attach`.`dateline`>=$historytime ORDER BY `attach`.`downloads` DESC";
			break;
			case 'weekdownloads':
				$week = gmdate('w', $timestamp) - 1;
				$week = $week != -1 ? $week : 6;
				$historytime = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp) - $week, date('Y', $timestamp));
				$orderbysql = "AND `attach`.`dateline`>=$historytime ORDER BY `attach`.`downloads` DESC";
			break;
			case 'monthdownloads':
				$historytime = mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp));
				$orderbysql = "AND `attach`.`dateline`>=$historytime ORDER BY `attach`.`downloads` DESC";
			break;
		}
		$query = $db->query("SELECT attach.*,t.tid,t.fid,t.digest,t.author,t.subject,t.displayorder
			FROM `{$tablepre}attachments` attach
			LEFT JOIN `{$tablepre}threads` t
			ON `t`.`tid`=`attach`.`tid`
			WHERE `attach`.`readperm`='0' AND `attach`.`price`='0'
			AND `displayorder`>='0'
			$imagesql
			$sql
			$methodsql
			$orderbysql
			LIMIT $startrow,$items;"
			);
		$attachurl = $_DCACHE['settings']['attachurl'];
		$attachurl = preg_match("/^((https?|ftps?):\/\/|www\.)/i", $attachurl) ? $attachurl : $boardurl.$attachurl;
		$i = 0;
		while($data = $db->fetch_array($query)) {
			$key = $requestdata['threadmethod'] ? $data['tid'] : $i++;
			$datalist[$key]['threadlink'] = $boardurl."redirect.php?goto=findpost&ptid=$data[tid]&pid=$data[pid]";
			$datalist[$key]['imgfile'] = ($data['remote'] ? $_DCACHE['settings']['ftp']['attachurl'] : $attachurl)."/$data[attachment]".($_DCACHE['settings']['thumbstatus'] && $data['thumb'] ? '.thumb.jpg' : '');
			$datalist[$key]['file'] = $boardurl."attachment.php?aid=$data[aid]&k=".md5($data[aid].md5($_DCACHE[settings][authkey]).$timestamp)."&t=$timestamp";
			$datalist[$key]['subject'] = str_replace('\'', '&nbsp;',$data['subject']);
			$datalist[$key]['filename'] = str_replace('\'', '&nbsp;',$data['filename']);
			$datalist[$key]['author'] = addslashes($data['author']);
			$datalist[$key]['downloads'] = $data['downloads'];
			$datalist[$key]['author'] = $data['author'];
			$datalist[$key]['filesize'] = number_format($data['filesize']);
			$datalist[$key]['dateline'] = gmdate("$dateformat $timeformat",$data['dateline'] + $_DCACHE['settings']['timeoffset'] * 3600);
			$datalist[$key]['fname'] = isset($_DCACHE['forums'][$data['fid']]['name']) ? str_replace('\'', '&nbsp;',addslashes($_DCACHE['forums'][$data['fid']]['name'])) : NULL;
			$datalist[$key]['description'] = $data['description'] ? str_replace('\'', '&nbsp;',addslashes($data['description'])) : NULL;
		}
		$writedata = '';
		if(is_array($datalist)) {
			$imgsize = ($maxwidth ? " width='$maxwidth'" : NULL).($maxheight ? " height='$maxheight'" : NULL);
			$requesttemplate = !$requesttemplate ? '{file} ({filesize} Bytes)<br />' : $requesttemplate;
			$order = 1;
			foreach($datalist AS $value) {
				$replace['{link}'] = $value['threadlink'];
				$replace['{imgfile}'] = $value['imgfile'];
				$replace['{url}'] = $value['file'];
				$replace['{subject}'] = $value['subject'];
				$replace['{filesubject}'] = $value['filename'];
				$replace['{filedesc}'] = $value['description'];
				$replace['{author}'] = $value['author'];
				$replace['{dateline}'] = $value['dateline'];
				$replace['{downloads}'] = $value['downloads'];
				$replace['{filesize}'] = $value['filesize'];
				$replace['{file}'] = "<a href='$value[file]'$LinkTarget>$value[filename]</a>";
				$replace['{image}'] = "<a href='$value[threadlink]'$LinkTarget><img$imgsize src='$value[imgfile]' border='0'></a>";
				$replace['{order}'] = $order++;
				$writedata .= nodereplace($replace, $requesttemplate);
			}
		}
	} else {
		return;
	}
	$data = parsenode($writedata, $requesttemplatebody);
	if($rewritestatus) {
		$searcharray = $replacearray = array();
		if($GLOBALS['rewritestatus'] & 1) {
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."forumdisplay\.php\?fid\=(\d+)\'/";
			$replacearray[] = "<a href='{$boardurl}forum-\\1-1.html'";
		}
		if($GLOBALS['rewritestatus'] & 2) {
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."viewthread\.php\?tid\=(\d+)\'/";
			$replacearray[] = "<a href='{$boardurl}thread-\\1-1-1.html'";
		}
		if($GLOBALS['rewritestatus'] & 4) {
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."space\.php\?uid\=(\d+)\'/";
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."space\.php\?username\=([^&]+?)\'/";
			$replacearray[] = "<a href='{$boardurl}space-uid-\\1.html'";
			$replacearray[] = "<a href='{$boardurl}space-username-\\1.html'";
		}
		$data = preg_replace($searcharray, $replacearray, $data);
	}
	return $data;
}

function nodereplace($replace, $requesttemplate) {
	$return = $requesttemplate;
	if(preg_match("/\[show=(\d+)\].+?\[\/show\]/is", $requesttemplate, $show)) {
		if($show[1] == $replace['{order}']) {
			$return = preg_replace("/\[show=\d+\](.+?)\[\/show\]/is", '\\1', $return);
		} else {
			$return = preg_replace("/\[show=\d+\].+?\[\/show\]/is", '', $return);
		}
	}
	return str_replace(array_keys($replace), $replace, $return);
}


function parsenode($data, $requesttemplatebody) {
	if($requesttemplatebody) {
		$data = preg_replace("/\[node\](.+?)\[\/node\]/is", $data, $requesttemplatebody, 1);
		$data = preg_replace("/\[node\](.+?)\[\/node\]/is", '', $data);
	}
	return $data;
}

function threadrange($range, $field, $params = 4) {
	$range = intval($range);
	$range = sprintf("%0".$params."d", decbin($range));
	$range = "$range";
	$range_filed = '';
	for($i = 0; $i < $params - 1; $i ++) {
		$range_filed .= $range[$i] == 1 ? ($i + 1) : '';
	}
	$range_filed .= $range[$params - 1] == 1 ? 0 : '';
	$return = str_replace('_', '\',\'', substr(chunk_split($range_filed,1,"_"),0,-1));
	return $return != '' ? ' AND `'.$field.'` IN (\''.$return.'\')' : '';
}

function writetorequestcache($cachfile, $requestcachelife, $data='') {
	global $timestamp, $_DCACHE;
	if(!$fp = @fopen($cachfile, 'wb')) {
		return;
	}
	$fp = @fopen($cachfile, 'wb');
	$cachedata = "if(!defined('IN_DISCUZ')) exit('Access Denied');\n\$expiration = '".($timestamp + $requestcachelife)."';\n".$data."\n";
	@fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!".
			"\n//Created: ".date("M j, Y, G:i").
			"\n//Identify: ".md5(basename($cachfile).$cachedata.$_DCACHE['settings']['authkey'])."\n\n$cachedata?>");
	@fclose($fp);
}

?>