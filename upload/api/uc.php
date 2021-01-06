<?php

define('UC_VERSION', '1.0.0');

define('API_DELETEUSER', 1);
define('API_RENAMEUSER', 1);
define('API_GETTAG', 1);
define('API_SYNLOGIN', 1);
define('API_SYNLOGOUT', 1);
define('API_UPDATEPW', 1);
define('API_UPDATEBADWORDS', 1);
define('API_UPDATEHOSTS', 1);
define('API_UPDATEAPPS', 1);
define('API_UPDATECLIENT', 1);
define('API_UPDATECREDIT', 1);
define('API_GETCREDITSETTINGS', 1);
define('API_UPDATECREDITSETTINGS', 1);

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

error_reporting(7);
set_magic_quotes_runtime(0);

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', substr(dirname(__FILE__), 0, -3));
define('UC_CLIENT_ROOT', DISCUZ_ROOT.'./uc_client/');

$_DCACHE = array();

require_once DISCUZ_ROOT.'./config.inc.php';

$code = $_GET['code'];
parse_str(authcode($code, 'DECODE', UC_KEY), $get);
if(MAGIC_QUOTES_GPC) {
	foreach($get as $key=>$val) {
		$get[$key] = stripslashes($val);
	}
}

if(time() - $get['time'] > 3600) {
	exit('Authracation has expiried');
}
if(empty($get)) {
	exit('Invalid Request');
}
$action = $get['action'];
$timestamp = time();

if($action == 'test') {

	exit(API_RETURN_SUCCEED);

} elseif($action == 'deleteuser') {

	!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$uids = $get['ids'];
	$threads = array();

	$query = $db->query("SELECT f.fid, t.tid FROM {$tablepre}threads t LEFT JOIN {$tablepre}forums f ON t.fid=f.fid WHERE t.authorid IN ($uids) ORDER BY f.fid");
	while($thread = $db->fetch_array($query)) {
		$threads[$thread['fid']] .= ($threads[$thread['fid']] ? ',' : '').$thread['tid'];
	}

	if($threads) {
		require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';
		foreach($threads as $fid => $tids) {
			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($tids)");
			while($attach = $db->fetch_array($query)) {
				@unlink($_DCACHE['settings']['attachdir'].'/'.$attach['attachment']);
				$attach['thumb'] && @unlink($_DCACHE['settings']['attachdir'].'/'.$attach['attachment'].'.thumb.jpg');
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
	}

	$query = $db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
	$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
	$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
	$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
	$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
	$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');

	$query = $db->query("SELECT uid, attachment, thumb, remote FROM {$tablepre}attachments WHERE uid IN ($uids)");
	while($attach = $db->fetch_array($query)) {
		@unlink($_DCACHE['settings']['attachdir'].'/'.$attach['attachment']);
		$attach['thumb'] && @unlink($_DCACHE['settings']['attachdir'].'/'.$attach['attachment'].'.thumb.jpg');
	}
	$db->query("DELETE FROM {$tablepre}attachments WHERE uid IN ($uids)");

	$db->query("DELETE FROM {$tablepre}posts WHERE authorid IN ($uids)");
	$db->query("DELETE FROM {$tablepre}trades WHERE sellerid IN ($uids)");

	exit(API_RETURN_SUCCEED);

} elseif($action == 'renameuser') {

	!API_RENAMEUSER && exit(API_RETURN_FORBIDDEN);

	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$uid = $get['uid'];
	$usernameold = $get['oldusername'];
	$usernamenew = $get['newusername'];

	$db->query("UPDATE {$tablepre}announcements SET author='$usernamenew' WHERE author='$usernameold'");
	$db->query("UPDATE {$tablepre}banned SET admin='$usernamenew' WHERE admin='$usernameold'");
	$db->query("UPDATE {$tablepre}forums SET lastpost=REPLACE(lastpost, '\t$usernameold', '\t$usernamenew')");
	$db->query("UPDATE {$tablepre}members SET username='$usernamenew' WHERE uid='$uid'");
	$db->query("UPDATE {$tablepre}pms SET msgfrom='$usernamenew' WHERE msgfromid='$uid'");
	$db->query("UPDATE {$tablepre}posts SET author='$usernamenew' WHERE authorid='$uid'");
	$db->query("UPDATE {$tablepre}threads SET author='$usernamenew' WHERE authorid='$uid'");
	$db->query("UPDATE {$tablepre}threads SET lastposter='$usernamenew' WHERE lastposter='$usernameold'");
	$db->query("UPDATE {$tablepre}threadsmod SET username='$usernamenew' WHERE uid='$uid'");
	error_log("$uid	$usernameold	$usernamenew", 3, 'd:\\rename.txt');
	exit(API_RETURN_SUCCEED);

} elseif($action == 'gettag') {

	!API_GETTAG && exit(API_RETURN_FORBIDDEN);

	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$name = trim($get['id']);
	if(empty($name) || !preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
		exit(API_RETURN_FAILED);
	}

	require_once DISCUZ_ROOT.'./include/misc.func.php';

	$tag = $db->fetch_first("SELECT * FROM {$tablepre}tags WHERE tagname='$name'");
	if($tag['closed']) {
		exit(API_RETURN_FAILED);
	}

	$tpp = 10;
	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$boardurl = 'http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';
	$query = $db->query("SELECT t.* FROM {$tablepre}threadtags tt LEFT JOIN {$tablepre}threads t ON t.tid=tt.tid AND t.displayorder>='0' WHERE tt.tagname='$name' ORDER BY tt.tid DESC LIMIT $tpp");
	$threadlist = array();
	while($tagthread = $db->fetch_array($query)) {
		if($tagthread['tid']) {
			$threadlist[] = array(
				'subject' => $tagthread['subject'],
				'uid' => $tagthread['authorid'],
				'username' => $tagthread['author'],
				'dateline' => $tagthread['dateline'],
				'url' => $boardurl.'viewthread.php?tid='.$tagthread['tid'],
			);
		}
	}

	$return = array($name, $threadlist);
	echo uc_serialize($return, 1);

} elseif($action == 'synlogin') {

	!API_SYNLOGIN && exit(API_RETURN_FORBIDDEN);

	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$cookietime = 2592000;
	$discuz_auth_key = md5($_DCACHE['settings']['authkey'].$_SERVER['HTTP_USER_AGENT']);
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	$uid = intval($get['uid']);
	$query = $db->query("SELECT username, uid, password, secques FROM {$tablepre}members WHERE uid='$uid'");
	if($member = $db->fetch_array($query)) {
		dsetcookie('sid', '', -86400 * 365);
		dsetcookie('cookietime', $cookietime, 31536000);
		dsetcookie('auth', authcode("$member[password]\t$member[secques]\t$member[uid]", 'ENCODE', $discuz_auth_key), $cookietime);
	} else {
		dsetcookie('cookietime', $cookietime, 31536000);
		dsetcookie('loginuser', $get['username'], $cookietime);
		dsetcookie('activationauth', authcode($get['username'], 'ENCODE', $discuz_auth_key), $cookietime);
	}

} elseif($action == 'synlogout') {

	!API_SYNLOGOUT && exit(API_RETURN_FORBIDDEN);

	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	dsetcookie('auth', '', -86400 * 365);
	dsetcookie('sid', '', -86400 * 365);
	dsetcookie('loginuser', '', -86400 * 365);
	dsetcookie('activationauth', '', -86400 * 365);

} elseif($action == 'updatepw') {

	!API_UPDATEPW && exit(API_RETURN_FORBIDDEN);

	$username = $get['username'];
	$password = $get['password'];
	$newpw = md5(time().rand(100000, 999999));
	$db->query("UPDATE {$tablepre}members SET password='$newpw' WHERE username='$username'");
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatebadwords') {

	!API_UPDATEBADWORDS && exit(API_RETURN_FORBIDDEN);

	$post = uc_unserialize(file_get_contents('php://input'));
	$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/badwords.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'badwords\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatehosts') {

	!API_UPDATEHOSTS && exit(API_RETURN_FORBIDDEN);

	$post = uc_unserialize(file_get_contents('php://input'));
	$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/hosts.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updateapps') {

	!API_UPDATEAPPS && exit(API_RETURN_FORBIDDEN);

	$post = uc_unserialize(file_get_contents('php://input'));
	$UC_API = $post['UC_API'];
	unset($post['UC_API']);

	$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/apps.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);

	if(is_writeable(DISCUZ_ROOT.'./config.inc.php')) {
		$configfile = trim(file_get_contents(DISCUZ_ROOT.'./config.inc.php'));
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
		if($fp = @fopen(DISCUZ_ROOT.'./config.inc.php', 'w')) {
			@fwrite($fp, trim($configfile));
			@fclose($fp);
		}
	}

	exit(API_RETURN_SUCCEED);

} elseif($action == 'updateclient') {

	!API_UPDATECLIENT && exit(API_RETURN_FORBIDDEN);

	$post = uc_unserialize(file_get_contents('php://input'));
	$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/settings.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatecredit') {

	!UPDATECREDIT && exit(API_RETURN_FORBIDDEN);

	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$credit = intval($get['credit']);
	$amount = intval($get['amount']);
	$uid = intval($get['uid']);

	$db->query("UPDATE {$tablepre}members SET extcredits$credit=extcredits$credit+'$amount' WHERE uid='$uid'");

	$discuz_user = $db->result_first("SELECT username FROM {$tablepre}members WHERE uid='$uid'");

	$db->query("INSERT INTO {$tablepre}creditslog (uid, fromto, sendcredits, receivecredits, send, receive, dateline, operation)
			VALUES ('$uid', '$discuz_user', '0', '$credit', '0', '$amount', '$timestamp', 'EXC')");
	exit(API_RETURN_SUCCEED);

} elseif($action == 'getcreditsettings') {

	!API_GETCREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';

	$credits = array();
	foreach($_DCACHE['settings']['extcredits'] as $id => $extcredits) {
		$credits[$id] = array($extcredits['title'], $extcredits['unit']);
	}
	echo uc_serialize($credits);

} elseif($action == 'updatecreditsettings') {

	!API_UPDATECREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

	$outextcredits = array();
	foreach($get['credit'] as $appid => $credititems) {
		if($appid == UC_APPID) {
			foreach($credititems as $value) {
				$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
					'creditsrc' => $value['creditsrc'],
					'title' => $value['title'],
					'unit' => $value['unit'],
					'ratio' => $value['ratio']
				);
			}
		}
	}

	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';
	require_once DISCUZ_ROOT.'./include/cache.func.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('outextcredits', '".addslashes(serialize($outextcredits))."');", 'UNBUFFERED');

	$_DCACHE['settings']['outextcredits'] = $outextcredits;
	updatesettings();

	exit(API_RETURN_SUCCEED);

} else {

	exit(API_RETURN_FAILED);

}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function dsetcookie($var, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,
		$life ? $timestamp + $life : 0, $cookiepath,
		$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function uc_serialize($arr, $htmlon = 0) {
	include_once UC_CLIENT_ROOT.'./lib/xml.class.php';
	return xml_serialize($arr, $htmlon);
}

function uc_unserialize($s) {
	include_once UC_CLIENT_ROOT.'./lib/xml.class.php';
	return xml_unserialize($s);
}