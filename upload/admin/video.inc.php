<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: video.inc.php 13435 2008-04-15 10:46:33Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$video = unserialize($db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='videoinfo'"));

if($operation == 'config') {

	if(empty($video['authkey'])) {
		cpmsg('insenz_invalidvideo', 'admincp.php?action=video&operation=bind');
	}

	$video['bbname'] = $video['bbname'] ? $video['bbname'] : $bbname;
	$video['url'] = $video['url'] ? $video['url'] : $boardurl;
	$videonew['sitetype'] = $video['sitetype'];

	$sitetypeselect = $br = '';
	if($sitetypearray = explode("\t", $video['sitetype'])) {
		foreach($sitetypearray as $key => $sitetype) {
			$br = ($key + 1) % 6 == 0 ? '<br />' : '';
			$selected = $video['type'] == $key + 1 ? 'checked' : '';
			$sitetypeselect .= '<input type="radio" class="radio" name="videonew[type]" value="'.($key + 1).'" '.$selected.'> '.$sitetype.'&nbsp;&nbsp;&nbsp;'.$br;
		}
	}

	if(!submitcheck('configsubmit')) {

		shownav('extended', 'nav_video');
		showsubmenu('nav_video', array(
			array('nav_video_bind', 'video&operation=bind', 0),
			array('nav_video_config', 'video&operation=config', 1),
			array('nav_video_class', 'video&operation=class', 0)
		));
		showtips('video_tips');
		showformheader('video&operation=config');
		showtableheader();
		showsetting('video_open', 'videonew[open]', $video['open'], 'radio');
		showsetting('video_site_name', 'videonew[bbname]', $video['bbname'], 'text');
		showsetting('video_site_url', 'videonew[url]', $video['url'], 'text');
		showsetting('video_site_email', 'videonew[email]', $video['email'], 'text');
		showsetting('video_site_logo', 'videonew[logo]', $video['logo'], 'text');
		showsetting('video_site_type', array('videonew[sitetype]', array(
			array(0, $lang['video_site_type_none']),
			array(1, $lang['video_site_type_1']),
			array(2, $lang['video_site_type_2']),
			array(3, $lang['video_site_type_3']),
			array(5, $lang['video_site_type_5']),
			array(6, $lang['video_site_type_6']),
			array(7, $lang['video_site_type_7']),
			array(8, $lang['video_site_type_8']),
			array(9, $lang['video_site_type_9']),
			array(10, $lang['video_site_type_10']),
			array(11, $lang['video_site_type_11']),
			array(12, $lang['video_site_type_12']),
			array(13, $lang['video_site_type_13']),
			array(14, $lang['video_site_type_14']),
			array(15, $lang['video_site_type_15']),
			array(16, $lang['video_site_type_16']),
			array(17, $lang['video_site_type_17']),
			array(18, $lang['video_site_type_18']),
			array(19, $lang['video_site_type_19']),
			array(20, $lang['video_site_type_20']),
			array(21, $lang['video_site_type_21']),
			array(22, $lang['video_site_type_22']),
			array(23, $lang['video_site_type_23']),
			array(24, $lang['video_site_type_24']),
			array(25, $lang['video_site_type_25']),
			array(26, $lang['video_site_type_26']),
			array(27, $lang['video_site_type_27']),
			array(28, $lang['video_site_type_28']),
			array(29, $lang['video_site_type_29']),
			array(30, $lang['video_site_type_30']),
			array(31, $lang['video_site_type_31']),
			array(32, $lang['video_site_type_32']),
			array(33, $lang['video_site_type_33']),
			array(34, $lang['video_site_type_34']),
			array(35, $lang['video_site_type_35']),
			array(36, $lang['video_site_type_36']),
			array(37, $lang['video_site_type_37']),
			array(38, $lang['video_site_type_38']),
			array(39, $lang['video_site_type_39']),
			array(40, $lang['video_site_type_40']),
			array(41, $lang['video_site_type_41'])
		)), $video['sitetype'], 'select');
		showsubmit('configsubmit');
		showtablefooter();
		showformfooter();

	} else {

		require_once DISCUZ_ROOT.'./include/insenz.func.php';
		require_once DISCUZ_ROOT.'./api/video.php';

		$videoAccount = new VideoClient_SiteService($appid, $video['siteid'], $video['authkey']);
		$result = $videoAccount->edit(insenz_convert($videonew['bbname']), $videonew['url'], $videonew['logo'], $videonew['sitetype']);

		if ($result->isError()) {

			cpmsg(insenz_convert($result->getMessage(), 0), '', 'error');

		} else {

			$video['open'] = intval($videonew['open']);
			$video['bbname'] = $videonew['bbname'];
			$video['url'] = $videonew['url'];
			$video['email'] = $videonew['email'];
			$video['logo'] = $videonew['logo'];
			$video['sitetype'] = $videonew['sitetype'];
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '".addslashes(serialize($video))."')");
			updatecache('settings');
			cpmsg('video_config_succeed', 'admincp.php?action=video&operation=config', 'succeed');

		}

	}

} elseif($operation == 'class') {

	$videodefault = array(
			1 => array('name' => $lang['video_video_type_1']),
			5 => array('name' => $lang['video_video_type_5']),
			7 => array('name' => $lang['video_video_type_7']),
			8 => array('name' => $lang['video_video_type_8']),
			11 => array('name' => $lang['video_video_type_11']),
			12 => array('name' => $lang['video_video_type_12']),
			14 => array('name' => $lang['video_video_type_14']),
			15 => array('name' => $lang['video_video_type_15']),
			16 => array('name' => $lang['video_video_type_16']),
			18 => array('name' => $lang['video_video_type_18']),
			19 => array('name' => $lang['video_video_type_19']),
			21 => array('name' => $lang['video_video_type_21']),
			22 => array('name' => $lang['video_video_type_22']),
			23 => array('name' => $lang['video_video_type_23']),
			24 => array('name' => $lang['video_video_type_24']),
			25 => array('name' => $lang['video_video_type_25']),
			26 => array('name' => $lang['video_video_type_26']),
			27 => array('name' => $lang['video_video_type_27']),
			28 => array('name' => $lang['video_video_type_28']),
			29 => array('name' => $lang['video_video_type_29']),
			30 => array('name' => $lang['video_video_type_30']),
			31 => array('name' => $lang['video_video_type_31']),
			32 => array('name' => $lang['video_video_type_32'])
			);

	if(!submitcheck('classsubmit')) {
		$videotype = !$video['videotype'] ? $videodefault : $video['videotype'];
		$videotypelist = '<ul class="dblist" onmouseover="altStyle(this);">';
		foreach($videotype as $id => $type) {
			$checked = $type['able'] ? ' checked="checked"' : '';
			$videotypelist .= '<li><input type="checkbox" name="videotypenew['.$id.'][able]" class="radio"'.$checked.' value="1"> <input type="text" class="txt" name="videotypenew['.$id.'][name]" value="'.$type['name'].'" size="8"></li>';
		}
		$videotypelist .= '</ul>';
		shownav('extended', 'nav_video');
		showsubmenu('nav_video', array(
			array('nav_video_bind', 'video&operation=bind', 0),
			array('nav_video_config', 'video&operation=config', 0),
			array('nav_video_class', 'video&operation=class', 1)
		));
		showformheader('video&operation=class');
		showtableheader();
		showtablerow('class="nobg"', 'class="td27"', lang('video_class').'('.lang('video_class_comment').')');
		showtablerow('', '', $videotypelist);
		showsubmit('classsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$video['videotype'] = $videotypenew;
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '".addslashes(serialize($video))."')");
		updatecache('settings');
		cpmsg('video_class_update_succeed', 'admincp.php?action=video&operation=class', 'succeed');
	}

} elseif($operation == 'bind') {

	if(!empty($video['authkey'])) {
		cpmsg('insenz_video_bind_dumplicate', '', 'error');
	}

	require_once DISCUZ_ROOT.'./include/insenz.func.php';
	require_once DISCUZ_ROOT.'./admin/insenz.func.php';
	require_once DISCUZ_ROOT.'./api/video.php';

	$do = in_array($do, array('register', 'binding')) ? $do : '';

	if(!$do) {

		shownav('extended', 'nav_video');
		showsubmenu('nav_video', array(
			array('nav_video_bind', 'video&operation=bind', 1),
			array('nav_video_config', 'video&operation=config', 0),
			array('nav_video_class', 'video&operation=class', 0)
		));
		showformheader('video&operation=bind&do=register');
		showtableheader('insenz_nav_regorbind');
		showtablerow('', '', lang('insenz_video_regorbind'));
		showsubmit('submit', 'insenz_register', '', '<input type="button" class="btn" value="'.$lang['insenz_binding'].'" onclick="window.location=\'admincp.php?action=video&operation=bind&do=binding\'" />');
		showtablefooter();
		showformfooter();

	} elseif($do == 'register') {

		$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
		if(!empty($insenz['authkey'])) {
			cpmsg('insenz_forcebinding', 'admincp.php?action=video&operation=bind&do=binding');
		}

		if(!submitcheck('registersubmit')) {

			shownav('extended', 'nav_video');
			showsubmenu('nav_video', array(
				array('nav_video_bind', 'video&operation=bind', 1),
				array('nav_video_config', 'video&operation=config', 0),
				array('nav_video_class', 'video&operation=class', 0)
			));
			showformheader('video&operation=bind&do=register&frame=no', 'target="register"', 'form1');
			showtableheader();
			showtitle('insenz_video_register');
			showsetting('insenz_register_username', 'handle', '', 'text');
			showsetting('insenz_register_password', 'password', '', 'password');
			showsetting('insenz_register_password2', 'password2', '', 'password');
			showsetting('insenz_register_name', 'name', '', 'text');
			showsetting('insenz_register_email1', 'email1', '', 'text');
			showsetting('insenz_register_mobile', 'mobile', '', 'text');
			showsetting('video_site_logo', 'logo', '', 'text');
			showsetting('video_site_type', array('cateid', array(
				array(0, $lang['video_site_type_none']),
				array(1, $lang['video_site_type_1']),
				array(2, $lang['video_site_type_2']),
				array(3, $lang['video_site_type_3']),
				array(5, $lang['video_site_type_5']),
				array(6, $lang['video_site_type_6']),
				array(7, $lang['video_site_type_7']),
				array(8, $lang['video_site_type_8']),
				array(9, $lang['video_site_type_9']),
				array(10, $lang['video_site_type_10']),
				array(11, $lang['video_site_type_11']),
				array(12, $lang['video_site_type_12']),
				array(13, $lang['video_site_type_13']),
				array(14, $lang['video_site_type_14']),
				array(15, $lang['video_site_type_15']),
				array(16, $lang['video_site_type_16']),
				array(17, $lang['video_site_type_17']),
				array(18, $lang['video_site_type_18']),
				array(19, $lang['video_site_type_19']),
				array(20, $lang['video_site_type_20']),
				array(21, $lang['video_site_type_21']),
				array(22, $lang['video_site_type_22']),
				array(23, $lang['video_site_type_23']),
				array(24, $lang['video_site_type_24']),
				array(25, $lang['video_site_type_25']),
				array(26, $lang['video_site_type_26']),
				array(27, $lang['video_site_type_27']),
				array(28, $lang['video_site_type_28']),
				array(29, $lang['video_site_type_29']),
				array(30, $lang['video_site_type_30']),
				array(31, $lang['video_site_type_31']),
				array(32, $lang['video_site_type_32']),
				array(33, $lang['video_site_type_33']),
				array(34, $lang['video_site_type_34']),
				array(35, $lang['video_site_type_35']),
				array(36, $lang['video_site_type_36']),
				array(37, $lang['video_site_type_37']),
				array(38, $lang['video_site_type_38']),
				array(39, $lang['video_site_type_39']),
				array(40, $lang['video_site_type_40']),
				array(41, $lang['video_site_type_41']))), '', 'select');
			showsetting('<b>'.$lang['insenz_video_secode'].':</b><br /><span class="smalltxt"><img src="./api/video.php?action=createcode"></span>', 'code', '', 'text');
			showsubmit('registersubmit');
			showtablefooter();
			showformfooter();
			echo '<iframe name="register" style="display: none"></iframe>';

		} else {

			$handle = trim($handle);
			if(strlen($handle) < 4 || strlen($handle) > 20) {
				insenz_alert('insenz_username_length_outof_range', 'handle');
			} elseif(!preg_match("/^\w+$/i", $handle)) {
				insenz_alert('insenz_username_length_out_of_ranger', 'handle');
			}

			if($password != $password2) {
				insenz_alert('insenz_password_twice_diffenrent', 'password2');
			} elseif(strlen($password) < 6 || strlen($password) > 20) {
				insenz_alert('insenz_password_length_outof_range', 'password');
			} elseif(!preg_match("/^[0-9a-z!#$%&()+\\-.\\[\\]\\/\\\\@?{}|:;]+$/i", $password)) {
				insenz_alert('insenz_password_include_special_character', 'password');
			}

			$name = trim($name);
			if(strlen($name) < 4 || strlen($name) > 30) {
				insenz_alert('insenz_name_length_outof_range', 'name');
			} elseif(htmlspecialchars($name) != $name) {
				insenz_alert('insenz_name_illegal', 'name');
			}

			$email1 = trim($email1);
			if(strlen($email1) < 7 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email1)) {
				insenz_alert('insenz_email_illegal', 'email1');
			}

			if(!preg_match("/^1(3|5)\d{9}$/", $mobile)) {
				insenz_alert('insenz_mobile_illegal', 'mobile');
			}

			if(!empty($logo) && (strlen($logo) > 255 || !preg_match("/(http:\/\/)?[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $logo))) {
				insenz_alert('insenz_video_logo_invalid', 'logo');
			}

			$videoAccount = new VideoClient_AccountService($appid);
			$result = $videoAccount->register(insenz_convert($handle), $password, $code, $email1, insenz_convert($name), '', '', '', $mobile, '', '');

			if ($result->isError()) {

				insenz_alert($msglang['insenz_video_register_invalid'].insenz_convert($result->getMessage(), 0));

			} else {

				$videoAccount = new VideoClient_AccountService($appid, insenz_convert($handle), $password);
				$result = $videoAccount->bind(md5($authkey.'Discuz!INSENZ'), insenz_convert($_DCACHE['settings']['bbname']), $boardurl, $logo, intval($cateid));

				if ($result->isError()) {

					insenz_alert($msglang['insenz_video_register_invalid'].insenz_convert($result->getMessage(), 0));

				} else {

					$video['authkey'] = $result->get('siteKey');
					$video['siteid'] = $result->get('siteId');
					$video['bbname'] = $_DCACHE['settings']['bbname'];
					$video['url'] = $boardurl;
					$video['logo'] = $logo;
					$video['email'] = $email1;
					$video['sitetype'] = $cateid;
					$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '".addslashes(serialize($video))."')");
					updatecache('settings');
					insenz_cpmsg('insenz_video_register_succeed', 'admincp.php?action=video&operation=config', 'succeed');

				}

			}

		}

	} else {

		if(!submitcheck('bindsubmit')) {

			$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
			$insenz['host'] = empty($insenz['host']) ? 'api.insenz.com' : $insenz['host'];

			if(empty($insenz['handle']) && !empty($insenz['siteid'])) {

				$response = insenz_request('<cmd id="queryhandle"></cmd>');
				if($response['status']) {
					$handle = '';
				} else {
					$handle = $response['data']['response'][0]['handle'][0]['VALUE'];
				}

			} else {

				$handle = $insenz['handle'];

			}

			shownav('extended', 'nav_video');
			showsubmenu('nav_video', array(
				array('nav_video_bind', 'video&operation=bind', 1),
				array('nav_video_config', 'video&operation=config', 0),
				array('nav_video_class', 'video&operation=class', 0)
			));
			showformheader('video&operation=bind&do=binding&frame=no', 'target="binding"', 'form1');
			showtableheader();
			showtitle('insenz_video_bind');
			showsetting('insenz_video_username', 'handle', $handle, 'text');
			showsetting('insenz_video_password', 'password', '', 'password');
			showsetting('video_site_logo', 'logo', '', 'text');
			showsetting('video_site_type', array('cateid', array(
				array(0, $lang['video_site_type_none']),
				array(1, $lang['video_site_type_1']),
				array(2, $lang['video_site_type_2']),
				array(3, $lang['video_site_type_3']),
				array(5, $lang['video_site_type_5']),
				array(6, $lang['video_site_type_6']),
				array(7, $lang['video_site_type_7']),
				array(8, $lang['video_site_type_8']),
				array(9, $lang['video_site_type_9']),
				array(10, $lang['video_site_type_10']),
				array(11, $lang['video_site_type_11']),
				array(12, $lang['video_site_type_12']),
				array(13, $lang['video_site_type_13']),
				array(14, $lang['video_site_type_14']),
				array(15, $lang['video_site_type_15']),
				array(16, $lang['video_site_type_16']),
				array(17, $lang['video_site_type_17']),
				array(18, $lang['video_site_type_18']),
				array(19, $lang['video_site_type_19']),
				array(20, $lang['video_site_type_20']),
				array(21, $lang['video_site_type_21']),
				array(22, $lang['video_site_type_22']),
				array(23, $lang['video_site_type_23']),
				array(24, $lang['video_site_type_24']),
				array(25, $lang['video_site_type_25']),
				array(26, $lang['video_site_type_26']),
				array(27, $lang['video_site_type_27']),
				array(28, $lang['video_site_type_28']),
				array(29, $lang['video_site_type_29']),
				array(30, $lang['video_site_type_30']),
				array(31, $lang['video_site_type_31']),
				array(32, $lang['video_site_type_32']),
				array(33, $lang['video_site_type_33']),
				array(34, $lang['video_site_type_34']),
				array(35, $lang['video_site_type_35']),
				array(36, $lang['video_site_type_36']),
				array(37, $lang['video_site_type_37']),
				array(38, $lang['video_site_type_38']),
				array(39, $lang['video_site_type_39']),
				array(40, $lang['video_site_type_40']),
				array(41, $lang['video_site_type_41'])
			)), '', 'select');
			showsubmit('bindsubmit');
			showtablefooter();
			showformfooter();
			echo '<iframe name="binding" style="display: none"></iframe></center></form>';

		} else {

			$videoAccount = new VideoClient_AccountService($appid, insenz_convert($handle), $password);
			$result = $videoAccount->bind(md5($authkey.'Discuz!INSENZ'), insenz_convert($_DCACHE['settings']['bbname']), $boardurl, $logo, $cateid);

			if ($result->isError()) {

				insenz_alert($msglang['insenz_video_register_invalid'].insenz_convert($result->getMessage(), 0));

			} else {

				$video['authkey'] = $result->get('siteKey');
				$video['siteid'] = $result->get('siteId');
				$video['sitetype'] = $cateid;
				$video['logo'] = $logo;
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '".addslashes(serialize($video))."')");
				updatecache('settings');
				insenz_cpmsg('insenz_video_register_succeed', 'admincp.php?action=video&operation=config', 'succeed');

			}

		}
	}
	exit;
}

?>