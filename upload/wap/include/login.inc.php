<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: login.inc.php 12321 2008-01-28 01:42:57Z monkey $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./include/misc.func.php';

if(empty($logout)) {

	if(empty($username)) {

		echo "<p>$lang[login_username]:<input type=\"text\" name=\"username\" maxlength=\"15\" format=\"M*m\" /><br />\n".
			"$lang[password]: <input type=\"password\" name=\"password\" value=\"\" format=\"M*m\" /><br />\n".
			"<anchor title=\"$lang[submit]\">$lang[submit]".
			"<go method=\"post\" href=\"index.php?action=login&amp;sid=$sid\">\n".
			"<postfield name=\"username\" value=\"$(username)\" />\n".
			"<postfield name=\"password\" value=\"$(password)\" />\n".
			"</go></anchor></p>\n";

	} else {

		$loginperm = logincheck();

		if(!$loginperm) {
			wapmsg('login_strike');
		}

		$secques = quescrypt($questionid, $answer);

		if(isset($loginauth)) {
			list($username, $password) = daddslashes(explode("\t", authcode($loginauth, 'DECODE')), 1);
		}

		require_once DISCUZ_ROOT.'./uc_client/client.php';
		$ucresult = uc_user_login($username, $password, preg_match("/^\d+$/", $username));
		list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email']) = daddslashes($ucresult);
		$ucresult = $tmp;

		if($ucresult['uid'] > 0) {
			$member = $db->fetch_first("SELECT uid AS discuz_uid, username AS discuz_user, password AS discuz_pw, secques AS discuz_secques, groupid, invisible
				FROM {$tablepre}members WHERE uid='$ucresult[uid]'");

			if(!$member) {
				if(!$wapregister) {
					wapmsg('activation_disable');
				}
				$groupinfo = $db->fetch_first("SELECT groupid FROM {$tablepre}usergroups WHERE ".($regverify ? "groupid='8'" : "creditshigher<=".intval($initcredits)." AND ".intval($initcredits)."<creditslower LIMIT 1"));

				$idstring = random(6);
				$password = md5(random(10));
				$authstr = $regverify == 1 ? "$timestamp\t2\t$idstring" : '';

				$db->query("REPLACE INTO {$tablepre}members (uid, username, password, secques, gender, adminid, groupid, regip, regdate, lastvisit, lastactivity, posts, credits, extcredits1, extcredits2, extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8, email, bday, sigstatus, tpp, ppp)
					VALUES ('$ucresult[uid]', '$ucresult[username]', '$password', '', '', '0', '$groupinfo[groupid]', '$onlineip', '$timestamp', '$timestamp', '$timestamp', '0', $initcredits, '$ucresult[email]', '', '', '20', '20')");
				$uid = $db->insert_id();

				$db->query("REPLACE INTO {$tablepre}memberfields (uid, authstr) VALUES ('$ucresult[uid]', '$authstr')");

				if($regverify == 2) {
					$db->query("REPLACE INTO {$tablepre}validating (uid, submitdate, moddate, admin, submittimes, status, message, remark)
						VALUES ('$uid', '$timestamp', '0', '', '1', '0', '$regmessage', '')");
				}

				$member = $db->fetch_first("SELECT uid AS discuz_uid, username AS discuz_user, password AS discuz_pw, secques AS discuz_secques, groupid, invisible
					FROM {$tablepre}members WHERE uid='$ucresult[uid]'");
				@extract($member);
				dsetcookie('auth', authcode("$discuz_pw\t$discuz_secques\t$discuz_uid", 'ENCODE'), 2592000);
				wapmsg('login_succeed');
			}

			if($member['discuz_secques'] && $member['discuz_secques'] != $secques) {
				$loginauth = authcode($ucresult['username']."\t".$ucresult['password'], 'ENCODE');
				echo "<p>$lang[security_question]:
					<select name=\"questionid\">
					<option value=\"0\">$lang[security_question_0]</option>
					<option value=\"1\">$lang[security_question_1]</option>
					<option value=\"2\">$lang[security_question_2]</option>
					<option value=\"3\">$lang[security_question_3]</option>
					<option value=\"4\">$lang[security_question_4]</option>
					<option value=\"5\">$lang[security_question_5]</option>
					<option value=\"6\">$lang[security_question_6]</option>
					<option value=\"7\">$lang[security_question_7]</option>
					</select><br />\n".
					"$lang[security_answer]: <input type=\"answer\" name=\"answer\" value=\"\" format=\"M*m\" /><br />\n".
					"<anchor title=\"$lang[submit]\">$lang[submit]".
					"<go method=\"post\" href=\"index.php?action=login&amp;sid=$sid\">\n".
					"<postfield name=\"questionid\" value=\"$(questionid)\" />\n".
					"<postfield name=\"answer\" value=\"$(answer)\" />\n".
					"<postfield name=\"username\" value=\"$member[discuz_user]\" />\n".
					"<postfield name=\"loginauth\" value=\"$loginauth\" />\n".
					"</go></anchor></p>\n";
			} else {
				@extract($member);
				dsetcookie('auth', authcode("$discuz_pw\t$discuz_secques\t$discuz_uid", 'ENCODE'), 2592000);
				wapmsg('login_succeed');
			}

		} else {

			$errorlog = dhtmlspecialchars(
				$timestamp."\t".
				($member['discuz_user'] ? $member['discuz_user'] : stripslashes($username))."\t".
				$password."\t".
				($secques ? "Ques #".intval($questionid) : '')."\t".
				$onlineip);
			writelog('illegallog', $errorlog);
			//loginfailed($loginperm);
			wapmsg('login_invalid');

		}

	}

} elseif(!empty($formhash) && $formhash == FORMHASH) {

	$discuz_uid = 0;
	$discuz_user = '';
	$groupid = 7;

	wapmsg('logout_succeed');

}

?>