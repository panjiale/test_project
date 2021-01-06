<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: members.inc.php 13475 2008-04-17 09:11:30Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

@set_time_limit(600);

cpheader();

if(!$operation) {

	if(!submitcheck('submit', 1)) {

		shownav('user', 'menu_members_edit');
		showsubmenu('menu_members_edit', array(
			array('search', 'members', 1),
			array('clean', 'members&operation=clean', 0),
		));
		showtips('members_admin_tips');
		searchmembers();

	} else {

		countmembers();

		$multipage = multi($membernum, $memberperpage, $page, "admincp.php?action=members&submit=yes".$urladd);

		$usergroups = array();
		$query = $db->query("SELECT groupid, type, grouptitle FROM {$tablepre}usergroups");
		while($group = $db->fetch_array($query)) {
			switch($group['type']) {
				case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
				case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
			}
			$usergroups[$group['groupid']] = $group;
		}

		$query = $db->query("SELECT uid, username, adminid, groupid, credits, extcredits1, extcredits2,
			extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8, posts FROM {$tablepre}members WHERE $conditions LIMIT $start_limit, $memberperpage");

		while($member = $db->fetch_array($query)) {
			$memberextcredits = array();
			foreach($extcredits as $id => $credit) {
				$memberextcredits[] = $extcredits[$id]['title'].': '.$member['extcredits'.$id];
			}
			$members .= showtablerow('', array('class="td25"', '', 'title="'.implode("\n", $memberextcredits).'"'), array(
				"<input type=\"checkbox\" name=\"uidarray[]\" value=\"$member[uid]\"".($member['adminid'] == 1 ? 'disabled' : '')." class=\"checkbox\">",
				"<a href=\"space.php?uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
				$member['credits'],
				$member['posts'],
				$usergroups[$member['adminid']]['grouptitle'],
				$usergroups[$member['groupid']]['grouptitle'],
				"<a href=\"admincp.php?action=members&operation=editgroups&uid=$member[uid]\" class=\"act\">$lang[usergroup]</a><a href=\"admincp.php?action=members&operation=access&uid=$member[uid]\" class=\"act\">$lang[members_access]</a>".
				($extcredits ? "<a href=\"admincp.php?action=members&operation=editcredits&uid=$member[uid]\" class=\"act\">$lang[credits]</a>" : "<span disabled>$lang[edit]</span>").
				"<a href=\"admincp.php?action=members&operation=editmedals&uid=$member[uid]\" class=\"act\">$lang[medals]</a><a href=\"admincp.php?action=members&operation=profile&uid=$member[uid]\" class=\"act\">$lang[detail]</a>"
			), TRUE);
		}

		shownav('user', 'menu_members_edit');
		showsubmenu('menu_members_edit');
		showformheader("members&operation=clean");
		eval("\$lang[members_search_result] = \"".$lang['members_search_result']."\";");
		showtableheader(lang('members_search_result').'<a href="javascript:history.go(-1);" class="act lightlink normal">'.lang('research').'</a>');
		showsubtitle(array('', 'username', 'credits', 'posts', 'admingroup', 'usergroup', ''));
		echo $members;
		showtablerow('', array('class="td25"', 'class="lineheight" colspan="7"'), array('', lang('members_admin_comment')));
		showsubmit('submit', 'submit', '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'uidarray\')" class="checkbox">'.lang('del'), '', $multipage);
		showtablefooter();
		showformfooter();

	}

} elseif($operation == 'clean') {

	if(!submitcheck('submit', 1)) {

		shownav('user', 'menu_members_edit');
		showsubmenu('menu_members_edit', array(
			array('search', 'members', 0),
			array('clean', 'members&operation=clean', 1),
		));

		searchmembers('clean');

	} else {

		countmembers();

		$uids = 0;
		$extra = '';

		if(empty($uidarray)) {
			$query = $db->query("SELECT uid, groupid, adminid FROM {$tablepre}members WHERE $conditions AND adminid<>1 AND groupid<>1");
		} else {
			$uids = is_array($uidarray) ? '\''.implode('\', \'', $uidarray).'\'' : '0';
			$query = $db->query("SELECT uid, groupid, adminid FROM {$tablepre}members WHERE uid IN($uids) AND adminid<>1 AND groupid<>1");
		}

		$membernum = $db->num_rows($query);

		$uids = $comma = '';
		while($member = $db->fetch_array($query)) {
			if($membernum < 2000 || !empty($uidarray)) {
				$extra .= '<input type="hidden" name="uidarray[]" value="'.$member['uid'].'" />';
			}
			$uids .= $comma.$member['uid'];
			$comma = ',';
		}

		$includepost .= '<br /><input type="checkbox" name="includepost" value="1" class="checkbox" />'.$lang['members_delete_post'];

		if((empty($membernum) || empty($uids))) {
			cpmsg('members_no_find_deluser', '', 'error');
		}

		if(!$confirmed) {

			cpmsg('members_delete_confirm', "admincp.php?action=members&operation=clean&submit=yes&confirmed=yes".$urladd, 'form', $extra.$includepost);

		} else {

			if(empty($includepost)) {

				$query = $db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
				$numdeleted = $db->affected_rows();
				$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');
				cpmsg('members_delete_succeed', '', 'succeed');

			} else {

				$numdeleted = $numdeleted ? $numdeleted : count($uidarray);
				$pertask = 1000;
				$current = intval($current);

				$next = $current + $pertask;
				$threads = $fids = $threadsarray = array();

				$query = $db->query("SELECT f.fid, t.tid FROM {$tablepre}threads t LEFT JOIN {$tablepre}forums f ON t.fid=f.fid WHERE t.authorid IN ($uids) ORDER BY f.fid LIMIT $pertask");
				while($thread = $db->fetch_array($query)) {
					$threads[$thread['fid']] .= ($threads[$thread['fid']] ? ',' : '').$thread['tid'];
				}

				$nextlink = "admincp.php?action=members&operation=clean&confirmed=yes&submit=yes&includepost=yes&current=$next&pertask=$pertask&lastprocess=$processed".$urladd;
				if($threads) {
					foreach($threads as $fid => $tids) {
						$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($tids)");
						while($attach = $db->fetch_array($query)) {
							dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
						}

						foreach(array('threads', 'threadsmod', 'relatedthreads', 'posts', 'polls', 'polloptions', 'trades', 'activities', 'activityapplies', 'debates', 'debateposts', 'attachments', 'favorites', 'mythreads', 'myposts', 'subscriptions', 'typeoptionvars', 'forumrecommend') as $value) {
							$db->query("DELETE FROM {$tablepre}$value WHERE tid IN ($tids)", 'UNBUFFERED');
						}

						require_once DISCUZ_ROOT.'./include/post.func.php';
						updateforumcount($fid);
					}
					if($globalstick && $stickmodify) {
						require_once DISCUZ_ROOT.'./include/cache.func.php';
						updatecache('globalstick');
					}

					cpmsg(lang('members_delete_post').': '.lang('members_delete_processing'), $nextlink, 'loadingform', $extra);

				} elseif($uids) {

					$query = $db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
					$numdeleted = $db->affected_rows();
					$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');

					$query = $db->query("SELECT uid, attachment, thumb, remote FROM {$tablepre}attachments WHERE uid IN ($uids) LIMIT $pertask");
					while($attach = $db->fetch_array($query)) {
						dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
					}
					$db->query("DELETE FROM {$tablepre}attachments WHERE uid IN ($uids)");

					$db->query("DELETE FROM {$tablepre}posts WHERE authorid IN ($uids)");
					$db->query("DELETE FROM {$tablepre}trades WHERE sellerid IN ($uids)");

					cpmsg('members_delete_succeed', '', 'succeed');

				} else {

					cpmsg('members_no_find_deluser', '', 'error');

				}
			}
		}
	}

} elseif($operation == 'newsletter') {

	if(!submitcheck('newslettersubmit', 1)) {

		shownav('user', 'nav_members_newsletter');
		showsubmenusteps('nav_members_newsletter', array(
			array('nav_members_select', !$submit),
			array('nav_members_notify', $submit),
		));

		searchmembers('newsletter');

		if(submitcheck('submit', 1)) {

			countmembers();

			showtagheader('div', 'newsletter', TRUE);
			showformheader('members&operation=newsletter'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
			} else {
				eval("\$lang[members_search_result] = \"".$lang['members_search_result']."\";");
				showtablerow('class="first"', array('class="th11"'), array(
					lang('members_newsletter_members'),
					"$lang[members_search_result] <a href=\"###\" onclick=\"$('searchmembers').style.display='';$('newsletter').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				showtagheader('tbody', 'messagebody', TRUE);
				shownewsletter();
				showtagfooter('tbody');

				showsubmit('newslettersubmit', 'submit', 'td');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {

		countmembers();
		notifymembers('newsletter', 'newsletter');

	}

} elseif($operation == 'reward') {

	if(!submitcheck('rewardsubmit', 1)) {

		shownav('user', 'nav_members_reward');
		showsubmenusteps('nav_members_reward', array(
			array('nav_members_select', !$submit),
			array('nav_members_reward', $submit),
		));

		searchmembers('reward');

		if(submitcheck('submit', 1)) {

			countmembers();

			showtagheader('div', 'reward', TRUE);
			showformheader('members&operation=reward'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
				showtablefooter();
			} else {

				$creditscols = array('credits_title');
				$creditsvalue = $resetcredits = array();
				$js_extcreditids = '';
				for($i=1; $i<=8; $i++) {
					$js_extcreditids .= (isset($extcredits[$i]) ? ($js_extcreditids ? ',' : '').$i : '');
					$creditscols[] = isset($extcredits[$i]) ? $extcredits[$i]['title'] : 'extcredits'.$i;
					$creditsvalue[] = isset($extcredits[$i]) ? '<input type="text" class="txt" size="3" id="addextcredits['.$i.']" name="addextcredits['.$i.']" value="0"> '.$extcredits['$i']['unit'] : '<input type="text" class="txt" size="3" value="N/A" disabled>';
					$resetcredits[] = isset($extcredits[$i]) ? '<input type="checkbox" id="resetextcredits['.$i.']" name="resetextcredits['.$i.']" value="1" class="radio" disabled> '.$extcredits['$i']['unit'] : '<input type="checkbox" disabled  class="radio">';
				}
				$creditsvalue = array_merge(array('<input type="radio" name="updatecredittype" id="updatecredittype0" value="0" class="radio" onclick="var extcredits = new Array('.$js_extcreditids.'); for(k in extcredits) {$(\'resetextcredits[\'+extcredits[k]+\']\').disabled = true; $(\'addextcredits[\'+extcredits[k]+\']\').disabled = false;}" checked="checked" /><label for="updatecredittype0">'.$lang['members_credits_value'].'</label>'), $creditsvalue);
				$resetcredits = array_merge(array('<input type="radio" name="updatecredittype" id="updatecredittype1" value="1" class="radio" onclick="var extcredits = new Array('.$js_extcreditids.'); for(k in extcredits) {$(\'addextcredits[\'+extcredits[k]+\']\').disabled = true; $(\'resetextcredits[\'+extcredits[k]+\']\').disabled = false;}" /><label for="updatecredittype1">'.$lang['members_credits_clean'].'</label>'), $resetcredits);

				eval("\$lang[members_search_result] = \"".$lang['members_search_result']."\";");
				showtablerow('class="first"', array('class="th11"'), array(
					lang('members_reward_members'),
					"$lang[members_search_result] <a href=\"###\" onclick=\"$('searchmembers').style.display='';$('reward').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				echo '<tr><td class="th12">'.lang('nav_members_reward').'</td><td>';
				showtableheader('', 'noborder');
				showsubtitle($creditscols);
				showtablerow('', array('class="td23"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $creditsvalue);
				showtablerow('', array('class="td23"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $resetcredits);
				showtablefooter();
				echo '</td></tr>';

				showtagheader('tbody', 'messagebody');
				shownewsletter();
				showtagfooter('tbody');

				showsubmit('rewardsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymembers" value="1" onclick="$(\'messagebody\').disabled=!this.checked;$(\'messagebody\').style.display = $(\'messagebody\').style.display == \'\' ? \'none\' : \'\'" id="credits_notify" /><label for="credits_notify">'.lang('members_credits_notify').'</label>');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {

		countmembers();
		notifymembers('reward', 'creditsnotify');

	}

} elseif($operation == 'confermedal') {

	$medals = '';
	$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available='1'");
	while($medal = $db->fetch_array($query)) {
		$medals .= showtablerow('', array('class="td25"', 'class="td25"'), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" />",
			"<img src=\"images/common/$medal[image]\" />",
			$medal[name]
		), TRUE);
	}

	if(!$medals) {
		cpmsg('members_edit_medals_nonexistence', 'admincp.php?action=medals', 'error');
	}

	if(!submitcheck('confermedalsubmit', 1)) {

		shownav('user', 'nav_members_confermedal');
		showsubmenusteps('nav_members_confermedal', array(
			array('nav_members_select', !$submit),
			array('nav_members_confermedal', $submit),
		));

		searchmembers('confermedal');

		if(submitcheck('submit', 1)) {

			countmembers();

			showtagheader('div', 'confermedal', TRUE);
			showformheader('members&operation=confermedal'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
				showtablefooter();
			} else {

				eval("\$lang[members_search_result] = \"".$lang['members_search_result']."\";");
				showtablerow('class="first"', array('class="th11"'), array(
					lang('members_confermedal_members'),
					"$lang[members_search_result] <a href=\"###\" onclick=\"$('searchmembers').style.display='';$('confermedal').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				echo '<tr><td class="th12">'.lang('members_confermedals').'</td><td>';
				showtableheader('', 'noborder');
				showsubtitle(array('medals_grant', 'medals_image', 'name'));
				echo $medals;
				showtablefooter();
				echo '</td></tr>';

				showtagheader('tbody', 'messagebody');
				shownewsletter();
				showtagfooter('tbody');

				showsubmit('confermedalsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymembers" value="1" onclick="$(\'messagebody\').disabled=!this.checked; $(\'messagebody\').style.display = $(\'messagebody\').style.display == \'\' ? \'none\' : \'\'" id="grant_notify"/><label for="grant_notify">'.lang('medals_grant_notify').'</label>');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {

		countmembers();
		notifymembers('confermedal', 'medalletter');

	}

} elseif($operation == 'add') {

	if(!submitcheck('addsubmit')) {

		$groupselect = '';
		$query = $db->query("SELECT groupid, type, grouptitle, creditshigher FROM {$tablepre}usergroups WHERE type='member' AND creditshigher='0' OR (groupid NOT IN ('5', '6', '7') AND radminid<>'1' AND type<>'member') ORDER BY type DESC, (creditshigher<>'0' || creditslower<>'0'), creditslower");
		while($group = $db->fetch_array($query)) {
			if($group['type'] == 'member' && $group['creditshigher'] == 0) {
				$groupselect .= "<option value=\"$group[groupid]\" selected>$group[grouptitle]</option>\n";
			} else {
				$groupselect .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
		}
		shownav('user', 'nav_members_add');
		showsubmenu('members_add');
		showformheader('members&operation=add');
		showtableheader();
		showsetting('username', 'newusername', '', 'text');
		showsetting('password', 'newpassword', '', 'text');
		showsetting('email', 'newemail', '', 'text');
		showsetting('usergroup', '', '', '<select name="newgroupid">'.$groupselect.'</select>');
		showsetting('members_add_email_notify', 'emailnotify', '', 'radio');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newusername = trim($newusername);
		$newpassword = trim($newpassword);
		$newemail = trim($newemail);

		if(!$newusername || !$newpassword || !$newemail) {
			cpmsg('members_add_invalid', '', 'error');
		}

		if($db->result_first("SELECT count(*) FROM {$tablepre}members WHERE username='$newusername'")) {
			cpmsg('members_add_username_duplicate', '', 'error');
		}

		require_once DISCUZ_ROOT.'./uc_client/client.php';

		$uid = uc_user_register($newusername, $newpassword, $newemail);
		if($uid <= 0) {
			if($uid == -1) {
				cpmsg('members_add_illegal', '', 'error');
			} elseif($uid == -2) {
				cpmsg('members_username_protect', '', 'error');
			} elseif($uid == -3) {
				cpmsg('members_add_username_activation', '', 'error');
			} elseif($uid == -4) {
				cpmsg('members_email_illegal', '', 'error');
			} elseif($uid == -5) {
				cpmsg('members_email_domain_illegal', '', 'error');
			} elseif($uid == -6) {
				cpmsg('members_email_duplicate', '', 'error');
			} else {
				cpmsg('undefined_action', '', 'error');
			}
		}

		$query = $db->query("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$newgroupid'");
		$group = $db->fetch_array($query);
		$newadminid = in_array($group['radminid'], array(1, 2, 3)) ? $group['radminid'] : ($group['type'] == 'special' ? -1 : 0);
		if($group['radminid'] == 1) {
			cpmsg('members_add_admin_none', '', 'error');
		}
		if(in_array($group['groupid'], array(5, 6, 7))) {
			cpmsg('members_add_ban_all_none', '', 'error');
		}

		$db->query("INSERT INTO {$tablepre}members (uid, username, password, secques, gender, adminid, groupid, regip, regdate, lastvisit, lastactivity, posts, credits, email, bday, sigstatus, tpp, ppp, styleid, dateformat, timeformat, showemail, newsletter, invisible, timeoffset)
			VALUES ('$uid', '$newusername', '".md5(random(10))."', '', '0', '$newadminid', '$newgroupid', 'Manual Acting', '$timestamp', '$timestamp', '$timestamp', '0', '0', '$newemail', '0000-00-00', '0', '0', '0', '0', '0', '{$_DCACHE[settings][timeformat]}', '1', '1', '0', '{$_DCACHE[settings][timeoffset]}')");

		$db->query("REPLACE INTO {$tablepre}memberfields (uid) VALUES ('$uid')");

		if($emailnotify) {
			sendmail("$newusername <$newemail>", 'add_member_subject', 'add_member_message');
		}

		updatecache('settings');
		$newusername = stripslashes($newusername);
		cpmsg('members_add_succeed', '', 'succeed');

	}

} elseif($operation == 'editgroups') {

	$member = $db->fetch_first("SELECT m.uid, m.username, m.adminid, m.groupid, m.groupexpiry, m.extgroupids, m.credits,
		mf.groupterms, u.type AS grouptype, u.grouptitle, u.radminid
		FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE m.uid='$uid'");

	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$checkadminid = array(($member['adminid'] >= 0 ? $member['adminid'] : 0) => 'checked');

		$member['groupterms'] = unserialize($member['groupterms']);

		if($member['groupterms']['main']) {
			$expirydate = gmdate('Y-n-j', $member['groupterms']['main']['time'] + $timeoffset * 3600);
			$expirydays = ceil(($member['groupterms']['main']['time'] - $timestamp) / 86400);
			$selecteaid = array($member['groupterms']['main']['adminid'] => 'selected');
			$selectegid = array($member['groupterms']['main']['groupid'] => 'selected');
		} else {
			$expirydate = $expirydays = '';
			$selecteaid = array($member['adminid'] => 'selected');
			$selectegid = array(($member['grouptype'] == 'member' ? 0 : $member['groupid']) => 'selected');
		}

		$extgroups = $expgroups = '';
		$radmingids = 0;
		$extgrouparray = explode("\t", $member['extgroupids']);
		$groups = array('system' => '', 'special' => '', 'member' => '');
		$group = array('groupid' => 0, 'radminid' => 0, 'type' => '', 'grouptitle' => $lang['usergroups_system_0'], 'creditshigher' => 0, 'creditslower' => '0');
		$query = $db->query("SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower
			FROM {$tablepre}usergroups WHERE groupid NOT IN ('6', '7') ORDER BY creditshigher, groupid");
		do {
			if($group['groupid'] && !in_array($group['groupid'], array(4, 5, 6, 7, 8)) && ($group['type'] == 'system' || $group['type'] == 'special')) {
				$extgroups .= showtablerow('', array('class="td22"', 'style="width:80%"'), array(
					'<input class="checkbox" type="checkbox" name="extgroupidsnew[]" value="'.$group['groupid'].'" '.(in_array($group['groupid'], $extgrouparray) ? 'checked' : '').' id="extgid_'.$group['groupid'].'" /><label for="extgid_'.$group['groupid'].'"> '.$group['grouptitle'].'</label>',
					'<input type="text" class="txt" size="9" name="extgroupexpirynew['.$group['groupid'].']" value="'.(in_array($group['groupid'], $extgrouparray) && !empty($member['groupterms']['ext'][$group['groupid']]) ? gmdate('Y-n-j', $member['groupterms']['ext'][$group['groupid']] + $timeoffset * 3600) : '').'" onclick="showcalendar(event, this)" />'
				), TRUE);
			}
			if($group['groupid'] && $group['type'] == 'member' && !($member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) && $member['groupid'] != $group['groupid']) {
				continue;
			}

			$expgroups .= '<option name="expgroupidnew" value="'.$group['groupid'].'" '.$selectegid[$group['groupid']].'>'.$group['grouptitle'].'</option>';

			if($group['groupid'] != 0) {
				$groups[$group['type']] .= '<option value="'.$group['groupid'].'"'.($member['groupid'] == $group['groupid'] ? 'selected="selected"' : '').' gtype="'.$group['type'].'">'.$group['grouptitle'].'</option>';
				if($group['type'] == 'special' && !$group['radminid']) {
					$radmingids .= ','.$group['groupid'];
				}
			}

		} while($group = $db->fetch_array($query));

		if(!$groups['member']) {
			$group = $db->fetch_first("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type='member' AND creditshigher>='0' ORDER BY creditshigher LIMIT 1");
			$groups['member'] = '<option value="'.$group['groupid'].'" gtype="member">'.$group['grouptitle'].'</option>';
		}

		shownav('user', 'members_edit_groups');
		eval("\$lang[members_edit_member_group] = \"".$lang['members_edit_member_group']."\";");
		showsubmenu('members_edit_member_group');
		echo '<script src="include/javascript/calendar.js" type="text/javascript"></script>';
		showformheader("members&operation=editgroups&uid=$member[uid]");
		showtableheader('usergroup', 'nobottom');
		showsetting('members_edit_groups_group', '', '', '<select name="groupidnew" onchange="if(in_array(this.value, ['.$radmingids.'])) {$(\'relatedadminid\').style.display = \'\';$(\'adminidnew\').name=\'adminidnew[\' + this.value + \']\';} else {$(\'relatedadminid\').style.display = \'none\';$(\'adminidnew\').name=\'adminidnew[0]\';}"><optgroup label="'.$lang['usergroups_system'].'">'.$groups['system'].'<optgroup label="'.$lang['usergroups_special'].'">'.$groups['special'].'<optgroup label="'.$lang['usergroups_member'].'">'.$groups['member'].'</select>');
		showtagheader('tbody', 'relatedadminid', $member['grouptype'] == 'special' && !$member['radminid'], 'sub');
		showsetting('members_edit_groups_related_adminid', '', '', '<select id="adminidnew" name="adminidnew[0]"><option value="0"'.($member['adminid'] == 0 ? ' selected' : '').'>'.$lang['none'].'</option><option value="3"'.($member['adminid'] == 3 ? ' selected' : '').'>'.$lang['usergroups_system_3'].'</option><option value="2"'.($member['adminid'] == 2 ? ' selected' : '').'>'.$lang['usergroups_system_2'].'</option><option value="1"'.($member['adminid'] == 1 ? ' selected' : '').'>'.$lang['usergroups_system_1'].'</option></select>');
		showtagfooter('tbody');
		showsetting('members_edit_groups_validity', 'expirydaysnew', $expirydate, 'calendar');
		showsetting('members_edit_groups_orig_adminid', '', '', '<select name="expgroupidnew">'.$expgroups.'</select>');
		showsetting('members_edit_groups_orig_groupid', '', '', '<select name="expadminidnew"><option value="0" '.$selecteaid[0].'>'.$lang['usergroups_system_0'].'</option><option value="1" '.$selecteaid[1].'>'.$lang['usergroups_system_1'].'</option><option value="2" '.$selecteaid[2].'>'.$lang['usergroups_system_2'].'</option><option value="3" '.$selecteaid[3].'>'.$lang['usergroups_system_3'].'</option></select>');
		showtablefooter();

		showtableheader('members_edit_groups_extended', 'noborder fixpadding');
		showsubtitle(array('usergroup', 'validity'));
		echo $extgroups;
		showtablerow('', 'colspan="2"', lang('members_edit_groups_extended_comment'));
		showtablefooter();

		showtableheader('members_edit_reason', 'notop');
		showsetting('members_edit_groups_ban_reason', 'reason', '', 'textarea');
		showsubmit('editsubmit');
		showtablefooter();

		showformfooter();

	} else {

		$group = $db->fetch_first("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$groupidnew'");
		if(!$group) {
			cpmsg('undefined_action', '', 'error');
		}

		if(strlen(is_array($extgroupidsnew) ? implode("\t", $extgroupidsnew) : '') > 60) {
			cpmsg('members_edit_groups_toomany', '', 'error');
		}

		$adminidnew = $adminidnew[$groupidnew];
		switch($group['type']) {
			case 'member':
				$groupidnew = in_array($adminidnew, array(1, 2, 3)) ? $adminidnew : $groupidnew;
				break;
			case 'special':
				if($group['radminid']) {
					$adminidnew = $group['radminid'];
				} elseif(!in_array($adminidnew, array(1, 2, 3))) {
					$adminidnew = -1;
				}
				break;
			case 'system':
				$adminidnew = in_array($groupidnew, array(1, 2, 3)) ? $groupidnew : -1;
				break;
		}

		$groupterms = array();
		if($expirytype == 'date' && $expirydatenew) {
			$maingroupexpirynew = strtotime($expirydatenew) - date('Z') + $timeoffset * 3600;
		} elseif($expirytype == 'days' && $expirydaysnew) {
			$maingroupexpirynew = $timestamp + $expirydaysnew * 86400;
		} else {
			$maingroupexpirynew = 0;
		}

		if($maingroupexpirynew) {

			$group = $db->fetch_first("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$expgroupidnew'");
			if(!$group) {
				$expgroupidnew = in_array($expadminidnew, array(1, 2, 3)) ? $expadminidnew : $expgroupidnew;
			} else {
				switch($group['type']) {
					case 'special':
						if($group['radminid']) {
							$expadminidnew = $group['radminid'];
						} elseif(!in_array($expadminidnew, array(1, 2, 3))) {
							$expadminidnew = -1;
						}
						break;
					case 'system':
						$expadminidnew = in_array($expgroupidnew, array(1, 2, 3)) ? $expgroupidnew : -1;
						break;
				}
			}

			if($expgroupidnew == $groupidnew) {
				cpmsg('members_edit_groups_illegal', '', 'error');
			} elseif($maingroupexpirynew > $timestamp) {
				if($expgroupidnew || $expadminidnew) {
					$groupterms['main'] = array('time' => $maingroupexpirynew, 'adminid' => $expadminidnew, 'groupid' => $expgroupidnew);
				} else {
					$groupterms['main'] = array('time' => $maingroupexpirynew);
				}
				$groupterms['ext'][$groupidnew] = $maingroupexpirynew;
			}

		}

		if(is_array($extgroupexpirynew)) {
			foreach($extgroupexpirynew as $extgroupid => $expiry) {
				if(is_array($extgroupidsnew) && in_array($extgroupid, $extgroupidsnew) && !isset($groupterms['ext'][$extgroupid]) && $expiry && ($expiry = strtotime($expiry) - date('Z') + $timeoffset * 3600) > $timestamp) {
					$groupterms['ext'][$extgroupid] = $expiry;
				}
			}
		}

		$grouptermsnew = addslashes(serialize($groupterms));
		$groupexpirynew = groupexpiry($groupterms);
		$extgroupidsnew = $extgroupidsnew && is_array($extgroupidsnew) ? implode("\t", $extgroupidsnew) : '';

		$db->query("UPDATE {$tablepre}members SET groupid='$groupidnew', adminid='$adminidnew', extgroupids='$extgroupidsnew', groupexpiry='$groupexpirynew' WHERE uid='$member[uid]'");
		$db->query("UPDATE {$tablepre}memberfields SET groupterms='$grouptermsnew' WHERE uid='$member[uid]'");

		if($groupidnew != $member['groupid'] && (in_array($groupidnew, array(4, 5)) || in_array($member['groupid'], array(4, 5)))) {
			banlog($member['username'], $member['groupid'], $groupidnew, $groupexpirynew, $reason);
		}

		cpmsg('members_edit_groups_succeed', "admincp.php?action=members&operation=editgroups&uid=$member[uid]", 'succeed');

	}

} elseif($operation == 'editcredits' && $uid && $extcredits) {

	$member = $db->fetch_first("SELECT m.*, u.grouptitle, u.type, u.creditslower, u.creditshigher
		FROM {$tablepre}members m
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE uid='$uid'");
	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow', '', 'error');
	}

	if(!submitcheck('creditsubmit')) {

		eval("\$membercredit = @round($creditsformula);");

		if($jscreditsformula = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='creditsformula'")) {
			$jscreditsformula = str_replace(array('digestposts', 'posts', 'pageviews', 'oltime'), array($member['digestposts'], $member['posts'],$member['pageviews'],$member['oltime']), $jscreditsformula);
		}

		$creditscols = array('members_edit_credits_ranges', 'credits');
		$creditsvalue = array($member['type'] == 'member' ? "$member[creditshigher]~$member[creditslower]" : 'N/A', '<input type="text" class="txt" name="jscredits" id="jscredits" value="'.$membercredit.'" size="3" disabled>');
		for($i = 1; $i <= 8; $i++) {
			$jscreditsformula = str_replace('extcredits'.$i, "extcredits[$i]", $jscreditsformula);
			$creditscols[] = isset($extcredits[$i]) ? $extcredits[$i]['title'] : 'extcredits'.$i;
			$creditsvalue[] = isset($extcredits[$i]) ? '<input type="text" class="txt" size="3" name="extcreditsnew['.$i.']" id="extcreditsnew['.$i.']" value="'.$member['extcredits'.$i].'" onkeyup="membercredits()"> '.$extcredits['$i']['unit'] : '<input type="text" class="txt" size="3" value="N/A" disabled>';
		}

		echo <<<EOT
<script language="JavaScript">
	var extcredits = new Array();
	function membercredits() {
		var credits = 0;
		for(var i = 1; i <= 8; i++) {
			e = $('extcreditsnew['+i+']');
			if(e && parseInt(e.value)) {
				extcredits[i] = parseInt(e.value);
			} else {
				extcredits[i] = 0;
			}
		}
		$('jscredits').value = Math.round($jscreditsformula);
	}
</script>
EOT;
		shownav('user', 'members_edit_credits');
		showsubmenu('members_edit_credits');
		showtips('members_edit_credits_tips');
		showformheader("members&operation=editcredits&uid=$uid");
		showtableheader(lang('members_edit_credits').' - '.$member['username']."($member[grouptitle])", 'nobottom');
		showsubtitle($creditscols);
		showtablerow('', array('', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $creditsvalue);
		showtablefooter();
		showtableheader('', 'notop');
		showtitle('members_edit_reason');
		showsetting('members_edit_credits_reason', 'reason', '', 'textarea');
		showsubmit('creditsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$diffarray = array();
		$sql = $comma = '';
		if(is_array($extcreditsnew)) {
			foreach($extcreditsnew as $id => $value) {
				if($member['extcredits'.$id] != ($value = intval($value))) {
					$diffarray[$id] = $value - $member['extcredits'.$id];
					$sql .= $comma."extcredits$id='$value'";
					$comma = ', ';
				}
			}
		}

		if($diffarray) {
			if(empty($reason)) {
				cpmsg('members_edit_reason_invalid', '', 'error');
			}

			foreach($diffarray as $id => $diff) {
				$logs[] = dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$member[username]\t$id\t$diff\t0\t\t$reason");
			}
			$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$uid'");
			writelog('ratelog', $logs);
		}

		cpmsg('members_edit_credits_succeed', "admincp.php?action=members&operation=editcredits&uid=$uid", 'succeed');

	}

} elseif($operation == 'editmedals' && $uid) {

	$member = $db->fetch_first("SELECT m.uid, m.username, mf.medals
		FROM {$tablepre}memberfields mf, {$tablepre}members m
		WHERE mf.uid='$uid' AND m.uid=mf.uid");

	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$membermedals = array();
		@include_once DISCUZ_ROOT.'./forumdata/cache/cache_medals.php';
		foreach (explode("\t", $member['medals']) as $key => $membermedal) {
			list($medalid, $medalexpiration) = explode("|", $membermedal);
			if(isset($_DCACHE['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > $timestamp)) {
				$membermedals[$key] = $medalid;
			} else {
				unset($membermedals[$key]);
			}
		}
		$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available='1'");
		while($medal = $db->fetch_array($query)) {
			$medals .= showtablerow('', array('class="td25"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" ".(in_array($medal['medalid'], $membermedals) ? 'checked' : '')." />",
				"<img src=\"images/common/$medal[image]\" />",
				$medal['name']

			), TRUE);
		}

		if(!$medals) {
			cpmsg('members_edit_medals_nonexistence', '', 'error');
		}

		shownav('user', 'nav_members_confermedal');
		showsubmenu('nav_members_confermedal');
		showformheader("members&operation=editmedals&uid=$uid");
		showtableheader("$lang[members_confermedals_to] <a href='space.php?uid=$uid' target='_blank'>$member[username]</a>", 'fixpadding');
		showsubtitle(array('medals_grant', 'medals_image', 'name'));
		echo $medals;
		showsubmit('medalsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$medalids = $comma = '';
		$medalsdel = $medalsadd = $medalsnew = $origmedalsarray = $medalsarray = array();
		if(is_array($medals)) {
			foreach($medals as $medalid => $newgranted) {
				if($newgranted) {
					$medalsarray[] = $medalid;
					$medalids .= "$comma'$medalid'";
					$comma = ',';
				}
			}
		}
		@include_once DISCUZ_ROOT.'./forumdata/cache/cache_medals.php';
		foreach($member['medals'] = explode("\t", $member['medals']) as $key => $modmedalid) {
			list($medalid, $medalexpiration) = explode("|", $modmedalid);
			if(isset($_DCACHE['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > $timestamp)) {
				$origmedalsarray[] = $medalid;
			}
		}
		foreach(array_unique(array_merge($origmedalsarray, $medalsarray)) as $medalid) {
			if($medalid) {
				$orig = in_array($medalid, $origmedalsarray);
				$new = in_array($medalid, $medalsarray);
				if($orig != $new) {
					if($orig && !$new) {
						$medalsdel[] = $medalid;
					} elseif(!$orig && $new) {
						$medalsadd[] = $medalid;
					}
				}
			}
		}
		if(!empty($medalids)) {
			$query = $db->query("SELECT * FROM {$tablepre}medals WHERE medalid IN ($medalids)");
			while($modmedal = $db->fetch_array($query)) {
					if(empty($modmedal['expiration'])) {
						$medalsnew[] = $modmedal[medalid];
						$medalstatus = 0;
					} else {
						$modmedal['expiration'] = $timestamp + $modmedal['expiration'] * 86400;
						$medalsnew[] = $modmedal[medalid].'|'.$modmedal['expiration'];
						$medalstatus = 1;
					}
				if(in_array($modmedal['medalid'], $medalsadd)) {
					$db->query("INSERT INTO {$tablepre}medallog (uid, medalid, type, dateline, expiration, status) VALUES ('$uid', '".$modmedal[medalid]."', '0', '$timestamp', '".$modmedal['expiration']."', '$medalstatus')");
				}
			}
		}
		if(!empty($medalsdel)) {
			$db->query("UPDATE {$tablepre}medallog SET status='0' WHERE uid='$uid' AND medalid IN (".implode(',', $medalsdel).")");
		}
		$medalsnew = implode("\t", $medalsnew);

		$db->query("UPDATE {$tablepre}memberfields SET medals='$medalsnew' WHERE uid='$uid'");

		cpmsg('members_edit_medals_succeed', "admincp.php?action=members&operation=editmedals&uid=$uid", 'succeed');

	}

} elseif($operation == 'ban') {

	if(!$allowbanuser) {
		cpmsg('action_noaccess', '', 'error');
	}

	$member = array();
	if(!empty($username) || !empty($uid)) {
		$member = $db->fetch_first("SELECT m.*, mf.*, u.type AS grouptype, u.allowsigbbcode, u.allowsigimgcode FROM {$tablepre}members m
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
			WHERE ".($uid ? "m.uid='$uid'" : "m.username='$username'"));

		if(!$member) {
			cpmsg('members_edit_nonexistence', '', 'error');
		} elseif(($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['grouptype'] == 'special') {
			cpmsg('members_edit_illegal', '', 'error');
		}

		$member['groupterms'] = unserialize($member['groupterms']);
		$member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? gmdate('Y-n-j', $member['groupterms']['main']['time'] + $timeoffset * 3600) : '';
	}

	if(!submitcheck('bansubmit')) {

		echo '<script src="include/javascript/calendar.js" type="text/javascript"></script>';
		shownav('user', 'members_edit_ban_user');
		showsubmenu($lang['members_edit_ban_user'].($member['username'] ? ' -'.$member['username'] : ''));
		showformheader('members&operation=ban');
		showtableheader();
		showsetting('members_edit_ban_username', 'username', $member['username'], 'text');
		if($member) {
			showtablerow('class="nobg"', 'class="td27"', lang('members_edit_current_status').'<span class="normal">: '.($member['groupid'] == 4 ? $lang['members_edit_ban_post'] : ($member['groupid'] == 5 ? $lang['members_edit_ban_visit'] : $lang['members_edit_ban_none'])).'</span>');
		}
		showsetting('members_edit_ban', array('bannew', array(
			array('', $lang['members_edit_ban_none']),
			array('post', $lang['members_edit_ban_post']),
			array('visit', $lang['members_edit_ban_visit'])
		)), '0', 'mradio');
		showsetting('members_edit_ban_validity', '', '', selectday('banexpirynew', array(0, 1, 3, 5, 7, 14, 30, 60, 90, 180, 365)));
		showsetting('members_edit_ban_delpost', 'delpost', '', 'radio');
		showsetting('members_edit_ban_reason', 'reason', '', 'textarea');
		showsubmit('bansubmit');
		showtablefooter();
		showformfooter();

	} else {

		$sql = 'uid=uid';
		$reason = trim($reason);
		if(!$reason && ($reasonpm == 1 || $reasonpm == 3)) {
			cpmsg('members_edit_reason_invalid', '', 'error');
		}

		if($bannew == 'post' || $bannew == 'visit') {
			$groupidnew = $bannew == 'post' ? 4 : 5;
			$banexpirynew = !empty($banexpirynew) ? $timestamp + $banexpirynew * 86400 : 0;
			$banexpirynew = $banexpirynew > $timestamp ? $banexpirynew : 0;
			if($banexpirynew) {
				$member['groupterms']['main'] = array('time' => $banexpirynew, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
				$member['groupterms']['ext'][$groupidnew] = $banexpirynew;
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$sql .= ', groupexpiry=0';
			}
			$adminidnew = -1;
		} elseif($member['groupid'] == 4 || $member['groupid'] == 5) {
			if(!empty($member['groupterms']['main']['groupid'])) {
				$groupidnew = $member['groupterms']['main']['groupid'];
				$adminidnew = $member['groupterms']['main']['adminid'];
				unset($member['groupterms']['main']);
				unset($member['groupterms']['ext'][$member['groupid']]);
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$groupidnew = $db->result_first("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
				$adminidnew = 0;
			}
		} else {
			$groupidnew = $member['groupid'];
			$adminidnew = $member['adminid'];
		}

		$sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
		$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$member[uid]'");

		if($allowbanuser && ($db->affected_rows($query))) {
			banlog($member['username'], $member['groupid'], $groupidnew, $banexpirynew, $reason);
		}

		$db->query("UPDATE {$tablepre}memberfields SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");

		if($delpost && $bannew && $adminid == 1) {
			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE uid='$member[uid]'");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			}

			$db->query("DELETE FROM {$tablepre}threads WHERE authorid='$member[uid]'", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}posts WHERE authorid='$member[uid]'", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}trades WHERE sellerid='$member[uid]'", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE uid='$member[uid]'", 'UNBUFFERED');
		}

		cpmsg('members_edit_succeed', 'admincp.php?action=members&operation=ban', 'succeed');

	}

} elseif($operation == 'access') {

	$member = $db->fetch_first("SELECT username, adminid, groupid FROM {$tablepre}members WHERE uid='$uid'");
	if(!$member) {
		cpmsg('undefined_action', '', 'error');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow', '', 'error');
	}

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	if(!submitcheck('accesssubmit')) {

		echo <<<EOT
<script type="text/JavaScript">
	function accessdefaultall(theform) {
		var checkboxes = theform.getElementsByTagName('input');
		for(var i = 0; i < checkboxes.length; i++) {
			if(checkboxes[i].id.substring(0, 8) == 'default_') accessdefault(checkboxes[i].id.substring(8));
		}
	}
	function accessdefault(fid) {
		var access = ['chkallv', 'allowview', 'allowpost', 'allowreply', 'allowgetattach', 'allowpostattach'];
		for(var i in access) {
			$(access[i] + '_' + fid).checked = false;
		}
	}
	function accessall(obj, fid) {
		$('default_' + fid).checked = obj.checked ? false : true;
	}
	function access(obj, fid) {
		if(obj.checked) $('default_' + fid).checked = false;
	}
</script>
EOT;
		shownav('user', 'members_access_edit');
		showsubmenu('members_access_edit');
		showtips('members_access_tips');
		showformheader("members&operation=access&uid=$uid");
		showtableheader(lang('members_access_edit').' - '.$member['username'], 'fixpadding');
		showsubtitle(array(lang('forum'), '<input class="checkbox" type="checkbox" name="chkall1" onclick="checkAll(\'prefix\', this.form, \'defaultnew\', \'chkall1\');accessdefaultall(this.form);" /> '.lang('members_access_default'), '<input class="checkbox" type="checkbox" name="chkall2" onclick="checkAll(\'prefix\', this.form, \'allowviewnew\', \'chkall2\')"> '.lang('members_access_view'), '<input class="checkbox" type="checkbox" name="chkall3" onclick="checkAll(\'prefix\', this.form, \'allowpostnew\', \'chkall3\')"> '.lang('members_access_post'), '<input class="checkbox" type="checkbox" name="chkall4" onclick="checkAll(\'prefix\', this.form, \'allowreplynew\', \'chkall4\')"> '.lang('members_access_reply'), '<input class="checkbox" type="checkbox" name="chkall5" onclick="checkAll(\'prefix\', this.form, \'allowgetattachnew\', \'chkall5\')"> '.lang('members_access_getattach'), '<input class="checkbox" type="checkbox" name="chkall6" onclick="checkAll(\'prefix\', this.form, \'allowpostattachnew\', \'chkall6\')"> '.lang('members_access_postattach')));

		$accessmasks = array();
		$query = $db->query("SELECT * FROM {$tablepre}access WHERE uid='$uid'");
		while($access = $db->fetch_array($query)) {
			$accessmasks[$access['fid']] = $access;
		}

		foreach($_DCACHE['forums'] as $fid => $forum) {
			if($forum['type'] != 'group') {
				if(isset($accessmasks[$fid])) {
					$check = array(
						'default'	=> '',
						'view'		=> ($accessmasks[$fid]['allowview'] ? 'checked' : ''),
						'post'		=> ($accessmasks[$fid]['allowpost'] ? 'checked' : ''),
						'reply'		=> ($accessmasks[$fid]['allowreply'] ? 'checked' : ''),
						'getattach'	=> ($accessmasks[$fid]['allowgetattach'] ? 'checked' : ''),
						'postattach'	=> ($accessmasks[$fid]['allowpostattach'] ? 'checked' : ''));
				} else {
					$check = array(
						'default'	=> 'checked',
						'view'		=> '',
						'post'		=> '',
						'reply'		=> '',
						'getattach'	=> '',
						'postattach'	=> '');
				}

				showtablerow('', '', array(
					"<input class=\"checkbox\" title=\"$lang[select_all]\" type=\"checkbox\" name=\"chkallv_$fid\" id=\"chkallv_$fid\" onclick=\"checkAll('value', this.form, $fid, 'chkallv_$fid');accessall(this, '$fid')\">".($forum['type'] == 'forum' ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')."&nbsp;<a href=\"admincp.php?action=forums&operation=edit&fid=$fid\">$forum[name]</a>",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"defaultnew[$fid]\" value=\"1\" $check[default] id=\"default_$fid\" onclick=\"accessdefault('$fid')\">",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"allowviewnew[$fid]\" value=\"$fid\" $check[view] id=\"allowview_$fid\" onclick=\"access(this, '$fid')\">",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"allowpostnew[$fid]\" value=\"$fid\" $check[post] id=\"allowpost_$fid\" onclick=\"access(this, '$fid')\">",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"allowreplynew[$fid]\" value=\"$fid\" $check[reply] id=\"allowreply_$fid\" onclick=\"access(this, '$fid')\">",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"allowgetattachnew[$fid]\" value=\"$fid\" $check[getattach] id=\"allowgetattach_$fid\" onclick=\"access(this, '$fid')\">",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"allowpostattachnew[$fid]\" value=\"$fid\" $check[postattach] id=\"allowpostattach_$fid\" onclick=\"access(this, '$fid')\">"
				));
			}
		}

		showsubmit('accesssubmit', 'submit', '<input type="reset" class="btn" name="accesssubmit" value="'.lang('reset').'">');
		showtablefooter();
		showformfooter();

	} else {

		$accessarray = array();
		if(is_array($_DCACHE['forums'])) {
			foreach($_DCACHE['forums'] as $fid => $forum) {
				if($forum['type'] != 'group') {
					if(!$defaultnew[$fid] && ($allowviewnew[$fid] || $allowpostnew[$fid] || $allowreplynew[$fid] || $allowgetattachnew[$fid] || $allowpostattachnew[$fid])) {
						$allowviewnew[$fid] = $allowviewnew[$fid] == $fid ? 1 : 0;
						$allowpostnew[$fid] = $allowpostnew[$fid] == $fid ? 1 : 0;
						$allowreplynew[$fid] = $allowreplynew[$fid] == $fid ? 1 : 0;
						$allowgetattachnew[$fid] = $allowgetattachnew[$fid] == $fid ? 1 : 0;
						$allowpostattachnew[$fid] = $allowpostattachnew[$fid] == $fid ? 1 : 0;
						$accessarray[$fid] = "'$allowviewnew[$fid]', '$allowpostnew[$fid]', '$allowreplynew[$fid]', '$allowgetattachnew[$fid]', '$allowpostattachnew[$fid]'";
					}
				}
			}
		}

		$db->query("DELETE FROM {$tablepre}access WHERE uid='$uid'");
		$db->query("UPDATE {$tablepre}members SET accessmasks='".($accessarray ? 1 : 0)."' WHERE uid='$uid'");

		foreach($accessarray as $fid => $access) {
			$db->query("INSERT INTO {$tablepre}access (uid, fid, allowview, allowpost, allowreply, allowgetattach, allowpostattach)
					VALUES ('$uid', '$fid', $access)");
		}

		updatecache('forums');

		cpmsg('members_access_succeed', 'admincp.php?action=members&operation=access&uid='.$uid, 'succeed');

	}

} elseif($operation == 'profile') {

	$member = $db->fetch_first("SELECT m.*, mf.*, o.*, u.type, u.allowsigbbcode, u.allowsigimgcode, u.allowcusbbcode, u.allowbiobbcode, u.allowbioimgcode, u.allowcusbbcode FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		LEFT JOIN {$tablepre}onlinetime o ON o.uid=m.uid
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE m.uid='$uid'");

	if(!$member) {
		cpmsg('undefined_action', '', 'error');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow', '', 'error');
	}
	$member['uid'] = intval($uid);

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_profilefields.php';
	$fields = array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']);

	if(!submitcheck('editsubmit')) {

		require_once DISCUZ_ROOT.'./include/editor.func.php';

		$styleselect = "<select name=\"styleidnew\">\n<option value=\"\">$lang[use_default]</option>";
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$styleselect .= "<option value=\"$style[styleid]\" ".($style['styleid'] == $member['styleid'] ? 'selected="selected"' : '').">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		$tfcheck = array($member['timeformat'] => 'checked');
		$gendercheck = array($member['gender'] => 'checked');
		$pscheck = array($member['pmsound'] => 'checked');

		$member['regdate'] = gmdate('Y-n-j h:i A', $member['regdate'] + $timeoffset * 3600);
		$member['lastvisit'] = gmdate('Y-n-j h:i A', $member['lastvisit'] + $timeoffset * 3600);

		$member['bio'] = html2bbcode($member['bio']);
		$member['signature'] = html2bbcode($member['sightml']);

		shownav('user', 'members_edit');
		showsubmenu("$lang[members_edit] - $member[username]");
		showformheader("members&operation=profile&uid=$uid");
		showtableheader();
		showsetting('members_edit_username', '', '', ' '.$member['username']);
		showsetting('members_edit_password', 'passwordnew', '', 'text');
		showsetting('members_edit_clearquestion', 'clearquestion', !$member['secques'], 'radio');
		showsetting('members_edit_nickname', 'nicknamenew', $member['nickname'], 'text');
		showsetting('members_edit_gender', '', '', '<input class="radio" type="radio" name="gendernew" value="1" '.$gendercheck[1].'> '.$lang['members_edit_gender_male'].' <input class="radio" type="radio" name="gendernew" value="2" '.$gendercheck[2].'> '.$lang['members_edit_gender_female'].' <input class="radio" type="radio" name="gendernew" value="0" '.$gendercheck[0].'> '.$lang['members_edit_gender_secret']);
		showsetting('members_edit_email', 'emailnew', $member['email'], 'text');
		showsetting('members_edit_posts', 'postsnew', $member['posts'], 'text');
		showsetting('members_edit_digestposts', 'digestpostsnew', $member['digestposts'], 'text');
		showsetting('members_edit_pageviews', 'pageviewsnew', $member['pageviews'], 'text');
		showsetting('members_edit_online_total', 'totalnew', $member['total'], 'text');
		showsetting('members_edit_online_thismonth', 'thismonthnew', $member['thismonth'], 'text');
		showsetting('members_edit_regip', 'regipnew', $member['regip'], 'text');
		showsetting('members_edit_regdate', 'regdatenew', $member['regdate'], 'text');
		showsetting('members_edit_lastvisit', 'lastvisitnew', $member['lastvisit'], 'text');
		showsetting('members_edit_lastip', 'lastipnew', $member['lastip'], 'text');

		showtitle('members_edit_info');
		showsetting('members_edit_site', 'sitenew', $member['site'], 'text');
		showsetting('members_edit_qq', 'qqnew', $member['qq'], 'text');
		showsetting('members_edit_icq', 'icqnew', $member['icq'], 'text');
		showsetting('members_edit_yahoo', 'yahoonew', $member['yahoo'], 'text');
		showsetting('members_edit_msn', 'msnnew', $member['msn'], 'text');
		showsetting('members_edit_taobao', 'taobaonew', $member['taobao'], 'text');
		showsetting('members_edit_alipay', 'alipaynew', $member['alipay'], 'text');
		showsetting('members_edit_location', 'locationnew', $member['location'], 'text');
		showsetting('members_edit_bday', 'bdaynew', $member['bday'], 'text');
		showsetting('members_edit_bio', 'bionew', $member['bio'], 'textarea');
		showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');

		showtitle('members_edit_option');
		showsetting('members_edit_style', '', '', $styleselect);
		showsetting('members_edit_tpp', 'tppnew', $member['tpp'], 'text');
		showsetting('members_edit_ppp', 'pppnew', $member['ppp'], 'text');
		showsetting('members_edit_cstatus', 'cstatusnew', $member['customstatus'], 'text');
		showsetting('members_edit_timeformat', '', '', '<input class="radio" type="radio" name="timeformatnew" value="0" '.$tfcheck[0].'> '.$lang['default'].' &nbsp; <input class="radio" type="radio" name="timeformatnew" value="1" '.$tfcheck[1].'> '.$lang['members_edit_timeformat_12'].' &nbsp; <input class="radio" type="radio" name="timeformatnew" value="2" '.$tfcheck[2].'> '.$lang['members_edit_timeformat_24']);
		showsetting('members_edit_timeoffset', 'timeoffsetnew', $member['timeoffset'], 'text');
		showsetting('members_edit_pmsound', '', '', '<input class="radio" type="radio" value="0" name="pmsoundnew" '.$pscheck[0].'>'.$lang['none'].' &nbsp; <input class="radio" type="radio" value="1" name="pmsoundnew" '.$pscheck[1].'><a href="images/sound/pm_1.wav">#1</a> &nbsp; <input class="radio" type="radio" value="2" name="pmsoundnew" '.$pscheck[2].'><a href="images/sound/pm_2.wav">#2</a> &nbsp; <input class="radio" type="radio" value="3" name="pmsoundnew" '.$pscheck[3].'><a href="images/sound/pm_3.wav">#3</a>');
		showsetting('members_edit_invisible', 'invisiblenew', $member['invisible'], 'radio');
		showsetting('members_edit_showemail', 'showemailnew', $member['showemail'], 'radio');
		showsetting('members_edit_newsletter', 'newsletternew', $member['newsletter'], 'radio');
		showsetting('members_edit_ignorepm', 'ignorepmnew', $member['ignorepm'], 'textarea');

		if($fields) {
			showtitle('members_edit_profilefield');
			foreach($fields as $field) {
				if($field['selective']) {
					$fieldselect = "<select name=\"field_$field[fieldid]new\"><option value=\"\">&nbsp;</option>";
					foreach($field['choices'] as $index => $choice) {
						$fieldselect .= "<option value=\"$index\" ".($index == $member['field_'.$field['fieldid']] ? 'selected="selected"' : '').">$choice</option>";
					}
					$fieldselect .= '</select>';
					showsetting($field['title'], '', '', $fieldselect);
				} else {
					showsetting($field['title'], "field_$field[fieldid]new", $member['field_'.$field['fieldid']], 'text');
				}
			}
		}

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		require_once DISCUZ_ROOT.'./uc_client/client.php';
		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$ucresult = uc_user_edit($member['username'], $passwordnew, $passwordnew, $emailnew, 1);


		$creditsnew = intval($creditsnew);

		$regdatenew = strtotime($regdatenew);
		$lastvisitnew = strtotime($lastvisitnew);

		$passwordnew = ", password='".md5(random(10))."'";
		$secquesadd = $clearquestion ? ", secques=''" : '';

		$signaturenew = censor($signaturenew);
		$sigstatusnew = $signaturenew ? 1 : 0;
		$sightmlnew = addslashes(discuzcode(stripslashes($signaturenew), 1, 0, 0, 0, ($member['allowsigbbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowsigimgcode'], 0));
		$bionew = censor(dhtmlspecialchars($bionew));
		$biohtmlnew = addslashes(discuzcode(stripslashes($bionew), 1, 0, 0, 0, ($member['allowbiobbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowbioimgcode'], 0));

		$oltimenew = round($totalnew / 60);

		$fieldadd = '';
		foreach(array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']) as $field) {
			$field_key = 'field_'.$field['fieldid'];
			$field_val = trim(${'field_'.$field['fieldid'].'new'});
			if($field['selective'] && $field_val != '' && !isset($field['choices'][$field_val])) {
				cpmsg('undefined_action', '', 'error');
			} else {
				$fieldadd .= ", $field_key='".dhtmlspecialchars($field_val)."'";
			}
		}

		$emailadd = $ucresult < 0 ? '' : "email='$emailnew', ";
		$passwordadd = $ucresult < 0 ? '' : $passwordadd;

		$db->query("UPDATE {$tablepre}members SET gender='$gendernew', $emailadd posts='$postsnew', digestposts='$digestpostsnew',
			pageviews='$pageviewsnew', regip='$regipnew', regdate='$regdatenew', lastvisit='$lastvisitnew', lastip='$lastipnew', bday='$bdaynew',
			styleid='$styleidnew', tpp='$tppnew', ppp='$pppnew', timeformat='$timeformatnew', oltime='$oltimenew',
			showemail='$showemailnew', newsletter='$newsletternew', invisible='$invisiblenew', timeoffset='$timeoffsetnew',
			pmsound='$pmsoundnew', sigstatus='$sigstatusnew' $passwordadd $secquesadd WHERE uid='$uid'");

		$db->query("UPDATE {$tablepre}memberfields SET nickname='$nicknamenew', site='$sitenew', qq='$qqnew', icq='$icqnew', yahoo='$yahoonew', msn='$msnnew',
			taobao='$taobaonew', alipay='$alipaynew', location='$locationnew', bio='$biohtmlnew', customstatus='$cstatusnew', ignorepm='$ignorepmnew', sightml='$sightmlnew'
			$fieldadd WHERE uid='$uid'");

		$db->query("REPLACE INTO {$tablepre}onlinetime (uid, thismonth, total)
			VALUES ('$uid', '$thismonthnew', '$totalnew')");

		cpmsg('members_edit_succeed', '', 'succeed');

	}

} elseif($operation == 'profilefields') {

	if(!submitcheck('fieldsubmit') && !submitcheck('editsubmit') && !$edit) {

		$query = $db->query("SELECT * FROM {$tablepre}profilefields");
		while($field = $db->fetch_array($query)) {
			$profilefields .= showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$field[fieldid]}]\" value=\"$field[fieldid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[{$field[fieldid]}]\" value=\"$field[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"18\" name=\"titlenew[{$field[fieldid]}]\" value=\"$field[title]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[{$field[fieldid]}]\" value=\"1\" ".($field['available'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"invisiblenew[{$field[fieldid]}]\" value=\"1\" ".($field['invisible'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeablenew[{$field[fieldid]}]\" value=\"1\" ".($field['unchangeable'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"showinthreadnew[{$field[fieldid]}]\" value=\"1\" ".($field['showinthread'] ? 'checked' : '').">",
				"<a href=\"admincp.php?action=members&operation=profilefields&edit=$field[fieldid]\" class=\"act\">$lang[detail]</a>"
			), TRUE);
		}
		shownav('user', 'members_edit_profilefields');
		showsubmenu('members_edit_profilefields');

		showtips('members_edit_profilefields_tips');
		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'', 'td28'],
			[6,'<input type="text" class="txt" name="newtitle[]" size="18">']
		]
	];
</script>
EOT;
		showformheader('members&operation=profilefields');
		showtableheader();
		showsubtitle(array('', 'display_order', 'fields_title', 'available', 'fields_invisible', 'fields_unchangeable', 'fields_show_in_thread', ''));
		echo $profilefields;
		echo '<tr><td></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['members_edit_profilefields_add'].'</a></div></td></tr>';
		showsubmit('fieldsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} elseif(submitcheck('fieldsubmit')) {

		if(is_array($titlenew)) {
			foreach($titlenew as $id => $val) {
				$db->query("UPDATE {$tablepre}profilefields SET title='$titlenew[$id]', available='$availablenew[$id]', invisible='$invisiblenew[$id]', displayorder='$displayordernew[$id]', unchangeable='$unchangeablenew[$id]', showinthread='$showinthreadnew[$id]' WHERE fieldid='$id'");
			}
		}

		if(is_array($delete)) {
			$ids = implode('\',\'', $delete);
			$dropfields = implode(',DROP field_', $delete);
			$db->query("DELETE FROM {$tablepre}profilefields WHERE fieldid IN ('$ids')");
			$db->query("ALTER TABLE {$tablepre}memberfields DROP field_$dropfields");
		}

		if(is_array($newtitle)) {
			foreach($newtitle as $value) {
				if($value = trim($value)) {
					$db->query("INSERT INTO {$tablepre}profilefields (available, invisible, title, size)
							VALUES ('1', '0', '$value', '50')");
					$fieldid = $db->insert_id();
					$db->query("ALTER TABLE {$tablepre}memberfields ADD field_$fieldid varchar(50) NOT NULL", 'SILENT');
				}
			}
		}

		updatecache(array('fields_required', 'fields_optional', 'custominfo'));
		cpmsg('fields_edit_succeed', 'admincp.php?action=members&operation=profilefields', 'succeed');

	} elseif($edit) {

		$field = $db->fetch_first("SELECT * FROM {$tablepre}profilefields WHERE fieldid='$edit'");
		if(!$field) {
			cpmsg('undefined_action', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			showsubmenu("$lang[fields_edit] - $field[title]");
			showformheader("members&operation=profilefields&edit=$edit");
			showtableheader();
			showsetting('fields_edit_title', 'titlenew', $field['title'], 'text');
			showsetting('fields_edit_desc', 'descriptionnew', $field['description'], 'text');
			showsetting('fields_edit_size', 'sizenew', $field['size'], 'text');
			showsetting('fields_edit_invisible', 'invisiblenew', $field['invisible'], 'radio');
			showsetting('fields_edit_required', 'requirednew', $field['required'], 'radio');
			showsetting('fields_edit_unchangeable', 'unchangeablenew', $field['unchangeable'], 'radio');
			showsetting('fields_edit_show_in_thread', 'showinthreadnew', $field['showinthread'], 'radio');
			showsetting('fields_edit_selective', 'selectivenew', $field['selective'], 'radio');
			showsetting('fields_edit_choices', 'choicesnew', $field['choices'], 'textarea');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$titlenew = trim($titlenew);
			$sizenew = $sizenew <= 255 ? $sizenew : 255;
			if(!$titlenew || !$sizenew) {
				cpmsg('fields_edit_invalid', '', 'error');
			}

			if($sizenew != $field['size']) {
				$db->query("ALTER TABLE {$tablepre}memberfields CHANGE field_$edit field_$edit varchar($sizenew) NOT NULL");
			}

			$db->query("UPDATE {$tablepre}profilefields SET title='$titlenew', description='$descriptionnew', size='$sizenew', invisible='$invisiblenew', required='$requirednew', unchangeable='$unchangeablenew', showinthread='$showinthreadnew', selective='$selectivenew', choices='$choicesnew' WHERE fieldid='$edit'");

			updatecache(array('fields_required', 'fields_optional', 'custominfo'));
			cpmsg('fields_edit_succeed', 'admincp.php?action=members&operation=profilefields', 'succeed');
		}

	}

} elseif($operation == 'ipban') {

	if(!submitcheck('ipbansubmit')) {

		require_once DISCUZ_ROOT.'./include/misc.func.php';

		$iptoban = explode('.', $ip);

		$ipbanned = '';
		$query = $db->query("SELECT * FROM {$tablepre}banned ORDER BY dateline");
		while($banned = $db->fetch_array($query)) {
			for($i = 1; $i <= 4; $i++) {
				if($banned["ip$i"] == -1) {
					$banned["ip$i"] = '*';
				}
			}
			$disabled = $adminid != 1 && $banned['admin'] != $discuz_userss ? 'disabled' : '';
			$banned['dateline'] = gmdate($dateformat, $banned['dateline'] + $timeoffset * 3600);
			$banned['expiration'] = gmdate($dateformat, $banned['expiration'] + $timeoffset * 3600);
			$theip = "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4]";
			$ipbanned .= showtablerow('', array('class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$banned[id]]\" value=\"$banned[id]\" $disabled />",
				$theip,
				convertip($theip, "./"),
				$banned[admin],
				$banned[dateline],
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"expirationnew[$banned[id]]\" value=\"$banned[expiration]\" $disabled />"
			), TRUE);
		}
		shownav('user', 'nav_members_ipban');
		showsubmenu('nav_members_ipban');
		showtips('members_ipban_tips');
		showformheader('members&operation=ipban');
		showtableheader();
		showsubtitle(array('', 'ip', 'members_ipban_location', 'operator', 'start_time', 'end_time'));
		echo $ipbanned;
		showtablerow('', array('', 'class="td28" colspan="3"', 'class="td28" colspan="2"'), array(
			$lang['add_new'],
			'<input type="text" class="txt" name="ip1new" value="'.$iptoban[0].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip2new" value="'.$iptoban[1].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip3new" value="'.$iptoban[2].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip4new" value="'.$iptoban[3].'" size="3" maxlength="3">',
			$lang['validity'].': <input type="text" class="txt" name="validitynew" value="30" size="3"> '.$lang['members_ipban_days']
		));
		showsubmit('ipbansubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}banned WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if($ip1new != '' && $ip2new != '' && $ip3new != '' && $ip4new != '') {
			$own = 0;
			$ip = explode('.', $onlineip);
			for($i = 1; $i <= 4; $i++) {
				if(!is_numeric(${'ip'.$i.'new'}) || ${'ip'.$i.'new'} < 0) {
					if($adminid != 1) {
						cpmsg('members_ipban_nopermission', '', 'error');
					}
					${'ip'.$i.'new'} = -1;
					$own++;
				} elseif(${'ip'.$i.'new'} == $ip[$i - 1]) {
					$own++;
				}
				${'ip'.$i.'new'} = intval(${'ip'.$i.'new'});
			}

			if($own == 4) {
				cpmsg('members_ipban_illegal', '', 'error');
			}

			$query = $db->query("SELECT * FROM {$tablepre}banned");
			while($banned = $db->fetch_array($query)) {
				$exists = 0;
				for($i = 1; $i <= 4; $i++) {
					if($banned["ip$i"] == -1) {
						$exists++;
					} elseif($banned["ip$i"] == ${"ip".$i."new"}) {
						$exists++;
					}
				}
				if($exists == 4) {
					cpmsg('members_ipban_invalid', '', 'error');
				}
			}

			$expiration = $timestamp + $validitynew * 86400;

			$db->query("UPDATE {$tablepre}sessions SET groupid='6' WHERE ('$ip1new'='-1' OR ip1='$ip1new') AND ('$ip2new'='-1' OR ip2='$ip2new') AND ('$ip3new'='-1' OR ip3='$ip3new') AND ('$ip4new'='-1' OR ip4='$ip4new')");
			$db->query("INSERT INTO {$tablepre}banned (ip1, ip2, ip3, ip4, admin, dateline, expiration)
				VALUES ('$ip1new', '$ip2new', '$ip3new', '$ip4new', '$discuz_user', '$timestamp', '$expiration')");

		}

		if(is_array($expirationnew)) {
			foreach($expirationnew as $id => $expiration) {
				$db->query("UPDATE {$tablepre}banned SET expiration='".strtotime($expiration)."' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
			}
		}

		updatecache('ipbanned');
		cpmsg('members_ipban_succeed', 'admincp.php?action=members&operation=ipban', 'succeed');

	}

}

function searchmembers($operation = '') {
	global $db, $tablepre, $usergroupid, $username, $srchemail, $lower, $higher, $extcredits, $submit, $lang;

	$groupselect = '';
	$usergroupid = isset($usergroupid) && is_array($usergroupid) ? $usergroupid : array();
	$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid NOT IN ('6', '7') ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower");
	while($group = $db->fetch_array($query)) {
		$groupselect .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupid) ? 'selected' : '').">$group[grouptitle]</option>\n";
	}

	$monthselect = $dayselect = '';
	for($m=1; $m<=12; $m++) {
		$m = sprintf("%02d", $m);
		$monthselect .= "<option value=\"$m\" ".($birthmonth == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d=1; $d<=31; $d++) {
		$d = sprintf("%02d", $d);
		$dayselect .= "<option value=\"$d\" ".($birthday == $d ? 'selected' : '').">$d</option>\n";
	}

	showtagheader('div', 'searchmembers', !$submit);
	echo '<script src="include/javascript/calendar.js" type="text/javascript"></script>';
	showformheader("members&operation=$operation", "onSubmit=\"if($('updatecredittype1') && $('updatecredittype1').checked && !window.confirm('$lang[members_credits_clean_alarm]')){return false;} else {return true;}\"");
	showtableheader();
	showsetting('members_search_user', 'username', $username, 'text');
	showsetting('members_search_group', '', '', '<select name="usergroupid[]" multiple="multiple" size="10"><option value="all"'.(in_array('all', $usergroupid) ? ' selected' : '').'>'.lang('unlimited').'</option>'.$groupselect.'</select>');

	showtagheader('tbody', 'advanceoption');
	showsetting('members_search_email', 'srchemail', $srchemail, 'text');
	showsetting("$lang[credits] $lang[members_search_between]", array("higher[credits]", "lower[credits]"), array($higher[credits], $lower[credits]), 'range');

	if(!empty($extcredits)) {
		foreach($extcredits as $id => $credit) {
			showsetting("$credit[title] $lang[members_search_between]", array("higher[extcredits$id]", "lower[extcredits$id]"), array($higher['extcredits'.$id], $lower['extcredits'.$id]), 'range');
		}
	}

	showsetting('members_postsrange', array('postshigher', 'postslower'), array($postshigher, $postslower), 'range');
	showsetting('members_search_regip', 'regip', $regip, 'text');
	showsetting('members_search_lastip', 'lastip', $lastip, 'text');
	showsetting('members_search_regdaterange', array('regdateafter', 'regdatebefore'), array($regdateafter, $regdatebefore), 'daterange');
	showsetting('members_search_lastvisitrange', array('lastvisitafter', 'lastvisitbefore'), array($lastvisitafter, $lastvisitbefore), 'daterange');
	showsetting('members_search_lastpostrange', array('lastpostafter', 'lastpostbefore'), array($lastpostafter, $lastpostbefore), 'daterange');

	showsetting('members_search_birthday', '', '', '<input type="text" class="txt" name="birthyear" style="width:86px; margin-right:0" value="'.dhtmlspecialchars($year).'"> '.$lang['year'].' <input type="text" class="txt" name="birthmonth" style="width:45px; margin-right:0" value="'.dhtmlspecialchars($month).'"> '.$lang['month'].' <input type="text" class="txt" name="birthday" style="width:45px; margin-right:0" value="'.dhtmlspecialchars($day).'"> '.$lang['day']);
	showtagfooter('tbody');
	showsubmit('submit', $operation == 'clean' ? 'members_delete' : 'search', '', 'more_options');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
}

function countmembers() {
	extract($GLOBALS, EXTR_SKIP);
	global $memberperpage, $page, $start_limit, $membernum, $conditions, $urladd;

	$memberperpage = 100;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $memberperpage;
	$dateoffset = date('Z') - ($timeoffset * 3600);
	$username = trim($username);

	$conditions = '';
	if($username != '') {
		$usernames = explode(',', $username);
		foreach($usernames as $username) {
			$usernameary[] = " username LIKE '".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $username)."'";
		}
		$conditions .= " AND (".implode(' OR ', $usernameary).")";
	}

	$conditions .= $srchemail != '' ? " AND email LIKE '".str_replace('*', '%', $srchemail)."'" : '';
	$conditions .= !empty($usergroupid) && !in_array('all', $usergroupid) != '' ? " AND groupid IN ('".implode('\',\'', $usergroupid)."')" : '';

	if(is_array($higher)) {
		foreach($higher as $credit => $value) {
			$credit = intval(substr($credit, 10));
			if($value != '' && $credit > 0 && $credit < 9) {
				$value = intval($value);
				$conditions .= " AND extcredits$credit>'$value'";
			}
		}
	}
	if(is_array($lower)) {
		foreach($lower as $credit => $value) {
			$credit = intval(substr($credit, 10));
			if($value != '' && $credit > 0 && $credit < 9) {
				$value = intval($value);
				$conditions .= " AND extcredits$credit<'$value'";
			}
		}
	}

	$conditions .= $postshigher != '' ? " AND posts>'$postshigher'" : '';
	$conditions .= $postslower != '' ? " AND posts<'$postslower'" : '';

	$conditions .= $higher['credits'] != '' ? " AND credits>'$higher[credits]'" : '';
	$conditions .= $lower['credits'] != '' ? " AND credits<'$lower[credits]'" : '';

	$conditions .= $regip != '' ? " AND regip LIKE '$regip%'" : '';
	$conditions .= $lastip != '' ? " AND lastip LIKE '$lastip%'" : '';

	$conditions .= $regdatebefore != '' ? " AND regdate<'".(strtotime($regdatebefore) + $dateoffset)."'" : '';
	$conditions .= $regdateafter != '' ? " AND regdate>'".(strtotime($regdateafter) + $dateoffset)."'" : '';
	$conditions .= $lastvisitafter != '' ? " AND lastvisit>'".(strtotime($lastvisitafter) + $dateoffset)."'" : '';
	$conditions .= $lastvisitbefore != '' ? " AND lastvisit<'".(strtotime($lastvisitbefore) + $dateoffset)."'" : '';
	$conditions .= $lastpostafter != '' ? " AND lastpost>'".(strtotime($lastpostafter) + $dateoffset)."'" : '';
	$conditions .= $lastpostbefore != '' ? " AND lastpost<'".(strtotime($lastpostbefore) + $dateoffset)."'" : '';

	$conditions .= $birthyear != '' || $birthmonth != '' || $birthday != '' ? " AND bday LIKE '".(($birthyear ? $birthyear : '%').'-'.($birthmonth? $birthmonth : '%').'-'.($birthday ? $birthday : '%'))."'" : '';

	$conditions .= $operation == 'newsletter' && (submitcheck('submit') || submitcheck('sendsubmit', 1)) ? " AND newsletter='1'" : '';

	if(!$conditions && !$uidarray && $operation == 'clean') {
		cpmsg('members_search_invalid', '', 'error');
	} else {
		$conditions = '1'.$conditions;
	}

	$urladd = "&username=".rawurlencode($username)."&srchemail=".rawurlencode($srchemail)."&regdatebefore=".rawurlencode($regdatebefore)."&regdateafter=".rawurlencode($regdateafter)."&postshigher=".rawurlencode($postshigher)."&postslower=".rawurlencode($postslower)."&regip=".rawurlencode($regip)."&lastip=".rawurlencode($lastip)."&lastvisitafter=".rawurlencode($lastvisitafter)."&lastvisitbefore=".rawurlencode($lastvisitbefore)."&lastpostafter=".rawurlencode($lastpostafter)."&lastpostbefore=".rawurlencode($lastpostbefore)."&birthyear=".rawurlencode($birthyear)."&birthmonth=".rawurlencode($birthmonth)."&birthday=".rawurlencode($birthday);
	if(is_array($usergroupid)) {
		foreach($usergroupid as $gid => $value) {
			if($value != '') {
				$urladd .= '&usergroupid[]='.rawurlencode($value);
			}
		}
	}

	foreach(array('lower', 'higher') as $key) {
		if(is_array($$key)) {
			foreach($$key as $column => $value) {
				$urladd .= '&'.$key.'['.$column.']='.rawurlencode($value);
			}
		}
	}

	$membernum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE $conditions");
}

function shownewsletter() {
	extract($GLOBALS, EXTR_SKIP);

	$subject = $message = '';
	if($settings = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='$variable'")) {
		$settings = unserialize($settings);
		$subject = $settings['subject'];
		$message = $settings['message'];
	}

	showtablerow('', array('class="th11"', 'class="longtxt"'), array(
		$lang['members_newsletter_subject'],
		'<input type="text" class="txt" name="subject" size="80" value='.dhtmlspecialchars($subject).'>'
	));
	showtablerow('', array('class="th12"', ''), array(
		$lang['members_newsletter_message'],
		'<textarea name="message" class="tarea" cols="80" rows="10">'.dhtmlspecialchars($message).'</textarea>'
	));
	showtablerow('', array('', 'class="td12"'), array(
		'',
		'<ul><li><input class="radio" type="radio" value="email" name="sendvia" id="viaemail" /><label for="viaemail"> '.$lang['email'].'</label></li><li><input class="radio" type="radio" value="pm" checked="checked" name="sendvia" id="viapm" /><label for="viapm"> '.$lang['pm'].'</label></li><li><span class="diffcolor2">'.$lang['members_newsletter_num'].'</span><input type="text" class="txt" name="pertask" value="100" size="10"></li></ul>'
	));

}

function notifymembers($operation, $variable) {
	extract($GLOBALS, EXTR_SKIP);

	if(!empty($current)) {

		$subject = $message = '';
		if($settings = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='$variable'")) {
			$settings = unserialize($settings);
			$subject = $settings['subject'];
			$message = $settings['message'];
		}

	} else {

		$current = 0;
		$subject = trim($subject);
		$message = trim(str_replace("\t", ' ', $message));
		if($notifymembers && !($subject && $message)) {
			cpmsg('members_newsletter_sm_invalid', '', 'error');
		}

		if($operation == 'reward') {

			$updatesql = '';
			if($updatecredittype == 0) {
				if(is_array($addextcredits) && !empty($addextcredits)) {
					foreach($addextcredits as $key => $value) {
						$value = intval($value);
						if(isset($extcredits[$key]) && !empty($value)) {
							$updatesql .= ", extcredits{$key}=extcredits{$key}+($value)";
						}
					}
				}
			} else {
				if(is_array($resetextcredits) && !empty($resetextcredits)) {
					foreach($resetextcredits as $key => $value) {
						$value = intval($value);
						if(isset($extcredits[$key]) && !empty($value)) {
							$updatesql .= ", extcredits{$key}=0";
						}
					}
				}
			}

			if(!empty($updatesql)) {
				$db->query("UPDATE {$tablepre}members set uid=uid $updatesql WHERE $conditions", 'UNBUFFTERED');
			} else {
				cpmsg('members_reward_invalid', '', 'error');
			}

			if(!$notifymembers) {
				cpmsg('members_reward_succeed', '', 'succeed');
			}

		} elseif ($operation == 'confermedal') {

			if(!empty($medals)) {
				$medalids = $comma = '';
				foreach($medals as $key=> $medalid) {
					$medalids .= "$comma'$key'";
					$comma = ',';
				}

				$medalsnew = $comma = '';
				$query = $db->query("SELECT medalid, expiration FROM {$tablepre}medals WHERE medalid IN ($medalids)");
				while($medal = $db->fetch_array($query)) {
					$medal['status'] = empty($medal['expiration']) ? 0 : 1;
					$medal['expiration'] = empty($medal['expiration'])? 0 : $timestamp + $medal['expiration'] * 86400;
					$medal['medal'] = $medal['medalid'].(empty($medal['expiration']) ? '' : '|'.$medal['expiration']);
					$medalsnew .= $comma.$medal['medal'];
					$medalsnewarray[] = $medal;
					$comma = "\t";
				}

				$uids = array();
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE $conditions");
				while ($medaluid = $db->fetch_array($query)) {
					$uids[] = $medaluid['uid'];
				}

				$query = $db->query("SELECT uid, medals FROM {$tablepre}memberfields WHERE uid IN (".implode(',', $uids).")");
				while($medalnew = $db->fetch_array($query)) {

					$medalnew['medals'] = empty($medalnew['medals']) ? $medalsnew : $medalnew['medals']."\t".$medalsnew;
					$db->query("UPDATE {$tablepre}memberfields SET medals='".$medalnew['medals']."' WHERE uid='".$medalnew['uid']."'", 'UNBUFFTERED');

					foreach($medalsnewarray as $medalnewarray) {
						$db->query("INSERT INTO {$tablepre}medallog (uid, medalid, type, dateline, expiration, status) VALUES ('".$medalnew['uid']."', '".$medalnewarray['medalid']."', '0', '$timestamp', '".$medalnewarray['expiration']."', '".$medalnewarray['status']."')");
					}
				}
			}

			if(!$notifymembers) {
				cpmsg('members_confermedal_succeed', '', 'succeed');
			}

		}

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('$variable', '".
			addslashes(serialize(array('subject' => $subject, 'message' => $message)))."')");
	}

	$pertask = intval($pertask);
	$current = intval($current);
	$continue = FALSE;

	if(in_array($sendvia, array('pm', 'email'))) {
		$query = $db->query("SELECT uid, username, groupid, email FROM {$tablepre}members WHERE $conditions LIMIT $current, $pertask");
                while($member = $db->fetch_array($query)) {
			$sendvia == 'pm' ? sendpm($member['uid'], $subject, $message, $discuz_uid) : sendmail("$member[username] <$member[email]>", $subject, $message);
			$continue = TRUE;
                }
        }

	if($continue) {
		$next = $current + $pertask;
		eval("\$lang[members_newsletter_processing] = \"".$lang['members_newsletter_processing']."\";");
		cpmsg("$lang[members_newsletter_send]: $lang[members_newsletter_processing]", "admincp.php?action=members&operation=$operation&{$operation}submit=yes&current=$next&pertask=$pertask&sendvia=".rawurlencode($sendvia).$urladd, 'loading');
	} else {
		cpmsg('members'.($operation ? '_'.$operation : '').'_notify_succeed', '', 'succeed');
	}

}

function banlog($username, $origgroupid, $newgroupid, $expiration, $reason) {
	global $discuz_userss, $groupid, $onlineip, $timestamp, $forum, $reason;
	writelog('banlog', dhtmlspecialchars("$timestamp\t$discuz_userss\t$groupid\t$onlineip\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason"));
}

function selectday($varname, $dayarray) {
	global $timestamp, $dateformat, $timeformat, $timeoffset, $lang;
	$selectday = '<select name="'.$varname.'">';
	if($dayarray && is_array($dayarray)) {
		foreach($dayarray as $day) {
			$langday = $day.'_day';
			$daydate = $day ? '('.gmdate("$dateformat $timeformat", ($timestamp + $day * 86400) + $timeoffset * 3600).')' : '';
			$selectday .= '<option value='.$day.'>'.$lang[$langday].'&nbsp;'.$daydate.'</option>';
		}
	}
	$selectday .= '</select>';

	return $selectday;
}
?>