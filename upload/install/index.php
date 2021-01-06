<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: index.php 13495 2008-04-18 10:03:58Z monkey $
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

@ob_start();

@set_time_limit(1000);
set_magic_quotes_runtime(0);

define('IN_DISCUZ', TRUE);
if(!defined('UC_API')) {
	define('UC_API', '');
}
define('DISCUZ_ROOT', '../');

require DISCUZ_ROOT.'./discuz_version.php';
require DISCUZ_ROOT.'./include/db_mysql.class.php';
require DISCUZ_ROOT.'./install/install.lang.php';
require DISCUZ_ROOT.'./install/install.func.php';

require DISCUZ_ROOT.'./install/install.config.php';

$self = basename(__FILE__);
$attachdir = './attachments';
$attachurl = 'attachments';

$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;

$sqlfile = DISCUZ_ROOT.'./install/discuz.sql';
$lockfile = DISCUZ_ROOT.'./forumdata/install.lock';

@include DISCUZ_ROOT.'./config.inc.php';

show_header();

foreach (array('dbhost', 'dbuser', 'dbpw', 'dbname', 'tablepre', 'dbcharset', 'charset') as $key) {
	if(!isset($$key)) {
		show_error('error_config_vars', array(), true);
	}
}

if(!ini_get('short_open_tag')) {
	show_error('short_open_tag_invalid', '', true);
} elseif(file_exists($lockfile)) {
	show_error('install_locked', '', true);
} elseif(!class_exists('dbstuff')) {
	show_error('database_nonexistence', '', true);
}

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

$uchidden = '';
if(getgpc('ucapi', 'p')) {
	$uchidden = var_to_hidden('ucapi', getgpc('ucapi', 'p'));
	$uchidden .= var_to_hidden('appurl', getgpc('appurl', 'p'));
	$uchidden .= var_to_hidden('ucfounderpw', getgpc('ucfounderpw', 'p'));
}

if($step == 0) {
	show_license();
} elseif($step == 1) {

	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$bbserver = 'http://'.$_SERVER['HTTP_HOST'].($_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '');
	$default_ucapi = $bbserver.'/ucenter';
	$default_appurl = $bbserver.substr($PHP_SELF, 0, strpos($PHP_SELF, 'install/') - 1);

	$envsubmit = getgpc('envsubmit', 'P');

	if(!$envsubmit) {
		$errors = check_env();
		$quit = $errors['quit'];
		unset($errors['quit']);
		if($errors) {
			show_error('error_env', $errors, $quit);
		}
	}

	$errors = array();
	$dns_error = false;

	$app_type = 'DISCUZ'; // Only For Discuz!
	$app_charset = $charset;
	$app_dbcharset = $dbcharset;

	$app_name = getgpc('app_name') ? getgpc('app_name') : 'Discuz! Board';
	$app_url = getgpc('app_url') ? getgpc('app_url') : $default_appurl;

	$ucapi = getgpc('ucapi', 'P') ? getgpc('ucapi', 'P') : (defined('UC_API') && UC_API ? UC_API : $default_ucapi);
	$ucip = getgpc('ucip', 'P');
	$ucfounderpw = getgpc('ucfounderpw', 'P');
	$app_tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
	'apptagtemplates[fields][subject]='.urlencode($lang['tagtemplates_subject']).'&'.
	'apptagtemplates[fields][uid]='.urlencode($lang['tagtemplates_uid']).'&'.
	'apptagtemplates[fields][username]='.urlencode($lang['tagtemplates_username']).'&'.
	'apptagtemplates[fields][dateline]='.urlencode($lang['tagtemplates_dateline']).'&'.
	'apptagtemplates[fields][url]='.urlencode($lang['tagtemplates_url']);

	if($envsubmit) {
		$ucapi = preg_replace("/\/$/", '', trim($ucapi));
		if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi)) {
			$errors['uc_url'] = 'uc_url_invalid';
		} else {
			if(!$ucip) {
				$temp = @parse_url($ucapi);
				$ucip = gethostbyname($temp['host']);
				if(ip2long($ucip) == -1 || ip2long($ucip) === FALSE) {
					$ucip = '';
					$dns_error = true;
				}
			}
		}

		if(!$errors && !$dns_error) {
			include_once DISCUZ_ROOT.'./uc_client/client.php';
			$ucinfo = dfopen($ucapi.'/index.php?m=app&a=ucinfo', 500, '', '', 1, $ucip);
			list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);
			$dbcharset = strtolower($dbcharset ? str_replace('-', '', $dbcharset) : $dbcharset);
			$ucdbcharset = strtolower($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset);
			if($status != 'UC_STATUS_OK') {
				show_error('uc_connent_invalid');
			} elseif(UC_VERSION > $ucversion) {
				show_error('uc_version_incorrect');
			} elseif($dbcharset && $ucdbcharset != $dbcharset) {
				show_error('uc_dbcharset_incorrect');
			}

			$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($ucfounderpw)."&apptype=".urlencode($app_type)."&appname=".urlencode($app_name)."&appurl=".urlencode($app_url)."&appip=&appcharset=".$app_charset.'&appdbcharset='.$app_dbcharset.'&'.$app_tagtemplates;
			$ucconfig = dfopen($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
			if(empty($ucconfig)) {
				$errors['uc_url'] = lang('');
			} elseif($ucconfig == '-1') {
				$errors['uc_adminpw'] = 'uc_admin_invalid';
			} else {
				list($appauthkey, $appid) = explode('|', $ucconfig);
				if(empty($appauthkey) || empty($appid)) {
					$errors['uc_url'] = 'uc_data_invalid';
				}elseif($succeed = save_uc_config($ucconfig."|$ucapi|$ucip", DISCUZ_ROOT.'./config.inc.php')) {
					redirect($self.'?step='.($step + 1));
				} else {
					$errors[] = 'config_unwriteable';
				}
			}
		}
	}

	show_tips('tips_uc_install');
	show_error('', $errors);

	show_setting('start');
	show_setting('uc_url', 'ucapi', $ucapi, 'text', $errors['uc_url']);
	$dns_error && show_setting('uc_ip', 'ucip', $ucip, 'text', true);
	show_setting('uc_adminpw', 'ucfounderpw', $ucfounderpw, 'password', $errors['uc_adminpw']);
	show_setting('uc_app_name', 'app_name', $app_name, 'text');
	show_setting('uc_app_url', 'app_url', $app_url, 'text');
	show_setting('', 'envsubmit', $lang['new_step'], 'submit');
	show_setting('hidden', 'step', $step);
	show_setting('end');

	show_footer();

} elseif($step == 2) {

	$error_config = $error_admin = $adminuser = array();
	$showforceinstall = false;

	if($_POST['boardsubmit']) {

		$dbhost = charcovert(getgpc('dbhost', 'p'));
		$dbuser = charcovert(getgpc('dbuser', 'p'));
		$dbpw = charcovert(getgpc('dbpw', 'p'));
		$dbname = charcovert(getgpc('dbname', 'p'));
		$adminemail = charcovert(getgpc('adminemail', 'p'));
		$tablepre = charcovert(getgpc('tablepre', 'p'));
		$forceinstall = getgpc('forceinstall', p);

		if(empty($dbname)) {
			$error_config['dbname'] = 'dbname_invalid';
		} else {
			if(!@mysql_connect($dbhost, $dbuser, $dbpw)) {
				$errno = mysql_errno();
				if($errno == 1045) {
					$error_config['dbuser'] = 1;
					$error_config['dbpw'] = 1;
				} elseif($errno == 2003) {
					$error_config['dbhost'] = 1;
				} else {
					$error_config[] = 'database_errno_'.$errno;
				}
			} else {
				if(mysql_get_server_info() > '4.1') {
					mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET $dbcharset");
				} else {
					mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`");
				}

				if(mysql_errno()) {
					$error_config[] = 'database_errno_'.mysql_errno();
				} else {
					$result = mysql_query("SELECT COUNT(*) FROM `$dbname`.{$tablepre}settings");
					if($result && !$forceinstall) {
						$error_config['forceinstall'] = true;
						$showforceinstall = true;
					}
				}
				mysql_close();
			}
		}

		if(strpos($tablepre, '.') !== false) {
			$error_config['tablepre'] = 'tablepre_invalid';
		}

		if(!is_writeable(DISCUZ_ROOT.'config.inc.php')) {
			$error_config[] = 'config_unwriteable';
		}

		$username = getgpc('username', 'p');
		$email = getgpc('email', 'p');
		$password1 = getgpc('password1', 'p');
		$password2 = getgpc('password2', 'p');

		if($username && $email && $password1 && $password2) {
			if($password1 != $password2) {
				$error_admin['password2'] = 'admin_password_invalid';
			} elseif(strlen($username) > 15 || preg_match("/^$|^c:\\con\\con$|¡¡|[,\"\s\t\<\>&]|^ÓÎ¿Í|^Guest/is", $username)) {
				$error_admin['username'] = 'admin_username_invalid';
			} elseif(!strstr($email, '@') || $email != stripslashes($email) || $email != htmlspecialchars($email)) {
				$error_admin['email'] = 'admin_email_invalid';
			} else {
				$adminuser = check_adminuser($username, $password1, $email);
				if($adminuser['uid'] < 1) {
					$error_admin[] = $adminuser['error'];
				}
			}
		} else {
			empty($username) && $error_admin['username'] = 1;
			empty($email) && $error_admin['email'] = 1;
			empty($password1) && $error_admin['password1'] = 1;
			$password1 != $password2 && $error_admin['password2'] = 1;
		}

		if(!$error_config){

			$configfile = @file_get_contents(DISCUZ_ROOT.'./config.inc.php');
			$configfile = trim($configfile);
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/[$]dbhost\s*\=\s*[\"'].*?[\"'];/is", "\$dbhost = '$dbhost';", $configfile);
			$configfile = preg_replace("/[$]dbuser\s*\=\s*[\"'].*?[\"'];/is", "\$dbuser = '$dbuser';", $configfile);
			$configfile = preg_replace("/[$]dbpw\s*\=\s*[\"'].*?[\"'];/is", "\$dbpw = '$dbpw';", $configfile);
			$configfile = preg_replace("/[$]dbname\s*\=\s*[\"'].*?[\"'];/is", "\$dbname = '$dbname';", $configfile);
			$configfile = preg_replace("/[$]adminemail\s*\=\s*[\"'].*?[\"'];/is", "\$adminemail = '$adminemail';", $configfile);
			$configfile = preg_replace("/[$]tablepre\s*\=\s*[\"'].*?[\"'];/is", "\$tablepre = '$tablepre';", $configfile);
			$configfile = preg_replace("/[$]cookiepre\s*\=\s*[\"'].*?[\"'];/is", "\$cookiepre = '".random(3)."_';", $configfile);
			if(!$error_admin) {
				$configfile = preg_replace("/[$]forumfounders\s*\=\s*[\"'].*?[\"'];/is", "\$forumfounders = '$adminuser[uid]';", $configfile);
			}
			@file_put_contents(DISCUZ_ROOT.'./config.inc.php', $configfile);
		}

		if(!$error_config && !$error_admin){
			$step ++;
			redirect("$self?step=$step&uid=$adminuser[uid]&username=".rawurlencode($username)."&email=".rawurlencode($email)."&password=".md5($password1));
		}

	} else {

		$email = 'admin@admin.com';
		$username = 'admin';
		$password = $password2 = '';
	}

	if(!$error_config) {
		show_tips('tips_db_config');
	} else {
		show_error('tips_db_config', $error_config);
	}

	show_setting('start');
	show_setting('dbhost', 'dbhost', $dbhost, 'text', $error_config['dbhost']);
	show_setting('dbuser', 'dbuser', $dbuser, 'text', $error_config['dbuser']);
	show_setting('dbpw', 'dbpw', $dbpw, 'password', $error_config['dbpw']);
	show_setting('dbname', 'dbname', stripslashes($dbname), 'text', $error_config['dbname']);
	show_setting('adminemail', 'adminemail', $adminemail, 'text');
	show_setting('tablepre', 'tablepre', $tablepre, 'text', $error_config['tablepre']);

	if(!empty($forceinstall)) {
		$showforceinstall = true;
		$showforceinstallcheck = 'checked';
	}

	if($showforceinstall) {
		show_setting('forceinstall', '', '<input type="checkbox" name="forceinstall" value="1" '.$showforceinstallcheck.'>&nbsp;'.lang('agree_forceinstall'), 'custum', true);
	}
	echo '</table>';

	if(!$error_admin) {
		show_tips('tips_admin_config');
	} else {
		show_error('tips_admin_config', $error_admin);
	}

	echo '<table class="tb2">';
	show_setting('username', 'username', $username, 'text', $error_admin['username']);
	show_setting('email', 'email', $email, 'text', $error_admin['email']);
	show_setting('password', 'password1', $password1, 'password', $error_admin['password1']);
	show_setting('repeat_password', 'password2', $password2, 'password', $error_admin['password2']);

	show_setting('hidden', 'step', $step);
	show_setting('', 'boardsubmit', $lang['new_step'], 'submit');
	show_setting('end');
	show_footer();

} elseif($step == 3) {


	$uid = getgpc('uid');
	$username = getgpc('username');
	$password = md5(getgpc('password'));
	$email = getgpc('email');
	if(empty($uid) || empty($username) || empty($password) || empty($email)) {
		$step --;
		redirect("$self?step=$step");
	}

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);

	show_tips('tips_install_process');
	echo '<div class="btnbox"><textarea name="notice" style="width: 80%;" readonly id="notice"></textarea></div>';
	echo '<div class="btnbox marginbot"><input type="button" name="submit" value="'.lang('install_in_processed').'" onclick="return false" id="laststep"></div>';
	show_footer(FALSE);
	@flush();

	$sql = file_get_contents($sqlfile);

	runquery($sql);
	runquery($extrasql);

	$timestamp = time();
	$backupdir = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].substr($timestamp, 0, 4)), 8, 6);
	@mkdir('forumdata/backup_'.$backupdir, 0777);

	$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 6)), 8, 6).random(10);

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$timestamp), 0, 4).random(6);

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('authkey', '$authkey')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('siteuniqueid', '$siteuniqueid')");

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('backupdir', '".$backupdir."')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('extcredits', '".addslashes(serialize($extcredits))."')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('attachdir', '$attachdir')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('attachurl', '$attachurl')");

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '".addslashes(serialize($videoinfo))."')");

	$db->query("DELETE FROM {$tablepre}members");
	$db->query("DELETE FROM {$tablepre}memberfields");

	$db->query("INSERT INTO {$tablepre}members (uid, username, password, secques, adminid, groupid, regip, regdate, lastvisit, lastpost, email, dateformat, timeformat, showemail, newsletter, timeoffset) VALUES ('$uid', '$username', '$password', '', '1', '1', 'hidden', '".time()."', '".time()."', '".time()."', '$email', '', '0', '1', '1', '9999');");
	$db->query("INSERT INTO {$tablepre}memberfields (uid) VALUES ('$uid')");
	$db->query("UPDATE {$tablepre}crons SET lastrun='0', nextrun='".($timestamp + 3600)."'");

	foreach($request_data as $k => $v) {
		$variable = $k;
		$type = $v['type'];
		$value = addslashes(serialize($v));
		$db->query("REPLACE INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}

	if(is_writeable(DISCUZ_ROOT.'./config.inc.php')) {
		$configfile = @file_get_contents(DISCUZ_ROOT.'./config.inc.php');
		$configfile = trim($configfile);
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		$configfile = preg_replace("/[$]forumfounders\s*\=\s*[\"'].*?[\"'];/is", "\$forumfounders = '$uid';", $configfile);
		@file_put_contents(DISCUZ_ROOT.'./config.inc.php', $configfile);
	}

	foreach($optionlist as $optionid => $option) {
		$db->query("INSERT INTO {$tablepre}typeoptions VALUES ('$optionid', '$option[classid]', '$option[displayorder]', '$option[title]', '', '$option[identifier]', '$option[type]', '".addslashes(serialize($option['rules']))."');");
	}

	$db->query("ALTER TABLE {$tablepre}typeoptions AUTO_INCREMENT=3001");

	$yearmonth = date('Ym_', time());

	loginit($yearmonth.'ratelog');
	loginit($yearmonth.'illegallog');
	loginit($yearmonth.'modslog');
	loginit($yearmonth.'cplog');
	loginit($yearmonth.'errorlog');
	loginit($yearmonth.'banlog');

	dir_clear(DISCUZ_ROOT.'./forumdata/templates');
	dir_clear(DISCUZ_ROOT.'./forumdata/cache');
	dir_clear(DISCUZ_ROOT.'./forumdata/threadcaches');
	dir_clear(DISCUZ_ROOT.'./uc_client/data');
	dir_clear(DISCUZ_ROOT.'./uc_client/data/cache');

	$step ++;
	echo '<iframe width="0" height="0" src="../index.php" style="display:none;"></iframe>';
	redirect("$self?step=$step");

} elseif($step == 4) {

	@touch($lockfile);

	show_tips('install_finished');
	echo '<div class="btnbox margintop marginbot"><input type="button" value="'.lang('install_succeed').'" onclick="window.location=\'../index.php\'; return false"></div>
		<iframe width="0" height="0" src="../index.php" style="display:none;"></iframe>
		';
	show_footer();

} else {
	redirect('index.php');
}

?>