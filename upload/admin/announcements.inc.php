<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: announcements.inc.php 13374 2008-04-11 08:04:21Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();
echo '<script type="text/javascript" src="include/javascript/calendar.js"></script>';

if(empty($operation)) {

	if(!submitcheck('announcesubmit')) {

		shownav('misc', 'announce', 'admin');
		showsubmenu('nav_announce', array(
			array('admin', 'announcements', 1),
			array('add', 'announcements&operation=add', 0)
		));
		showtips('announce_tips');
		showformheader('announcements');
		showtableheader();
		showsubtitle(array('', 'display_order', 'author', 'subject', 'message', 'announce_type', 'start_time', 'end_time', ''));

		$announce_type = array(0=>$lang['announce_words'], 1=>$lang['announce_url']);
		$query = $db->query("SELECT * FROM {$tablepre}announcements ORDER BY displayorder, starttime DESC, id DESC");
		while($announce = $db->fetch_array($query)) {
			$disabled = $adminid != 1 && $announce['author'] != $discuz_userss ? 'disabled' : NULL;
			$announce['starttime'] = $announce['starttime'] ? gmdate($dateformat, $announce['starttime'] + $_DCACHE['settings']['timeoffset'] * 3600) : $lang['unlimited'];
			$announce['endtime'] = $announce['endtime'] ? gmdate($dateformat, $announce['endtime'] + $_DCACHE['settings']['timeoffset'] * 3600) : $lang['unlimited'];
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$announce[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" name=\"displayordernew[{$announce[id]}]\" value=\"$announce[displayorder]\" size=\"2\" $disabled>",
				"<a href=\"./space.php?username=".rawurlencode($announce['author'])."\" target=\"_blank\">$announce[author]</a>",
				dhtmlspecialchars($announce['subject']),
				cutstr(strip_tags($announce['message']), 20),
				$announce_type[$announce['type']],
				$announce['starttime'],
				$announce['endtime'],
				"<a href=\"admincp.php?action=announcements&operation=edit&announceid=$announce[id]\" $disabled>$lang[edit]</a>"
			));
		}
		showsubmit('announcesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($delete)) {
			$ids = $comma = '';
			foreach($delete as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM {$tablepre}announcements WHERE id IN ($ids) AND ('$adminid'='1' OR author='$discuz_user')");
		}

		if(is_array($displayordernew)) {
			foreach($displayordernew as $id => $displayorder) {
				$db->query("UPDATE {$tablepre}announcements SET displayorder='$displayorder' WHERE id='$id' AND ('$adminid'='1' OR author='$discuz_user')");
			}
		}

		updatecache(array('pmlist', 'announcements', 'announcements_forum'));
		cpmsg('announce_update_succeed', 'admincp.php?action=announcements', 'succeed');

	}

} elseif($operation == 'add') {

	if(!submitcheck('addsubmit')) {

		$newstarttime = gmdate('Y-n-j', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
		$newendtime = gmdate('Y-n-j', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600 + 86400* 7);

		$groupselect = '<select name="usergroupid[]" size="5" multiple="multiple" size="10"><option value="" selected>'.$lang['all'].'</option>';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid<4 OR groupid>6");
		while($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$groupselect .= '</select>';

		shownav('misc', 'announce', 'add');
		showsubmenu('nav_announce', array(
			array('admin', 'announcements', 0),
			array('add', 'announcements&operation=add', 1)
		));
		showformheader('announcements&operation=add');
		showtableheader('announce_add');
		showsetting($lang[subject], 'newsubject', '', 'text');
		showsetting($lang['start_time'], 'newstarttime', $newstarttime, 'calendar');
		showsetting($lang['end_time'], 'newendtime', $newendtime, 'calendar');
		showsetting('announce_type', array('newtype', array(
			array(0, $lang['announce_words']),
			array(1, $lang['announce_url']))), 0, 'mradio');
		showsetting('announce_usergroup', '', '', $groupselect);
		showsetting('announce_message', 'newmessage', '', 'textarea');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newstarttime = $newstarttime ? strtotime($newstarttime) : 0;
		$newendtime = $newendtime ? strtotime($newendtime) : 0;

		if(!$newstarttime) {
			cpmsg('announce_time_invalid', '', 'error');
		} elseif(!($newsubject = trim($newsubject)) || !($newmessage = trim($newmessage))) {
			cpmsg('announce_invalid', '', 'error');
		} else {
			$newmessage = $newtype == 1 ? explode("\n", $newmessage) : array(0 => $newmessage);
			$groups = in_array(0, $usergroupid) ? '' : implode(',', $usergroupid);
			$db->query("INSERT INTO {$tablepre}announcements (author, subject, type, starttime, endtime, message, groups)
				VALUES ('$discuz_user', '$newsubject', '$newtype', '$newstarttime', '$newendtime', '{$newmessage[0]}', '".$groups."')");
			updatecache(array('announcements', 'announcements_forum', 'pmlist'));
			cpmsg('announce_succeed', 'admincp.php?action=announcements', 'succeed');
		}

	}

} elseif($operation == 'edit' && $announceid) {

	$announce = $db->fetch_first("SELECT * FROM {$tablepre}announcements WHERE id='$announceid' AND ('$adminid'='1' OR author='$discuz_user')");
	if(!$announce) {
		cpmsg('announce_nonexistence', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$groupselect = '<select name="usergroupid[]" size="5" multiple="multiple" size="10"><option value="" selected>'.$lang['all'].'</option>';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid<4 OR groupid>6");
		$pmgroups = explode(',', $announce['groups']);

		$groupselectall = empty($announce['groups']) || in_array(0, $pmgroups) ? ' selected' : '';
		while($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=\"$group[groupid]\" ".(!$groupselectall && in_array($group['groupid'], $pmgroups) ? 'selected' : '').">$group[grouptitle]</option>\n";
		}
		$groupselect .= '</select>';

		$announce['starttime'] = $announce['starttime'] ? gmdate('Y-n-j', $announce['starttime'] + $_DCACHE['settings']['timeoffset'] * 3600) : "";
		$announce['endtime'] = $announce['endtime'] ? gmdate('Y-n-j', $announce['endtime'] + $_DCACHE['settings']['timeoffset'] * 3600) : "";

		shownav('misc', 'announce');
		showsubmenu('nav_announce', array(
			array('admin', 'announcements', 0),
			array('add', 'announcements&operation=add', 0)
		));
		showformheader("announcements&operation=edit&announceid=$announceid");
		showtableheader();
		showtitle('announce_edit');
		showsetting('subject', 'subjectnew', dhtmlspecialchars($announce['subject']), 'text');
		showsetting('start_time', 'starttimenew', dhtmlspecialchars($announce['starttime']), 'calendar');
		showsetting('end_time', 'endtimenew', dhtmlspecialchars($announce['endtime']), 'calendar');
		showsetting('announce_type', array('typenew', array(
			array(0, $lang['announce_words']),
			array(1, $lang['announce_url']))), $announce['type'], 'mradio');
		showsetting('announce_usergroup', '', '', $groupselect);
		showsetting('announce_message', 'messagenew', dhtmlspecialchars($announce['message']), 'textarea');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(strpos($starttimenew, '-')) {
			$time = explode('-', $starttimenew);
			$starttimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $_DCACHE['settings']['timeoffset'] * 3600;
		} else {
			$starttimenew = 0;
		}
		if(strpos($endtimenew, '-')) {
			$time = explode('-', $endtimenew);
			$endtimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $_DCACHE['settings']['timeoffset'] * 3600;
		} else {
			$endtimenew = 0;
		}

		if(!$starttimenew || ($endtimenew && $endtimenew <= $timestamp)) {
			cpmsg('announce_time_invalid', '', 'error');
		} elseif(!($subjectnew = trim($subjectnew)) || !($messagenew = trim($messagenew))) {
			cpmsg('announce_invalid', '', 'error');
		} else {
			$messagenew = $typenew == 1 ? explode("\n", $messagenew) : array(0 => $messagenew);
			$groups = in_array(0, $usergroupid) ? '' : implode(',', $usergroupid);
			$db->query("UPDATE {$tablepre}announcements SET subject='$subjectnew', type='$typenew', starttime='$starttimenew', endtime='$endtimenew', message='{$messagenew[0]}', groups='$groups' WHERE id='$announceid' AND ('$adminid'='1' OR author='$discuz_user')");
			updatecache('announcements', 'announcements_forum', 'pmlist');
			cpmsg('announce_succeed', 'admincp.php?action=announcements', 'succeed');
		}
	}

}

?>