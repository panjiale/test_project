<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: settings.inc.php 13497 2008-04-20 17:23:40Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$settings = array();
$query = $db->query("SELECT * FROM {$tablepre}settings");
while($setting = $db->fetch_array($query)) {
	$settings[$setting['variable']] = $setting['value'];
}

if(!$isfounder) {
	unset($settings['ftp']);
}

$extbutton = '';

if(!submitcheck('settingsubmit')) {

	if($operation == 'ecommerce') {
		if($from == 'creditwizard') {
			shownav('tools', 'nav_creditwizard', 'settings_ecommerce');
		} else {
			shownav('extended', 'nav_ec', 'nav_ec_config');
		}
	} elseif(in_array($operation, array('seo', 'cachethread', 'serveropti'))) {
		shownav('global', 'settings_optimize', 'settings_'.$operation);
	} else {
		shownav('global', 'settings_'.$operation);
	}

	if(in_array($operation, array('seo', 'cachethread', 'serveropti'))) {
		$current = array($operation => 1);
		showsubmenu('settings_optimize', array(
			array('settings_seo', 'settings&operation=seo', $current['seo']),
			array('settings_cachethread', 'settings&operation=cachethread', $current['cachethread']),
			array('settings_serveropti', 'settings&operation=serveropti', $current['serveropti'])
		));
	} elseif($operation == 'ecommerce') {
		if($from == 'creditwizard') {
			showsubmenu('nav_creditwizard', array(
				array('creditwizard_step_menu_1', 'creditwizard&step=1', 0),
				array('creditwizard_step_menu_2', 'creditwizard&step=2', 0),
				array('creditwizard_step_menu_3', 'creditwizard&step=3', 0),
				array('creditwizard_step_menu_4', 'settings&operation=ecommerce&from=creditwizard', 1),
				array('alipay', 'ecommerce&operation=alipay&from=creditwizard', 0),
			));
		} else {
			showsubmenu('nav_ec', array(
				array('nav_ec_config', 'settings&operation=ecommerce', 1),
				array('nav_ec_alipay', 'ecommerce&operation=alipay', 0),
				array('nav_ec_credit', 'ecommerce&operation=ec_credit', 0),
				array('nav_ec_orders', 'ecommerce&operation=orders', 0),
				array('nav_ec_tradelog', 'tradelog', 0)
			));
		}
	} elseif($operation == 'access') {
		$anchor = in_array($anchor, array('register', 'access')) ? $anchor : 'register';
		showsubmenuanchors('settings_access', array(
			array('settings_subtitle_register', 'register', $anchor == 'register'),
			array('settings_subtitle_access', 'access', $anchor == 'access')
		));
	} elseif($operation == 'mail') {
		$anchor = in_array($anchor, array('settings', 'check')) ? $anchor : 'settings';
		showsubmenuanchors('settings_mail', array(
			array('config', 'mailsettings', $anchor == 'settings'),
			array('settings_mail_check', 'mailcheck', $anchor == 'check')
		));
	} elseif($operation == 'sec') {
		$anchor = in_array($anchor, array('seclevel', 'seccode', 'secqaa')) ? $anchor : 'seclevel';
		showsubmenuanchors('settings_sec', array(
			array('settings_sec_seclevel', 'seclevel', $anchor == 'seclevel'),
			array('settings_sec_seccode', 'seccode', $anchor == 'seccode'),
			array('settings_sec_secqaa', 'secqaa', $anchor == 'secqaa')
		));
	} elseif($operation == 'attachments') {
		$anchor = in_array($anchor, array('basic', 'image', 'ftp', 'antileech')) ? $anchor : 'basic';
		showsubmenuanchors('settings_attachments', array(
			array('settings_attachments_basic', 'basic', $anchor == 'basic'),
			array('settings_attachments_image', 'image', $anchor == 'image'),
			$isfounder ? array('settings_attachments_ftp', 'ftp', $anchor == 'ftp') : '',
			array('settings_attachments_antileech', 'antileech', $anchor == 'antileech'),
		));
	} elseif($operation == 'styles') {
		$anchor = in_array($anchor, array('global', 'index', 'forumdisplay', 'viewthread', 'member', 'refresh')) ? $anchor : 'global';
		showsubmenuanchors('settings_styles', array(
			array('settings_subtitle_global', 'global', $anchor == 'global'),
			array('settings_subtitle_index', 'index', $anchor == 'index'),
			array('settings_subtitle_forumdisplay', 'forumdisplay', $anchor == 'forumdisplay'),
			array('settings_subtitle_viewthread', 'viewthread', $anchor == 'viewthread'),
			array('settings_subtitle_member', 'member', $anchor == 'member'),
			array('settings_subtitle_refresh', 'refresh', $anchor == 'refresh'),
		));
	} elseif($operation == 'functions') {
		$anchor = in_array($anchor, array('editor', 'stat', 'mod', 'tags', 'other')) ? $anchor : 'editor';
		showsubmenuanchors('settings_functions', array(
			array('settings_subtitle_editor', 'editor', $anchor == 'editor'),
			array('settings_subtitle_stat', 'stat', $anchor == 'stat'),
			array('settings_subtitle_mod', 'mod', $anchor == 'mod'),
			array('settings_subtitle_tags', 'tags', $anchor == 'tags'),
			array('settings_subtitle_other', 'other', $anchor == 'other'),
		));
	} else {
		showsubmenu('settings_'.$operation);
	}
	showformheader('settings&edit=yes');
	showhiddenfields(array('operation' => $operation));

	if($operation == 'access') {

		$wmsgcheck = array($settings['welcomemsg'] =>'checked');
		$settings['inviteconfig'] = unserialize($settings['inviteconfig']);
		$settings['extcredits'] = unserialize($settings['extcredits']);

		$buycredits = $rewardcredist = '';
		for($i = 0; $i <= 8; $i++) {
			$extcredit = 'extcredits'.$i.($settings['extcredits'][$i]['available'] ? ' ('.$settings['extcredits'][$i]['title'].')' : '');
			$buycredits .= '<option value="'.$i.'" '.($i == intval($settings['inviteconfig']['invitecredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			$rewardcredits .= '<option value="'.$i.'" '.($i == intval($settings['inviteconfig']['inviterewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
		}

		$groupselect = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type='special'");
		while($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $settings['inviteconfig']['invitegroupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		showtableheader('', 'nobottom', 'id="register"'.($anchor != 'register' ? ' style="display: none"' : ''));
		showsetting('settings_regstatus', array('settingsnew[regstatus]', array(
			array(0, $lang['settings_register_close'], array('showinvite' => 'none')),
			array(1, $lang['settings_register_open'], array('showinvite' => 'none')),
			array(2, $lang['settings_register_invite'], array('showinvite' => '')),
			array(3, $lang['settings_register_open_invite'], array('showinvite' => ''))
		)), $settings['regstatus'], 'mradio');

		showtagheader('tbody', 'showinvite', $settings['regstatus'] > 1, 'sub');
		showsetting('settings_register_invite_credit', '', '', '<select name="settingsnew[inviteconfig][inviterewardcredit]">'.$rewardcredits.'</select>');
		showsetting('settings_register_invite_addcredit', 'settingsnew[inviteconfig][inviteaddcredit]', $settings['inviteconfig']['inviteaddcredit'], 'text');
		showsetting('settings_register_invite_invitedcredit', 'settingsnew[inviteconfig][invitedaddcredit]', $settings['inviteconfig']['invitedaddcredit'], 'text');
		showsetting('settings_register_invite_addfriend', 'settingsnew[inviteconfig][inviteaddbuddy]', $settings['inviteconfig']['inviteaddbuddy'], 'radio');
		showsetting('settings_register_invite_group', '', '', '<select name="settingsnew[inviteconfig][invitegroupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$groupselect.'</select>');
		showtagfooter('tbody');
		showsetting('settings_reg_name', 'settingsnew[regname]', $settings['regname'], 'text');
		showsetting('settings_reglink_name', 'settingsnew[reglinkname]', $settings['reglinkname'], 'text');
		showsetting('settings_register_advanced', 'settingsnew[regadvance]', $settings['regadvance'], 'radio');
		showsetting('settings_censoruser', 'settingsnew[censoruser]', $settings['censoruser'], 'textarea');
		showsetting('settings_regverify', array('settingsnew[regverify]', array(
			array(0, $lang['none']),
			array(1, $lang['settings_regverify_email']),
			array(2, $lang['settings_regverify_manual'])
		)), $settings['regverify'], 'select');
		showsetting('settings_regctrl', 'settingsnew[regctrl]', $settings['regctrl'], 'text');
		showsetting('settings_regfloodctrl', 'settingsnew[regfloodctrl]', $settings['regfloodctrl'], 'text');
		showsetting('settings_ipregctrl', 'settingsnew[ipregctrl]', $settings['ipregctrl'], 'textarea');
		showsetting('settings_welcomemsg', array('settingsnew[welcomemsg]', array(
			array(0, $lang['settings_welcomemsg_nosend'], array('welcomemsgext' => 'none')),
			array(1, $lang['settings_welcomemsg_pm'], array('welcomemsgext' => '')),
			array(2, $lang['settings_welcomemsg_email'], array('welcomemsgext' => ''))
		)), $settings['welcomemsg'], 'mradio');
		showtagheader('tbody', 'welcomemsgext', $settings['welcomemsg'], 'sub');
		showsetting('settings_welcomemsgtitle', 'settingsnew[welcomemsgtitle]', $settings['welcomemsgtitle'], 'text');
		showsetting('settings_welcomemsgtxt', 'settingsnew[welcomemsgtxt]', $settings['welcomemsgtxt'], 'textarea');
		showtagfooter('tbody');
		showsetting('settings_bbrules', 'settingsnew[bbrules]', $settings['bbrules'], 'radio', '', 1);
		showsetting('settings_bbrulestxt', 'settingsnew[bbrulestxt]', $settings['bbrulestxt'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="access"'.($anchor != 'access' ? ' style="display: none"' : ''));
		showsetting('settings_newbiespan', 'settingsnew[newbiespan]', $settings['newbiespan'], 'text');
		showsetting('settings_ipaccess', 'settingsnew[ipaccess]', $settings['ipaccess'], 'textarea');
		showsetting('settings_adminipaccess', 'settingsnew[adminipaccess]', $settings['adminipaccess'], 'textarea');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'styles') {

		$jsmenu = array();
		$settings['jsmenustatus'] = sprintf('%b', $settings['jsmenustatus']);
		for($i = 1; $i <= strlen($settings['jsmenustatus']); $i++) {
			$jsmenu[$i] = substr($settings['jsmenustatus'], -$i, 1) ? 'checked' : '';
		}

		$showsettings = str_pad(decbin($settings['showsettings']), 3, 0, STR_PAD_LEFT);
		$settings['showsignatures'] = $showsettings{0};
		$settings['showavatars'] = $showsettings{1};
		$settings['showimages'] = $showsettings{2};
		$settings['postnocustom'] = implode("\n", (array)unserialize($settings['postnocustom']));

		$settings['infosidestatus'] = unserialize($settings['infosidestatus']);
		$sideselect[0] = '<select name="settingsnew[infosidestatus][0]"><option value=""></option>';
		$sideselect[1] = '<select name="settingsnew[infosidestatus][1]"><option value=""></option>';
		$query = $db->query("SELECT variable FROM {$tablepre}request WHERE type=-2");
		while($side = $db->fetch_array($query)) {
			$sideselect[0] .= "<option value=\"$side[variable]\" ".($settings['infosidestatus'][0] == $side['variable'] ? 'selected="selected"' : NULL).">$side[variable]</option>\n";
			$sideselect[1] .= "<option value=\"$side[variable]\" ".($settings['infosidestatus'][1] == $side['variable'] ? 'selected="selected"' : NULL).">$side[variable]</option>\n";
		}
		$sideselect[0] .= '</select>';
		$sideselect[1] .= '</select>';

		showtips('settings_tips', 'global_tips', $anchor == 'global');
		showtips('settings_tips', 'index_tips', $anchor == 'index');
		showtips('settings_tips', 'forumdisplay_tips', $anchor == 'forumdisplay');

		showtableheader('', 'nobottom', 'id="global"'.($anchor != 'global' ? ' style="display: none"' : ''));
		showtitle('settings_subtitle_menu');
		showsetting('settings_jsmenu', 'settingsnew[forumjump]', $settings['forumjump'], 'radio');
		showsetting('settings_jsmenu_enable', '', '', '<ul class="nofloat" onmouseover="altStyle(this);"><li'.($jsmenu[1] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][1]" value="1" '.$jsmenu[1].'> '.$lang['settings_jsmenu_enable_jump'].'</li><li'.($jsmenu[2] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][2]" value="1" '.$jsmenu[2].'> '.$lang['settings_jsmenu_enable_memcp'].'</li><li'.($jsmenu[3] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][3]" value="1" '.$jsmenu[3].'> '.$lang['settings_jsmenu_enable_stat'].'</li><li'.($jsmenu[4] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][4]" value="1" '.$jsmenu[4].'> '.$lang['settings_jsmenu_enable_my'].'</li></ul>');
		showsetting('settings_pluginjsmenu', 'settingsnew[pluginjsmenu]', $settings['pluginjsmenu'], 'text');
		showtitle('settings_subtitle_frameon');
		showsetting('settings_frameon', array('settingsnew[frameon]', array(
			array(0, $lang['settings_frameon_0'], array('frameonext' => 'none')),
			array(1, $lang['settings_frameon_1'], array('frameonext' => '')),
			array(2, $lang['settings_frameon_2'], array('frameonext' => ''))
		)), $settings['frameon'], 'mradio');
		showtagheader('tbody', 'frameonext', $settings['frameon'], 'sub');
		showsetting('settings_framewidth', 'settingsnew[framewidth]', $settings['framewidth'], 'text');
		showtagfooter('tbody');
		showtitle('settings_subtitle_infoside');
		showsetting('settings_sideselect', '', '', '
			<ul>
				<li class="clear">'.$lang['settings_sideselect_0'].'</li>
				<li class="clear">'.$sideselect[0].'</li>
				<li class="clear">'.$lang['settings_sideselect_1'].'</li>
				<li class="clear">'.$sideselect[1].'</li>
				<li class="clear">'.$lang['settings_sideselect_replies_condition'].' <input type="text" class="txt" name="settingsnew[infosidestatus][posts]" value="'.$settings['infosidestatus']['posts'].'" style="width:50px;" /> '.$lang['settings_sideselect_replies_show'].'</li>
			</ul>
		');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="index"'.($anchor != 'index' ? ' style="display: none"' : ''));
		showsetting('settings_subforumsindex', 'settingsnew[subforumsindex]', $settings['subforumsindex'], 'radio');
		showsetting('settings_forumlinkstatus', 'settingsnew[forumlinkstatus]', $settings['forumlinkstatus'], 'radio');
		showsetting('settings_index_members', 'settingsnew[maxbdays]', $settings['maxbdays'], 'text');
		showsetting('settings_moddisplay', array('settingsnew[moddisplay]', array(
			array('flat', $lang['settings_moddisplay_flat']),
			array('selectbox', $lang['settings_moddisplay_selectbox'])
		)), $settings['moddisplay'], 'mradio');
		showsetting('settings_whosonline', array('settingsnew[whosonlinestatus]', array(
			array(0, $lang['settings_display_none']),
			array(1, $lang['settings_whosonline_index']),
			array(2, $lang['settings_whosonline_forum']),
			array(3, $lang['settings_whosonline_both'])
		)), $settings['whosonlinestatus'], 'select');
		showsetting('settings_whosonline_contract', 'settingsnew[whosonline_contract]', $settings['whosonline_contract'], 'radio');
		showsetting('settings_online_more_members', 'settingsnew[maxonlinelist]', $settings['maxonlinelist'], 'text');
		showsetting('settings_hideprivate', 'settingsnew[hideprivate]', $settings['hideprivate'], 'radio');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="forumdisplay"'.($anchor != 'forumdisplay' ? ' style="display: none"' : ''));

		showsetting('settings_tpp', 'settingsnew[topicperpage]', $settings['topicperpage'], 'text');
		showsetting('settings_threadmaxpages', 'settingsnew[threadmaxpages]', $settings['threadmaxpages'], 'text');
		showsetting('settings_hottopic', 'settingsnew[hottopic]', $settings['hottopic'], 'text');
		showsetting('settings_fastpost', 'settingsnew[fastpost]', $settings['fastpost'], 'radio');
		showsetting('settings_globalstick', 'settingsnew[globalstick]', $settings['globalstick'], 'radio');
		showsetting('settings_stick', 'settingsnew[threadsticky]', $settings['threadsticky'], 'text');
		showsetting('settings_visitedforums', 'settingsnew[visitedforums]', $settings['visitedforums'], 'text');
		showtablefooter();

		showtagheader('div', 'viewthread', $anchor == 'viewthread');
		showtableheader('settings_subtitle_viewthread', 'nobottom');
		showsetting('settings_ppp', 'settingsnew[postperpage]', $settings['postperpage'], 'text');
		showsetting('settings_starthreshold', 'settingsnew[starthreshold]', $settings['starthreshold'], 'text');
		showsetting('settings_maxsigrows', 'settingsnew[maxsigrows]', $settings['maxsigrows'], 'text');
		showsetting('settings_rate_number', 'settingsnew[ratelogrecord]', $settings['ratelogrecord'], 'text');
		showsetting('settings_show_signature', 'settingsnew[showsignatures]', $settings['showsignatures'], 'radio');
		showsetting('settings_show_face', 'settingsnew[showavatars]', $settings['showavatars'], 'radio');
		showsetting('settings_show_images', 'settingsnew[showimages]', $settings['showimages'], 'radio');
		showsetting('settings_zoomstatus', 'settingsnew[zoomstatus]', $settings['zoomstatus'], 'radio');
		showsetting('settings_vtonlinestatus', array('settingsnew[vtonlinestatus]', array(
			array(0, $lang['settings_display_none']),
			array(1, $lang['settings_online_easy']),
			array(2, $lang['settings_online_exactitude'])
		)), $settings['vtonlinestatus'], 'select');
		showsetting('settings_userstatusby', array('settingsnew[userstatusby]', array(
			array(0, $lang['settings_display_none']),
			array(1, $lang['settings_userstatusby_usergroup']),
			array(2, $lang['settings_userstatusby_rank'])
		)), $settings['userstatusby'], 'select');
		showsetting('settings_postno', 'settingsnew[postno]', $settings['postno'], 'text');
		showsetting('settings_postnocustom', 'settingsnew[postnocustom]', $settings['postnocustom'], 'textarea');
		showsetting('settings_maxsmilies', 'settingsnew[maxsmilies]', $settings['maxsmilies'], 'text');

		showtableheader($lang['settings_customauthorinfo'].'('.$lang['settings_customauthorinfo_comment'].')', 'noborder fixpadding');
		$authorinfoitems = array(
			'uid' => 'UID',
			'posts' => $lang['settings_userinfo_posts'],
			'digest' => $lang['settings_userinfo_digest'],
			'credits' => $lang['settings_userinfo_credits'],
		);
		if(!empty($extcredits)) {
			foreach($extcredits as $key => $value) {
				if($value['showinthread']) {
					$authorinfoitems['extcredits'.$key] = $value['title'];
				}
			}
		}
		$query = $db->query("SELECT fieldid,title FROM {$tablepre}profilefields WHERE available='1' AND invisible='0' AND showinthread='1' ORDER BY displayorder");
		while($profilefields = $db->fetch_array($query)) {
			$authorinfoitems['field_'.$profilefields['fieldid']] = $profilefields['title'];
		}
		$authorinfoitems = array_merge($authorinfoitems, array(
			'readperm' => $lang['settings_userinfo_readperm'],
			'gender' => $lang['settings_userinfo_gender'],
			'location' => $lang['settings_userinfo_location'],
			'oltime' => $lang['settings_userinfo_oltime'],
			'regtime' => $lang['settings_userinfo_regtime'],
			'lastdate' => $lang['settings_userinfo_lastdate'],
		));

		showsubtitle(array('', 'settings_userinfo_left', 'settings_userinfo_special', 'settings_userinfo_menu'));

		$settings['customauthorinfo'] = unserialize($settings['customauthorinfo']);
		$settings['customauthorinfo'] = $settings['customauthorinfo'][0];
		$authorinfoitemsetting = '';
		foreach($authorinfoitems as $key => $value) {
			$authorinfoitemsetting .= '<tr><td>'.$value.
				'</td><td><input name="settingsnew[customauthorinfo]['.$key.'][left]" type="checkbox" class="checkbox" value="1" '.($settings['customauthorinfo'][$key]['left'] ? 'checked' : '').'>'.
				'</td><td><input name="settingsnew[customauthorinfo]['.$key.'][special]" type="checkbox" class="checkbox" value="1" '.($settings['customauthorinfo'][$key]['special'] ? 'checked' : '').'>'.
				'</td><td><input name="settingsnew[customauthorinfo]['.$key.'][menu]" type="checkbox" class="checkbox" value="1" '.($settings['customauthorinfo'][$key]['menu'] ? 'checked' : '').'>'.
				'</td></tr>';
		}

		echo $authorinfoitemsetting;
		showtablefooter();
		showtagfooter('div');

		showtableheader('', 'nobottom', 'id="member"'.($anchor != 'member' ? ' style="display: none"' : ''));
		showsetting('settings_mpp', 'settingsnew[memberperpage]', $settings['memberperpage'], 'text');
		showsetting('settings_membermaxpages', 'settingsnew[membermaxpages]', $settings['membermaxpages'], 'text');

		$settings['msgforward'] = !empty($settings['msgforward']) ? unserialize($settings['msgforward']) : array();
		$settings['msgforward']['messages'] = !empty($settings['msgforward']['messages']) ? implode("\n", $settings['msgforward']['messages']) : '';
		showtablefooter();

		showtableheader('', 'nobottom', 'id="refresh"'.($anchor != 'refresh' ? ' style="display: none"' : ''));
		showsetting('settings_refresh_refreshtime', 'settingsnew[msgforward][refreshtime]', $settings['msgforward']['refreshtime'], 'text');
		showsetting('settings_refresh_quick', 'settingsnew[msgforward][quick]', $settings['msgforward']['quick'], 'radio', '', 1);
		showsetting('settings_refresh_messages', 'settingsnew[msgforward][messages]', $settings['msgforward']['messages'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'seo') {

		showtips('settings_tips');
		showtableheader();
		showtitle('settings_seo');
		showsetting('settings_archiverstatus', array('settingsnew[archiverstatus]', array(
			array(0, $lang['settings_archiverstatus_none']),
			array(1, $lang['settings_archiverstatus_full']),
			array(2, $lang['settings_archiverstatus_searchengine']),
			array(3, $lang['settings_archiverstatus_browser']))), $settings['archiverstatus'], 'mradio');
		showsetting('settings_rewritestatus', array('settingsnew[rewritestatus]', array(
			$lang['settings_rewritestatus_forumdisplay'],
			$lang['settings_rewritestatus_viewthread'],
			$lang['settings_rewritestatus_space'],
			$lang['settings_rewritestatus_tag'],
			$lang['settings_rewritestatus_archiver'])), $settings['rewritestatus'], 'binmcheckbox');
		showsetting('settings_rewritecompatible', 'settingsnew[rewritecompatible]', $settings['rewritecompatible'], 'radio');
		showsetting('settings_seotitle', 'settingsnew[seotitle]', $settings['seotitle'], 'text');
		showsetting('settings_seokeywords', 'settingsnew[seokeywords]', $settings['seokeywords'], 'text');
		showsetting('settings_seodescription', 'settingsnew[seodescription]', $settings['seodescription'], 'text');
		showsetting('settings_seohead', 'settingsnew[seohead]', $settings['seohead'], 'textarea');

		showtitle('settings_subtitle_sitemap');
		showsetting('settings_sitemap_baidu_open', 'settingsnew[baidusitemap]', $settings['baidusitemap'], 'radio', '', 1);
		showsetting('settings_sitemap_baidu_expire', 'settingsnew[baidusitemap_life]', $settings['baidusitemap_life'], 'text');
		showtagfooter('tbody');

	} elseif($operation == 'functions') {

		$editoroptions = str_pad(decbin($settings['editoroptions']), 2, 0, STR_PAD_LEFT);
		$settings['defaulteditormode'] = $editoroptions{0};
		$settings['allowswitcheditor'] = $editoroptions{1};

		showtips('settings_tips', 'stat_tips', $anchor == 'stat');
		showtips('settings_tips', 'mod_tips', $anchor == 'mod');
		showtips('settings_tips', 'other_tips', $anchor == 'other');

		showtableheader('', 'nobottom', 'id="editor"'.($anchor != 'editor' ? ' style="display: none"' : ''));
		showsetting('settings_editor_mode_default', array('settingsnew[defaulteditormode]', array(
			array(0, $lang['settings_editor_mode_discuzcode']),
			array(1, $lang['settings_editor_mode_wysiwyg']))), $settings['defaulteditormode'], 'mradio');
		showsetting('settings_editor_swtich_enable', 'settingsnew[allowswitcheditor]', $settings['allowswitcheditor'], 'radio');
		showsetting('settings_bbinsert', 'settingsnew[bbinsert]', $settings['bbinsert'], 'radio');
		showsetting('settings_smileyinsert', 'settingsnew[smileyinsert]', $settings['smileyinsert'], 'radio', '', 1);
		showsetting('settings_smthumb', 'settingsnew[smthumb]', $settings['smthumb'], 'text');
		showsetting('settings_smcols', 'settingsnew[smcols]', $settings['smcols'], 'text');
		showsetting('settings_smrows', 'settingsnew[smrows]', $settings['smrows'], 'text');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="stat"'.($anchor != 'stat' ? ' style="display: none"' : ''));
		showsetting('settings_statstatus', 'settingsnew[statstatus]', $settings['statstatus'], 'radio');
		showsetting('settings_statscachelife', 'settingsnew[statscachelife]', $settings['statscachelife'], 'text');
		showsetting('settings_pvfrequence', 'settingsnew[pvfrequence]', $settings['pvfrequence'], 'text');
		showsetting('settings_oltimespan', 'settingsnew[oltimespan]', $settings['oltimespan'], 'text');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="mod"'.($anchor != 'mod' ? ' style="display: none"' : ''));
		showsetting('settings_modworkstatus', 'settingsnew[modworkstatus]', $settings['modworkstatus'], 'radio');
		showsetting('settings_maxmodworksmonths', 'settingsnew[maxmodworksmonths]', $settings['maxmodworksmonths'], 'text');
		showsetting('settings_myfunction_savetime', 'settingsnew[myrecorddays]', $settings['myrecorddays'], 'text');
		showsetting('settings_losslessdel', 'settingsnew[losslessdel]', $settings['losslessdel'], 'text');
		showsetting('settings_modreasons', 'settingsnew[modreasons]', $settings['modreasons'], 'textarea');
		showsetting('settings_bannedmessages', 'settingsnew[bannedmessages]', $settings['bannedmessages'], 'radio');
		showsetting('settings_warninglimit', 'settingsnew[warninglimit]', $settings['warninglimit'], 'text');
		showsetting('settings_warningexpiration', 'settingsnew[warningexpiration]', $settings['warningexpiration'], 'text');

		showtableheader('', 'nobottom', 'id="tags"'.($anchor != 'tags' ? ' style="display: none"' : ''));
		showsetting('settings_tagstatus', array('settingsnew[tagstatus]', array(
			array(0, $lang['forums_edit_tagstatus_none'], array('tagext' => 'none')),
			array(1, $lang['forums_edit_tagstatus_use'], array('tagext' => '')),
			array(2, $lang['forums_edit_tagstatus_quired'], array('tagext' => ''))
		)), $settings['tagstatus'], 'mradio');
		showtagheader('tbody', 'tagext', $settings['tagstatus'], 'sub');
		showsetting('settings_index_hottags', 'settingsnew[hottags]', $settings['hottags'], 'text');
		showsetting('settings_viewthtrad_hottags', 'settingsnew[viewthreadtags]', $settings['viewthreadtags'], 'text');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="other"'.($anchor != 'other' ? ' style="display: none"' : ''));
		showsetting('settings_rssstatus', 'settingsnew[rssstatus]', $settings['rssstatus'], 'radio');
		showsetting('settings_rssttl', 'settingsnew[rssttl]', $settings['rssttl'], 'text');
		showsetting('settings_send_birthday', 'settingsnew[bdaystatus]', $settings['bdaystatus'], 'radio');
		showsetting('settings_debug', 'settingsnew[debug]', $settings['debug'], 'radio');
		showsetting('settings_activity_type', 'settingsnew[activitytype]', $settings['activitytype'], 'textarea');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'credits') {

		showtips('settings_credits_tips');

		if(!empty($projectid)) {
			$settings = @array_merge($settings, unserialize($db->result_first("SELECT value FROM {$tablepre}projects WHERE id='$projectid'")));
		}

		$projectselect = "<select name=\"projectid\" onchange=\"window.location='admincp.php?action=settings&operation=credits&projectid='+this.options[this.options.selectedIndex].value\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
		$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='extcredit'");
		while($project = $db->fetch_array($query)) {
			$projectselect .= "<option value=\"$project[id]\" ".($project['id'] == $projectid ? 'selected="selected"' : NULL).">$project[name]</option>\n";
		}
		$projectselect .= '</select>';

		showtableheader('settings_credits_scheme_title', 'nobottom');
		showsetting('settings_credits_scheme', '', '', $projectselect);
		showtablefooter();
		echo <<<EOT
<script type="text/JavaScript">
	function switchpolicy(obj, col) {
		var status = !obj.checked;
		$("policy" + col).disabled = status;
		var policytable = $("policytable");
		for(var row=2; row<14; row++) {
			if(is_opera) {
				policytable.rows[row].cells[col].firstChild.disabled = true;
			} else {
				policytable.rows[row].cells[col].disabled = status;
			}
		}
	}
</script>
EOT;
		showtableheader('settings_credits_extended', 'noborder fixpadding');
		showsubtitle(array('credits_id', 'credits_title', 'credits_unit', 'settings_credits_ratio', 'settings_credits_init', 'settings_credits_available', 'settings_credits_show_in_thread', 'credits_inport', 'credits_import'));

		$settings['extcredits'] = unserialize($settings['extcredits']);
		$settings['initcredits'] = explode(',', $settings['initcredits']);
		for($i = 1; $i <= 8; $i++) {
			showtablerow('', array('class="td22"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), array(
				'extcredits'.$i,
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingsnew[extcredits][$i][title]\" value=\"{$settings['extcredits'][$i]['title']}\">",
				"<input type=\"text\" class=\"txt\" size=\"5\" name=\"settingsnew[extcredits][$i][unit]\" value=\"{$settings['extcredits'][$i]['unit']}\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"settingsnew[extcredits][$i][ratio]\" value=\"".(float)$settings['extcredits'][$i]['ratio']."\" onkeyup=\"if(this.value != '0' && \$('allowexchangeout$i').checked == false && \$('allowexchangein$i').checked == false) {\$('allowexchangeout$i').checked = true;\$('allowexchangein$i').checked = true;} else if(this.value == '0') {\$('allowexchangeout$i').checked = false;\$('allowexchangein$i').checked = false;}\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"settingsnew[initcredits][$i]\" value=\"".intval($settings['initcredits'][$i])."\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingsnew[extcredits][$i][available]\" value=\"1\" ".($settings['extcredits'][$i]['available'] ? 'checked' : '')." onclick=\"switchpolicy(this, $i)\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingsnew[extcredits][$i][showinthread]\" value=\"1\" ".($settings['extcredits'][$i]['showinthread'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" size=\"3\" name=\"settingsnew[extcredits][$i][allowexchangeout]\" value=\"1\" ".($settings['extcredits'][$i]['allowexchangeout'] ? 'checked' : '')." id=\"allowexchangeout$i\">",
				"<input class=\"checkbox\" type=\"checkbox\" size=\"3\" name=\"settingsnew[extcredits][$i][allowexchangein]\" value=\"1\" ".($settings['extcredits'][$i]['allowexchangein'] ? 'checked' : '')." id=\"allowexchangein$i\">"
			));
		}
		showtablerow('', 'colspan="9" class="lineheight"', $lang['settings_credits_extended_comment']);
		showtablefooter();

		showtableheader('settings_credits_policy', 'noborder fixpadding', 'id="policytable"');
		echo '<tr><th valign="top">'.$lang['credits_id'].'</th>';
		$settings['creditspolicy'] = unserialize($settings['creditspolicy']);
		for($i = 1; $i <= 8; $i++) {
			echo "<th id=\"policy$i\" ".($settings['extcredits'][$i]['available'] ? '' : 'disabled')." valign=\"top\"> extcredits$i<br />".($settings['extcredits'][$i]['title'] ? '('.$settings['extcredits'][$i]['title'].')' : '')."</th>";
		}
		echo '</tr>';
		foreach(array('post', 'reply', 'digest', 'postattach', 'getattach', 'search', 'promotion_visit', 'promotion_register', 'tradefinished', 'votepoll', 'lowerlimit') as $policy) {
			showtablerow('title="'.$lang['settings_credits_policy_'.$policy.'_comment'].'"', array('class="td22"', 'class="td28"',  'class="td28"',  'class="td28"',  'class="td28"',  'class="td28"',  'class="td28"',  'class="td28"',  'class="td28"'), creditsrow($policy));
		}

		showtablerow('', 'class="lineheight" colspan="9"', $lang['settings_credits_policy_comment']);
		showtablefooter();
		showtableheader('', 'noborder');
		showtitle('settings_credits');
		showsetting('settings_creditsformula', 'settingsnew[creditsformula]', $settings['creditsformula'], 'textarea');

		$creditstrans = '';
		for($i = 0; $i <= 8; $i++) {
			$creditstrans .= '<option value="'.$i.'" '.($i == intval($settings['creditstrans']) ? 'selected' : '').'>'.($i ? 'extcredits'.$i.($settings['extcredits'][$i]['title'] ? '('.$settings['extcredits'][$i]['title'].')' : '') : $lang['none']).'</option>';
		}
		showsetting('settings_creditstrans', '', '', '<select name="settingsnew[creditstrans]">'.$creditstrans.'</select>');
		showsetting('settings_creditstax', 'settingsnew[creditstax]', $settings['creditstax'], 'text');
		showsetting('settings_transfermincredits', 'settingsnew[transfermincredits]', $settings['transfermincredits'], 'text');
		showsetting('settings_exchangemincredits', 'settingsnew[exchangemincredits]', $settings['exchangemincredits'], 'text');
		showsetting('settings_maxincperthread', 'settingsnew[maxincperthread]', $settings['maxincperthread'], 'text');
		showsetting('settings_maxchargespan', 'settingsnew[maxchargespan]', $settings['maxchargespan'], 'text');

		$extbutton = '&nbsp;&nbsp;&nbsp;<input name="projectsave" type="hidden" value="0"><input type="button" class="btn" onclick="$(\'cpform\').projectsave.value=1;$(\'cpform\').settingsubmit.click()" value="'.$lang['saveconf'].'">';

	} elseif($operation == 'serveropti') {

		$checkgzipfunc = !function_exists('ob_gzhandler') ? 1 : 0;

		showtips('settings_tips');
		showtableheader();
		showtitle('settings_serveropti');
		showsetting('settings_gzipcompress', 'settingsnew[gzipcompress]', $settings['gzipcompress'], 'radio', $checkgzipfunc);
		showsetting('settings_delayviewcount', array('settingsnew[delayviewcount]', array(
			array(0, $lang['none']),
			array(1, $lang['settings_delayviewcount_thread']),
			array(2, $lang['settings_delayviewcount_attach']),
			array(3, $lang['settings_delayviewcount_thread_attach']))), $settings['delayviewcount'], 'select');
		showsetting('settings_nocacheheaders', 'settingsnew[nocacheheaders]', $settings['nocacheheaders'], 'radio');
		showsetting('settings_transsidstatus', 'settingsnew[transsidstatus]', $settings['transsidstatus'], 'radio');
		showsetting('settings_maxonlines', 'settingsnew[maxonlines]', $settings['maxonlines'], 'text');
		showsetting('settings_onlinehold', 'settingsnew[onlinehold]', $settings['onlinehold'], 'text');
		showsetting('settings_loadctrl', 'settingsnew[loadctrl]', $settings['loadctrl'], 'text');
		showsetting('settings_floodctrl', 'settingsnew[floodctrl]', $settings['floodctrl'], 'text');

		showtitle('settings_subtitle_search');
		showsetting('settings_searchctrl', 'settingsnew[searchctrl]', $settings['searchctrl'], 'text');
		showsetting('settings_maxspm', 'settingsnew[maxspm]', $settings['maxspm'], 'text');
		showsetting('settings_maxsearchresults', 'settingsnew[maxsearchresults]', $settings['maxsearchresults'], 'text');

	} elseif($operation == 'sec') {

		echo '<script type="text/JavaScript">
		function updateseccode(op) {
			if(isUndefined(op)) {
				var x = new Ajax();
				x.get(\'ajax.php?action=updateseccode&inajax=1\', function(s) {
					$(\'seccodeimage\').innerHTML = s;
				});
			} else {
				window.document.seccodeplayer.SetVariable("isPlay", "1");
			}
		}
		</script>';

		$checksc = array();
		$settings['seccodedata'] = unserialize($settings['seccodedata']);

		$seccodetypearray = array(
			array(0, $lang['settings_seccodetype_image'], array('seccodeimageext' => '', 'seccodeimagewh' => '')),
			array(1, $lang['settings_seccodetype_chnfont'], array('seccodeimageext' => '', 'seccodeimagewh' => '')),
			array(2, $lang['settings_seccodetype_flash'], array('seccodeimageext' => 'none', 'seccodeimagewh' => '')),
			array(3, $lang['settings_seccodetype_wav'], array('seccodeimageext' => 'none', 'seccodeimagewh' => 'none')),
		);

		showtips('settings_seccode_tips', 'seccode_tips', $anchor == 'seccode');
		showtips('settings_secqaa_tips', 'secqaa_tips', $anchor == 'secqaa');
		showtableheader('', '', 'id="seclevel"'.($anchor != 'seclevel' ? ' style="display: none"' : ''));
		showsetting('settings_seclevel', array('settingsnew[seclevel]', array(
			array(0, $lang['settings_seclevel_lower']),
			array(1, $lang['settings_seclevel_higher']))), $settings['seclevel'], 'mradio');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="seccode"'.($anchor != 'seccode' ? ' style="display: none"' : ''));
		showsetting('settings_seccodestatus', array('settingsnew[seccodestatus]', array(
			$lang['settings_seccodestatus_register'],
			$lang['settings_seccodestatus_login'],
			$lang['settings_seccodestatus_post'],
			$lang['settings_seccodestatus_profile'])), $settings['seccodestatus'], 'binmcheckbox');
		showsetting('settings_seccodeminposts', 'settingsnew[seccodedata][minposts]', $settings['seccodedata']['minposts'], 'text');
		showsetting('settings_seccodeloginfailedcount', 'settingsnew[seccodedata][loginfailedcount]', $settings['seccodedata']['loginfailedcount'], 'radio');
		showsetting('settings_seccodenoclick', 'settingsnew[seccodedata][noclick]', $settings['seccodedata']['noclick'], 'radio');
		showsetting('settings_seccodetype', array('settingsnew[seccodedata][type]', $seccodetypearray), $settings['seccodedata']['type'], 'mradio');
		showtagheader('tbody', 'seccodeimagewh', $settings['seccodedata']['type'] != 3, 'sub');
		showsetting('settings_seccodewidth', 'settingsnew[seccodedata][width]', $settings['seccodedata']['width'], 'text');
		showsetting('settings_seccodeheight', 'settingsnew[seccodedata][height]', $settings['seccodedata']['height'], 'text');
		showtagfooter('tbody');
		showtagheader('tbody', 'seccodeimageext', $settings['seccodedata']['type'] != 2 && $settings['seccodedata']['type'] != 3, 'sub');
		showsetting('settings_seccodebackground', 'settingsnew[seccodedata][background]', $settings['seccodedata']['background'], 'radio');
		showsetting('settings_seccodeadulterate', 'settingsnew[seccodedata][adulterate]', $settings['seccodedata']['adulterate'], 'radio');
		showsetting('settings_seccodettf', 'settingsnew[seccodedata][ttf]', $settings['seccodedata']['ttf'], 'radio', !function_exists('imagettftext'));
		showsetting('settings_seccodeangle', 'settingsnew[seccodedata][angle]', $settings['seccodedata']['angle'], 'radio');
		showsetting('settings_seccodecolor', 'settingsnew[seccodedata][color]', $settings['seccodedata']['color'], 'radio');
		showsetting('settings_seccodesize', 'settingsnew[seccodedata][size]', $settings['seccodedata']['size'], 'radio');
		showsetting('settings_seccodeshadow', 'settingsnew[seccodedata][shadow]', $settings['seccodedata']['shadow'], 'radio');
		showsetting('settings_seccodeanimator', 'settingsnew[seccodedata][animator]', $settings['seccodedata']['animator'], 'radio', !function_exists('imagegif'));
		showtagfooter('tbody');
		showsubmit('settingsubmit');
		showtablefooter();
		echo '<script language="JavaScript">updateseccode()</script>';

		$settings['secqaa'] = unserialize($settings['secqaa']);
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * 10;
		$secqaanums = $db->result_first("SELECT COUNT(*) FROM {$tablepre}itempool");
		$multipage = multi($secqaanums, 10, $page, 'admincp.php?action=settings&operation=sec');

		$query = $db->query("SELECT * FROM {$tablepre}itempool LIMIT $start_limit, 10");

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1,''], [1,'<input name="newquestion[]" type="text" class="txt">','td26'], [1, '<input name="newanswer[]" type="text" class="txt">']],
	];
	</script>
EOT;
		showtagheader('div', 'secqaa', $anchor == 'secqaa');
		showtableheader('settings_secqaa', 'nobottom');
		showsetting('settings_secqaa_status', array('settingsnew[secqaa][status]', array(
			$lang['settings_seccodestatus_register'],
			$lang['settings_seccodestatus_post'])), $settings['secqaa']['status'], 'binmcheckbox');
		showsetting('settings_secqaa_minposts', 'settingsnew[secqaa][minposts]', $settings['secqaa']['minposts'], 'text');
		showtablefooter();

		showtableheader('settings_secqaa_qaa', 'noborder fixpadding');
		showsubtitle(array('', 'settings_secqaa_question', 'settings_secqaa_answer'));

		while($item = $db->fetch_array($query)) {
			showtablerow('', array('', 'class="td26"'), array(
				'<input class="checkbox" type="checkbox" name="delete[]" value="'.$item['id'].'">',
				'<input type="text" class="txt" name="question['.$item['id'].']" value="'.dhtmlspecialchars($item['question']).'" class="txtnobd" onblur="this.className=\'txtnobd\'" onfocus="this.className=\'txt\'">',
				'<input type="text" class="txt" name="answer['.$item['id'].']" value="'.$item['answer'].'" class="txtnobd" onblur="this.className=\'txtnobd\'" onfocus="this.className=\'txt\'">'
			));
		}

		echo '<tr><td></td><td class="td26"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['settings_secqaa_add'].'</a></div></td><td></td></tr>';
		showsubmit('settingsubmit', 'submit', 'del', '', $multipage);
		showtablefooter();
		showtagfooter('div');

		showformfooter();
		exit;


	} elseif($operation == 'datetime') {

		$checktimeformat = array($settings['timeformat'] == 'H:i' ? 24 : 12 => 'checked');

		$settings['userdateformat'] = dateformat($settings['userdateformat']);
		$settings['dateformat'] = dateformat($settings['dateformat']);

		showtableheader();
		showtitle('settings_subtitle_datetime');
		showsetting('settings_dateformat', 'settingsnew[dateformat]', $settings['dateformat'], 'text');
		showsetting('settings_timeformat', '', '', '<input class="radio" type="radio" name="settingsnew[timeformat]" value="24" '.$checktimeformat[24].'> 24 '.$lang['hour'].' <input class="radio" type="radio" name="settingsnew[timeformat]" value="12" '.$checktimeformat[12].'> 12 '.$lang['hour'].'');
		showsetting('settings_timeoffset', 'settingsnew[timeoffset]', $settings['timeoffset'], 'text');
		showsetting('settings_customformat', 'settingsnew[userdateformat]', $settings['userdateformat'], 'textarea');

		showtitle('settings_subtitle_periods');
		showsetting('settings_visitbanperiods', 'settingsnew[visitbanperiods]', $settings['visitbanperiods'], 'textarea');
		showsetting('settings_postbanperiods', 'settingsnew[postbanperiods]', $settings['postbanperiods'], 'textarea');
		showsetting('settings_postmodperiods', 'settingsnew[postmodperiods]', $settings['postmodperiods'], 'textarea');
		showsetting('settings_ban_downtime', 'settingsnew[attachbanperiods]', $settings['attachbanperiods'], 'textarea');
		showsetting('settings_searchbanperiods', 'settingsnew[searchbanperiods]', $settings['searchbanperiods'], 'textarea');

	} elseif($operation == 'permissions') {

		showtableheader();
		showsetting('settings_memliststatus', 'settingsnew[memliststatus]', $settings['memliststatus'], 'radio');
		showsetting('settings_reportpost', array('settingsnew[reportpost]', array(
			array(0, $lang['settings_reportpost_none']),
			array(1, $lang['settings_reportpost_level_1']),
			array(2, $lang['settings_reportpost_level_2']),
			array(3, $lang['settings_reportpost_level_3']))), $settings['reportpost'], 'select');
		showsetting('settings_minpostsize', 'settingsnew[minpostsize]', $settings['minpostsize'], 'text');
		showsetting('settings_maxpostsize', 'settingsnew[maxpostsize]', $settings['maxpostsize'], 'text');
		showsetting('settings_favorite_storage', 'settingsnew[maxfavorites]', $settings['maxfavorites'], 'text');
		showsetting('settings_subscriptions', 'settingsnew[maxsubscriptions]', $settings['maxsubscriptions'], 'text');
		showsetting('settings_maxpolloptions', 'settingsnew[maxpolloptions]', $settings['maxpolloptions'], 'text');
		showsetting('settings_edittimelimit', 'settingsnew[edittimelimit]', $settings['edittimelimit'], 'text');
		showsetting('settings_editby', 'settingsnew[editedby]', $settings['editedby'], 'radio');

		showtitle('settings_subtitle_rate');
		showsetting('settings_karmaratelimit', 'settingsnew[karmaratelimit]', $settings['karmaratelimit'], 'text');
		showsetting('settings_modratelimit', 'settingsnew[modratelimit]', $settings['modratelimit'], 'radio');
		showsetting('settings_dupkarmarate', 'settingsnew[dupkarmarate]', $settings['dupkarmarate'], 'radio');

	} elseif($operation == 'attachments') {

		$checkwm = array($settings['watermarkstatus'] => 'checked');
		$checkmkdirfunc = !function_exists('mkdir') ? 'disabled' : '';
		$settings['watermarktext'] = unserialize($settings['watermarktext']);
		$settings['watermarktext']['fontpath'] = str_replace(array('ch/', 'en/'), '', $settings['watermarktext']['fontpath']);

		$fontlist = '<select name="settingsnew[watermarktext][fontpath]">';
		$dir = opendir(DISCUZ_ROOT.'./images/fonts/en');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist .= '<option value="'.$entry.'"'.($entry == $settings['watermarktext']['fontpath'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$dir = opendir(DISCUZ_ROOT.'./images/fonts/ch');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist .= '<option value="'.$entry.'"'.($entry == $settings['watermarktext']['fontpath'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$fontlist .= '</select>';

		showtableheader('', '', 'id="basic"'.($anchor != 'basic' ? ' style="display: none"' : ''));
		showsetting('settings_attachdir', 'settingsnew[attachdir]', $settings['attachdir'], 'text');
		showsetting('settings_attachurl', 'settingsnew[attachurl]', $settings['attachurl'], 'text');
		showsetting('settings_attachimgpost', 'settingsnew[attachimgpost]', $settings['attachimgpost'], 'radio');
		showsetting('settings_attachsave', array('settingsnew[attachsave]', array(
			array(0, $lang['settings_attachsave_default']),
			array(1, $lang['settings_attachsave_forum']),
			array(2, $lang['settings_attachsave_type']),
			array(3, $lang['settings_attachsave_month']),
			array(4, $lang['settings_attachsave_day'])
		)), $settings['attachsave'], 'select', $checkmkdirfunc);
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="image"'.($anchor != 'image' ? ' style="display: none"' : ''));
		showsetting('settings_imagelib', array('settingsnew[imagelib]', array(
			array(0, $lang['settings_watermarktype_GD'], array('imagelibext' => 'none')),
			array(1, $lang['settings_watermarktype_IM'], array('imagelibext' => ''))
		)), $settings['imagelib'], 'mradio');
		showtagheader('tbody', 'imagelibext', $settings['imagelib'], 'sub');
		showsetting('settings_imageimpath', 'settingsnew[imageimpath]', $settings['imageimpath'], 'text');
		showtagfooter('tbody');
		showsetting('settings_thumbstatus', array('settingsnew[thumbstatus]', array(
			array(0, $lang['settings_thumbstatus_none'], array('thumbext' => 'none')),
			array(1, $lang['settings_thumbstatus_add'], array('thumbext' => '')),
			array(3, $lang['settings_thumbstatus_addfix'], array('thumbext' => '')),
			array(2, $lang['settings_thumbstatus_replace'], array('thumbext' => ''))
		)), $settings['thumbstatus'], 'mradio');
		showtagheader('tbody', 'thumbext', $settings['thumbstatus'], 'sub');
		showsetting('settings_thumbquality', 'settingsnew[thumbquality]', $settings['thumbquality'], 'text');
		showsetting('settings_thumbwidthheight', array('settingsnew[thumbwidth]', 'settingsnew[thumbheight]'), array(intval($settings['thumbwidth']), intval($settings['thumbheight'])), 'multiply');
		showtagfooter('tbody');
		showsetting('settings_watermarkstatus', '', '', '<table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="0" '.$checkwm[0].'>'.$lang['settings_watermarkstatus_none'].'</td></tr><tr><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="1" '.$checkwm[1].'> #1</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="2" '.$checkwm[2].'> #2</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="3" '.$checkwm[3].'> #3</td></tr><tr><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="4" '.$checkwm[4].'> #4</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="5" '.$checkwm[5].'> #5</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="6" '.$checkwm[6].'> #6</td></tr><tr><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="7" '.$checkwm[7].'> #7</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="8" '.$checkwm[8].'> #8</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="9" '.$checkwm[9].'> #9</td></tr></table>');
		showsetting('settings_watermarkminwidthheight', array('settingsnew[watermarkminwidth]', 'settingsnew[watermarkminheight]'), array(intval($settings['watermarkminwidth']), intval($settings['watermarkminheight'])), 'multiply');
		showsetting('settings_watermarktype', array('settingsnew[watermarktype]', array(
			array(0, $lang['settings_watermarktype_gif'], array('watermarktypeext' => 'none')),
			array(1, $lang['settings_watermarktype_png'], array('watermarktypeext' => 'none')),
			array(2, $lang['settings_watermarktype_text'], array('watermarktypeext' => ''))
		)), $settings['watermarktype'], 'mradio');
		showsetting('settings_watermarktrans', 'settingsnew[watermarktrans]', $settings['watermarktrans'], 'text');
		showsetting('settings_watermarkquality', 'settingsnew[watermarkquality]', $settings['watermarkquality'], 'text');
		showtagheader('tbody', 'watermarktypeext', $settings['watermarktype'] == 2, 'sub');
		showsetting('settings_watermarktext_text', 'settingsnew[watermarktext][text]', $settings['watermarktext']['text'], 'textarea');
		showsetting('settings_watermarktext_fontpath', '', '', $fontlist);
		showsetting('settings_watermarktext_size', 'settingsnew[watermarktext][size]', $settings['watermarktext']['size'], 'text');
		showsetting('settings_watermarktext_angle', 'settingsnew[watermarktext][angle]', $settings['watermarktext']['angle'], 'text');
		showsetting('settings_watermarktext_color', 'settingsnew[watermarktext][color]', $settings['watermarktext']['color'], 'color');
		showsetting('settings_watermarktext_shadowx', 'settingsnew[watermarktext][shadowx]', $settings['watermarktext']['shadowx'], 'text');
		showsetting('settings_watermarktext_shadowy', 'settingsnew[watermarktext][shadowy]', $settings['watermarktext']['shadowy'], 'text');
		showsetting('settings_watermarktext_shadowcolor', 'settingsnew[watermarktext][shadowcolor]', $settings['watermarktext']['shadowcolor'], 'color');
		showsetting('settings_watermarktext_imtranslatex', 'settingsnew[watermarktext][translatex]', $settings['watermarktext']['translatex'], 'text');
		showsetting('settings_watermarktext_imtranslatey', 'settingsnew[watermarktext][translatey]', $settings['watermarktext']['translatey'], 'text');
		showsetting('settings_watermarktext_imskewx', 'settingsnew[watermarktext][skewx]', $settings['watermarktext']['skewx'], 'text');
		showsetting('settings_watermarktext_imskewy', 'settingsnew[watermarktext][skewy]', $settings['watermarktext']['skewy'], 'text');
		showtagfooter('tbody');
		showsubmit('settingsubmit');
		showtablefooter();

		if($isfounder) {
			$settings['ftp'] = unserialize($settings['ftp']);
			$settings['ftp'] = is_array($settings['ftp']) ? $settings['ftp'] : array();
			$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($authkey));
			$settings['ftp']['password'] = $settings['ftp']['password'] ? $settings['ftp']['password']{0}.'********'.$settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} : '';

			showtableheader('', '', 'id="ftp"'.($anchor != 'ftp' ? ' style="display: none"' : ''));
			showsetting('settings_remote_enabled', array('settingsnew[ftp][on]', array(
				array(1, $lang['yes'], array('ftpext' => '', 'ftpcheckbutton' => '')),
				array(0, $lang['no'], array('ftpext' => 'none', 'ftpcheckbutton' => 'none'))
			), TRUE), $settings['ftp']['on'], 'mradio');
			showtagheader('tbody', 'ftpext', $settings['ftp']['on'], 'sub');
			showsetting('settings_remote_enabled_ssl', 'settingsnew[ftp][ssl]', $settings['ftp']['ssl'], 'radio');
			showsetting('settings_remote_ftp_host', 'settingsnew[ftp][host]', $settings['ftp']['host'], 'text');
			showsetting('settings_remote_ftp_port', 'settingsnew[ftp][port]', $settings['ftp']['port'], 'text');
			showsetting('settings_remote_ftp_user', 'settingsnew[ftp][username]', $settings['ftp']['username'], 'text');
			showsetting('settings_remote_ftp_pass', 'settingsnew[ftp][password]', $settings['ftp']['password'], 'text');
			showsetting('settings_remote_ftp_pasv', 'settingsnew[ftp][pasv]', $settings['ftp']['pasv'], 'radio');
			showsetting('settings_remote_dir', 'settingsnew[ftp][attachdir]', $settings['ftp']['attachdir'], 'text');
			showsetting('settings_remote_url', 'settingsnew[ftp][attachurl]', $settings['ftp']['attachurl'], 'text');
			showsetting('settings_remote_timeout', 'settingsnew[ftp][timeout]', $settings['ftp']['timeout'], 'text');
			showsetting('settings_remote_mirror', array('settingsnew[ftp][mirror]', array(
				array(1, lang('settings_remote_mirror_1')),
				//array(2, lang('settings_remote_mirror_2')),
				array(0, lang('settings_remote_mirror_0'))
			)), intval($settings['ftp']['mirror']), 'mradio');
			showsetting('settings_remote_allowedexts', 'settingsnew[ftp][allowedexts]', $settings['ftp']['allowedexts'], 'textarea');
			showsetting('settings_remote_disallowedexts', 'settingsnew[ftp][disallowedexts]', $settings['ftp']['disallowedexts'], 'textarea');
			showsetting('settings_remote_minsize', 'settingsnew[ftp][minsize]', $settings['ftp']['minsize'], 'text');
			showtagfooter('tbody');
			showsubmit('settingsubmit', 'submit', '', '<span id="ftpcheckbutton" style="display: '.($settings['ftp']['on'] ? '' : 'none').'"><input type="submit" class="btn" name="ftpcheck" value="'.$lang['settings_remote_ftpcheck'].'" onclick="this.form.action=\'admincp.php?action=checktools&operation=ftpcheck&frame=no\';this.form.target=\'ftpcheckiframe\';"></span><iframe name="ftpcheckiframe" style="display: none"></iframe>');
			showtablefooter();
		}

		showtableheader('', '', 'id="antileech"'.($anchor != 'antileech' ? ' style="display: none"' : ''));
		showsetting('settings_attachexpire', 'settingsnew[attachexpire]', $settings['attachexpire'], 'text');
		showsetting('settings_attachrefcheck', 'settingsnew[attachrefcheck]', $settings['attachrefcheck'], 'radio');
		showsetting('settings_remote_hide_dir', 'settingsnew[ftp][hideurl]', $settings['ftp']['hideurl'], 'radio');
		showsubmit('settingsubmit');
		showtablefooter();

		showformfooter();
		exit;

	} elseif($operation == 'wap') {

		$settings['wapdateformat'] = dateformat($settings['wapdateformat']);

		showtableheader();
		showsetting('settings_wapstatus', 'settingsnew[wapstatus]', $settings['wapstatus'], 'radio', '', 1);
		showsetting('settings_wap_register', 'settingsnew[wapregister]', $settings['wapregister'], 'radio');
		showsetting('settings_wapcharset', array('settingsnew[wapcharset]', array(
			array(1, 'UTF-8'),
			array(2, 'UNICODE'))), $settings['wapcharset'], 'mradio');
		showsetting('settings_waptpp', 'settingsnew[waptpp]', $settings['waptpp'], 'text');
		showsetting('settings_wapppp', 'settingsnew[wapppp]', $settings['wapppp'], 'text');
		showsetting('settings_wapdateformat', 'settingsnew[wapdateformat]', $settings['wapdateformat'], 'text');
		showsetting('settings_wapmps', 'settingsnew[wapmps]', $settings['wapmps'], 'text');
		showtagfooter('tbody');

	} elseif($operation == 'cachethread') {

		include_once DISCUZ_ROOT.'./include/forum.func.php';
		$forumselect = '<select name="fids[]" multiple="multiple" size="10"><option value="all">'.$lang['all'].'</option><option value="">&nbsp;</option>'.forumselect().'</select>';
		showtableheader();
		showtitle('settings_cachethread');
		showsetting('settings_cachethread_indexlife', 'settingsnew[cacheindexlife]', $settings['cacheindexlife'], 'text');
		showsetting('settings_cachethread_life', 'settingsnew[cachethreadlife]', $settings['cachethreadlife'], 'text');
		showsetting('settings_cachethread_dir', 'settingsnew[cachethreaddir]', $settings['cachethreaddir'], 'text');

		showtitle('settings_cachethread_coefficient_set');
		showsetting('settings_cachethread_coefficient', 'settingsnew[threadcaches]', '', "<input type=\"text\" class=\"txt\" size=\"30\" name=\"settingsnew[threadcaches]\" value=\"\">");
		showsetting('settings_cachethread_coefficient_forum', '', '', $forumselect);

	} elseif($operation == 'ecommerce') {

		$settings['tradetypes'] = unserialize($settings['tradetypes']);

		$query = $db->query("SELECT * FROM {$tablepre}threadtypes WHERE special='1' ORDER BY displayorder");
		$tradetypeselect = '<select name="settingsnew[tradetypes][]" size="10" multiple="multiple">';
		while($type = $db->fetch_array($query)) {
			$checked = @in_array($type['typeid'], $settings['tradetypes']);
			$tradetypeselect .= '<option value="'.$type['typeid'].'"'.($checked ? ' selected="selected"' : '').'>'.$type['name'].'</option>';
		}
		$tradetypeselect .= '</select>';

		showtableheader();
		showtitle('settings_ecommerce_sub_credittrade');
		showsetting('alipay_ratio', 'settingsnew[ec_ratio]', $settings['ec_ratio'], 'text');
		showsetting('alipay_mincredits', 'settingsnew[ec_mincredits]', $settings['ec_mincredits'], 'text');
		showsetting('alipay_maxcredits', 'settingsnew[ec_maxcredits]', $settings['ec_maxcredits'], 'text');
		showsetting('alipay_maxcreditspermonth', 'settingsnew[ec_maxcreditspermonth]', $settings['ec_maxcreditspermonth'], 'text');

		showtitle('settings_ecommerce_sub_goodstrade');
		showsetting('settings_trade_biosize', 'settingsnew[maxbiotradesize]', $settings['maxbiotradesize'], 'text');
		showsetting('settings_trade_imagewidthheight', array('settingsnew[tradeimagewidth]', 'settingsnew[tradeimageheight]'), array(intval($settings['tradeimagewidth']), intval($settings['tradeimageheight'])), 'multiply');
		showsetting('settings_trade_type', '', '', $tradetypeselect);

	} elseif($operation == 'mail' && $isfounder) {

		$settings['mail'] = unserialize($settings['mail']);

		//showtableheader();
		showtableheader('', '', 'id="mailsettings"'.($anchor != 'settings' ? ' style="display: none"' : ''));
		showsetting('settings_mail_send', array('settingsnew[mail][mailsend]', array(
			array(1, $lang['settings_mail_send_1'], array('hidden1' => 'none', 'hidden2' => 'none')),
			array(2, $lang['settings_mail_send_2'], array('hidden1' => '', 'hidden2' => '')),
			array(3, $lang['settings_mail_send_3'], array('hidden1' => '', 'hidden2' => 'none'))
		)), $settings['mail']['mailsend'], 'mradio');
		showtagheader('tbody', 'hidden1', $settings['mail']['mailsend'] != 1, 'sub');
		showsetting('settings_mail_server', 'settingsnew[mail][server]', $settings['mail']['server'], 'text');
		showsetting('settings_mail_port', 'settingsnew[mail][port]', $settings['mail']['port'], 'text');
		showtagfooter('tbody');
		showtagheader('tbody', 'hidden2', $settings['mail']['mailsend'] == 2, 'sub');
		showsetting('settings_mail_auth', 'settingsnew[mail][auth]', $settings['mail']['auth'], 'radio');
		showsetting('settings_mail_from', 'settingsnew[mail][from]', $settings['mail']['from'], 'text');
		showsetting('settings_mail_username', 'settingsnew[mail][auth_username]', $settings['mail']['auth_username'], 'text');
		showsetting('settings_mail_password', 'settingsnew[mail][auth_password]', $settings['mail']['auth_password'], 'text');
		showtagfooter('tbody');
		showsetting('settings_mail_delimiter', array('settingsnew[mail][maildelimiter]', array(
			array(1, $lang['settings_mail_delimiter_crlf']),
			array(0, $lang['settings_mail_delimiter_lf']),
			array(2, $lang['settings_mail_delimiter_cr']))),  $settings['mail']['maildelimiter'], 'mradio');
		showsetting('settings_mail_includeuser', 'settingsnew[mail][mailusername]', $settings['mail']['mailusername'], 'radio');
		showsetting('settings_mail_silent', 'settingsnew[mail][sendmail_silent]', $settings['mail']['sendmail_silent'], 'radio');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="mailcheck"'.($anchor != 'check' ? ' style="display: none"' : ''));
		showsetting('settings_mail_test_from', 'test_from', '', 'text');
		showsetting('settings_mail_test_to', 'test_to', '', 'textarea');
		showsubmit('', '', '<input type="submit" class="btn" name="mailcheck" value="'.$lang['settings_mailcheck'].'" onclick="this.form.action=\'admincp.php?action=checktools&operation=mailcheck&frame=no\';this.form.target=\'mailcheckiframe\'">', '<iframe name="mailcheckiframe" style="display: none"></iframe>');
		showtablefooter();

		showformfooter();
		exit;

	} elseif($operation == 'uc' && $isfounder) {

		$disable = !is_writeable('./config.inc.php');

		require_once DISCUZ_ROOT.'./uc_client/client.php';
		$ucapparray = uc_app_ls();

		$feedopen = FALSE;
		$apparraylist = array();
		foreach($ucapparray as $apparray) {
			if($apparray['appid'] != UC_APPID) {
				$apparraylist[] = $apparray;
			}
			if($apparray['type'] == 'UCHOME') {
				$feedopen = TRUE;
			}
		}

		showtips('settings_uc_tips');
		showtableheader();
		showsetting('settings_uc_appid', 'settingsnew[uc][appid]', UC_APPID, 'text', $disable);
		showsetting('settings_uc_key', 'settingsnew[uc][key]', UC_KEY, 'text', $disable);
		showsetting('settings_uc_api', 'settingsnew[uc][api]', UC_API, 'text', $disable);
		showsetting('settings_uc_ip', 'settingsnew[uc][ip]', UC_IP, 'text', $disable);
		showsetting('settings_uc_connect', array('settingsnew[uc][connect]', array(
				array('mysql', $lang['settings_uc_connect_mysql'], array('ucmysql' => '')),
				array('', $lang['settings_uc_connect_api'], array('ucmysql' => 'none')))), UC_CONNECT, 'mradio', $disable);
		list($ucdbname, $uctablepre) = explode('.', str_replace('`', '', UC_DBTABLEPRE));
		showtagheader('tbody', 'ucmysql', UC_CONNECT, 'sub');
		showsetting('settings_uc_dbhost', 'settingsnew[uc][dbhost]', UC_DBHOST, 'text', $disable);
		showsetting('settings_uc_dbuser', 'settingsnew[uc][dbuser]', UC_DBUSER, 'text', $disable);
		showsetting('settings_uc_dbpass', 'settingsnew[uc][dbpass]', '********', 'text', $disable);
		showsetting('settings_uc_dbname', 'settingsnew[uc][dbname]', $ucdbname, 'text', $disable);
		showsetting('settings_uc_dbtablepre', 'settingsnew[uc][dbtablepre]', $uctablepre, 'text', $disable);
		showtagfooter('tbody');

		if($apparraylist || $feedopen) {
			$applist = '';
			$settings['uc'] = unserialize($settings['uc']);

			foreach($apparraylist as $apparray) {
				$checked = $settings['uc']['navlist'][$apparray['appid']] ? 'checked="checked"': '';
				$applist .= "<input type=\"checkbox\" class=\"checkbox\" name=\"settingsnew[uc][navlist][$apparray[appid]]\" value=\"1\" $checked>$apparray[name]&nbsp;&nbsp;";
			}

			showtitle('settings_uc');
			showsetting('settings_uc_nav_open', array('settingsnew[uc][navopen]', array(
				array(1, $lang['yes'], array('navext' => '')),
				array(0, $lang['no'], array('navext' => 'none'))), TRUE), $settings['uc']['navopen'], 'mradio', $disable);
			showtagheader('tbody', 'navext', $settings['uc']['navopen'], 'sub');
			showsetting('settings_uc_nav_list', '', '', $applist);
			showtagfooter('tbody');

			if($feedopen) {
				showsetting('settings_uc_feed', array('settingsnew[uc][addfeed]', array(
					$lang['settings_uc_feed_thread'],
					$lang['settings_uc_feed_sepcialthread'],
					$lang['settings_uc_feed_reply'])), $settings['uc']['addfeed'], 'binmcheckbox');
			}
		}

	} else {

		showtableheader();
		showsetting('settings_bbname', 'settingsnew[bbname]', $settings['bbname'], 'text');
		showsetting('settings_sitename', 'settingsnew[sitename]', $settings['sitename'], 'text');
		showsetting('settings_siteurl', 'settingsnew[siteurl]', $settings['siteurl'], 'text');
		showsetting('settings_index_name', 'settingsnew[indexname]', $settings['indexname'], 'text');
		showsetting('settings_icp', 'settingsnew[icp]', $settings['icp'], 'text');
		showsetting('settings_boardlicensed', 'settingsnew[boardlicensed]', $settings['boardlicensed'], 'radio');
		showsetting('settings_bbclosed', 'settingsnew[bbclosed]', $settings['bbclosed'], 'radio');
		showsetting('settings_closedreason', 'settingsnew[closedreason]', $settings['closedreason'], 'textarea');

	}

	showtablerow('class="nobg"', 'colspan="2"', '<input type="submit" class="btn" name="settingsubmit" value="'.lang('submit').'"  />'.$extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	showformfooter();

} else {

	if(isset($settingsnew['uc']) && $isfounder && is_writeable('./config.inc.php')) {
		$ucdbpassnew = $settingsnew['uc']['dbpass'] == '********' ? UC_DBPW : $settingsnew['uc']['dbpass'];
		if($settingsnew['uc']['connect']) {
			$uc_dblink = @mysql_connect($settingsnew['uc']['dbhost'], $settingsnew['uc']['dbuser'], $ucdbpassnew, 1);
			if(!$uc_dblink) {
				cpmsg('uc_database_connect_error', '', 'error');
			} else {
				mysql_close($uc_dblink);
			}
		}

		$fp = fopen('./config.inc.php', 'r');
		$configfile = fread($fp, filesize('./config.inc.php'));
		$configfile = trim($configfile);
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		fclose($fp);

		$connect = '';
		if($settingsnew['uc']['connect']) {
			require './config.inc.php';
			$connect = 'mysql';
			$samelink = ($dbhost == $settingsnew['uc']['dbhost'] && $dbuser == $settingsnew['uc']['dbuser'] && $dbpw == $ucdbpassnew);
			$samecharset = !($dbcharset == 'gbk' && UC_DBCHARSET == 'latin1' || $dbcharset == 'latin1' && UC_DBCHARSET == 'gbk');
			$configfile = insertconfig($configfile, "/define\('UC_DBHOST',\s*'.*?'\);/i", "define('UC_DBHOST', '".$settingsnew['uc']['dbhost']."');");
			$configfile = insertconfig($configfile, "/define\('UC_DBUSER',\s*'.*?'\);/i", "define('UC_DBUSER', '".$settingsnew['uc']['dbuser']."');");
			$configfile = insertconfig($configfile, "/define\('UC_DBPW',\s*'.*?'\);/i", "define('UC_DBPW', '".$ucdbpassnew."');");
			$configfile = insertconfig($configfile, "/define\('UC_DBNAME',\s*'.*?'\);/i", "define('UC_DBNAME', '".$settingsnew['uc']['dbname']."');");
			$configfile = insertconfig($configfile, "/define\('UC_DBTABLEPRE',\s*'.*?'\);/i", "define('UC_DBTABLEPRE', '`".$settingsnew['uc']['dbname'].'`.'.$settingsnew['uc']['dbtablepre']."');");
			//$configfile = insertconfig($configfile, "/define\('UC_LINK',\s*'?.*?'?\);/i", "define('UC_LINK', ".($samelink && $samecharset ? 'TRUE' : 'FALSE').");");
		}
		$configfile = insertconfig($configfile, "/define\('UC_CONNECT',\s*'.*?'\);/i", "define('UC_CONNECT', '$connect');");
		$configfile = insertconfig($configfile, "/define\('UC_KEY',\s*'.*?'\);/i", "define('UC_KEY', '".$settingsnew['uc']['key']."');");
		$configfile = insertconfig($configfile, "/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '".$settingsnew['uc']['api']."');");
		$configfile = insertconfig($configfile, "/define\('UC_IP',\s*'.*?'\);/i", "define('UC_IP', '".$settingsnew['uc']['ip']."');");
		$configfile = insertconfig($configfile, "/define\('UC_APPID',\s*'?.*?'?\);/i", "define('UC_APPID', '".$settingsnew['uc']['appid']."');");

		$fp = fopen('./config.inc.php', 'w');
		if(!($fp = @fopen('./config.inc.php', 'w'))) {
			cpmsg('uc_config_write_error', '', 'error');
		}
		@fwrite($fp, trim($configfile));
		@fclose($fp);
		$settingsnew['uc']['addfeed'] = bindec(intval($settingsnew['uc']['addfeed'][3]).intval($settingsnew['uc']['addfeed'][2]).intval($settingsnew['uc']['addfeed'][1]));
		$settingsnew['uc'] = serialize($settingsnew['uc']);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('uc', '$settingsnew[uc]')");
	}

	if(isset($settingsnew['bbname'])) {
		$settingsnew['bbname'] = dhtmlspecialchars($settingsnew['bbname']);
	}

	if(isset($settingsnew['regname'])) {
		$settingsnew['regname'] = dhtmlspecialchars($settingsnew['regname']);
	}

	if(isset($settingsnew['reglinkname'])) {
		$settingsnew['reglinkname'] = dhtmlspecialchars($settingsnew['reglinkname']);
	}

	if(isset($settingsnew['censoruser'])) {
		$settingsnew['censoruser'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['censoruser']));
	}

	if(isset($settingsnew['ipregctrl'])) {
		$settingsnew['ipregctrl'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['ipregctrl']));
	}

	if(isset($settingsnew['ipaccess'])) {
		if($settingsnew['ipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['ipaccess']))) {
			if(!ipaccess($onlineip, $settingsnew['ipaccess'])) {
				cpmsg('settings_ipaccess_invalid', '', 'error');
			}
		}
	}

	if(isset($settingsnew['adminipaccess'])) {
		if($settingsnew['adminipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['adminipaccess']))) {
			if(!ipaccess($onlineip, $settingsnew['adminipaccess'])) {
				cpmsg('settings_adminipaccess_invalid', '', 'error');
			}
		}
	}

	if(isset($settingsnew['welcomemsgtitle'])) {
		$settingsnew['welcomemsgtitle'] = cutstr(trim(dhtmlspecialchars($settingsnew['welcomemsgtitle'])), 75);
	}

	if(isset($settingsnew['showsignatures']) && isset($settingsnew['showavatars']) && isset($settingsnew['showimages'])) {
		$settingsnew['showsettings'] = bindec($settingsnew['showsignatures'].$settingsnew['showavatars'].$settingsnew['showimages']);
	}

	if(!empty($settingsnew['globalstick'])) {
		updatecache('globalstick');
	}

	if(isset($settingsnew['inviteconfig'])) {
		$settingsnew['inviteconfig'] = addslashes(serialize($settingsnew['inviteconfig']));
	}

	if(isset($settingsnew['smthumb'])) {
		$settingsnew['smthumb'] = intval($settingsnew['smthumb']) >= 20 && intval($settingsnew['smthumb']) <= 40 ? intval($settingsnew['smthumb']) : 20;
	}

	if(isset($settingsnew['defaulteditormode']) && isset($settingsnew['allowswitcheditor'])) {
		$settingsnew['editoroptions'] = bindec($settingsnew['defaulteditormode'].$settingsnew['allowswitcheditor']);
	}

	if(isset($settingsnew['myrecorddays'])) {
		$settingsnew['myrecorddays'] = intval($settingsnew['myrecorddays']) > 0 ? intval($settingsnew['myrecorddays']) : 30;
	}

	if(!empty($settingsnew['thumbstatus']) && !function_exists('imagejpeg')) {
		$settingsnew['thumbstatus'] = 0;
	}

	if(isset($settingsnew['creditsformula']) && isset($settingsnew['extcredits']) && isset($settingsnew['creditspolicy']) && isset($settingsnew['initcredits']) && isset($settingsnew['creditstrans']) && isset($settingsnew['creditstax'])) {
		if(!preg_match("/^([\+\-\*\/\.\d\(\)]|((extcredits[1-8]|digestposts|posts|pageviews|oltime)([\+\-\*\/\(\)]|$)+))+$/", $settingsnew['creditsformula']) || !is_null(@eval(preg_replace("/(digestposts|posts|pageviews|oltime|extcredits[1-8])/", "\$\\1", $settingsnew['creditsformula']).';'))) {
			cpmsg('settings_creditsformula_invalid', '', 'error');
		}

		$extcreditsarray = array();
		if(is_array($settingsnew['extcredits'])) {
			foreach($settingsnew['extcredits'] as $key => $value) {
				if($value['available'] && !$value['title']) {
					cpmsg('settings_credits_title_invalid', '', 'error');
				}
				$extcreditsarray[$key] = array
					(
					'title'	=> dhtmlspecialchars(stripslashes($value['title'])),
					'unit' => dhtmlspecialchars(stripslashes($value['unit'])),
					'ratio' => ($value['ratio'] > 0 ? (float)$value['ratio'] : 0),
					'available' => $value['available'],
					'lowerlimit' => intval($settingsnew['creditspolicy']['lowerlimit'][$key]),
					'showinthread' => $value['showinthread'],
					'allowexchangein' => $value['allowexchangein'],
					'allowexchangeout' => $value['allowexchangeout']
					);
				$settingsnew['initcredits'][$key] = intval($settingsnew['initcredits'][$key]);
			}
		}
		if(is_array($settingsnew['creditspolicy'])) {
			foreach($settingsnew['creditspolicy'] as $key => $value) {
				for($i = 1; $i <= 8; $i++) {
					if(empty($value[$i])) {
						unset($settingsnew['creditspolicy'][$key][$i]);
					} else {
						$value[$i] = $value[$i] > 99 ? 99 : ($value[$i] < -99 ? -99 : $value[$i]);
						$settingsnew['creditspolicy'][$key][$i] = intval($value[$i]);
					}
				}
			}
		} else {
			$settingsnew['creditspolicy'] = array();
		}

		if($settingsnew['creditstrans'] && empty($settingsnew['extcredits'][$settingsnew['creditstrans']]['available'])) {
			cpmsg('settings_creditstrans_invalid', '', 'error');
		}
		$settingsnew['creditspolicy'] = addslashes(serialize($settingsnew['creditspolicy']));

		$settingsnew['creditsformulaexp'] = $settingsnew['creditsformula'];
		foreach(array('digestposts', 'posts', 'oltime', 'pageviews', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8') as $var) {
			if($extcreditsarray[$creditsid = preg_replace("/^extcredits(\d{1})$/", "\\1", $var)]['available']) {
				$replacement = $extcreditsarray[$creditsid]['title'];
			} else {
				$replacement = $lang['settings_creditsformula_'.$var];
			}
			$settingsnew['creditsformulaexp'] = str_replace($var, '<u>'.$replacement.'</u>', $settingsnew['creditsformulaexp']);
		}
		$settingsnew['creditsformulaexp'] = addslashes('<u>'.$lang['settings_creditsformula_credits'].'</u>='.$settingsnew['creditsformulaexp']);

		$initformula = str_replace('posts', '0', $settingsnew['creditsformula']);
		for($i = 1; $i <= 8; $i++) {
			$initformula = str_replace('extcredits'.$i, $settingsnew['initcredits'][$i], $initformula);
		}
		eval("\$initcredits = round($initformula);");

		$settingsnew['extcredits'] = addslashes(serialize($extcreditsarray));
		$settingsnew['initcredits'] = $initcredits.','.implode(',', $settingsnew['initcredits']);
		if($settingsnew['creditstax'] < 0 || $settingsnew['creditstax'] >= 1) {
			$settingsnew['creditstax'] = 0;
		}
	}

	if(isset($settingsnew['gzipcompress'])) {
		if(!function_exists('ob_gzhandler') && $settingsnew['gzipcompress']) {
			cpmsg('settings_gzip_invalid', '', 'error');
		}
	}

	if(isset($settingsnew['maxonlines'])) {
		if($settingsnew['maxonlines'] > 65535 || !is_numeric($settingsnew['maxonlines'])) {
			cpmsg('settings_maxonlines_invalid', '', 'error');
		}

		$db->query("ALTER TABLE {$tablepre}sessions MAX_ROWS=$settingsnew[maxonlines]");
		if($settingsnew['maxonlines'] < $settings['maxonlines']) {
			$db->query("DELETE FROM {$tablepre}sessions");
		}
	}

	if(isset($settingsnew['seccodedata'])) {
		$settingsnew['seccodedata']['width'] = intval($settingsnew['seccodedata']['width']);
		$settingsnew['seccodedata']['height'] = intval($settingsnew['seccodedata']['height']);
		$settingsnew['seccodedata']['width'] = $settingsnew['seccodedata']['width'] < 100 ? 100 : ($settingsnew['seccodedata']['width'] > 200 ? 200 : $settingsnew['seccodedata']['width']);
		$settingsnew['seccodedata']['height'] = $settingsnew['seccodedata']['height'] < 50 ? 50 : ($settingsnew['seccodedata']['height'] > 80 ? 80 : $settingsnew['seccodedata']['height']);
		$settingsnew['seccodedata']['loginfailedcount'] = !empty($settingsnew['seccodedata']['loginfailedcount']) ? 3 : 0;
		$settingsnew['seccodedata'] = addslashes(serialize($settingsnew['seccodedata']));
	}

	if($operation == 'sec') {
		$settingsnew['seccodestatus'] = bindec(intval($settingsnew['seccodestatus'][5]).intval($settingsnew['seccodestatus'][4]).intval($settingsnew['seccodestatus'][3]).intval($settingsnew['seccodestatus'][2]).intval($settingsnew['seccodestatus'][1]));
		if(is_array($delete)) {
			$db->query("DELETE FROM	{$tablepre}itempool WHERE id IN (".implodeids($delete).")");
		}

		if(is_array($question)) {
			foreach($question as $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($answer[$key])), 50);
				if($q && $a) {
					$db->query("UPDATE {$tablepre}itempool SET question='$q', answer='$a' WHERE id='$key'");
				}
			}
		}

		if(is_array($newquestion) && is_array($newanswer)) {
			foreach($newquestion as $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($newanswer[$key])), 50);
				if($q && $a) {
					$db->query("INSERT INTO	{$tablepre}itempool (question, answer) VALUES ('$q', '$a')");
				}
			}
		}

		updatecache('secqaa');

		$settingsnew['secqaa']['status'] = bindec(intval($settingsnew['secqaa']['status'][3]).intval($settingsnew['secqaa']['status'][2]).intval($settingsnew['secqaa']['status'][1]));
		$settingsnew['secqaa'] = serialize($settingsnew['secqaa']);
	}

	if($operation == 'seo') {
		$settingsnew['rewritestatus'] = bindec(intval($settingsnew['rewritestatus'][5]).intval($settingsnew['rewritestatus'][4]).intval($settingsnew['rewritestatus'][3]).intval($settingsnew['rewritestatus'][2]).intval($settingsnew['rewritestatus'][1]));
		$settingsnew['baidusitemap_life'] = max(1, min(24, intval($settingsnew['baidusitemap_life'])));
	}

	if($operation == 'ecommerce') {
		if($settingsnew['ec_ratio']) {
			if($settingsnew['ec_ratio'] < 0) {
				cpmsg('alipay_ratio_invalid', '', 'error');
			}
		} else {
			$settingsnew['ec_mincredits'] = $settingsnew['ec_maxcredits'] = 0;
		}
		foreach(array('ec_ratio', 'ec_mincredits', 'ec_maxcredits', 'ec_maxcreditspermonth', 'tradeimagewidth', 'tradeimageheight') as $key) {
			$settingsnew[$key] = intval($settingsnew[$key]);
		}
		$settingsnew['tradetypes'] = addslashes(serialize($settingsnew['tradetypes']));
	}

	if(isset($settingsnew['visitbanperiods']) && isset($settingsnew['postbanperiods']) && isset($settingsnew['postmodperiods']) && isset($settingsnew['searchbanperiods'])) {
		foreach(array('visitbanperiods', 'postbanperiods', 'postmodperiods', 'searchbanperiods') as $periods) {
			$periodarray = array();
			foreach(explode("\n", $settingsnew[$periods]) as $period) {
				if(preg_match("/^\d{1,2}\:\d{2}\-\d{1,2}\:\d{2}$/", $period = trim($period))) {
					$periodarray[] = $period;
				}
			}
			$settingsnew[$periods] = implode("\r\n", $periodarray);
		}
	}

	if(isset($settingsnew['infosidestatus'])) {
		$settingsnew['infosidestatus'] = addslashes(serialize($settingsnew['infosidestatus']));
	}

	if(isset($settingsnew['timeformat'])) {
		$settingsnew['timeformat'] = $settingsnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
	}

	if(isset($settingsnew['dateformat'])) {
		$settingsnew['dateformat'] = dateformat($settingsnew['dateformat'], 'format');
	}

	if(isset($settingsnew['userdateformat'])) {
		$settingsnew['userdateformat'] = dateformat($settingsnew['userdateformat'], 'format');
	}

	if($isfounder && isset($settingsnew['ftp'])) {
		$settings['ftp'] = unserialize($settings['ftp']);
		$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($authkey));
		if(!empty($settingsnew['ftp']['password'])) {
			$pwlen = strlen($settingsnew['ftp']['password']);
			if($pwlen < 3) {
				cpmsg('ftp_password_short', '', 'error');
			}
			if($settingsnew['ftp']['password']{0} == $settings['ftp']['password']{0} && $settingsnew['ftp']['password']{$pwlen - 1} == $settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} && substr($settingsnew['ftp']['password'], 1, $pwlen - 2) == '********') {
				$settingsnew['ftp']['password'] = $settings['ftp']['password'];
			}
			$settingsnew['ftp']['password'] = authcode($settingsnew['ftp']['password'], 'ENCODE', md5($authkey));
		}
		$settingsnew['ftp'] = serialize($settingsnew['ftp']);
	}

	if($isfounder && isset($settingsnew['mail'])) {
		$settingsnew['mail'] = serialize($settingsnew['mail']);
	}

	if(isset($settingsnew['jsrefdomains'])) {
		$settingsnew['jsrefdomains'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['jsrefdomains']));
	}

	if(isset($settingsnew['jsdateformat'])) {
		$settingsnew['jsdateformat'] = dateformat($settingsnew['jsdateformat'], 'format');
	}

	if(isset($settingsnew['wapdateformat'])) {
		$settingsnew['wapdateformat'] = dateformat($settingsnew['wapdateformat'], 'format');
	}

	if(isset($settingsnew['cachethreaddir']) && isset($settingsnew['threadcaches'])) {
		if($settingsnew['cachethreaddir'] && !is_writable(DISCUZ_ROOT.'./'.$settingsnew['cachethreaddir'])) {
			cpmsg('cachethread_dir_noexists', '', 'error');
		}
		if(!empty($fids)) {
			$sqladd = in_array('all', $fids) ? '' :  " WHERE fid IN ('".implode("', '", $fids)."')";
			$db->query("UPDATE {$tablepre}forums SET threadcaches='$settingsnew[threadcaches]'$sqladd");
		}
	}

	if($operation == 'attachments') {
		$settingsnew['thumbwidth'] = intval($settingsnew['thumbwidth']) > 0 ? intval($settingsnew['thumbwidth']) : 200;
		$settingsnew['thumbheight'] = intval($settingsnew['thumbheight']) > 0 ? intval($settingsnew['thumbheight']) : 300;
	}

	if(isset($settingsnew['watermarktext'])) {
		$settingsnew['watermarktext']['size'] = intval($settingsnew['watermarktext']['size']);
		$settingsnew['watermarktext']['angle'] = intval($settingsnew['watermarktext']['angle']);
		$settingsnew['watermarktext']['shadowx'] = intval($settingsnew['watermarktext']['shadowx']);
		$settingsnew['watermarktext']['shadowy'] = intval($settingsnew['watermarktext']['shadowy']);
		$settingsnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $settingsnew['watermarktext']['fontpath']);
		if($settingsnew['watermarktype'] == 2 && $settingsnew['watermarktext']['fontpath']) {
			$fontpath = $settingsnew['watermarktext']['fontpath'];
			$fontpathnew = 'ch/'.$fontpath;
			$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
			if(!$settingsnew['watermarktext']['fontpath']) {
				$fontpathnew = 'en/'.$fontpath;
				$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
			}
			if(!$settingsnew['watermarktext']['fontpath']) {
				cpmsg('watermarkpreview_fontpath_error', '', 'error');
			}
		}
		$settingsnew['watermarktext'] = addslashes(serialize($settingsnew['watermarktext']));
	}

	if(isset($settingsnew['msgforward'])) {
		if(!empty($settingsnew['msgforward']['messages'])) {
			$tempmsg = explode("\n", $settingsnew['msgforward']['messages']);
			$settingsnew['msgforward']['messages'] = array();
			foreach($tempmsg as $msg) {
				if($msg = strip_tags(trim($msg))) {
					$settingsnew['msgforward']['messages'][] = $msg;
				}
			}
		} else {
			$settingsnew['msgforward']['messages'] = array();
		}

		$tmparray = array(
			'refreshtime' => intval($settingsnew['msgforward']['refreshtime']),
			'quick' => $settingsnew['msgforward']['quick'] ? 1 : 0,
			'messages' => $settingsnew['msgforward']['messages']
		);
		$settingsnew['msgforward'] = addslashes(serialize($tmparray));
	}

	if(isset($settingsnew['onlinehold'])) {
		$settingsnew['onlinehold'] = intval($settingsnew['onlinehold']) > 0 ? intval($settingsnew['onlinehold']) : 15;
	}

	if(isset($settingsnew['postno'])) {
		$settingsnew['postno'] = trim($settingsnew['postno']);
	}
	if(isset($settingsnew['postnocustom'])) {
		$settingsnew['postnocustom'] = addslashes(serialize(explode("\n", $settingsnew['postnocustom'])));
	}

	if($operation == 'styles') {
		$jsmenumax = is_array($settingsnew['jsmenustatus']) ? max(array_keys($settingsnew['jsmenustatus'])) : 0;
		$jsmenustatus = '';
		for($i = $jsmenumax; $i > 0; $i --) {
			$jsmenustatus .= intval($settingsnew['jsmenustatus'][$i]);
		}
		$settingsnew['jsmenustatus'] = bindec($jsmenustatus);
		$settingsnew['customauthorinfo'] = addslashes(serialize(array($settingsnew['customauthorinfo'])));
	}

	$updatecache = FALSE;
	foreach($settingsnew as $key => $val) {
		if(isset($settings[$key]) && $settings[$key] != $val) {
			$$key = $val;
			$updatecache = TRUE;
			if(in_array($key, array('newbiespan', 'topicperpage', 'postperpage', 'memberperpage', 'hottopic', 'starthreshold', 'delayviewcount', 'attachexpire',
				'visitedforums', 'maxsigrows', 'timeoffset', 'statscachelife', 'pvfrequence', 'oltimespan', 'seccodestatus',
				'maxprice', 'rssttl', 'rewritestatus', 'bdaystatus', 'maxonlines', 'loadctrl', 'floodctrl', 'regctrl', 'regfloodctrl',
				'searchctrl', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6',
				'extcredits7', 'extcredits8', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan',
				'maxspm', 'maxsearchresults', 'maxsmilies', 'threadmaxpages', 'membermaxpages', 'maxpostsize', 'minpostsize',
				'maxpolloptions', 'karmaratelimit', 'losslessdel', 'edittimelimit', 'smcols',
				'watermarktrans', 'watermarkquality', 'jscachelife', 'waptpp', 'wapppp', 'wapmps', 'maxmodworksmonths', 'frameon', 'maxonlinelist'))) {
				$val = (float)$val;
			}
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$key', '$val')");

		}
	}

	if($updatecache) {
		updatecache('settings');
		if(isset($settingsnew['forumlinkstatus']) && $settingsnew['forumlinkstatus'] != $settings['forumlinkstatus']) {
			updatecache('forumlinks');
		}
		if(isset($settingsnew['userstatusby']) && $settingsnew['userstatusby'] != $settings['userstatusby']) {
			updatecache('usergroups');
			updatecache('ranks');
		}
		if((isset($settingsnew['tagstatus']) && $settingsnew['tagstatus'] != $settings['tagstatus']) || (isset($settingsnew['hottags']) && $settingsnew['hottags'] != $settings['hottags']) || (isset($settingsnew['viewthreadtags']) && $settingsnew['viewthreadtags'] != $settings['viewthreadtags'])) {
			updatecache(array('tags_index', 'tags_viewthread'));
		}
		if((isset($settingsnew['smthumb']) && $settingsnew['smthumb'] != $settings['smthumb']) || (isset($settingsnew['smcols']) && $settingsnew['smcols'] != $settings['smcols']) || (isset($settingsnew['smrows']) && $settingsnew['smrows'] != $settings['smrows'])) {
			updatecache('smilies_display');
		}
		if(isset($settingsnew['customauthorinfo']) && $settingsnew['customauthorinfo'] != $settings['customauthorinfo']) {
			updatecache('custominfo');
		}
		if($operation == 'credits') {
			updatecache('custominfo');
		}
	}

	if($operation == 'credits' && $projectsave) {
		$projectid = intval($projectid);
		dheader("Location: {$boardurl}admincp.php?action=project&operation=add&type=extcredit&projectid=$projectid");
	}
	cpmsg('settings_update_succeed', 'admincp.php?action=settings&operation='.$operation.(!empty($anchor) ? '&anchor='.$anchor : '').(!empty($from) ? '&from='.$from : ''), 'succeed');
}

function creditsrow($policy) {
	global $settings;
	$policyarray = array(lang('settings_credits_policy_'.$policy));
	for($i = 1; $i <= 8; $i++) {
		$policyarray[] = "<input type=\"text\" class=\"txt\" size=\"2\" name=\"settingsnew[creditspolicy][$policy][$i]\" ".($settings['extcredits'][$i]['available'] ? '' : 'readonly')." value=\"".intval($settings['creditspolicy'][$policy][$i])."\">";
	}
	return $policyarray;
}

function dateformat($string, $operation = 'formalise') {
	$string = htmlspecialchars(trim($string));
	$replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
	return str_replace($replace[0], $replace[1], $string);
}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}

?>