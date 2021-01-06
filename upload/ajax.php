<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: ajax.php 13491 2008-04-18 07:06:36Z monkey $
*/

define('CURSCRIPT', 'ajax');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';
if($action == 'updatesecqaa') {

	$message = '';
	if($secqaa) {
		require_once DISCUZ_ROOT.'./forumdata/cache/cache_secqaa.php';
		$secqaa = max(1, random(1, 1));
		$message = $_DCACHE['secqaa'][$secqaa]['question'];
		if($seclevel) {
			$seccode = $secqaa * 1000000 + substr($seccode, -6);
			updatesession();
		} else {
			dsetcookie('secq', authcode($secqaa."\t".$timestamp."\t".$discuz_uid, 'ENCODE'), 3600);
		}
	}
	showmessage($message);

} elseif($action == 'updateseccode') {

	$message = '';
	if($seccodestatus) {
		$secqaa = substr($seccode, 0, 1);
		$seccode = random(6, 1);
		$rand = random(5, 1);
		if($seclevel) {
			$seccode += $secqaa * 1000000;
			updatesession();
		} else {
			$key = $seccodedata['type'] != 3 ? '' : $_DCACHE['settings']['authkey'].date('Ymd');
			dsetcookie('secc', authcode($seccode."\t".$timestamp."\t".$discuz_uid, 'ENCODE', $key), 3600);
		}
		if($seccodedata['type'] == 2) {
			$message = extension_loaded('ming') ? '<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'.$seccodedata['width'].'" height="'.$seccodedata['height'].'" align="middle">'.
				'<param name="allowScriptAccess" value="sameDomain" /><param name="quality" value="high" /><param name="wmode" value="transparent" /><param name="bgcolor" value="#ffffff" /><param name="movie" value="seccode.php?update='.$rand.'" />'.
				'<embed src="seccode.php?update='.$rand.'" quality="high" wmode="transparent" bgcolor="#ffffff" width="'.$seccodedata['width'].'" height="'.$seccodedata['height'].'" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>' :
				'<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'.$seccodedata['width'].'" height="'.$seccodedata['height'].'" align="middle">'.
				'<param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="'.$boardurl.'images/seccode/flash/flash2.swf" />'.
				'<param name="FlashVars" value="sFile='.$boardurl.'seccode.php?update='.$rand.'" />'.
				'<embed src="'.$boardurl.'images/seccode/flash/flash2.swf" FlashVars="sFile='.$boardurl.'seccode.php?update='.$rand.'" quality="high" width="'.$seccodedata['width'].'" height="'.$seccodedata['height'].'" swLiveConnect="true" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
		} elseif($seccodedata['type'] == 3) {
			$flashcode = '<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" id="seccodeplayer" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="0" height="0" align="middle">'.
				'<param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="'.$boardurl.'images/seccode/flash/flash1.swf" />'.
				'<param name="FlashVars" value="sFile='.$boardurl.'seccode.php?update='.$rand.'" />'.
				'<embed src="'.$boardurl.'images/seccode/flash/flash1.swf" FlashVars="sFile='.$boardurl.'seccode.php?update='.$rand.'" quality="high" width="0" height="0" name="seccodeplayer" swLiveConnect="true" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
			$message = 'seccode_player';
		} else {
			$message = '<img id="seccode" onclick="updateseccode()" width="'.$seccodedata['width'].'" height="'.$seccodedata['height'].'" src="seccode.php?update='.$rand.'" class="absmiddle" alt="" />';
		}
	}
	showmessage($message);

} elseif($action == 'checkseccode') {

	if($seclevel) {
		$tmp = $seccode;
	} else {
		$key = $seccodedata['type'] != 3 ? '' : $_DCACHE['settings']['authkey'].date('Ymd');
		list($tmp, $expiration, $seccodeuid) = explode("\t", authcode($_DCOOKIE['secc'], 'DECODE', $key));
		if($seccodeuid != $discuz_uid || $timestamp - $expiration > 600) {
			showmessage('submit_seccode_invalid');
		}
	}
	seccodeconvert($tmp);
	strtoupper($seccodeverify) != $tmp && showmessage('submit_seccode_invalid');

} elseif($action == 'checksecanswer') {

	if($seclevel) {
		$tmp = $seccode;
	} else {
		list($tmp, $expiration, $seccodeuid) = explode("\t", authcode($_DCOOKIE['secq'], 'DECODE'));
		if($seccodeuid != $discuz_uid || $timestamp - $expiration > 600) {
			showmessage('submit_secqaa_invalid');
		}
	}

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_secqaa.php';
	!$headercharset && @dheader('Content-Type: text/html; charset='.$charset);

	if(md5($secanswer) != $_DCACHE['secqaa'][substr($tmp, 0, 1)]['answer']) {
		showmessage('submit_secqaa_invalid');
	}

} elseif($action == 'checkusername') {

	$username = trim($username);

	require_once DISCUZ_ROOT.'./uc_client/client.php';

	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('profile_username_illegal');
	} elseif($ucresult == -2) {
		showmessage('profile_username_protect');
	} elseif($ucresult == -3) {
		if($db->result_first("SELECT uid FROM {$tablepre}members WHERE username='$username'")) {
			showmessage('register_check_found');
		} else {
			showmessage('register_activation');
		}
	}

} elseif($action == 'checkemail') {

	$email = trim($email);

	require_once DISCUZ_ROOT.'./uc_client/client.php';

	$ucresult = uc_user_checkemail($email);
	if($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	}

} elseif($action == 'checkuserexists') {

	$check = $db->result_first("SELECT uid FROM {$tablepre}members WHERE username='".trim($username)."'");
	$check ? showmessage('<img src="'.IMGDIR.'/check_right.gif" width="13" height="13">')
		: showmessage('username_nonexistence');

} elseif($action == 'checkinvitecode') {

	$check = $db->result_first("SELECT invitecode FROM {$tablepre}invites WHERE invitecode='".trim($invitecode)."' AND status IN ('1', '3')");
	!$check && showmessage('invite_invalid');

}

showmessage('succeed');

?>