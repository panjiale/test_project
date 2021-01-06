<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: groups.inc.php 13394 2008-04-12 16:14:59Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($operation == 'admin') {

	if(!$do) {

		shownav('user', 'nav_admingroups');
		showsubmenu('nav_admingroups');
		showtips('admingroups_tips');
		showtableheader('', 'fixpadding');
		showsubtitle(array('name', 'type', 'admingroups_level', '', ''));

		$query = $db->query("SELECT a.*, u.radminid, u.grouptitle FROM {$tablepre}admingroups a
			LEFT JOIN {$tablepre}usergroups u ON u.groupid=a.admingid
			WHERE a.admingid<>'1' ORDER BY u.radminid, a.admingid");
		while($group = $db->fetch_array($query)) {
			showtablerow('', '', array(
				$group[grouptitle],
				$group['admingid'] <= 3 ? $lang['admingroups_type_system'] : $lang['admingroups_type_user'],
				$lang['usergroups_system_'.$group['radminid']],
				"<a href=\"admincp.php?action=groups&operation=user&do=edit&id={$group[admingid]}&return=admin\">$lang[admingroups_settings_user]</a>",
				"<a href=\"admincp.php?action=groups&operation=admin&do=edit&id=$group[admingid]\">$lang[admingroups_settings_admin]</a>"
			));
		}

		showtablefooter();

	} elseif($do == 'edit' && $id) {

		$actionarray = array('settings',
			'forums', 'forums_edit', 'forums_moderators', 'forums_delete', 'forums_merge', 'forums_copy', 'threadtypes',
			'members', 'members_add', 'members_editgroups', 'members_access', 'members_editcredits', 'members_editmedals', 'members_profile', 'members_profilefields', 'members_ipban', 'members_merge',
			'groups_user', 'groups_admin', 'groups_ranks',
			'styles', 'templates', 'templates_add', 'templates_edit',
			'moderate_members', 'moderate_threads', 'moderate_replies', 'threads', 'prune', 'recyclebin',
			'announcements', 'misc_forumlinks', 'misc_onlinelist', 'misc_censor', 'misc_discuzcodes', 'misc_tags', 'smilies', 'misc_icons', 'misc_attachtypes', 'misc_crons',
			'advertisements', 'advertisements_add', 'advertisements_edit',
			'database_runquery', 'database_optimize', 'database_export', 'database_import',
			'tools_updatecache', 'tools_fileperms', 'tools_relatedtag',
			'attachments', 'counter', 'jswizard', 'creditwizard',
			'google_config', 'qihoo_config', 'qihoo_topics',
			'ecommerce_alipay', 'ecommerce_orders',
			'medals',
			'plugins', 'plugins_config', 'plugins_edit', 'plugins_hooks', 'plugins_vars',
			'logs_illegal', 'logs_rate', 'logs_mods', 'logs_medals', 'logs_ban', 'logs_cp', 'logs_credits', 'logs_error'
		);
	
		if(!submitcheck('groupsubmit')) {
	
			$id = intval($id);
			$group = $db->fetch_first("SELECT a.*, aa.disabledactions, u.radminid, u.grouptitle FROM {$tablepre}admingroups a
				LEFT JOIN {$tablepre}usergroups u ON u.groupid=a.admingid
				LEFT JOIN {$tablepre}adminactions aa ON aa.admingid=a.admingid
				WHERE a.admingid='$id' AND a.admingid<>'1'");
	
			if(!$group) {
				cpmsg('undefined_action', '', 'error');
			}
	
			showsubmenu($lang['admingroups_edit'].' - '.$group['grouptitle']);
			showformheader("groups&operation=admin&do=edit&id=$id");
			showtableheader();
	
			if($group['radminid'] == 1) {
	
				$group['disabledactions'] = $group['disabledactions'] ? unserialize($group['disabledactions']) : array();
	
				foreach($actionarray as $actionstr) {
					showsetting('admingroups_edit_action_'.$actionstr, 'disabledactionnew['.$actionstr.']', !in_array($actionstr, $group['disabledactions']), 'radio');
				}
	
			} else {
	
				$checkstick = array($group['allowstickthread'] => 'checked');
	
				showsetting('admingroups_edit_edit_post', 'alloweditpostnew', $group['alloweditpost'], 'radio');
				showsetting('admingroups_edit_edit_poll', 'alloweditpollnew', $group['alloweditpoll'], 'radio');
				showsetting('admingroups_edit_stick_thread', '', '', '<input class="radio" type="radio" name="allowstickthreadnew" value="0" '.$checkstick[0].'> '.$lang['admingroups_edit_stick_thread_none'].'<br /><input class="radio" type="radio" name="allowstickthreadnew" value="1" '.$checkstick[1].'> '.$lang['admingroups_edit_stick_thread_1'].'<br /><input class="radio" type="radio" name="allowstickthreadnew" value="2" '.$checkstick[2].'> '.$lang['admingroups_edit_stick_thread_2'].'<br /><input class="radio" type="radio" name="allowstickthreadnew" value="3" '.$checkstick[3].'> '.$lang['admingroups_edit_stick_thread_3'].'');
				showsetting('admingroups_edit_mod_post', 'allowmodpostnew', $group['allowmodpost'], 'radio');
				showsetting('admingroups_edit_del_post', 'allowdelpostnew', $group['allowdelpost'], 'radio');
				showsetting('admingroups_edit_ban_post', 'allowbanpostnew', $group['allowbanpost'], 'radio');
				showsetting('admingroups_edit_mass_prune', 'allowmassprunenew', $group['allowmassprune'], 'radio');
				showsetting('admingroups_edit_refund', 'allowrefundnew', $group['allowrefund'], 'radio');
				showsetting('admingroups_edit_censor_word', 'allowcensorwordnew', $group['allowcensorword'], 'radio');
				showsetting('admingroups_edit_view_ip', 'allowviewipnew', $group['allowviewip'], 'radio');
				showsetting('admingroups_edit_ban_ip', 'allowbanipnew', $group['allowbanip'], 'radio');
				showsetting('admingroups_edit_edit_user', 'alloweditusernew', $group['allowedituser'], 'radio');
				showsetting('admingroups_edit_ban_user', 'allowbanusernew', $group['allowbanuser'], 'radio');
				showsetting('admingroups_edit_mod_user', 'allowmodusernew', $group['allowmoduser'], 'radio');
				showsetting('admingroups_edit_post_announce', 'allowpostannouncenew', $group['allowpostannounce'], 'radio');
				showsetting('admingroups_edit_view_log', 'allowviewlognew', $group['allowviewlog'], 'radio');
				showsetting('admingroups_edit_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
	
			}
	
			showsubmit('groupsubmit');
			showtablefooter();
			showformfooter();
	
		} else {
	
			$group = $db->fetch_first("SELECT groupid, radminid FROM {$tablepre}usergroups WHERE groupid='$id'");
			if(!$group) {
				cpmsg('undefined_action', '', 'error');
			}
	
			if($group['radminid'] == 1) {
	
				$dactionarray = array();
				if(is_array($disabledactionnew)) {
					foreach($disabledactionnew as $key => $value) {
						if(in_array($key, $actionarray) && !$value) {
							$dactionarray[] = $key;
						}
					}
				}
	
				$db->query("REPLACE INTO {$tablepre}adminactions (admingid, disabledactions)
					VALUES ('$group[groupid]', '".addslashes(serialize($dactionarray))."')");
	
			} else {
	
				$db->query("UPDATE {$tablepre}admingroups SET alloweditpost='$alloweditpostnew', alloweditpoll='$alloweditpollnew',
					allowstickthread='$allowstickthreadnew', allowmodpost='$allowmodpostnew', allowbanpost='$allowbanpostnew', allowdelpost='$allowdelpostnew',
					allowmassprune='$allowmassprunenew', allowrefund='$allowrefundnew', allowcensorword='$allowcensorwordnew',
					allowviewip='$allowviewipnew', allowbanip='$allowbanipnew', allowedituser='$alloweditusernew', allowbanuser='$allowbanusernew',
					allowmoduser='$allowmodusernew', allowpostannounce='$allowpostannouncenew', allowviewlog='$allowviewlognew',
					disablepostctrl='$disablepostctrlnew' WHERE admingid='$group[groupid]' AND admingid<>'1'");
	
			}
	
			updatecache('usergroups');
			updatecache('admingroups');
			cpmsg('admingroups_edit_succeed', 'admincp.php?action=groups&operation=admin&do=edit&id='.$id, 'succeed');
	
		}

	}

} elseif($operation == 'user') {

	if(!$do) {

		if(!submitcheck('groupsubmit')) {

			$sgroups = $smembers = array();
			$sgroupids = '0';
			$smembernum = $membergroup = $specialgroup = $sysgroup = '';
			$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
			$conditions = !empty($insenz['groupid']) ? "WHERE groupid<>$insenz[groupid]" : '';
			$query = $db->query("SELECT groupid, type, grouptitle, creditshigher, creditslower, stars, color, groupavatar FROM {$tablepre}usergroups $conditions ORDER BY creditshigher");
			while($group = $db->fetch_array($query)) {
				if($group['type'] == 'member') {
					$membergroup .= showtablerow('', array('class="td25"', '', '', 'class=td28'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
						"<input type=\"text\" class=\"txt\" size=\"12\" name=\"groupnew[$group[groupid]][grouptitle]\" value=\"$group[grouptitle]\">",
						"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditshigher]\" value=\"$group[creditshigher]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditslower]\" value=\"$group[creditslower]\" disabled />",
						"<input type=\"text\" class=\"txt\" size=\"2\" name=\"groupnew[$group[groupid]][stars]\" value=\"$group[stars]\">",
						"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][color]\" value=\"$group[color]\">",
						"<input type=\"text\" class=\"txt\" size=\"12\" name=\"groupnew[$group[groupid]][groupavatar]\" value=\"$group[groupavatar]\">",
						"<a href=\"admincp.php?action=groups&operation=user&do=edit&id=$group[groupid]\" class=\"act\">$lang[detail]</a>"
					), TRUE);
				} elseif($group['type'] == 'system') {
					$sysgroup .= showtablerow('', array('', '', 'class="td28"'), array(
						"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
						$lang['usergroups_system_'.$group['groupid']],
						"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
						"<input type=\"text\" class=\"txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\">",
						"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_avatar[$group[groupid]]\" value=\"$group[groupavatar]\">",
						"<a href=\"admincp.php?action=groups&operation=user&do=edit&id=$group[groupid]\" class=\"act\">$lang[detail]</a>"
					), TRUE);
				} elseif($group['type'] == 'special') {
					$sgroups[] = $group;
					$sgroupids .= ','.$group['groupid'];
				}
			}

			$projectselect = '';
			$project = array();
			$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='group'");
			while($project = $db->fetch_array($query)) {
				$projectselect .= '<option value="'.$project['id'].'">'.$project['name'].'</option>';
			}

			foreach($sgroups as $group) {
				if(is_array($smembers[$group['groupid']])) {
					$num = count($smembers[$group['groupid']]);
					$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="admincp.php?action=members&submit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br/>');
					unset($smembers[$group['groupid']]);
				} else {
					$specifiedusers = '';
					$num = 0;
				}
				$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";

				$specialgroup .= showtablerow('', array('class="td25"', '', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
					"<input type=\"text\" class=\"txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_avatar[$group[groupid]]\" value=\"$group[groupavatar]\">",
					"<a href=\"admincp.php?action=groups&operation=user&sgroupid=$group[groupid]&do=viewsgroup\" onclick=\"ajaxget(this.href, 'sgroup_$group[groupid]', 'sgroup_$group[groupid]', 'auto');doane(event);\" class=\"act\">$lang[view]</a>",
					"<a href=\"admincp.php?action=groups&operation=user&do=edit&id=$group[groupid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
				$specialgroup .= showtablerow('', array('', 'colspan="6" id="sgroup_'.$group['groupid'].'" style="display: none"'), array(
					'',
					''
				), TRUE);
			}

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" size="12" name="groupnewadd[grouptitle][]">'],
			[1,'<input type="text" class="txt" size="6" name="groupnewadd[creditshigher][]">'],
			[1,'<input type="text" class="txt" size="2" name="groupnewadd[stars][]">', 'td28'],
			[4,'<select name="groupnewadd[projectid][]"><option value="">$lang[usergroups_scheme]</option><option value="0"><$lang[none]</option>$projectselect</select>']
		],
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" size="12" name="grouptitlenewadd[]">'],
			[1,'<input type="text" class="txt" size="2" name="starsnewadd[]">', 'td28'],
			[1,'<input type="text" class="txt" size="6" name="colornewadd[]">'],
			[1,'<input type="text" class="txt" size="12" name="groupavatarnewadd[]">'],
			[2, '']
		]
	];
</script>
EOT;
			shownav('user', 'nav_usergroups');
			showsubmenuanchors('nav_usergroups', array(
				array('usergroups_member', 'membergroups', !$type || $type == 'member'),
				array('usergroups_system', 'systemgroups', $type == 'system'),
				array('usergroups_special', 'specialgroups', $type == 'special')
			));
			showtips('usergroups_tips');

			showformheader('groups&operation=user&type=member');
			showtableheader('usergroups_member', 'fixpadding', 'id="membergroups"'.($type && $type != 'member' ? ' style="display: none"' : ''));
			showsubtitle(array('', 'usergroups_title', 'members_creditsrange', 'usergroups_stars', 'usergroups_color', 'usergroups_avatar', ''));
			echo $membergroup;
			echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_add'].'</a></div></td></tr>';
			showsubmit('groupsubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			showformheader('groups&operation=user&type=special');
			showtableheader('usergroups_special', 'fixpadding', 'id="specialgroups"'.($type != 'special' ? ' style="display: none"' : ''));
			showsubtitle(array('', 'usergroups_title', 'usergroups_stars', 'usergroups_color', 'usergroups_avatar', '', ''));
			echo $specialgroup;
			echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['usergroups_sepcial_add'].'</a></div></td></tr>';
			showsubmit('groupsubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			showformheader('groups&operation=user&type=system');
			showtableheader('usergroups_system', 'fixpadding', 'id="systemgroups"'.($type != 'system' ? ' style="display: none"' : ''));
			showsubtitle(array('usergroups_title', 'usergroups_status', 'usergroups_stars', 'usergroups_color', 'usergroups_avatar', ''));
			echo $sysgroup;
			showsubmit('groupsubmit');
			showtablefooter();
			showformfooter();

		} else {

			if($type == 'member') {

				$groupnewadd = array_flip_keys($groupnewadd);
				foreach($groupnewadd as $k=>$v) {
					if(!$v['grouptitle'] || !$v['creditshigher']) {
						unset($groupnewadd[$k]);
					}
				}
				$groupnewkeys = array_keys($groupnew);
				$maxgroupid = max($groupnewkeys);
				foreach($groupnewadd as $k=>$v) {
					$groupnew[$k+$maxgroupid+1] = $v;
				}
				$orderarray = array();
				if(is_array($groupnew)) {
					foreach($groupnew as $id => $group) {
						if((is_array($delete) && in_array($id, $delete)) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
							unset($groupnew[$id]);
						} else {
							$orderarray[$group['creditshigher']] = $id;
						}
					}
				}

				if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
					cpmsg('usergroups_update_credits_invalid', '', 'error');
				}

				ksort($orderarray);
				$rangearray = array();
				$lowerlimit = array_keys($orderarray);
				for($i = 0; $i < count($lowerlimit); $i++) {
					$rangearray[$orderarray[$lowerlimit[$i]]] = array
						(
						'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
						'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
						);
				}

				$project = $projects = array();
				$query = $db->query("SELECT * FROM {$tablepre}projects WHERE type='group'");
				while($project = $db->fetch_array($query)) {
					$project['value'] = unserialize($project['value']);
					$sqladd = '';
					foreach($project['value'] as $k=>$v) {
						$sqladd .= ",$k='$v'";
					}
					$project['sqladd'] = $sqladd;
					$projects[$project['id']] = $project;
				}

				foreach($groupnew as $id => $group) {
					$creditshighernew = $rangearray[$id]['creditshigher'];
					$creditslowernew = $rangearray[$id]['creditslower'];
					if($creditshighernew == $creditslowernew) {
						cpmsg('usergroups_update_credits_duplicate', '', 'error');
					}

					if(in_array($id, $groupnewkeys)) {
						$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]', color='$group[color]', groupavatar='$group[groupavatar]' WHERE groupid='$id' AND type='member'");
					} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
						$project = $projects[$group['projectid']];
						$db->query("INSERT INTO {$tablepre}usergroups SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]' $project[sqladd]");
					}
				}

				if(!empty($delete)) {
					$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid IN ('".implode('\',\'', $delete)."') AND type='member'");
				}

			} elseif($type == 'special') {

				if(is_array($grouptitlenewadd)) {
					foreach($grouptitlenewadd as $k=>$v) {
						if($v) {
							$db->query("INSERT INTO {$tablepre}usergroups (type, grouptitle, stars, color, groupavatar, allowvisit, readaccess, allowpost, allowsigbbcode)
								VALUES ('special', '$grouptitlenewadd[$k]', '$starsnewadd[$k]', '$colornewadd[$k]', '$groupavatarnewadd[$k]', '1', '1', '1', '1')");
						}
					}
				}
				$ids = $comma = '';
				if(is_array($group_title)) {
					foreach($group_title as $id => $title) {
						if($delete[$id]) {
							$ids .= "$comma'$id'";
							$comma = ',';
						} else {
							$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group_title[$id]', stars='$group_stars[$id]', color='$group_color[$id]', groupavatar='$group_avatar[$id]' WHERE groupid='$id'");
						}
					}
				}
				if($ids) {
					$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid IN ($ids) AND type='special'");
					$db->query("DELETE FROM {$tablepre}admingroups WHERE admingid IN ($ids)");
					$db->query("DELETE FROM {$tablepre}adminactions WHERE admingid IN ($ids)");
					$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
					$db->query("UPDATE {$tablepre}members SET groupid='".$db->result($query, 0)."', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
					//$db->query("UPDATE {$tablepre}members SET groupid='".$db->result($query, 0)."', adminid='0' WHERE groupid IN ($ids) AND adminid='-1'", 'UNBUFFERED');
					//$db->query("UPDATE {$tablepre}members SET groupid=adminid WHERE groupid IN ($ids) AND adminid IN ('1', '2', '3')", 'UNBUFFERED');
				}
			} elseif($type == 'system') {
				if(is_array($group_title)) {
					foreach($group_title as $id => $title) {
						$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group_title[$id]', stars='$group_stars[$id]', color='$group_color[$id]', groupavatar='$group_avatar[$id]' WHERE groupid='$id'");
					}
				}
			}

			updatecache('usergroups');
			cpmsg('usergroups_update_succeed', 'admincp.php?action=groups&operation=user&type='.$type, 'succeed');
		}

	} elseif($do == 'viewsgroup') {

		$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE groupid='$sgroupid'");
		$query = $db->query("SELECT uid, username FROM {$tablepre}members WHERE groupid='$sgroupid' LIMIT 80");
		$sgroups = '';
		while($member = $db->fetch_array($query)) {
			$sgroups .= '<li><a href="space.php?uid='.$member['uid'].'" target="_blank">'.$member['username'].'</a></li>';
		}
		ajaxshowheader();
		echo '<ul class="userlist"><li class="unum">'.$lang['usernum'].$num.($num > 80 ? '&nbsp;<a href="admincp.php?action=members&submit=yes&usergroupid[]='.$sgroupid.'">'.$lang['more'].'&raquo;</a>' : '').'</li>'.$sgroups.'</ul>';
		ajaxshowfooter();
		exit;

	} elseif($do == 'edit' && $id) {

		$group = $db->fetch_first("SELECT * FROM {$tablepre}usergroups WHERE groupid='$id'");

		if(!submitcheck('detailsubmit') && !submitcheck('saveconfigsubmit')) {
			$projectselect = "<select name=\"projectid\" onchange=\"window.location='admincp.php?action=groups&operation=user&do=edit&id=$id&projectid='+this.options[this.options.selectedIndex].value\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
			$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='group'");
			while($project = $db->fetch_array($query)) {
				$projectselect .= "<option value=\"$project[id]\" ".($project['id'] == $projectid ? 'selected="selected"' : NULL).">$project[name]</option>";
			}
			$projectselect .= '</select>';

			if(!empty($projectid)) {
				$group = @array_merge($group, unserialize($db->result_first("SELECT value FROM {$tablepre}projects WHERE id='$projectid'")));
			}

			$group['exempt'] = strrev(sprintf('%0'.strlen($group['exempt']).'b', $group['exempt']));

			$anchor = in_array($anchor, array('basic', 'specialthread', 'thread', 'attachment', 'magic', 'invite', 'exempt')) ? $anchor : 'basic';
			showsubmenuanchors(lang('usergroups_edit').' - '.$group['grouptitle'], array(
				array('usergroups_edit_basic', 'basic', $anchor == 'basic'),
				$group['type'] == 'special' ? array('usergroups_edit_system', 'system', $anchor == 'system') : array(),
				array('usergroups_specialthread', 'specialthread', $anchor == 'specialthread'),
				array('usergroups_edit_thread', 'thread', $anchor == 'thread'),
				array('usergroups_edit_attachment', 'attachment', $anchor == 'attachment'),
				array('usergroups_magic', 'magic', $anchor == 'magic'),
				array('usergroups_invite', 'invite', $anchor == 'invite'),
				array('usergroups_edit_credits', 'exempt', $anchor == 'exempt')
			));
			if($group['type'] == 'special') {
				showtips('usergroups_edit_system_tips', 'system_tips', $anchor == 'system');
			}
			showtips('usergroups_magic_tips', 'magic_tips', $anchor == 'magic');
			showtips('usergroups_invite_tips', 'invite_tips', $anchor == 'invite');
			showformheader("groups&operation=user&do=edit&id=$id".($return == 'admin' ? '&return=admin' : ''));
			showtableheader();

			if($group['type'] == 'special') {
				showtagheader('tbody', 'system', $anchor == 'system');
				if($group['system'] == 'private') {
					$system = array('public' => 0, 'dailyprice' => 0, 'minspan' => 0);
				} else {
					$system = array('public' => 1, 'dailyprice' => 0, 'minspan' => 0);
					list($system['dailyprice'], $system['minspan']) = explode("\t", $group['system']);
				}
				showsetting('usergroups_edit_system_public', 'system_publicnew', $system['public'], 'radio');
				showsetting('usergroups_edit_system_dailyprice', 'system_dailypricenew', $system['dailyprice'], 'text');
				showsetting('usergroups_edit_system_minspan', 'system_minspannew', $system['minspan'], 'text');
				showtagfooter('tbody');
			}

			showtagheader('tbody', 'basic', $anchor == 'basic');
			showsetting('usergroups_edit_title', 'grouptitlenew', $group['grouptitle'], 'text');
			showsetting('usergroups_scheme', '', '', $projectselect);

			if($group['type'] == 'special') {
				$selectra = array($group['radminid'] => 'selected="selected"');
				showsetting('usergroups_edit_radminid', '', '', "<select name=\"radminidnew\"><option value=\"0\" $selectra[0]>$lang[none]</option><option value=\"1\" $selectra[1]>$lang[usergroups_system_1]</option><option value=\"2\" $selectra[2]>$lang[usergroups_system_2]</option><option value=\"3\" $selectra[3]>$lang[usergroups_system_3]</option>");
			}

			if(in_array($group['groupid'], array(1, 7))) {
				echo '<input type="hidden" name="allowvisitnew" value="1">';
			} else {
				showsetting('usergroups_edit_visit', 'allowvisitnew', $group['allowvisit'], 'radio');
			}
			showsetting('usergroups_edit_read_access', 'readaccessnew', $group['readaccess'], 'text');
			showsetting('usergroups_edit_view_profile', 'allowviewpronew', $group['allowviewpro'], 'radio');
			showsetting('usergroups_edit_view_stats', 'allowviewstatsnew', $group['allowviewstats'], 'radio');
			showsetting('usergroups_edit_invisible', 'allowinvisiblenew', $group['allowinvisible'], 'radio');
			showsetting('usergroups_edit_multigroups', 'allowmultigroupsnew', $group['allowmultigroups'], 'radio');
			showsetting('usergroups_edit_allowtransfer', 'allowtransfernew', $group['allowtransfer'], 'radio');
			showsetting('usergroups_edit_search', array('allowsearchnew', array(
				array(0, $lang['usergroups_edit_search_disable']),
				array(1, $lang['usergroups_edit_search_thread']),
				array(2, $lang['usergroups_edit_search_post'])
			)), $group['allowsearch'], 'mradio');
			showsetting('usergroups_edit_reasonpm', array('reasonpmnew', array(
				array(0, $lang['usergroups_edit_reasonpm_none']),
				array(1, $lang['usergroups_edit_reasonpm_reason']),
				array(2, $lang['usergroups_edit_reasonpm_pm']),
				array(3, $lang['usergroups_edit_reasonpm_both'])
			)), $group['reasonpm'], 'mradio');
			showsetting('usergroups_edit_nickname', 'allownicknamenew', $group['allownickname'], 'radio');
			showsetting('usergroups_edit_cstatus', 'allowcstatusnew', $group['allowcstatus'], 'radio');
			showsetting('usergroups_edit_disable_periodctrl', 'disableperiodctrlnew', $group['disableperiodctrl'], 'radio');
			showsetting('usergroups_edit_hour_posts', 'maxpostsperhournew', $group['maxpostsperhour'], 'text');
			showtagfooter('tbody');

			showtagheader('tbody', 'specialthread', $anchor == 'specialthread');
			showsetting('usergroups_special_activity', 'allowpostactivitynew', $group['allowpostactivity'], 'radio');
			showsetting('usergroups_edit_post_poll', 'allowpostpollnew', $group['allowpostpoll'], 'radio');
			showsetting('usergroups_edit_vote', 'allowvotenew', $group['allowvote'], 'radio');
			showsetting('usergroups_special_reward', 'allowpostrewardnew', $group['allowpostreward'], 'radio');
			showsetting('usergroups_special_reward_min', 'minrewardpricenew', $group['minrewardprice'], "text");
			showsetting('usergroups_special_reward_max', 'maxrewardpricenew', $group['maxrewardprice'], "text");
			showsetting('usergroups_special_trade', 'allowposttradenew', $group['allowposttrade'], 'radio');
			showsetting('usergroups_special_trade_min', 'mintradepricenew', $group['mintradeprice'], "text");
			showsetting('usergroups_special_trade_max', 'maxtradepricenew', $group['maxtradeprice'], "text");
			showsetting('usergroups_special_trade_stick', 'tradesticknew', $group['tradestick'], "text");
			showsetting('usergroups_special_debate', 'allowpostdebatenew', $group['allowpostdebate'], "radio");
			$videoopen && showsetting('usergroups_special_video', 'allowpostvideonew', $group['allowpostvideo'], "radio");
			showtagfooter('tbody');

			showtagheader('tbody', 'thread', $anchor == 'thread');
			showsetting('usergroups_edit_post', 'allowpostnew', $group['allowpost'], 'radio');
			showsetting('usergroups_edit_reply', 'allowreplynew', $group['allowreply'], 'radio');
			showsetting('usergroups_edit_direct_post', array('allowdirectpostnew', array(
				array(0, $lang['usergroups_edit_direct_post_none']),
				array(1, $lang['usergroups_edit_direct_post_reply']),
				array(2, $lang['usergroups_edit_direct_post_thread']),
				array(3, $lang['usergroups_edit_direct_post_all'])
			)), $group['allowdirectpost'], 'mradio');
			showsetting('usergroups_edit_anonymous', 'allowanonymousnew', $group['allowanonymous'], 'radio');
			showsetting('usergroups_edit_set_read_perm', 'allowsetreadpermnew', $group['allowsetreadperm'], 'radio');
			showsetting('usergroups_edit_maxprice', 'maxpricenew', $group['maxprice'], 'text');
			showsetting('usergroups_edit_hide_code', 'allowhidecodenew', $group['allowhidecode'], 'radio');
			showsetting('usergroups_edit_html', 'allowhtmlnew', $group['allowhtml'], 'radio');
			showsetting('usergroups_edit_custom_bbcode', 'allowcusbbcodenew', $group['allowcusbbcode'], 'radio');
			showsetting('usergroups_edit_bio_bbcode', 'allowbiobbcodenew', $group['allowbiobbcode'], 'radio');
			showsetting('usergroups_edit_bio_img_code', 'allowbioimgcodenew', $group['allowbioimgcode'], 'radio');
			showsetting('usergroups_edit_max_bio_size', 'maxbiosizenew', $group['maxbiosize'], 'text');
			showsetting('usergroups_edit_sig_bbcode', 'allowsigbbcodenew', $group['allowsigbbcode'], 'radio');
			showsetting('usergroups_edit_sig_img_code', 'allowsigimgcodenew', $group['allowsigimgcode'], 'radio');
			showsetting('usergroups_edit_max_sig_size', 'maxsigsizenew', $group['maxsigsize'], 'text');
			showtagfooter('tbody');

			showtagheader('tbody', 'attachment', $anchor == 'attachment');
			showsetting('usergroups_edit_get_attach', 'allowgetattachnew', $group['allowgetattach'], 'radio');
			showsetting('usergroups_edit_post_attach', 'allowpostattachnew', $group['allowpostattach'], 'radio');
			showsetting('usergroups_edit_set_attach_perm', 'allowsetattachpermnew', $group['allowsetattachperm'], 'radio');
			showsetting('usergroups_edit_max_attach_size', 'maxattachsizenew', $group['maxattachsize'], 'text');
			showsetting('usergroups_edit_max_size_per_day', 'maxsizeperdaynew', $group['maxsizeperday'], 'text');
			showsetting('usergroups_edit_attach_ext', 'attachextensionsnew', $group['attachextensions'], 'text');
			showtagfooter('tbody');

			showtagheader('tbody', 'magic', $anchor == 'magic');
			showsetting('usergroups_magic_permission', array('allowmagicsnew', array(
				array(0, $lang['usergroups_magic_unallowed']),
				array(1, $lang['usergroups_magic_allow']),
				array(2, $lang['usergroups_magic_allow_and_pass'])
			)), $group['allowmagics'], 'mradio');
			showsetting('usergroups_magic_discount', 'magicsdiscountnew', $group['magicsdiscount'], 'text');
			showsetting('usergroups_magic_max', 'maxmagicsweightnew', $group['maxmagicsweight'], 'text');
			showtagfooter('tbody');

			showtagheader('tbody', 'invite', $anchor == 'invite');
			showsetting('usergroups_invite_permission', 'allowinvitenew', $group['allowinvite'], 'radio');
			showsetting('usergroups_invitesend_permission', 'allowmailinvitenew', $group['allowmailinvite'], 'radio');
			showsetting('usergroups_invite_price', 'invitepricenew', $group['inviteprice'], 'text');
			showsetting('usergroups_invite_buynum', 'maxinvitenumnew', $group['maxinvitenum'], 'text');
			showsetting('usergroups_invite_maxinviteday', 'maxinvitedaynew', $group['maxinviteday'], 'text');
			showtagfooter('tbody');

			showtagheader('tbody', 'exempt', $anchor == 'exempt');
			showsetting('usergroups_exempt_search', 'exemptnew[1]', $group['exempt'][1], 'radio');
			if($group['radminid']) {
				showsetting($lang['usergroups_exempt_outperm'].$lang['usergroups_exempt_getattch'], 'exemptnew[2]', $group['exempt'][2], 'radio');
				showsetting($lang['usergroups_exempt_inperm'].$lang['usergroups_exempt_getattch'], 'exemptnew[5]', $group['exempt'][5], 'radio');
				showsetting($lang['usergroups_exempt_outperm'].$lang['usergroups_exempt_attachpay'], 'exemptnew[3]', $group['exempt'][3], 'radio');
				showsetting($lang['usergroups_exempt_inperm'].$lang['usergroups_exempt_attachpay'], 'exemptnew[6]', $group['exempt'][6], 'radio');
				showsetting($lang['usergroups_exempt_outperm'].$lang['usergroups_exempt_threadpay'], 'exemptnew[4]', $group['exempt'][4], 'radio');
				showsetting($lang['usergroups_exempt_inperm'].$lang['usergroups_exempt_threadpay'], 'exemptnew[7]', $group['exempt'][7], 'radio');
			} else {
				showsetting('usergroups_exempt_getattch', 'exemptnew[2]', $group['exempt'][2], 'radio');
				showsetting('usergroups_exempt_attachpay', 'exemptnew[3]', $group['exempt'][3], 'radio');
				showsetting('usergroups_exempt_threadpay', 'exemptnew[4]', $group['exempt'][4], 'radio');
			}
			echo '<tr><td colspan="2">'.$lang['usergroups_exempt_comment'].'</td></tr>';

			$raterangearray = array();
			foreach(explode("\n", $group['raterange']) as $range) {
				$range = explode("\t", $range);
				$raterangearray[$range[0]] = array('min' => $range[1], 'max' => $range[2], 'mrpd' => $range[3]);
			}

			echo '<tr><td colspan="2">';
			showtableheader('usergroups_edit_raterange', 'noborder');
			showsubtitle(array('', 'credits_id', 'credits_title', 'usergroups_edit_raterange_min', 'usergroups_edit_raterange_max', 'usergroups_edit_raterange_mrpd'));
			for($i = 1; $i <= 8; $i++) {
				if(isset($extcredits[$i])) {
					echo '<tr><td><input class="checkbox" type="checkbox" name="raterangenew['.$i.'][allowrate]" value="1" '.(empty($raterangearray[$i]) ? '' : 'checked').'></td>'.
						'<td>extcredits'.$i.'</td>'.
						'<td>'.$extcredits[$i]['title'].'</td>'.
						'<td><input type="text" class="txt" name="raterangenew['.$i.'][min]" size="3" value="'.$raterangearray[$i]['min'].'"></td>'.
						'<td><input type="text" class="txt" name="raterangenew['.$i.'][max]" size="3" value="'.$raterangearray[$i]['max'].'"></td>'.
						'<td><input type="text" class="txt" name="raterangenew['.$i.'][mrpd]" size="3" value="'.$raterangearray[$i]['mrpd'].'"></td></tr>';
				}
			}
			echo '<tr><td colspan="6">'.$lang['usergroups_edit_raterange_comment'].'</td></tr></td></tr>';
			showtablefooter();
			echo '</td></tr>';
			showtagfooter('tbody');
			showsubmit('detailsubmit', 'submit', '', "<input type=\"submit\" class=\"btn\" name=\"saveconfigsubmit\" value=\"".$lang['saveconf']."\">");
			showtablefooter();
			showformfooter();

		} else {

			$systemnew = 'private';

			if($group['type'] == 'special') {
				if($system_publicnew) {
					if($radminidnew) {
						cpmsg('usergroups_edit_public_invalid', '', 'error');
					} else {
						if($system_dailypricenew > 0) {
							if(!$creditstrans) {
								cpmsg('usergroups_edit_creditstrans_disabled', '', 'error');
							} else {
								$system_minspannew = $system_minspannew <= 0 ? 1 : $system_minspannew;
								$systemnew = intval($system_dailypricenew)."\t".intval($system_minspannew);
							}
						} else {
							$systemnew = "0\t0";
						}
					}
				}
				if(in_array($radminidnew, array(1, 2, 3))) {
					$query = $db->query("SELECT admingid FROM {$tablepre}admingroups WHERE admingid='$group[groupid]'");
					if(!$db->num_rows($query)) {
						if($radminidnew == 1) {
							$db->query("REPLACE INTO {$tablepre}admingroups (admingid, alloweditpost, alloweditpoll, allowstickthread, allowmodpost, allowdelpost, allowmassprune, allowcensorword, allowviewip, allowbanip, allowedituser, allowmoduser, allowbanuser, allowpostannounce, allowviewlog, disablepostctrl)
								VALUES ('$group[groupid]', 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)");
							$db->query("REPLACE INTO {$tablepre}adminactions (admingid, disabledactions)
								VALUES ('$group[groupid]', '')");
						} else {
							$db->query("REPLACE INTO {$tablepre}admingroups (admingid)
								VALUES ('$group[groupid]')");
						}
					}
				} else {
					$radminidnew = 0;
					$db->query("DELETE FROM {$tablepre}admingroups WHERE admingid='$group[groupid]'");
				}
			} else {
				$radminidnew = $group['type'] == 'system' && in_array($group['groupid'], array(1, 2, 3)) ? $group['groupid'] : 0;
			}

			if(is_array($raterangenew)) {
				foreach($raterangenew as $key => $rate) {
					if($key >= 1 && $key <= 8 && $rate['allowrate']) {
						$rate['min'] = intval($rate['min'] < -999 ? -999 : $rate['min']);
						$rate['max'] = intval($rate['max'] > 999 ? 999 : $rate['max']);
						$rate['mrpd'] = intval($rate['mrpd'] > 99999 ? 99999 : $rate['mrpd']);
						if(!$rate['mrpd'] || $rate['max'] <= $rate['min'] || $rate['mrpd'] < max(abs($rate['min']), abs($rate['max']))) {
							cpmsg('usergroups_edit_rate_invalid', '', 'error');
						} else {
							$raterangenew[$key] = implode("\t", array($key, $rate['min'], $rate['max'], $rate['mrpd']));
						}
					} else {
						unset($raterangenew[$key]);
					}
				}
			}
			$raterangenew = $raterangenew ? implode("\n", $raterangenew) : '';
			$maxpricenew = $maxpricenew < 0 ? 0 : intval($maxpricenew);
			$maxpostsperhournew = $maxpostsperhournew > 255 ? 255 : intval($maxpostsperhournew);

			$extensionarray = array();
			foreach(explode(',', $attachextensionsnew) as $extension) {
				if($extension = trim($extension)) {
					$extensionarray[] = $extension;
				}
			}
			$attachextensionsnew = implode(', ', $extensionarray);

			if($maxtradepricenew == $mintradepricenew || $maxtradepricenew < 0 || $mintradepricenew <= 0 || ($maxtradepricenew && $maxtradepricenew < $mintradepricenew)) {
				cpmsg('trade_fee_error', '', 'error');
			} elseif(($maxrewardpricenew != 0 && $minrewardpricenew >= $maxrewardpricenew) || $minrewardpricenew < 1 || $minrewardpricenew< 0 || $maxrewardpricenew < 0) {
				cpmsg('reward_credits_error', '', 'error');
			}

			$exemptnewbin = '';
			for($i = 0;$i < 8;$i++) {
				$exemptnewbin = intval($exemptnew[$i]).$exemptnewbin;
			}
			$exemptnew = bindec($exemptnewbin);

			$tradesticknew = $tradesticknew > 0 ? intval($tradesticknew) : 0;
			$maxinvitedaynew = $maxinvitedaynew > 0 ? intval($maxinvitedaynew) : 10;

			$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$grouptitlenew', radminid='$radminidnew', system='$systemnew', allowvisit='$allowvisitnew',
				readaccess='$readaccessnew', allowmultigroups='$allowmultigroupsnew', allowtransfer='$allowtransfernew', allowviewpro='$allowviewpronew',
				allowviewstats='$allowviewstatsnew', allowinvisible='$allowinvisiblenew', allowsearch='$allowsearchnew',
				reasonpm='$reasonpmnew', allownickname='$allownicknamenew', allowcstatus='$allowcstatusnew',
				disableperiodctrl='$disableperiodctrlnew', maxpostsperhour='$maxpostsperhournew', maxinvitenum='$maxinvitenumnew', maxinviteday='$maxinvitedaynew', allowpost='$allowpostnew', allowreply='$allowreplynew',
				allowanonymous='$allowanonymousnew', allowsetreadperm='$allowsetreadpermnew', maxprice='$maxpricenew', allowhidecode='$allowhidecodenew',
				allowhtml='$allowhtmlnew', allowpostpoll='$allowpostpollnew', allowdirectpost='$allowdirectpostnew', allowvote='$allowvotenew',
				allowcusbbcode='$allowcusbbcodenew', allowsigbbcode='$allowsigbbcodenew', allowsigimgcode='$allowsigimgcodenew', allowinvite='$allowinvitenew', allowmailinvite='$allowmailinvitenew', raterange='$raterangenew',
				maxsigsize='$maxsigsizenew', allowgetattach='$allowgetattachnew', allowpostattach='$allowpostattachnew',
				allowsetattachperm='$allowsetattachpermnew', allowpostreward='$allowpostrewardnew', maxrewardprice='$maxrewardpricenew', minrewardprice='$minrewardpricenew', inviteprice='$invitepricenew',
				maxattachsize='$maxattachsizenew', maxsizeperday='$maxsizeperdaynew', attachextensions='$attachextensionsnew',
				allowbiobbcode='$allowbiobbcodenew', allowbioimgcode='$allowbioimgcodenew', maxbiosize='$maxbiosizenew', exempt='$exemptnew',
				maxtradeprice='$maxtradepricenew', mintradeprice='$mintradepricenew', tradestick='$tradesticknew', allowposttrade='$allowposttradenew', allowpostactivity='$allowpostactivitynew', ".($videoopen ? "allowpostvideo='$allowpostvideonew', " :'')."allowmagics='$allowmagicsnew', maxmagicsweight='$maxmagicsweightnew', magicsdiscount='$magicsdiscountnew', allowpostdebate='$allowpostdebatenew' WHERE groupid='$id'");

			if($allowinvisiblenew == 0 && $group['allowinvisible'] != $allowinvisiblenew) {
				$db->query("UPDATE {$tablepre}members SET invisible='0' WHERE groupid='$id'");
			}

			if($group['type'] == 'special' && $radminidnew != $group['radminid']) {
				$db->query("UPDATE {$tablepre}members SET adminid='".($radminidnew ? $radminidnew : -1)."' WHERE groupid='$id' AND adminid='$group[radminid]'");
			}

			updatecache('usergroups');

			if(submitcheck('saveconfigsubmit')) {
				$projectid = intval($projectid);
				dheader("Location: {$boardurl}admincp.php?action=project&operation=add&id=$id&type=group&projectid=$projectid");
			} else {
				cpmsg('usergroups_edit_succeed', 'admincp.php?action=groups&operation=user&do=edit&id='.$id.'&anchor='.$anchor, 'succeed');
			}
		}

	}

} elseif($operation == 'ranks') {

	if(!submitcheck('ranksubmit')) {

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" size="12" name="newranktitle[]">'],
			[1,'<input type="text" class="txt" size="6" name="newpostshigher[]">'],
			[1,'<input type="text" class="txt" size="2" name="newstars[]">', 'td28'],
			[1,'<input type="text" class="txt" size="6" name="newcolor[]">']
		]
	];
</script>
EOT;
		shownav('user', 'nav_ranks');
		showsubmenu('nav_ranks');
		showtips('ranks_tips');
		showformheader('groups&operation=ranks');
		showtableheader();
		showsubtitle(array('', 'ranks_title', 'ranks_postshigher', 'ranks_stars', 'ranks_color'));

		$query = $db->query("SELECT * FROM {$tablepre}ranks ORDER BY postshigher");
		while($rank = $db->fetch_array($query)) {
			showtablerow('', array('class="td25"', '', '', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$rank[rankid]}]\" value=\"$rank[rankid]\">",
				"<input type=\"text\" class=\"txt\" size=\"12\" name=\"ranktitlenew[{$rank[rankid]}]\" value=\"$rank[ranktitle]\">",
				"<input type=\"text\" class=\"txt\" size=\"6\" name=\"postshighernew[{$rank[rankid]}]\" value=\"$rank[postshigher]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"starsnew[{$rank[rankid]}]\" value=\"$rank[stars]\">",
				"<input type=\"text\" class=\"txt\" size=\"6\"name=\"colornew[{$rank[rankid]}]\" value=\"$rank[color]\">",
			));
		}

		echo '<tr><td></td><td colspan="4"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_level_add'].'</a></div></td></tr>';
		showsubmit('ranksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($delete) {
			$ids = implode('\',\'', $delete);
			$db->query("DELETE FROM {$tablepre}ranks WHERE rankid IN ('$ids')");
		}

		foreach($ranktitlenew as $id => $value) {
			$db->query("UPDATE {$tablepre}ranks SET ranktitle='$ranktitlenew[$id]', postshigher='$postshighernew[$id]', stars='$starsnew[$id]', color='$colornew[$id]' WHERE rankid='$id'");
		}

		if(is_array($newranktitle)) {
			foreach($newranktitle as $key => $value) {
				if($value = trim($value)) {
					$db->query("INSERT INTO {$tablepre}ranks (ranktitle, postshigher, stars, color)
						VALUES ('$value', '$newpostshigher[$key]', '$newstars[$key]', '$newcolor[$key]')");
				}
			}
		}

		updatecache('ranks');
		cpmsg('ranks_succeed', 'admincp.php?action=groups&operation=ranks', 'succeed');
	}
}

function array_flip_keys($arr) {
	$arr2 = array();
	$arrkeys = @array_keys($arr);
	list(, $first) = @each(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k=>$v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
}

?>