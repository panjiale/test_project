<?php

// Upgrade Discuz! Board from 6.0.1 UC to 6.1.0 Final

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 6.0.1 UC';
$version_new = 'Discuz! 6.1.0 ��ʽ��';
$timestamp = time();

@include(DISCUZ_ROOT."./config.inc.php");
@include(DISCUZ_ROOT."./include/db_mysql.class.php");

header("Content-Type: text/html; charset=$charset");
showheader();

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

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
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

$upgrade1 = <<<EOT
DROP TABLE IF EXISTS cdb_reportlog;
CREATE TABLE cdb_reportlog (
  id int(10) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL,
  pid int(10) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL,
  username char(15) NOT NULL,
  status tinyint(1) unsigned NOT NULL default '1',
  type tinyint(1) NOT NULL,
  reason char(40) NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY pid (pid,uid),
  KEY dateline (fid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_warnings;
CREATE TABLE cdb_warnings (
  wid smallint(6) unsigned NOT NULL auto_increment,
  pid int(10) unsigned NOT NULL,
  operatorid mediumint(8) unsigned NOT NULL,
  operator char(15) NOT NULL,
  authorid mediumint(8) unsigned NOT NULL,
  author char(15) NOT NULL,
  dateline int(10) unsigned NOT NULL,
  reason char(40) NOT NULL,
  PRIMARY KEY  (wid),
  UNIQUE KEY pid (pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_medallog;
CREATE TABLE cdb_medallog (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  medalid smallint(6) unsigned NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY type (type),
  KEY status (status,expiration),
  KEY uid (uid,medalid,type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_admincustom;
CREATE TABLE cdb_admincustom (
  id smallint(6) unsigned NOT NULL auto_increment,
  title varchar(50) NOT NULL,
  url varchar(255) NOT NULL,
  sort tinyint(1) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL,
  clicks smallint(6) unsigned NOT NULL default '1',
  uid mediumint(8) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  KEY uid (uid),
  KEY displayorder (displayorder)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_virtualforums;
CREATE TABLE cdb_virtualforums (
  fid smallint(6) unsigned NOT NULL auto_increment,
  cid mediumint(8) unsigned NOT NULL,
  fup smallint(6) unsigned NOT NULL,
  `type` enum('group','forum') NOT NULL default 'forum',
  `name` varchar(255) NOT NULL,
  description text NOT NULL,
  logo varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  threads mediumint(8) unsigned NOT NULL DEFAULT '0',
  posts mediumint(8) unsigned NOT NULL DEFAULT '0',
  lastpost varchar(255) NOT NULL DEFAULT '',
  displayorder tinyint(3) NOT NULL,
  PRIMARY KEY  (fid),
  KEY forum (`status`,`type`,displayorder),
  KEY fup (fup)
) TYPE=MyISAM;
EOT;

$upgradetable = array(

	array('forums', 'ADD', 'allowtag', "TINYINT(1) NOT NULL DEFAULT '1'"),
	array('forums', 'ADD', 'modworks', "TINYINT(1) UNSIGNED NOT NULL"),

	array('medals', 'ADD', 'type', "TINYINT( 1 ) NOT NULL DEFAULT '0'"),
	array('medals', 'ADD', 'displayorder', "TINYINT( 3 ) NOT NULL DEFAULT '0'"),
	array('medals', 'INDEX', '', "ADD INDEX displayorder (displayorder)"),
	array('medals', 'ADD', 'description', "VARCHAR( 255 ) NOT NULL"),
	array('medals', 'ADD', 'expiration', "SMALLINT( 6 ) unsigned NOT NULL DEFAULT '0'"),
	array('medals', 'ADD', 'permission', "MEDIUMTEXT NOT NULL"),

	array('memberfields', 'CHANGE', 'medals', "medals TEXT"),

	array('usergroups', 'ADD', 'exempt', "TINYINT(1) unsigned NOT NULL"),

	array('members', 'ADD', 'customaddfeed', "TINYINT( 1 ) NOT NULL DEFAULT '0'"),

	array('campaigns', 'ADD', 'url', "CHAR(255) NOT NULL"),
	array('campaigns', 'ADD', 'autoupdate', "TINYINT(1) unsigned NOT NULL"),
	array('campaigns', 'ADD', 'lastupdated', "INT(10) unsigned NOT NULL"),

	array('access', 'ADD', 'adminuser', "MEDIUMINT(8) unsigned NOT NULL DEFAULT '0'"),
	array('access', 'ADD', 'dateline', "INT(10) unsigned NOT NULL DEFAULT '0'"),
	array('access', 'INDEX', '', "ADD INDEX listorder (fid,dateline)"),

);

$upgrade3 = <<<EOT

REPLACE INTO cdb_settings (variable, value) VALUES ('attachexpire', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seclevel', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('warninglimit', '3');
REPLACE INTO cdb_settings (variable, value) VALUES ('warningexpiration', '30');
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbquality', '100');
REPLACE INTO cdb_settings (variable, value) VALUES ('relatedtag', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('outextcredits', '');
DELETE FROM cdb_settings WHERE variable='allowcsscache';
DELETE FROM cdb_settings WHERE variable='seccodeanimator';
DELETE FROM cdb_settings WHERE variable='maxavatarsize';
DELETE FROM cdb_settings WHERE variable='maxavatarpixel';

INSERT INTO cdb_crons VALUES (NULL,'1','system','ÿ��ѫ�¸���','medals_daily.inc.php','0','1170600452','-1','-1','0','0');

UPDATE cdb_usergroups SET exempt=255 WHERE radminid = 1;
UPDATE cdb_usergroups SET exempt=255 WHERE radminid = 2;
UPDATE cdb_usergroups SET exempt=224 WHERE radminid = 3;


EOT;

$upgrademsg = array(

	1 => '��̳������ 1 ��: �������ݱ�<br /><br />',
	2 => '��̳������ 2 ��: ������̳���ݱ�ṹ<br /><br />',
	3 => '��̳������ 3 ��: ���²�������<br /><br />',
	10 => '��̳������ 4 ��: ����ȫ�����<br /><br />',
);

$errormsg = '';
if(!isset($dbhost)) {
	showerror("<span class=error>û���ҵ� config.inc.php �ļ�!</span><br />��ȷ�����Ѿ��ϴ������� $version_new �ļ�");
} elseif(!isset($cookiepre)) {
	showerror("<span class=error>config.inc.php �汾����!</span><br />���ϴ� $version_new �� config.inc.php�������������ݿ�����Ȼ�����½�������");
} elseif(!$dblink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
	showerror("<span class=error>config.inc.php ���ô���!</span><br />���޸� config.inc.php ���й������ݿ�����ã�Ȼ���ϴ�����̳Ŀ¼�����¿�ʼ����");
}

@mysql_close($dblink);
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$action) {

	if(!$tableinfo = loadtable('threads')) {
		showerror("<span class=error>�޷��ҵ� Discuz! ��̳���ݱ�!</span><br />���޸� config.inc.php ���й������ݿ�����ã�Ȼ���ϴ�����̳Ŀ¼�����¿�ʼ����");
	} elseif($db->version() > '4.1') {
		$old_dbcharset = substr($tableinfo['subject']['Collation'], 0, strpos($tableinfo['subject']['Collation'], '_'));
		if($old_dbcharset <> $dbcharset) {
			showerror("<span class=error>config.inc.php ���ݿ��ַ������ô���!</span><br />".
				"<li>ԭ�����ַ�������Ϊ��$old_dbcharset".
				"<li>��ǰʹ�õ��ַ���Ϊ��$dbcharset".
				"<li>���飺�޸� config.inc.php�� �����е� <b>\$dbcharset = ''</b> ���� <b>\$dbcharset = '$dbcharset'</b> �޸�Ϊ�� <b>\$dbcharset = '$old_dbcharset'</b>".
				"<li>�޸���Ϻ��ϴ� config.inc.php��Ȼ�����½�������"
			);
		}
	}

	echo <<< EOT
<span class="red">
����ǰ�������� JavaScript ֧��,�����������Զ���ɵ�,�����˹�����͸�Ԥ.<br />
����֮ǰ��ر������ݿ����ϣ���������ʧ���޷��ָ�<br /></span><br />
��ȷ����������Ϊ:
<ol>
	<li>�ر�ԭ����̳,�ϴ� $version_new ��ȫ���ļ���Ŀ¼, ���Ƿ������ϵ� $version_old
	<li>�ϴ�����������̳Ŀ¼�У�<b>�������ú� config.inc.php</b>
	<li>���б�����,ֱ������������ɵ���ʾ
	<li>�����;ʧ�ܣ���ʹ��Discuz!�����䣨./utilities/tools.php����������ݻָ����߻ָ�����, ȥ��������������б�����
</ol>
<a href="$PHP_SELF?action=upgrade&step=1"><font size="2" color="red"><b>&gt;&gt;&nbsp;�������ȷ���������Ĳ���,�����������</b></font></a>
<br /><br />
EOT;
	showfooter();

} else {

	$step = intval($step);
	echo '&gt;&gt;'.$upgrademsg[$step];
	flush();

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		runquery($upgrade1);

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

			echo "�������ݱ� [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:";
			$successed = upgradetable($upgradetable[$start]);

			if($successed === TRUE) {
				echo ' <font color=green>OK</font><br />';
			} elseif($successed === FALSE) {
				//echo ' <font color=red>ERROR</font><br />';
			} elseif($successed == 'TABLE NOT EXISTS') {
				showerror('<span class=red>���ݱ�����</span>�����޷���������ȷ��������̳�汾�Ƿ���ȷ!</font><br />');
			}
		}

		$start ++;
		if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {
			redirect("?action=upgrade&step=$step&start=$start");
		}

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade3);
		upg_adminactions();
		upg_insenz();
		upg_js();

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} else {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');
		$configfile = DISCUZ_ROOT.'./config.inc.php';

		$s = file_get_contents($configfile);
		$s = preg_replace("/define\('UC_DBTABLEPRE',\s*'(.*?)'\);/i", "define('UC_DBTABLEPRE', '`".UC_DBNAME."`.\\1');", $s);
		if($fp = @fopen($configfile, 'w')) {
			fwrite($fp, $s);
			fclose($fp);
		} else {
			echo 'config.inc.php �ļ��޷�д�룬���ֶ��������޸ģ�<br>'.
			'���ң�define(\'UC_DBTABLEPRE\', \''.UC_DBTABLEPRE.'\');<br />'.
			'�滻Ϊ��define(\'UC_DBTABLEPRE\', \'`'.UC_DBNAME.'`.'.UC_DBTABLEPRE.'\');<br /><br />';
		}

		echo '<br />��ϲ����̳���������ɹ���������������<ol><li><b>��ɾ��������</b>'.
		'<li>ʹ�ù���Ա��ݵ�¼��̳�������̨�����»���'.
		'<li>������̳ע�ᡢ��¼�������ȳ�����ԣ����������Ƿ�����'.
		'<li>�����ϣ������ <b>'.$version_new.'</b> �ṩ���¹��ܣ��㻹��Ҫ������̳�������á���Ŀ����Ա��ȵȽ�����������</ol><br />'.
		'<b>��л��ѡ�����ǵĲ�Ʒ��</b><a href="index.php" target="_blank">�����ڿ��Է�����̳���鿴�������</a><iframe width="0" height="0" src="index.php"></iframe>';
		showfooter();
	}
}

instfooter();

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	@touch($dir.'/index.htm');
	$directory->close();
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
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

function instfooter() {
	echo '</table></body></html>';
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function runquery($query) {
	global $db, $tablepre, $dbcharset;

	$query = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $query));
	$expquery = explode(";\n", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function loadtable($table, $force = 0) {
	global $db, $tablepre, $dbcharset;
	static $tables = array();

	if(!isset($tables[$table]) || $force) {
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM {$tablepre}$table", 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM {$tablepre}$table", 'SILENT');
		}
		while($field = @$db->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}

function upgradetable($updatesql) {
	global $db, $tablepre, $dbcharset;

	$successed = TRUE;

	if(is_array($updatesql) && !empty($updatesql[0])) {

		list($table, $action, $field, $sql) = $updatesql;

		if(empty($field) && !empty($sql)) {

			$query = "ALTER TABLE {$tablepre}{$table} ";
			if($action == 'INDEX') {
				$successed = $db->query("$query $sql", "SILENT");
			} elseif ($action == 'UPDATE') {
				$successed = $db->query("UPDATE {$tablepre}{$table} SET $sql", 'SILENT');
			}

		} elseif($tableinfo = loadtable($table)) {

			$fieldexist = isset($tableinfo[$field]) ? 1 : 0;

			$query = "ALTER TABLE {$tablepre}{$table} ";

			if($action == 'MODIFY') {

				$query .= $fieldexist ? "MODIFY $field $sql" : "ADD $field $sql";
				$successed = $db->query($query, 'SILENT');

			} elseif($action == 'CHANGE') {

				$field2 = trim(substr($sql, 0, strpos($sql, ' ')));
				$field2exist = isset($tableinfo[$field2]);

				if($fieldexist && ($field == $field2 || !$field2exist)) {
					$query .= "CHANGE $field $sql";
				} elseif($fieldexist && $field2exist) {
					$db->query("ALTER TABLE {$tablepre}{$table} DROP $field2", 'SILENT');
					$query .= "CHANGE $field $sql";
				} elseif(!$fieldexist && $fieldexist2) {
					$db->query("ALTER TABLE {$tablepre}{$table} DROP $field2", 'SILENT');
					$query .= "ADD $sql";
				} elseif(!$fieldexist && !$field2exist) {
					$query .= "ADD $sql";
				}
				$successed = $db->query($query);

			} elseif($action == 'ADD') {

				$query .= $fieldexist ? "CHANGE $field $field $sql" :  "ADD $field $sql";
				$successed = $db->query($query);

			} elseif($action == 'DROP') {
				if($fieldexist) {
					$successed = $db->query("$query DROP $field", "SILENT");
				}
				$successed = TRUE;
			}

		} else {

			$successed = 'TABLE NOT EXISTS';

		}
	}
	return $successed;
}

function showheader() {
	global $version_old, $version_new;

	print <<< EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Discuz! ��������( $version_old &gt;&gt; $version_new)</title>
<meta name="MSSmartTagsPreventParsing" content="TRUE">
<meta http-equiv="MSThemeCompatible" content="Yes">
<style>
a:visited	{color: #FF0000; text-decoration: none}
a:link		{color: #FF0000; text-decoration: none}
a:hover		{color: #FF0000; text-decoration: underline}
body,table,td	{color: #3a4273; font-family: Tahoma, verdana, arial; font-size: 12px; line-height: 20px; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
input		{color: #085878; font-family: Tahoma, verdana, arial; font-size: 12px; background-color: #3a4273; color: #ffffff; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
.install	{font-family: Arial, Verdana; font-size: 14px; font-weight: bold; color: #000000}
.header		{font: 12px Tahoma, Verdana; font-weight: bold; background-color: #3a4273 }
.header	td	{color: #ffffff}
.red		{color: red; font-weight: bold}
.bg1		{background-color: #e3e3ea}
.bg2		{background-color: #eeeef6}
</style>
</head>

<body bgcolor="#3A4273" text="#000000">
<table width="95%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
<tr>
<td>
<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="install" height="30" valign="bottom"><font color="#FF0000">&gt;&gt;</font>
Discuz! ��������( $version_old &gt;&gt; $version_new)</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr>
<td align="center">
<b>����������ֻ�ܴ� $version_old ������ $version_new ������֮ǰ����ȷ���Ѿ��ϴ������ļ������������ݱ���<br />
�����������κ���������ʼ���֧��վ�� <a href="http://www.discuz.net" target="_blank">http://www.discuz.net</a></b>
</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr><td>
EOT;
}

function showfooter() {
	echo <<< EOT
</td></tr></table></td></tr>
<tr><td height="100%">&nbsp;</td></tr>
</table>
</body>
</html>
EOT;
	exit();
}

function showerror($message, $break = 1) {
	echo '<br /><br />'.$message.'<br /><br />';
	if($break) showfooter();
}

function redirect($url) {

	$url = $url.(strstr($url, '&') ? '&' : '?').'t='.time();

	echo <<< EOT
<hr size=1>
<script language="JavaScript">
	function redirect() {
		window.location.replace('$url');
	}
	setTimeout('redirect();', 1000);
</script>
<br /><br />
&gt;&gt;<a href="$url">��������Զ���תҳ�棬�����˹���Ԥ�����ǵ������������ʱ��û���Զ���תʱ����������</a>
<br /><br />
EOT;
	showfooter();
}

function upg_adminactions() {
	global $db, $tablepre;

	$actionarray = array(
		'settings' => 'settings',
		'forumsedit' => 'forums',
		'forumdetail' => 'forums_edit',
		'moderators' => 'forums_moderators',
		'forumdelete' => 'forums_delete',
		'forumsmerge' => 'forums_merge',
		'forumcopy' => 'forums_copy',
		'threadtypes' => 'threadtypes',
		'members' => 'members',
		'forumadd' => 'members_add',
		'editgroups' => 'members_editgroups',
		'access' => 'members_access',
		'editcredits' => 'members_editcredits',
		'editmedals' => 'members_editmedals',
		'memberprofile' => 'members_profile',
		'profilefields' => 'members_profilefields',
		'ipban' => 'members_ipban',
		'membersmerge' => 'members_merge',
		'usergroups' => 'groups_user',
		'admingroups' => 'groups_admin',
		'ranks' => 'groups_ranks',
		'styles' => 'styles',
		'templates' => 'templates',
		'tpladd' => 'templates_add',
		'tpledit' => 'templates_edit',
		'modmembers' => 'moderate_members',
		'modthreads' => 'moderate_threads',
		'modreplies' => 'moderate_replies',
		'threads' => 'threads',
		'prune' => 'prune',
		'recyclebin' => 'recyclebin',
		'announcements' => 'announcements',
		'forumlinks' => 'misc_forumlinks',
		'onlinelist' => 'misc_onlinelist',
		'censor' => 'misc_censor',
		'discuzcodes' => 'misc_discuzcodes',
		'tags' => 'misc_tags',
		'smilies' => 'smilies',
		'icons' => 'misc_icons',
		'attachtypes' => 'misc_attachtypes',
		'crons' => 'misc_crons',
		'adv' => 'advertisements',
		'advadd' => 'advertisements_add',
		'advedit' => 'advertisements_edit',
		'runquery' => 'database_runquery',
		'optimize' => 'database_optimize',
		'export' => 'database_export',
		'import' => 'database_import',
		'updatecache' => 'tools_updatecache',
		'fileperms' => 'tools_fileperms',
		'relatedtag' => 'tools_relatedtag',
		'attachments' => 'attachments',
		'counter' => 'counter',
		'jswizard' => 'jswizard',
		'creditwizard' => 'creditwizard',
		'google_config' => 'google_config',
		'qihoo_config' => 'qihoo_config',
		'qihoo_topics' => 'qihoo_topics',
		'alipay' => 'ecommerce_alipay',
		'orders' => 'ecommerce_orders',
		'medals' => 'medals',
		'plugins' => 'plugins',
		'pluginsconfig' => 'plugins_config',
		'pluginsedit' => 'plugins_edit',
		'pluginhooks' => 'plugins_hooks',
		'pluginvars' => 'plugins_vars',
		'illegallog' => 'logs_illegal',
		'ratelog' => 'logs_rate',
		'modslog' => 'logs_mods',
		'medalslog' => 'logs_medals',
		'banlog' => 'logs_ban',
		'cplog' => 'logs_cp',
		'creditslog' => 'logs_credits',
		'errorlog' => 'logs_error'
	);
	
	$da = array();
	$query = $db->query("SELECT * FROM {$tablepre}adminactions");
	while($a = $db->fetch_array($query)) {
		if($a['disabledactions']) {
			$da = @unserialize($a['disabledactions']);
			if(is_array($da) && $da) {
				foreach($da as $k => $v) {
					if(isset($actionarray[$v])) {
						$da[$k] = $actionarray[$v];
					} else {
						unset($da[$k]);
					}
				}
			} else {
				$da = array();
			}
			$db->query("UPDATE {$tablepre}adminactions SET disabledactions='".addslashes(serialize($da))."' WHERE admingid='$a[admingid]'");
		}
	}
}

function upg_insenz() {
	global $db, $tablepre;

	$insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'");
	$insenz = $insenz ? unserialize($insenz) : array();
	if($insenz) {
		if($insenz['admin_masks'] && is_array($insenz['admin_masks'])) {
			$insenz['admin_masks'] = array_keys($insenz['admin_masks']);
		}
		if($insenz['member_masks'] && is_array($insenz['member_masks'])) {
			$insenz['member_masks'] = array_keys($insenz['member_masks']);
		}
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
	}
}

function upg_js() {
	global $db, $tablepre;

$request_tpl = array(
	0 => '<div class=\\"box\\">
<h4>������������</h4>
<ul class=\\"textinfolist\\">
[node]<li>[{author}]{subject}</li>[/node]
</ul>
</div>',
	1 => '<div class=\\"box\\">
<h4>������������</h4>
<ul class=\\"textinfolist\\">
[node]<li>[{author}]{subject}</li>[/node]
</ul>
</div>',
	2 => '<div class=\\"box\\">
<h4>�������»ظ�</h4>
<ul class=\\"textinfolist\\">
[node]<li>{subject} ({replies}/{views})</li>[/node]
</ul>
</div>',
	3 => '<div class=\\"box\\">
<h4>��Ծ��Ա</h4>
<ul class=\\"imginfolist\\">
[node]<li>{avatarsmall}<p>{member}</p></li>[/node]
</ul>
</div>',
);
	$request_data = array (
		'default_hotthreads' => array (
			'url' => 'function=threads&sidestatus=1&maxlength=50&fnamelength=0&messagelength=&startrow=0&picpre=&items=10&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=views&hours=0&jscharset=0&cachelife=900&jstemplate='.rawurlencode($request_tpl[0]),
			'parameter' => array (
					'jstemplate' => $request_tpl[0],
					'cachelife' => '900',
					'sidestatus' => '1',
					'startrow' => '0',
					'items' => '10',
					'maxlength' => '50',
					'fnamelength' => '0',
					'messagelength' => '',
					'picpre' => '',
					'tids' => '',
					'keyword' => '',
					'tag' => '',
					'threadtype' => '0',
					'highlight' => '0',
					'recommend' => '0',
					'newwindow' => 1,
					'orderby' => 'views',
					'hours' => '',
					'jscharset' => '0',
			    ),
			    'comment' => '������������',
			    'type' => '0',
	  	),
		'default_hotthreads24hrs' => array (
			'url' => 'function=threads&sidestatus=0&maxlength=50&fnamelength=0&messagelength=&startrow=0&picpre=&items=10&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=hourviews&hours=24&jscharset=0&jstemplate='.rawurlencode($request_tpl[1]),
			'parameter' => array (
				'jstemplate' => $request_tpl[1],
				'cachelife' => '',
				'sidestatus' => '0',
				'startrow' => '0',
				'items' => '10',
				'maxlength' => '50',
				'fnamelength' => '0',
				'messagelength' => '',
				'picpre' => '',
				'tids' => '',
				'keyword' => '',
				'tag' => '',
				'threadtype' => '0',
				'highlight' => '0',
				'recommend' => '0',
				'newwindow' => 1,
				'orderby' => 'hourviews',
				'hours' => '24',
				'jscharset' => '0',
			),
			'comment' => '������������',
			'type' => '0',
		),
		'default_newreplies' => array (
			'url' => 'function=threads&sidestatus=1&maxlength=50&fnamelength=0&messagelength=&startrow=0&picpre=&items=10&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=lastpost&hours=0&jscharset=0&cachelife=900&jstemplate='.rawurlencode($request_tpl[2]),
			'parameter' => array (
				'jstemplate' => $request_tpl[2],
				'cachelife' => '900',
				'sidestatus' => '1',
				'startrow' => '0',
				'items' => '10',
				'maxlength' => '50',
				'fnamelength' => '0',
				'messagelength' => '',
				'picpre' => '',
				'tids' => '',
				'keyword' => '',
				'tag' => '',
				'threadtype' => '0',
				'highlight' => '0',
				'recommend' => '0',
				'newwindow' => 1,
				'orderby' => 'lastpost',
				'hours' => '',
				'jscharset' => '0',
			),
			'comment' => '�������»ظ�',
			'type' => '0',
		),
		'default_hotmembers' => array (
			'url' => 'function=memberrank&startrow=0&items=9&newwindow=1&extcredit=1&orderby=posts&hours=0&jscharset=0&cachelife=1800&jstemplate='.rawurlencode($request_tpl[3]),
			'parameter' => array (
				'jstemplate' => $request_tpl[3],
				'cachelife' => '1800',
				'startrow' => '0',
				'items' => '9',
				'newwindow' => 1,
				'extcredit' => '1',
				'orderby' => 'posts',
				'hours' => '',
				'jscharset' => '0',
			),
			'comment' => '��Ծ��Ա',
			'type' => '2',
		),
		'����1' => array (
			'url' => 'function=side&jscharset=&jstemplate=%5Bmodule%5Ddefault_hotthreads%5B%2Fmodule%5D%5Bmodule%5Ddefault_hotmembers%5B%2Fmodule%5D',
			'parameter' => array (
				'selectmodule' =>
				array (
					0 => 'default_hotthreads',
					1 => 'default_hotmembers',
				),
				'cachelife' => '',
				'jstemplate' => '[module]default_hotthreads[/module][module]default_hotmembers[/module]',
			),
			'comment' => NULL,
			'type' => '-2',
		),
		'����2' => array (
			'url' => 'function=side&jscharset=&jstemplate=%5Bmodule%5Ddefault_newreplies%5B%2Fmodule%5D%5Bmodule%5Ddefault_hotthreads24hrs%5B%2Fmodule%5D',
			'parameter' =>
			array (
				'selectmodule' =>
				array (
						0 => 'default_newreplies',
						1 => 'default_hotthreads24hrs',
				),
				'cachelife' => '',
				'jstemplate' => '[module]default_newreplies[/module][module]default_hotthreads24hrs[/module]',
			),
			'comment' => NULL,
			'type' => '-2',
		),
	);

	foreach($request_data as $k => $v) {
		$variable = $k;
		$type = $v['type'];
		$value = addslashes(serialize($v));

		$db->query("REPLACE INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}
}

?>