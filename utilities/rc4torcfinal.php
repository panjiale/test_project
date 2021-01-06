<?php

// Upgrade Discuz! Board from 4.0.0RC4 to 4.0.0RCFinal

@set_time_limit(1000);
define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("�����ϴ������°汾�ĳ����ļ��������б���������");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$step = $_GET['step'];
$start = $_GET['start'];

$upgrade1 = <<<EOT
ALTER TABLE cdb_onlinetime ADD lastupdate int(10) UNSIGNED NOT NULL;
ALTER TABLE cdb_usergroups ADD system char(8) NOT NULL default 'private' AFTER type;
UPDATE cdb_usergroups SET system='0\t0' WHERE public<>0;
ALTER TABLE cdb_usergroups DROP public;
ALTER TABLE cdb_memberfields ADD groupterms text NOT NULL AFTER ignorepm;
REPLACE INTO cdb_smilies (id, displayorder, type, code, url) VALUES ('25', '0', 'icon', '', 'icon7.gif');
UPDATE cdb_usergroups SET system='0\t0' WHERE system<>'private';
UPDATE cdb_forums SET threads=0, posts=0 WHERE type='group';

REPLACE INTO cdb_settings (variable, value)VALUES ('ec_securitycode', '');
REPLACE INTO cdb_settings (variable, value)VALUES ('ec_account', '');
REPLACE INTO cdb_settings (variable, value)VALUES ('ec_ratio', '0');

DROP TABLE IF EXISTS cdb_orders;
CREATE TABLE cdb_orders (
  orderid char(32) NOT NULL default '',
  `status` char(3) NOT NULL default '',
  buyer char(50) NOT NULL default '',
  admin char(15) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  amount smallint(6) unsigned NOT NULL default '0',
  price float(7,2) unsigned NOT NULL default '0.00',
  submitdate int(10) unsigned NOT NULL default '0',
  confirmdate int(10) unsigned NOT NULL default '0',
  UNIQUE KEY orderid (orderid),
  KEY submitdate (submitdate),
  KEY uid (uid,submitdate)
) TYPE=MyISAM;

REPLACE INTO cdb_settings (variable, value) VALUES ('ec_mincredits', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_maxcredits', '1000');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_maxcreditspermonth', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('losslessdel', '365');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarkstatus', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarktrans', '65');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxsmilies', '3');
ALTER TABLE cdb_usergroups CHANGE raterange raterange CHAR(150) NOT NULL;
ALTER TABLE cdb_forums ADD allowtrade TINYINT(1) NOT NULL AFTER allowblog;
UPDATE cdb_forums SET allowtrade='3' WHERE type IN ('forum', 'sub');
DELETE FROM cdb_settings WHERE variable IN ('ec_allowposttrade', 'ec_allowpaytoauthor');
ALTER TABLE cdb_memberfields ADD alipay VARCHAR(50) NOT NULL AFTER site, ADD taobao varchar(40) NOT NULL AFTER msn;
REPLACE INTO cdb_settings (variable, value) VALUES ('modratelimit', '0');
ALTER TABLE cdb_forums ADD disablewatermark TINYINT(1) NOT NULL AFTER jammer;

DROP TABLE IF EXISTS cdb_advertisements;
CREATE TABLE cdb_advertisements (
  advid mediumint(8) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  `type` varchar(50) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  targets varchar(255) NOT NULL default '',
  parameters text NOT NULL,
  code text NOT NULL,
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (advid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_plugins;
CREATE TABLE cdb_plugins (
  pluginid smallint(6) UNSIGNED NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  adminid tinyint(1) unsigned NOT NULL default '0',
  name varchar(40) NOT NULL default '',
  title varchar(40) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  datatables varchar(255) NOT NULL default '',
  directory varchar(100) NOT NULL default '',
  copyright varchar(100) NOT NULL default '',
  modules text NOT NULL,
  PRIMARY KEY  (pluginid),
  UNIQUE KEY  (title)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_pluginvars;
CREATE TABLE cdb_pluginvars (
  pluginvarid mediumint(8) unsigned NOT NULL auto_increment,
  pluginid smallint(6) UNSIGNED NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(100) NOT NULL default '',
  description varchar(100) NOT NULL default '',
  type varchar(20) NOT NULL default 'text',
  variable varchar(40) NOT NULL default '',
  value text NOT NULL,
  extra text NOT NULL,
  PRIMARY KEY  (pluginvarid),
  KEY (pluginid)
) TYPE=MyISAM;
EOT;

$upgrade3 = <<<EOT
ALTER TABLE cdb_memberfields DROP origgroup;
EOT;

if(!$action) {
	echo"�������������� Discuz! 4.0.0RC4 �� Discuz! 4.0.0RCFinal,��ȷ��֮ǰ�Ѿ�˳����װ Discuz! 4.0.0RC4<br><br><br>";
	echo"<b><font color=\"red\">����������ֻ�ܴ� 4.0.0RC4 ������ 4.0.0RCFinal,����֮ǰ,��ȷ���Ѿ��ϴ� 4.0.0RCFinal ��ȫ���ļ���Ŀ¼</font></b><br>";
	echo"<b><font color=\"red\">����ǰ�������� JavaScript ֧��,�����������Զ���ɵ�,�����˹�����͸�Ԥ.<br>����֮ǰ��ر������ݿ�����,������ܲ����޷��ָ��ĺ��!<br></font></b><br><br>";
	echo"��ȷ����������Ϊ:<br>1. �ر�ԭ����̳,�ϴ� Discuz! 4.0.0RCFinal ���ȫ���ļ���Ŀ¼,���Ƿ������ϵ� 4.0.0RC4<br>2. �ϴ������� Discuz! Ŀ¼��;<br>4. ���б�����,ֱ������������ɵ���ʾ;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">�������ȷ���������Ĳ���,�����������</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		runquery($upgrade1);

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		$query = $db->query("SELECT m.uid, m.groupid, m.groupexpiry, mf.origgroup FROM {$tablepre}members m, {$tablepre}memberfields mf WHERE mf.origgroup<>'' AND m.uid=mf.uid AND m.groupexpiry>0");
		while($member = $db->fetch_array($query)) {
			$member['origgroup'] = explode("\t", $member['origgroup']);
			if($member['origgroup'][0] || $member['origgroup'][1]) {
				$array = array('main' => array('time' => $member['groupexpiry'], 'groupid' => $member['origgroup'][1], 'adminid' => $member['origgroup'][0]), 'ext' => array($member['groupid'] => $member['groupexpiry']));
				$db->query("UPDATE {$tablepre}memberfields SET groupterms='".addslashes(serialize($array))."' WHERE uid='$member[uid]'");
			}
		}

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade3);

		echo "��ϲ�������ɹ�,�����ɾ��������.";

	}
}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	$directory->close();
}

function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function loginit($log) {
	global $lang;

	$fp = @fopen('./forumdata/'.$log.'.php');
	@fwrite($fp, "<?PHP exit(\"Access Denied\"); ?>\n");
	@fclose($fp);
}

function runquery($query) {
	global $db, $tablepre;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql != "" && $sql[0] != "#") {
			$db->query(str_replace("cdb_", $tablepre, $sql));
		}
	}
}

function redirect($url) {

	echo"<script>";
	echo"function redirect() {window.location.replace('$url');}\n";
	echo"setTimeout('redirect();', 500);\n";
	echo"</script>";
	echo"<br><br><a href=\"$url\">��������Զ���תҳ�棬�����˹���Ԥ��<br>���ǵ����������û���Զ���תʱ����������</a>";

}

?>