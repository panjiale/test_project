<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: logging.php 12677 2008-03-05 02:56:02Z monkey $
*/

define('NOROBOT', TRUE);
define('CURSCRIPT', 'logging');

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/misc.func.php';

if($action == 'logout' && !empty($formhash)) {

	if($_DCACHE['settings']['frameon'] && $_DCOOKIE['frameon'] == 'yes') {
		$extrahead .= '<script>if(top != self) {parent.leftmenu.location.reload();}</script>';
	}

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	$ucsynlogout = uc_user_synlogout();

	if($formhash != FORMHASH) {
		showmessage('logout_succeed', dreferer());
	}

	clearcookies();
	$groupid = 7;
	$discuz_uid = 0;
	$discuz_user = $discuz_pw = '';
	$styleid = $_DCACHE['settings']['styleid'];

	showmessage('logout_succeed', dreferer());

} elseif($action == 'rename') {

	$auth = explode("\t", authcode($auth, 'DECODE'));
	if(FORMHASH != $auth[1]) {
		dheader('location: logging.php?action=login');
	}
	$username = $auth[0];

	if(!submitcheck('loginsubmit')) {

		$auth = authcode("$auth[0]\t".FORMHASH, 'ENCODE');
		include template('login_rename');

	} else {

		$loginperm = logincheck();
		if(!$loginperm) {
			showmessage('login_strike');
		}

		require_once DISCUZ_ROOT.'./uc_client/client.php';

		$user = $db->fetch_first("SELECT uid, password, email, secques FROM {$tablepre}members WHERE username='".addslashes($username)."'");
		$secques = $user['secques'] ? quescrypt($questionid, $answer) : '';
		if($user['password'] != md5($password) || $secques && $user['secques'] != $secques) {

			$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
			$errorlog = dhtmlspecialchars(
				$timestamp."\t".
				($ucresult['username'] ? $ucresult['username'] : stripslashes($username))."\t".
				$password."\t".
				($secques ? "Ques #".intval($questionid) : '')."\t".
				$onlineip);
			writelog('illegallog', $errorlog);
			loginfailed($loginperm);
			showmessage('login_rename_invalid');

		}

		$uid = uc_user_merge($username, $usernamenew, $user['uid'], $password, $user['email']);
		if($uid <= 0) {
			if($uid == -1) {
				showmessage('profile_username_illegal');
			} elseif($uid == -2) {
				showmessage('profile_username_protect');
			} elseif($uid == -3) {
				showmessage('profile_username_duplicate');
			} else {
				showmessage('undefined_action', NULL, 'HALTED');
			}
		}

		$db->query("UPDATE {$tablepre}announcements SET author='$usernamenew' WHERE author='$username'");
		$db->query("UPDATE {$tablepre}banned SET admin='$usernamenew' WHERE admin='$username'");
		$db->query("UPDATE {$tablepre}forums SET lastpost=REPLACE(lastpost, '\t$username', '\t$usernamenew')");
		$db->query("UPDATE {$tablepre}members SET username='$usernamenew' WHERE uid='$uid'");
		$db->query("UPDATE {$tablepre}pms SET msgfrom='$usernamenew' WHERE msgfromid='$uid'");
		$db->query("UPDATE {$tablepre}posts SET author='$usernamenew' WHERE authorid='$uid'");
		$db->query("UPDATE {$tablepre}threads SET author='$usernamenew' WHERE authorid='$uid'");
		$db->query("UPDATE {$tablepre}threads SET lastposter='$usernamenew' WHERE lastposter='$username'");
		$db->query("UPDATE {$tablepre}threadsmod SET username='$usernamenew' WHERE uid='$uid'");

		showmessage('login_rename_succeed', 'logging.php?action=login');
	}

} elseif($action == 'login') {

	if($discuz_uid) {

		require_once DISCUZ_ROOT.'./uc_client/client.php';
		$ucsynlogin = uc_user_synlogin($discuz_uid);
		showmessage('login_succeed', $indexname);

	}
	$field = $loginfield == 'uid' ? 'uid' : 'username';

	$seccodecheck = $seccodestatus & 2;

	if($seccodecheck && $seccodedata['loginfailedcount']) {
		$seccodecheck = $db->result_first("SELECT count(*) FROM {$tablepre}failedlogins WHERE ip='$onlineip' AND count>='$seccodedata[loginfailedcount]' AND $timestamp-lastupdate<=900");
	}

	$seccodemiss = !empty($loginsubmit) && $seccodecheck && !$seccodeverify ? TRUE : FALSE;
	if(!submitcheck('loginsubmit', 1, $seccodemiss ? FALSE : $seccodecheck)) {

		if(!empty($_DCOOKIE['activationauth'])) {
			$activationuser = authcode($_DCOOKIE['activationauth'], 'DECODE');
			require_once DISCUZ_ROOT.'./uc_client/client.php';
			if($activation = daddslashes(uc_get_user($activationuser))) {
				list(, $username) = $activation;
				$auth = authcode("$username\t".FORMHASH, 'ENCODE');
				dheader('location: '.$regname.'?action=activation&auth='.rawurlencode($auth));
			}
		}
		$discuz_action = 6;

		$referer = dreferer();

		$thetimenow = '(GMT '.($timeoffset > 0 ? '+' : '').$timeoffset.') '.
			gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600).

		$styleselect = '';
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles WHERE available='1'");
		while($styleinfo = $db->fetch_array($query)) {
			$styleselect .= "<option value=\"$styleinfo[styleid]\">$styleinfo[name]</option>\n";
		}

		$_DCOOKIE['cookietime'] = isset($_DCOOKIE['cookietime']) ? $_DCOOKIE['cookietime'] : 2592000;
		$cookietimecheck = array((isset($_DCOOKIE['cookietime']) ? intval($_DCOOKIE['cookietime']) : 2592000) => 'checked="checked"');

		if($seccodecheck) {
			$seccode = random(6, 1) + $seccode{0} * 1000000;
		}

		include template('login');

	} else {

		$loginperm = logincheck();
		if(!$loginperm) {
			showmessage('login_strike');
		}

		$secques = quescrypt($questionid, $answer);

		if(isset($loginauth)) {
			list($username, $password) = daddslashes(explode("\t", authcode($loginauth, 'DECODE')), 1);
		}

		require_once DISCUZ_ROOT.'./uc_client/client.php';

		$ucresult = uc_user_login($username, $password, $loginfield == 'uid');
		list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email'], $rename) = daddslashes($ucresult);
		$ucresult = $tmp;

		if($rename) {
			$auth = authcode("$ucresult[username]\t".FORMHASH, 'ENCODE');
			$username = $ucresult['username'];
			showmessage('login_rename', 'logging.php?action=rename&auth='.rawurlencode($auth));
		}

		$discuz_uid = 0;
		$discuz_user = $discuz_pw = $discuz_secques;
		$member = array();

		if($ucresult['uid'] > 0) {

			$member = $db->fetch_first("SELECT m.uid AS discuz_uid, m.username AS discuz_user, m.password AS discuz_pw, m.secques AS discuz_secques,
					m.adminid, m.groupid, m.styleid AS styleidmem, m.lastvisit, m.lastpost, u.allowinvisible
					FROM {$tablepre}members m LEFT JOIN {$tablepre}usergroups u USING (groupid)
					WHERE m.uid='$ucresult[uid]'");

			if(!$member) {
				$ucresult['username'] = addslashes($ucresult['username']);
				$auth = authcode("$ucresult[username]\t".FORMHASH, 'ENCODE');
				showmessage('login_activation', $regname.'?action=activation&auth='.rawurlencode($auth));
			}
			if($member['discuz_secques'] == $secques && !$seccodemiss) {

				extract($member);

				$discuz_userss = $discuz_user;
				$discuz_user = addslashes($discuz_user);

				$db->query("UPDATE {$tablepre}members SET secques='$discuz_secques',email='$ucresult[email]' WHERE uid='$ucresult[uid]'");

				if(($allowinvisible && $loginmode == 'invisible') || $loginmode == 'normal') {
					$db->query("UPDATE {$tablepre}members SET invisible='".($loginmode == 'invisible' ? 1 : 0)."' WHERE uid='$member[discuz_uid]'", 'UNBUFFERED');
				}

				$styleid = intval(empty($_POST['styleid']) ? ($styleidmem ? $styleidmem :
						$_DCACHE['settings']['styleid']) : $_POST['styleid']);

				$cookietime = intval(isset($_POST['cookietime']) ? $_POST['cookietime'] :
						($_DCOOKIE['cookietime'] ? $_DCOOKIE['cookietime'] : 0));

				dsetcookie('cookietime', $cookietime, 31536000);
				dsetcookie('auth', authcode("$discuz_pw\t$discuz_secques\t$discuz_uid", 'ENCODE'), $cookietime);
				dsetcookie('loginuser', '', -86400 * 365);
				dsetcookie('activationauth', '', -86400 * 365);

				$sessionexists = 0;

				if($_DCACHE['settings']['frameon'] && $_DCOOKIE['frameon'] == 'yes') {
					$extrahead .= '<script>if(top != self) {parent.leftmenu.location.reload();}</script>';
				}

				$ucsynlogin = uc_user_synlogin($discuz_uid);

				if($groupid == 8) {
					showmessage('login_succeed_inactive_member', 'memcp.php');
				} else {
					showmessage('login_succeed', dreferer());
				}

			} else {

				$username = dhtmlspecialchars($ucresult['username']);
				$loginmode = dhtmlspecialchars($loginmode);
				$styleid = intval($styleid);
				$cookietime = intval($cookietime);
				$discuz_secques = $member['discuz_secques'] != $secques;
				$loginauth = authcode($ucresult['username']."\t".$ucresult['password'], 'ENCODE');

				include template('login_secques');
				dexit();

			}

		} else {

			$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
			$errorlog = dhtmlspecialchars(
				$timestamp."\t".
				($ucresult['username'] ? $ucresult['username'] : stripslashes($username))."\t".
				$password."\t".
				($secques ? "Ques #".intval($questionid) : '')."\t".
				$onlineip);
			writelog('illegallog', $errorlog);
			loginfailed($loginperm);
			showmessage('login_invalid', 'logging.php?action=login', 'HALTED');

		}

	}

} else {
	showmessage('undefined_action');
}

?>