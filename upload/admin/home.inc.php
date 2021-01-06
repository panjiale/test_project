<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home.inc.php 13437 2008-04-15 10:49:41Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(@file_exists(DISCUZ_ROOT.'./install/index.php')) {
	@unlink(DISCUZ_ROOT.'./install/index.php');
	if(@file_exists(DISCUZ_ROOT.'./install/index.php')) {
		dexit('Please delete install/index.php via FTP!');
	}
}

@include_once DISCUZ_ROOT.'./discuz_version.php';
require_once DISCUZ_ROOT.'./include/attachment.func.php';

$siteuniqueid = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='siteuniqueid'");
if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$discuz_user.$timestamp), 0, 4).random(6);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('siteuniqueid', '$siteuniqueid')");
}

if(empty($_DCACHE['settings']['authkey']) || strlen($_DCACHE['settings']['authkey']) < 16) {
	$authkey = $_DCACHE['settings']['authkey'] = substr(md5($siteuniqueid.$bbname.$timestamp), 8, 8).random(8);
	$db->query("REPLACE INTO {$tablepre}settings SET variable='authkey', value='$authkey'");
	updatesettings();
}


if(submitcheck('notesubmit', 1)) {
	if($noteid) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id='$noteid' AND (admin='$discuz_user' OR adminid>='$adminid')");
	}
	if($newmessage) {
		$newaccess[$adminid] = 1;
		$newaccess = bindec(intval($newaccess[1]).intval($newaccess[2]).intval($newaccess[3]));
		$newexpiration = strtotime($newexpiration) - $timeoffset * 3600 + date('Z');
		$newmessage = nl2br(dhtmlspecialchars($newmessage));
		$db->query("INSERT INTO {$tablepre}adminnotes (admin, access, adminid, dateline, expiration, message)
			VALUES ('$discuz_user', '$newaccess', '$adminid', '$timestamp', '$newexpiration', '$newmessage')");
	}
}

$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
$dbversion = $db->result_first("SELECT VERSION()");

if(@ini_get('file_uploads')) {
	$fileupload = ini_get('upload_max_filesize');
} else {
	$fileupload = '<font color="red">'.$lang['no'].'</font>';
}

$groupselect = '';
$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups ORDER BY creditslower, groupid");
while($group = $db->fetch_array($query)) {
	$groupselect .= '<option value="'.$group['groupid'].'">'.$group['grouptitle'].'</option>';
}

$dbsize = 0;
$query = $db->query("SHOW TABLE STATUS LIKE '$tablepre%'", 'SILENT');
while($table = $db->fetch_array($query)) {
	$dbsize += $table['Data_length'] + $table['Index_length'];
}
$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

$attachsize = $db->result_first("SELECT SUM(filesize) FROM {$tablepre}attachments");
$attachsize = is_numeric($attachsize) ? sizecount($attachsize) : $lang['unknown'];

$membersmod = $db->result_first("SELECT COUNT(*) FROM {$tablepre}validating WHERE status='0'");
$postsmod = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE first='0' AND invisible='-2'");
$threadsdel = $threadsmod = 0;
$query = $db->query("SELECT displayorder FROM {$tablepre}threads WHERE displayorder<'0'");
while($thread = $db->fetch_array($query)) {
	if($thread['displayorder'] == -1) {
		$threadsdel++;
	} elseif($thread['displayorder'] == -2) {
		$threadsmod++;
	}
}

cpheader();
shownav();
echo '<div class="smallfont">';

$save_mastermobile = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='mastermobile'");
$save_mastermobile = !empty($save_mastermobile) ? authcode($save_mastermobile, 'DECODE', $authkey) : '';

$securityadvise = '';
$securityadvise .= !$discuz_secques ? $lang['home_secques_invalid'] : '';
$securityadvise .= empty($forumfounders) ? $lang['home_security_nofounder'] : '';
$securityadvise .= $admincp['tpledit'] ? $lang['home_security_tpledit'] : '';
$securityadvise .= $admincp['runquery'] ? $lang['home_security_runquery'] : '';

if(isfounder()) {
	if($securyservice) {
		$new_mastermobile = trim($new_mastermobile);
		if(empty($new_mastermobile)) {
			$save_mastermobile = $new_mastermobile;
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('mastermobile', '$new_mastermobile')");
		} elseif($save_mastermobile != $new_mastermobile && strlen($new_mastermobile) == 11 && is_numeric($new_mastermobile) && (substr($new_mastermobile, 0, 2) == '13' || substr($new_mastermobile, 0, 2) == '15')) {
			$save_mastermobile = $new_mastermobile;
			$new_mastermobile = authcode($new_mastermobile, 'ENCODE', $authkey);
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('mastermobile', '$new_mastermobile')");
		}
	}

	$view_mastermobile = !empty($save_mastermobile) ? substr($save_mastermobile, 0 , 3).'*****'.substr($save_mastermobile, -3) : '';

	$securityadvise = '<li><p>'.lang('home_security_service_info').'</p><form method="post" action="admincp.php?action=home&securyservice=yes">'.lang('home_security_service_mobile').': <input type="text" class="txt" name="new_mastermobile" value="'.$view_mastermobile.'" size="30" /> <input type="submit" class="btn" name="securyservice" value="'.lang($view_mastermobile ? 'submit' : 'home_security_service_open').'"  /> <span class="lightfont">'.lang($view_mastermobile ? 'home_security_service_mobile_save' : 'home_security_service_mobile_none').'</span></form></li>'.$securityadvise;
}

showsubmenu('home_security_tips');
echo '<ul class="safelist">'.$securityadvise.'</ul>';

if($isfounder) {
	$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
	if(empty($insenz['authkey'])) {
		echo '<div class="news"><h3>'.lang('insenz_note').'</h3><p class="lineheight">'.lang('insenz_note_join_insenz').'<a href="admincp.php?action=insenz"><u>'.$lang['insenz_note_register'].'</u></a></p></div>';
	} elseif($insenz['status']) {
		require_once DISCUZ_ROOT.'./include/insenz.func.php';
		echo '<div id="insenznews"></div>';
	}
}

echo '<div id="boardnews"></div>';

showtableheader('', 'nobottom fixpadding');
if($membersmod || $threadsmod || $postsmod || $threadsdel) {
	showtablerow('class="nobg"', '', '<h3 class="left margintop">'.lang('home_mods').': </h3><p class="left difflink">'.
		($membersmod ? '<a href="admincp.php?action=moderate&operation=members">'.lang('home_mod_members').'</a>(<em class="lightnum">'.$membersmod.'</em>)' : '').
		($threadsmod ? '<a href="admincp.php?action=moderate&operation=threads">'.lang('home_mod_threads').'</a>(<em class="lightnum">'.$threadsmod.'</em>)' : '').
		($postsmod ? '<a href="admincp.php?action=moderate&operation=replies">'.lang('home_mod_posts').'</a>(<em class="lightnum">'.$postsmod.'</em>)' : '').
		($threadsdel ? '<a href="admincp.php?action=recyclebin">'.lang('home_del_threads').'</a>(<em class="lightnum">'.$threadsdel.'</em>)' : '').
		'</p><div class="clear"></div>'
	);
}

$onlines = array();
$query = $db->query("SELECT a.*, m.username, m.adminid, m.regip
	FROM {$tablepre}adminsessions a
	LEFT JOIN {$tablepre}members m USING(uid)
	WHERE panel='1' AND errorcount='-1'
	ORDER BY a.errorcount");
while($member = $db->fetch_array($query)) {
	$onlines[] = '<a href="space.php?uid='.$member['uid'].'" target="_blank" title="'.
		"$lang[time]: ".gmdate("$dateformat $timeformat", $member['dateline'] + $timeoffset * 3600)."\n".
		($adminid <= $member['adminid'] || $member['adminid'] <= 0 ? "$lang[home_online_regip]: $member[regip]\n$lang[home_onlines_ip]: $member[ip]" : '').
		'">'.$member['username'].'</a>';
}
showtablerow('class="nobg"', '', '<h3 class="left margintop">'.lang('home_onlines').': </h3><p class="left difflink">'.implode(', ', $onlines).	'</p><div class="clear"></div>'
);
showtablefooter();

showformheader('home');
showtableheader('', 'fixpadding');

echo '<tr><th colspan="3" class="partition" style="padding:10px 0 5px;background:none">'.lang('home_notes').'</th></tr>';

$query = $db->query("SELECT * FROM {$tablepre}adminnotes WHERE access IN (4,5,6,7) ORDER BY dateline DESC");
while($note = $db->fetch_array($query)) {
	if($note['expiration'] < $timestamp) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id='$note[id]'");
	} else {
		$note['adminenc'] = rawurlencode($note['admin']);
		$note['dateline'] = gmdate($dateformat, $note['dateline'] + $timeoffset * 3600);
		$note['expiration'] = gmdate($dateformat, $note['expiration'] + $timeoffset * 3600);
		showtablerow('', array('', '', ''), array(
			'<a href="admincp.php?action=home&notesubmit=yes&noteid='.$note['id'].'"><img src="images/admincp/close.gif" width="7" height="8" title="'.lang('delete').'" /></a>',
			"<span class=\"bold\"><a href=\"space.php?username=$note[adminenc]\" target=\"_blank\">$note[admin]</a>: </span>$note[message]",
			"$note[dateline] ~ $note[expiration]"
		));
	}
}

showtablerow('class="nobg"', array('', '', ''), array(
	lang('home_notes_add'),
	'<input type="text" class="txt" name="newmessage" value="" style="width:300px;" />',
	lang('validity').': <input type="text" class="txt" name="newexpiration" value="'.gmdate('Y-n-j', $timestamp + $timeoffset * 3600 + 86400 * 30).'" size="8" /><input name="notesubmit" value="'.lang('submit').'" type="submit" class="btn" />'
));
showtablefooter();
showformfooter();

showtableheader('home_sys_info', 'noborder fixpadding');
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_discuz_version'),
	'Discuz! '.DISCUZ_VERSION.' Release '.DISCUZ_RELEASE.' <a href="http://www.discuz.net/forumdisplay.php?fid=10" class="lightlink smallfont" target="_blank">'.lang('home_check_newversion').'</a> '
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_environment'),
	$serverinfo
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_database'),
	$dbversion
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_upload_perm'),
	$fileupload
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_database_size'),
	$dbsize
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_attach_size'),
	$attachsize
));
showtablefooter();

showtableheader('home_dev', 'fixpadding');
showtablerow('', array('class="vtop td24 lineheight"'), array(
	lang('home_dev_copyright'),
	'<span class="bold"><a href="http://www.comsenz.com" class="lightlink2" target="_blank">&#x5eb7;&#x76db;&#x521b;&#x60f3;(&#x5317;&#x4eac;)&#x79d1;&#x6280;&#x6709;&#x9650;&#x516c;&#x53f8; (Comsenz Inc.)</a></span>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_dev_manager'),
	'<a href="http://www.discuz.net/space.php?uid=1" class="lightlink smallfont" target="_blank">&#x6234;&#x5FD7;&#x5EB7; (Kevin \'Crossday\' Day)</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	lang('home_dev_team'),
	'<a href="http://www.discuz.net/space.php?uid=2691" class="lightlink smallfont" target="_blank">Liang \'Readme\' Chen</a>,
	<a href="http://www.discuz.net/space.php?uid=1519" class="lightlink smallfont" target="_blank">Yang \'Summer\' Xia</a>,
	<a href="http://www.discuz.net/space.php?uid=859" class="lightlink smallfont" target="_blank">Hypo \'cnteacher\' Wang</a>,
	 <a href="http://www.discuz.net/space.php?uid=16678" class="lightlink smallfont" target="_blank">Yang \'Tiger\' Song</a>,
	 <a href="http://www.discuz.net/space.php?uid=10407" class="lightlink smallfont" target="_blank">Qiang Liu</a>,
	 <a href="http://www.discuz.net/space.php?uid=80629" class="lightlink smallfont" target="_blank">Ning \'Monkey\' Hou</a>,
	 <a href="http://www.discuz.net/space.php?uid=122246" class="lightlink smallfont" target="_blank">Min \'Heyond\' Huang</a>,
	 <a href="http://www.discuz.net/space.php?uid=210272" class="lightlink smallfont" target="_blank">XiaoDun \'Kenshine\' Fang</a>,
	 <a href="http://www.discuz.net/space.php?uid=492114" class="lightlink smallfont" target="_blank">Liang \'Metthew\' Xu</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
	lang('home_dev_addons'),
	'<a href="http://www.discuz.net/space.php?uid=9600" class="lightlink smallfont" target="_blank">theoldmemory</a>,
	<a href="http://www.discuz.net/space.php?uid=2629" class="lightlink smallfont" target="_blank">rain5017</a>,
	<a href="http://www.discuz.net/space.php?uid=26926" class="lightlink smallfont" target="_blank">Snow Wolf</a>,
	<a href="http://www.discuz.net/space.php?uid=17149" class="lightlink smallfont" target="_blank">hehechuan</a>,
	<a href="http://www.discuz.net/space.php?uid=9132" class="lightlink smallfont" target="_blank">pk0909</a>,
	<a href="http://www.discuz.net/space.php?uid=248" class="lightlink smallfont" target="_blank">feixin</a>,
	<a href="http://www.discuz.net/space.php?uid=675" class="lightlink smallfont" target="_blank">Laobing Jiuba</a>,
	<a href="http://www.discuz.net/space.php?uid=7155" class="lightlink smallfont" target="_blank">Gregry</a>,
	<a href="http://www.discuzsupport.net/" class="lightlink smallfont" target="_blank">Discuz! Support Team</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
	lang('home_dev_skins'),
	'<a href="http://www.discuz.net/space.php?uid=294092" class="lightlink smallfont" target="_blank">Fangming \'Lushnis\' Li</a>,
	<a href="http://www.discuz.net/space.php?uid=362790" class="lightlink smallfont" target="_blank">Defeng \'Dfox\' Xu</a>,
	<a href="http://www.discuz.net/space.php?uid=13877" class="lightlink smallfont" target="_blank">Artery</a>,
	<a href="http://www.discuz.net/space.php?uid=233" class="lightlink smallfont" target="_blank">Huli Hutu</a>,
	<a href="http://www.discuz.net/space.php?uid=122" class="lightlink smallfont" target="_blank">Lao Gui</a>,
	<a href="http://www.discuz.net/space.php?uid=159" class="lightlink smallfont" target="_blank">Tyc</a>,
	<a href="http://www.discuz.net/space.php?uid=177" class="lightlink smallfont" target="_blank">stoneage</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
	lang('home_dev_links'),
	'<a href="http://www.comsenz.com" class="lightlink" target="_blank">&#x516C;&#x53F8;&#x7F51;&#x7AD9;</a>, <a href="http://idc.comsenz.com" class="lightlink" target="_blank">&#x865A;&#x62DF;&#x4E3B;&#x673A;</a>, <a href="http://www.comsenz.com/category-51" class="lightlink" target="_blank">&#x8D2D;&#x4E70;&#x6388;&#x6743;</a>, <a href="http://www.discuz.com/" class="lightlink" target="_blank">&#x44;&#x69;&#x73;&#x63;&#x75;&#x7A;&#x21;&#x20;&#x4EA7;&#x54C1;</a>, <a href="http://www.discuz.net/forumdisplay.php?fid=21" class="lightlink" target="_blank">&#x6A21;&#x677F;</a>, <a href="http://www.discuz.net/forumdisplay.php?fid=26" class="lightlink" target="_blank">&#x63D2;&#x4EF6;</a>, <a href="http://www.discuz.net/forumdisplay.php?fid=88" class="lightlink" target="_blank">&#x6587;&#x6863;</a>, <a href="http://www.discuz.net/" class="lightlink" target="_blank">&#x8BA8;&#x8BBA;&#x533A;</a>'
));
showtablefooter();

echo '</div>';

?>