<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: register.php 13280 2008-04-03 09:52:38Z cnteacher $
*/

define('CURSCRIPT', 'register');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_profilefields.php';

$discuz_action = 5;

if($discuz_uid) {
	showmessage('login_succeed', $indexname);
} elseif (!$regstatus) {
	showmessage('register_disable');
}

$inviteconfig = array();
$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable IN ('bbrules', 'bbrulestxt', 'welcomemsg', 'welcomemsgtitle', 'welcomemsgtxt', 'inviteconfig')");
while($setting = $db->fetch_array($query)) {
	$$setting['variable'] = $setting['value'];
}

$invitecode = $regstatus > 1 && $invitecode ? dhtmlspecialchars($invitecode) : '';
if($regstatus > 1) {
	$inviterewardcredit = $inviteaddcredit = $invitedaddcredit = '';
	@extract(unserialize($inviteconfig));
}


$groupinfo = $db->fetch_first("SELECT groupid, allownickname, allowcstatus, allowcusbbcode, allowsigbbcode, allowsigimgcode, maxsigsize FROM {$tablepre}usergroups WHERE ".($regverify ? "groupid='8'" : "creditshigher<=".intval($initcredits)." AND ".intval($initcredits)."<creditslower LIMIT 1"));

$seccodecheck = $seccodestatus & 1;
$secqaacheck = $secqaa['status'][1];

$fromuid = !empty($_DCOOKIE['promotion']) && $creditspolicy['promotion_register'] ? intval($_DCOOKIE['promotion']) : 0;

$action = isset($action) ? $action : '';
$username = isset($username) ? $username : '';

if(!submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)) {

	$referer = isset($referer) ? dhtmlspecialchars($referer) : dreferer();

	if($bbrules && !submitcheck('rulesubmit')) {

		$bbrulestxt = nl2br("\n$bbrulestxt\n\n");
		if($action == 'activation') {
			$auth = dhtmlspecialchars($auth);
		}

	} else {

		if($action == 'activation') {
			$auth = explode("\t", authcode($auth, 'DECODE'));
			if(FORMHASH != $auth[1]) {
				showmessage('register_activation_invalid', 'logging.php?action=login');
			}
			$username = $auth[0];
			$activationauth = authcode("$auth[0]\t".FORMHASH, 'ENCODE');
		}

		$accessexp = '/('.str_replace("\r\n", '|', preg_quote($accessemail, '/')).')$/i';
		$censorexp = '/('.str_replace("\r\n", '|', preg_quote($censoremail, '/')).')$/i';
		$accessemail = str_replace("\r\n", '/', $accessemail);
		$censoremail = str_replace("\r\n", '/', $censoremail);
		$advdisplay = $advcheck = '';
		if($regadvance || $action == 'activation' && $regstatus == 1 && empty($_DCACHE['fields_required'])) {
			$advcheck = 'checked="checked"';
		} else {
			$advdisplay = 'none';
		}
		$fromuser = !empty($fromuser) ? dhtmlspecialchars($fromuser) : '';

		$styleselect = $dayselect = '';
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles WHERE available='1'");
		while($styleinfo = $db->fetch_array($query)) {
			$styleselect .= '<option value="'.$styleinfo['styleid'].'">'.$styleinfo['name'].'</option>'."\n";
		}

		if($fromuid) {
			$query = $db->query("SELECT username FROM {$tablepre}members WHERE uid='$fromuid'");
			if($db->num_rows($query)) {
				$fromuser = dhtmlspecialchars($db->result($query, 0));
			} else {
				dsetcookie('promotion', '');
			}
		}

		for($num = 1; $num <= 31; $num++) {
			$dayselect .= '<option value="'.$num.'">'.$num.'</option>';
		}

		$dateformatlist = array();
		if(!empty($userdateformat) && ($count = count($userdateformat))) {
			for($num =1; $num <= $count; $num ++) {
				$dateformatlist[$num] = str_replace(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy'), $userdateformat[$num-1]);
			}
		}

	}

	if($seccodecheck) {
		$seccode = random(6, 1);
	}
	if($secqaa['status'][1]) {
		$seccode = random(1, 1) * 1000000 + substr($seccode, -6);
	}

	$username = dhtmlspecialchars($username);

	include template('register');

} else {

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

	$activation = array();
	if(isset($activationauth)) {
		$activationauth = explode("\t", authcode($activationauth, 'DECODE'));
		if($activationauth[1] == FORMHASH && !($activation = daddslashes(uc_get_user($activationauth[0])))) {
			showmessage('register_activation_invalid', 'logging.php?action=login');
		}
	}

	if(!$activation) {
		$username = addslashes(trim(stripslashes($username)));
		if(uc_get_user($username) && !$db->result_first("SELECT uid FROM {$tablepre}members WHERE username='$username'")) {
			showmessage('register_activation_message', 'logging.php?action=login');
		}

		if($password != $password2) {
			showmessage('profile_passwd_notmatch');
		}

		if(!$password || $password != addslashes($password)) {
			showmessage('profile_passwd_illegal');
		}

		$email = trim($email);

	}

	$alipay = trim($alipay);

	$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';

	$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censoruser = trim($censoruser)), '/')).')$/i';

	if($censoruser && (@preg_match($censorexp, $nickname) || @preg_match($censorexp, $cstatus))) {
		showmessage('profile_nickname_cstatus_illegal');
	}

	if($alipay && !isemail($alipay)) {
		showmessage('profile_alipay_illegal');
	}

	if($msn && !isemail($msn)) {
		showmessage('profile_alipay_msn');
	}

	$fieldadd1 = $fieldadd2 = '';
	foreach(array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']) as $field) {
		$field_key = 'field_'.$field['fieldid'];
		$field_val = ${'field_'.$field['fieldid'].'new'};
		if($field['required'] && trim($field_val) == '') {
			showmessage('profile_required_info_invalid');
		} elseif($field['selective'] && $field_val != '' && !isset($field['choices'][$field_val])) {
			showmessage('undefined_action', NULL, 'HALTED');
		} else {
			$fieldadd1 .= ", $field_key";
			$fieldadd2 .= ', \''.dhtmlspecialchars($field_val).'\'';
		}
	}

	if($regverify == 2 && !trim($regmessage)) {
		showmessage('profile_required_info_invalid');
	}

	if($groupinfo['maxsigsize']) {
		if(strlen($signature) > $groupinfo['maxsigsize']) {
			$maxsigsize = $groupinfo['maxsigsize'];
			showmessage('profile_sig_toolong');
		}
	} else {
		$signature = '';
	}

	if($ipregctrl) {
		foreach(explode("\n", $ipregctrl) as $ctrlip) {
			if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $onlineip)) {
				$ctrlip = $ctrlip.'%';
				$regctrl = 72;
				break;
			} else {
				$ctrlip = $onlineip;
			}
		}
	} else {
		$ctrlip = $onlineip;
	}

	if($regstatus > 1) {
		if($regstatus == 2 && !$invitecode) {
			showmessage('register_invite_notfound');
		} elseif($invitecode) {
			$groupinfo['groupid'] = $invitegroupid ? intval($invitegroupid) : $groupinfo['groupid'];
			$invite = $db->fetch_first("SELECT uid, invitecode, inviteip, expiration FROM {$tablepre}invites WHERE invitecode='$invitecode' AND status IN ('1', '3')");
			if(!$invite) {
				showmessage('register_invite_error');
			} else {
				if($invite['inviteip'] == $onlineip) {
					showmessage('register_invite_iperror');
				} elseif($invite['expiration'] < $timestamp) {
					showmessage('register_invite_expiration');
				}
			}
		}
	}

	if($regctrl) {
		$query = $db->query("SELECT ip FROM {$tablepre}regips WHERE ip LIKE '$ctrlip' AND count='-1' AND dateline>$timestamp-'$regctrl'*3600 LIMIT 1");
		if($db->num_rows($query)) {
			showmessage('register_ctrl', NULL, 'HALTED');
		}
	}

	if($regfloodctrl) {
		if($regattempts = $db->result_first("SELECT count FROM {$tablepre}regips WHERE ip='$onlineip' AND count>'0' AND dateline>'$timestamp'-86400")) {
			if($regattempts >= $regfloodctrl) {
				showmessage('register_flood_ctrl', NULL, 'HALTED');
			} else {
				$db->query("UPDATE {$tablepre}regips SET count=count+1 WHERE ip='$onlineip' AND count>'0'");
			}
		} else {
			$db->query("INSERT INTO {$tablepre}regips (ip, count, dateline)
				VALUES ('$onlineip', '1', '$timestamp')");
		}
	}

	$secques = quescrypt($questionid, $answer);

	if(!$activation) {
		$uid = uc_user_register($username, $password, $email);
		if($uid <= 0) {
			if($uid == -1) {
				showmessage('profile_username_illegal');
			} elseif($uid == -2) {
				showmessage('profile_username_protect');
			} elseif($uid == -3) {
				showmessage('profile_username_duplicate');
			} elseif($uid == -4) {
				showmessage('profile_email_illegal');
			} elseif($uid == -5) {
				showmessage('profile_email_domain_illegal');
			} elseif($uid == -6) {
				showmessage('profile_email_duplicate');
			} else {
				showmessage('undefined_action', NULL, 'HALTED');
			}
		}
	} else {
		list($uid, $username, $email) = $activation;
	}

	$tppnew = in_array($tppnew, array(10, 20, 30)) ? $tppnew : 0;
	$pppnew = in_array($pppnew, array(5, 10, 15)) ? $pppnew : 0;

	$dateformatnew = ($dateformatnew = intval($dateformatnew)) && !empty($userdateformat[$dateformatnew -1]) ? $dateformatnew : 0;

	$icq = preg_match("/^([0-9]+)$/", $icq) && strlen($icq) >= 5 && strlen($icq) <= 12 ? $icq : '';
	$qq = preg_match("/^([0-9]+)$/", $qq) && strlen($qq) >= 5 && strlen($qq) <= 12 ? $qq : '';
	$bday = datecheck($bday) ? $bday : '0000-00-00';

	//$avatar = dhtmlspecialchars($avatar);

	$yahoo = dhtmlspecialchars($yahoo);
	$taobao = dhtmlspecialchars($taobao);
	$email = dhtmlspecialchars($email);
	$msn = dhtmlspecialchars($msn);
	$alipay = dhtmlspecialchars($alipay);
	$bday = dhtmlspecialchars($bday);

	$signature = censor($signature);
	$sigstatus = $signature ? 1 : 0;
	$sightml = addslashes(discuzcode(stripslashes($signature), 1, 0, 0, 0, ($groupinfo['allowsigbbcode'] ? ($groupinfo['allowcusbbcode'] ? 2 : 1) : 0), $groupinfo['allowsigimgcode'], 0));

	$bio = censor(dhtmlspecialchars($bio));
	$site = dhtmlspecialchars(trim(preg_match("/^https?:\/\/.+/i", $site) ? $site : ($site ? 'http://'.$site : '')));

	$locationnew = cutstr(censor(dhtmlspecialchars($locationnew)), 30);
	$nickname = $groupinfo['allownickname'] ? cutstr(censor(dhtmlspecialchars($nickname)), 30) : '';
	$cstatus = $groupinfo['allowcstatus'] ? cutstr(censor(dhtmlspecialchars($cstatus)), 30) : '';

	$invisiblenew = $invisiblenew && $groupinfo['allowinvisible'] ? 1 : 0;

	$idstring = random(6);
	$authstr = $regverify == 1 ? "$timestamp\t2\t$idstring" : '';
	$password = md5(random(10));

	$db->query("INSERT INTO {$tablepre}members (uid, username, password, secques, gender, adminid, groupid, regip, regdate, lastvisit, lastactivity, posts, credits, extcredits1, extcredits2, extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8, email, bday, sigstatus, tpp, ppp, styleid, dateformat, timeformat, pmsound, showemail, newsletter, invisible, timeoffset)
		VALUES ('$uid', '$username', '$password', '$secques', '$gendernew', '0', '$groupinfo[groupid]', '$onlineip', '$timestamp', '$timestamp', '$timestamp', '0', $initcredits, '$email', '$bday', '$sigstatus', '$tppnew', '$pppnew', '$styleidnew', '$dateformatnew', '$timeformatnew', '$pmsoundnew', '$showemailnew', '$newsletter', '$invisiblenew', '$timeoffsetnew')");

	$db->query("INSERT INTO {$tablepre}memberfields (uid, nickname, site, icq, qq, yahoo, msn, taobao, alipay, location, bio, sightml, customstatus, authstr $fieldadd1)
		VALUES ('$uid', '$nickname', '$site', '$icq', '$qq', '$yahoo', '$msn', '$taobao', '$alipay', '$locationnew', '$bio', '$sightml', '$cstatus', '$authstr' $fieldadd2)");

	if($regctrl || $regfloodctrl) {
		$db->query("DELETE FROM {$tablepre}regips WHERE dateline<='$timestamp'-".($regctrl > 72 ? $regctrl : 72)."*3600", 'UNBUFFERED');
		if($regctrl) {
			$db->query("INSERT INTO {$tablepre}regips (ip, count, dateline)
				VALUES ('$onlineip', '-1', '$timestamp')");
		}
	}

	if($regverify == 2) {
		$db->query("REPLACE INTO {$tablepre}validating (uid, submitdate, moddate, admin, submittimes, status, message, remark)
			VALUES ('$uid', '$timestamp', '0', '', '1', '0', '$regmessage', '')");
	}

	if($invitecode && $regstatus > 1) {
		$db->query("UPDATE {$tablepre}invites SET reguid='$uid', regdateline='$timestamp', status='2' WHERE invitecode='$invitecode'");
		if($inviteaddbuddy) {
			include_once DISCUZ_ROOT.'./uc_client/client.php';
			uc_friend_add($invite['uid'], $uid, '');
		}

		if($inviterewardcredit) {
			if($inviteaddcredit) {
				$db->query("UPDATE {$tablepre}members SET extcredits$inviterewardcredit=extcredits$inviterewardcredit+'$inviteaddcredit' WHERE uid='$uid'");
			}
			if($invitedaddcredit) {
				$db->query("UPDATE {$tablepre}members SET extcredits$inviterewardcredit=extcredits$inviterewardcredit+'$invitedaddcredit' WHERE uid='$invite[uid]'");
			}
		}
	}

	$discuz_uid = $uid;
	$discuz_user = $username;
	$discuz_userss = stripslashes($discuz_user);
	$discuz_pw = $password;
	$discuz_secques = $secques;
	$groupid = $groupinfo['groupid'];
	$styleid = $styleid ? $styleid : $_DCACHE['settings']['styleid'];

	if($welcomemsg && !empty($welcomemsgtxt)) {
		$welcomtitle = !empty($welcomemsgtitle) ? $welcomemsgtitle : "Welcome to $bbname!";
		$welcomtitle = addslashes(replacesitevar($welcomtitle));
		$welcomemsgtxt = addslashes(replacesitevar($welcomemsgtxt));
		if($welcomemsg == 1) {
			sendpm($uid, $welcomtitle, $welcomemsgtxt, 0);
		} elseif($welcomemsg == 2) {
			sendmail("$username <$email>", $welcomtitle, $welcomemsgtxt);
		}
	}

	if($fromuid) {
		updatecredits($fromuid, $creditspolicy['promotion_register']);
		dsetcookie('promotion', '');
	}

	require_once DISCUZ_ROOT.'./include/cache.func.php';
	$_DCACHE['settings']['totalmembers']++;
	$_DCACHE['settings']['lastmember'] = $discuz_userss;
	updatesettings();

	dsetcookie('loginuser', '', -86400 * 365);
	dsetcookie('activationauth', '', -86400 * 365);

	switch($regverify) {
		case 1:
			sendmail("$username <$email>", 'email_verify_subject', 'email_verify_message');
			showmessage('profile_email_verify');
			break;
		case 2:
			showmessage('register_manual_verify', 'memcp.php');
			break;
		default:
			if($_DCACHE['settings']['frameon'] && $_DCOOKIE['frameon'] == 'yes') {
				$extrahead .= '<script>if(top != self) {parent.leftmenu.location.reload();}</script>';
			}
			showmessage('register_succeed', dreferer());
			break;
	}

}

function replacesitevar($string, $replaces = array()) {
	global $sitename, $bbname, $timestamp, $timeoffset, $adminemail, $adminemail, $discuz_user;
	$sitevars = array(
		'{sitename}' => $sitename,
		'{bbname}' => $bbname,
		'{time}' => gmdate('Y-n-j H:i', $timestamp + $timeoffset * 3600),
		'{adminemail}' => $adminemail,
		'{username}' => $discuz_user,
		'{myname}' => $discuz_user
	);
	$replaces = array_merge($sitevars, $replaces);
	return str_replace(array_keys($replaces), array_values($replaces), $string);
}

?>