<?php

@set_magic_quotes_runtime(0);

@set_time_limit(0);


define('DISCUZ_ROOT', getcwd().'/');
define('IN_DISCUZ', TRUE);

$lang = array(
	'error_message' => '������Ϣ',
	'message_return' => '����',
	'old_step' => '��һ��',
	'new_step' => '��һ��',
	'uc_appname' => '��̳',
	'uc_appreg' => 'ע��',
	'uc_appreg_succeed' => '�� UCenter �ɹ���',
	'uc_continue' => '����������',
	'uc_setup' => '<font color="red">���û�а�װ����������ﰲװ UCenter</font>',
	'uc_title_ucenter' => '����д UCenter �������Ϣ',
	'uc_url' => 'UCenter �� URL',
	'uc_ip' => 'UCenter �� IP',
	'uc_admin' => 'UCenter �Ĺ���Ա�ʺ�',
	'uc_adminpw' => 'UCenter �Ĺ���Ա����',
	'uc_title_app' => '�����Ϣ',
	'uc_app_name' => '������',
	'uc_app_url' => '�� URL',
	'uc_app_ip' => '�� IP',
	'uc_app_ip_comment' => '������ DNS ������ʱ��Ҫ���ã�Ĭ���뱣��Ϊ��',
	'uc_connent_invalid1' => '���ӷ�����',
	'uc_connent_invalid2' => ' ʧ�ܣ��뷵�ؼ�顣',
	'error_message' => '��ʾ��Ϣ',
	'error_return' => '����',

	'tagtemplates_subject' => '����',
	'tagtemplates_uid' => '�û� ID',
	'tagtemplates_username' => '������',
	'tagtemplates_dateline' => '����',
	'tagtemplates_url' => '�����ַ',
);

$msglang = array(
	'redirect_msg' => '��������Զ���תҳ�棬�����˹���Ԥ�����ǵ������������ʱ��û���Զ���תʱ����������',
	'uc_url_empty' => '��û����д UCenter �� URL���뷵����д��',
	'uc_url_invalid' => 'UCenter �� URL ��ʽ���Ϸ��������ĸ�ʽΪ�� http://www.domain.com ���뷵�ؼ�顣',
	'uc_ip_invalid' => '<font color="red">�޷�����������������дվ���IP��</font>',
	'uc_admin_invalid' => '��¼ UCenter �Ĺ���Ա�ʺ���������뷵�ؼ�顣',
	'uc_data_invalid' => 'UCenter ��ȡ����ʧ�ܣ��뷵�ؼ�� UCenter URL������Ա�ʺš����롣 ',
);

require DISCUZ_ROOT.'./include/db_mysql.class.php';
@include DISCUZ_ROOT.'./config.inc.php';

instheader();
if(!$dbhost || !$dbname || !$dbuser) {
	instmsg('��̳���ݿ�����������ݿ������û���Ϊ�ա�');
}

$db = new dbstuff();
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
function get_charset($tablename) {
	global $db;
	$tablestruct = $db->fetch_first("show create table $tablename");
	preg_match("/CHARSET=(\w+)/", $tablestruct['Create Table'], $m);
	return $m[1];
}

if($db->version() > '4.1.0') {
	$tablethreadcharset = get_charset($tablepre.'threads');
	if($dbcharset && $dbcharset !=  $tablethreadcharset) {
		instmsg("���������ļ� (./config.inc.php) �е��ַ��� ($dbcharset) �����ַ��� ($tablethreadcharset) ��ƥ�䡣");
	}
}

$version['old'] = 'Discuz! 6.0.0 ��ʽ��';
$version['new'] = 'Discuz! 6.1.0 ��ʽ��';
$charset = 'GBK';

$upgrade1 = <<<EOT
DROP TABLE IF EXISTS cdb_request;
CREATE TABLE cdb_request (
  variable varchar(32) NOT NULL DEFAULT '',
  value mediumtext NOT NULL,
  type tinyint(1) NOT NULL,
  PRIMARY KEY (variable),
  KEY type (type)
) TYPE=MyISAM;

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

DROP TABLE IF EXISTS cdb_advcaches;
CREATE TABLE cdb_advcaches (
  advid mediumint(8) unsigned NOT NULL auto_increment,
  `type` varchar(50) NOT NULL default '0',
  target smallint(6) NOT NULL,
  `code` mediumtext NOT NULL,
  PRIMARY KEY  (advid)
) ENGINE=MyISAM;

EOT;

$upgradetable = array(

	array('forums', 'ADD', 'allowtag', "TINYINT(1) NOT NULL DEFAULT '1'"),
	array('forums', 'ADD', 'modworks', "TINYINT(1) UNSIGNED NOT NULL"),
	array('forums', 'DROP', 'allowpaytoauthor', ""),

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

	array('videos', 'ADD', 'displayorder', "TINYINT(3) NOT NULL DEFAULT '0' AFTER dateline"),
	array('videos', 'INDEX', '', "ADD INDEX displayorder (displayorder)"),

);

$upgrade3 = <<<EOT

REPLACE INTO cdb_settings (variable, value) VALUES ('attachexpire', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('admode', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('infosidestatus', '');
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

DELETE FROM cdb_crons WHERE filename='supe_daily.inc.php';

INSERT INTO cdb_crons VALUES (NULL,'1','system','ÿ��ѫ�¸���','medals_daily.inc.php','0','1170600452','-1','-1','0','0');

UPDATE cdb_usergroups SET exempt=255 WHERE radminid = 1;
UPDATE cdb_usergroups SET exempt=255 WHERE radminid = 2;
UPDATE cdb_usergroups SET exempt=224 WHERE radminid = 3;

EOT;

$uchidden = '<input type="hidden" name="ucdbhost" value="'.$_POST['ucdbhost'].'">';
$uchidden .= '<input type="hidden" name="ucdbname" value="'.$_POST['ucdbname'].'">';
$uchidden .= '<input type="hidden" name="ucdbuser" value="'.$_POST['ucdbuser'].'">';
$uchidden .= '<input type="hidden" name="ucdbpw" value="'.$_POST['ucdbpw'].'">';
$uchidden .= '<input type="hidden" name="uctablepre" value="'.$_POST['uctablepre'].'">';
$uchidden .= '<input type="hidden" name="ucdbcharset" value="'.$_POST['ucdbcharset'].'">';
$uchidden .= '<input type="hidden" name="ucapi" value="'.$_POST['ucapi'].'">';
$uchidden .= '<input type="hidden" name="ucip" value="'.$_POST['ucip'].'">';
$uchidden .= '<input type="hidden" name="uccharset" value="'.$_POST['uccharset'].'">';
$uchidden .= '<input type="hidden" name="appid" value="'.$_POST['appid'].'">';
$uchidden .= '<input type="hidden" name="appauthkey" value="'.$_POST['appauthkey'].'">';

$step = getgpc('step');
$step = empty($step) ? 1 : $step;

if(!isset($cookiepre)) {
	instmsg('config_nonexistence');
} elseif(!ini_get('short_open_tag')) {
	instmsg('short_open_tag_invalid');
}



if($step == 1) {

$msg = '';
if(file_exists(DISCUZ_ROOT.'forumdata/upgrademaxuid.log')) {
	$msg = '<b><font color="red">���������⵽��ִ�й�����������һ�����ӣ�ѡ�������</font></b><br />
	<li><a href="'.$PHP_SELF.'?step=uc&restart=yes"><font size="2">���ѻָ��ɰ�����, ����������������</font></a><br />
	<li><a href="'.$PHP_SELF.'?step=uc"><font size="2">������ǰ������</font></a><br />';
} else {
	$msg = '<a href="'.$PHP_SELF.'?step=uc"><font size="2"><b>&gt;&gt;&nbsp;�������ȷ���������Ĳ���,�����������</b></font></a>';
}

echo <<<EOT
<h4>����������ֻ�ܴ� $version[old] ������ $version[new]<br /></h4>
����֮ǰ<b>��ر������ݿ�����</b>����������ʧ���޷��ָ�<br /><br />
��ȷ����������Ϊ:
<ol>
	<li>��ȷ���Ѿ���װ�� UCenter
	<li>�ر�ԭ����̳���ϴ� $version[new] ��ȫ���ļ���Ŀ¼�����Ƿ������ϵ� $version[old]
	<li>�ϴ�����������̳Ŀ¼�У��������ú� config.inc.php
	<li>���б�����ֱ������������ɵ���ʾ
	<li>�����;ʧ�ܣ���ʹ��Discuz!�����䣨./utilities/tools.php����������ݻָ����߻ָ����ݣ�ȥ��������������б�����
</ol>
$msg

EOT;

	instfooter();

} elseif($step == 'uc') {

	if(!empty($_GET['restart'])) {
		@unlink(DISCUZ_ROOT.'forumdata/upgrademaxuid.log');
		@unlink(DISCUZ_ROOT.'forumdata/upgrade.log');
	}

	define('APP_NAME', $_POST['appname'] ? $_POST['appname'] : $lang['uc_appname']);
	define('APP_TYPE', 'DISCUZ');
	define('APP_CHARSET', $charset);
	define('APP_DBCHARSET', $dbcharset ? $dbcharset : (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8')) ? str_replace('-', '', $charset) : 'gbk'));
	define('APP_URL', strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
	define('APP_NEXTSTEP', $_SERVER['PHP_SELF'].'?step=2');

	$ucip = $ucapi = $uciperror = '';
	if(!empty($_POST['ucsubmit'])) {

		$ucapi = getgpc('ucapi', 'P');
		$ucip = getgpc('ucip', 'P');
		$ucfounderpw = getgpc('ucfounderpw', 'P');

		$appip = getgpc('appip', 'P');

		$hidden .= var_to_hidden('ucapi', $ucapi);
		$hidden .= var_to_hidden('ucfounderpw', $ucfounderpw);

		$ucapi = preg_replace("/\/$/", '', trim($ucapi));
		if(empty($ucapi)) {
			instmsg('uc_url_empty');
		} elseif(!preg_match("/^(http:\/\/)/i", $ucapi)) {
			instmsg('uc_url_invalid');
		}

		if(!$ucip) {
			parse_url($ucapi);
			$matches = parse_url($ucapi);
			$host = $matches['host'];
			$port = !empty($matches['port']) ? $matches['port'] : 80;
			if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $host)) {
				$ucip = gethostbyname($host);
				$ucip = $ucip == $host ? '' : $ucip;
			} else {
				$ucip = $host;
			}
		}
		if(!$ucip) {
			$uciperror = $msglang['uc_ip_invalid'];
		} else {
			$app_tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
				'apptagtemplates[fields][subject]='.urlencode($lang['tagtemplates_subject']).'&'.
				'apptagtemplates[fields][uid]='.urlencode($lang['tagtemplates_uid']).'&'.
				'apptagtemplates[fields][username]='.urlencode($lang['tagtemplates_username']).'&'.
				'apptagtemplates[fields][dateline]='.urlencode($lang['tagtemplates_dateline']).'&'.
				'apptagtemplates[fields][url]='.urlencode($lang['tagtemplates_url']);

			$postdata = "m=app&a=add&ucfounderpw=".urlencode($ucfounderpw)."&apptype=".urlencode(APP_TYPE)."&appname=".urlencode(APP_NAME)."&appurl=".urlencode(APP_URL)."&appip=&appcharset=".APP_CHARSET.'&appdbcharset='.APP_DBCHARSET.'&'.$app_tagtemplates;
			$s = dfopen($ucapi.'/index.php', 0, $postdata, '', 1, $ucip);
			if(empty($s)) {
				instmsg($lang['uc_connent_invalid1'].$ucapi.' ('.$ucip.')'.$lang['uc_connent_invalid2']);
			} elseif($s == '-1') {
				instmsg('uc_admin_invalid');
			} else {
				list($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset) = explode('|', $s);
				if(empty($appauthkey) || empty($appid)) {
					instmsg('uc_data_invalid');
				} else {
					$apphidden = var_to_hidden('ucdbhost', $ucdbhost);
					$apphidden .= var_to_hidden('ucdbname', $ucdbname);
					$apphidden .= var_to_hidden('ucdbuser', $ucdbuser);
					$apphidden .= var_to_hidden('ucdbpw', $ucdbpw);
					$apphidden .= var_to_hidden('uctablepre', $uctablepre);
					$apphidden .= var_to_hidden('ucdbcharset', $ucdbcharset);

					$apphidden .= var_to_hidden('ucapi', $ucapi);
					$apphidden .= var_to_hidden('ucip', $ucip);
					$apphidden .= var_to_hidden('uccharset', $uccharset);
					$apphidden .= var_to_hidden('appid', $appid);
					$apphidden .= var_to_hidden('appauthkey', $appauthkey);

					instmsg($lang['uc_appreg'].APP_NAME.$lang['uc_appreg_succeed'].'<form action="'.APP_NEXTSTEP.'" method="post">'.$apphidden.'</form><br /><a href="javascript:document.forms[0].submit();">'.$lang['uc_continue'].'</a><script type="text/javascript">setTimeout("document.forms[0].submit()", 1000);</script>');
				}
			}
		}
	}

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>?step=uc">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" colspan="2"><?=$lang['uc_title_ucenter']?></td></tr>
<tr>
<td class="altbg1"><?=$lang['uc_url']?>:</td>
<td class="altbg2"><input class="txt" type="text" name="ucapi" id="ucapi" value="" size="60"></td>
</tr>
<?

	if($uciperror) {

?>
<tr>
<td class="altbg1"><?=$lang['uc_ip']?>:</td>
<td class="altbg2"><input class="txt" type="text" name="ucip" value="<?=$ucip?>" size="60"> <?=$uciperror?></td>
</tr>
<?

	}

?>
<tr>
<td class="altbg1"><?=$lang['uc_admin']?>:</td>
<td class="altbg2"><input class="txt" type="text" name="ucfounder" value="UCenterAdministrator" disabled="disabled" size="30" id="ucfounder"></td>
</tr>
<tr>
<td class="altbg1"><?=$lang['uc_adminpw']?>:</td>
<td class="altbg2"><input class="txt" type="password" name="ucfounderpw" id="ucfounderpw" size="30"></td>
</tr>
</table>
</div>
<br />
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273">
<td colspan="2" style="color: #FFFFFF; padding-left: 10px" colspan="2"><?=APP_NAME.$lang['uc_title_app']?></td>
</tr>
<td class="altbg1"><?=APP_NAME.$lang['uc_app_name']?>:</td>
<td class="altbg2"><input type="text" name="appname" value="<?=APP_NAME?>" size="30"></td>
</tr>
<tr>
<td class="altbg1"><?=APP_NAME.$lang['uc_app_url']?>:</td>
<td class="altbg2"><input type="text" name="appurl" value="<?=APP_URL?>" size="60"></td>
</tr>
<tbody style="display: none;" id="appip">
<tr>
<td class="altbg1"><?=APP_NAME.$lang['uc_app_ip']?>:</td>
<td class="altbg2"><input type="text" name="appip" value="" size="30"> <?=$lang['uc_app_ip_comment']?></td>
</tr>
</tbody>
</table>
<input type="hidden" name="apptype" value="<?=APP_TYPE?>">
<center>
<input type="button" name="ucsubmit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="history.back()">&nbsp;
<input type="submit" name="ucsubmit" value=" <?=$lang['new_step']?> " style="height: 25"></center>
</center>
</form>
<?

	instfooter();
	exit;

} elseif($step == 2) {

	$dirs = array('config.inc.php', 'uc_client/data', 'uc_client/data/cache');

	echo "<h4>���Ŀ¼Ȩ��</h4>";
	echo '<form action="?step=3" method="post">'.$uchidden;
	echo '<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">';
        echo '<tr class="header"><td>Ŀ¼�ļ�</td><td>����״̬</td><td>��ǰ״̬</td></tr>';
        $pass = TRUE;
	foreach($dirs as $dir) {
		$iswritable = is_writable(DISCUZ_ROOT.'./'.$dir);
		$pass == TRUE && !$iswritable && $pass = FALSE;
		echo '<tr align="center"><td class="altbg1">'.$dir.'</td><td class="altbg2">��д</td><td class="altbg1">'.($iswritable ? '<font color="green">��д</font>' : '<font color="red">����д</font>').'</td></tr>';
	}
	if($pass) {
		$nextstep = ' <input type="submit" value="��һ��" style="height: 25">';
	} else {
		$nextstep = ' <input type="button" disabled value="�뽫����Ŀ¼Ȩ��ȫ������Ϊ 777��Ȼ�������һ����װ��" style="height: 25">';
	}
	echo '</table>';
	echo '<p align="center"><input type="button" onclick="history.back()" value="��һ��" style="height: 25"> '.$nextstep.'</p></form>';
	instfooter();

} elseif($step == 3) {

	echo "<h4>���������ļ�</h4>";
	$discuzconfig = DISCUZ_ROOT.'./config.inc.php';
	$ucdbhost = $_POST['ucdbhost'];
	$ucdbuser = $_POST['ucdbuser'];
	$ucdbpw = $_POST['ucdbpw'];
	$ucdbname = $_POST['ucdbname'];
	$ucdbcharset = $_POST['ucdbcharset'];
	$uctablepre = $_POST['uctablepre'];
	$appauthkey = $_POST['appauthkey'];
	$ucapi = $_POST['ucapi'];
	$appid = $_POST['appid'];
	$uccharset = $_POST['uccharset'];
	$ucip = $_POST['ucip'];
	$samelink = ($dbhost == $ucdbhost && $dbuser == $ucdbuser && $dbpw == $ucdbpw);
	$s = file_get_contents($discuzconfig);
	$s = trim($s);
	$s = substr($s, -2) == '?>' ? substr($s, 0, -2) : $s;

	$link = mysql_connect($ucdbhost, $ucdbuser, $ucdbpw, 1);
	$uc_connnect = $link && mysql_select_db($ucdbname, $link) ? 'mysql' : '';
	$s = insertconfig($s, "/define\('UC_CONNECT',\s*'.*?'\);/i", "define('UC_CONNECT', '$uc_connnect');");
	$s = insertconfig($s, "/define\('UC_DBHOST',\s*'.*?'\);/i", "define('UC_DBHOST', '$ucdbhost');");
	$s = insertconfig($s, "/define\('UC_DBUSER',\s*'.*?'\);/i", "define('UC_DBUSER', '$ucdbuser');");
	$s = insertconfig($s, "/define\('UC_DBPW',\s*'.*?'\);/i", "define('UC_DBPW', '$ucdbpw');");
	$s = insertconfig($s, "/define\('UC_DBNAME',\s*'.*?'\);/i", "define('UC_DBNAME', '$ucdbname');");
	$s = insertconfig($s, "/define\('UC_DBCHARSET',\s*'.*?'\);/i", "define('UC_DBCHARSET', '$ucdbcharset');");
	$s = insertconfig($s, "/define\('UC_DBTABLEPRE',\s*'.*?'\);/i", "define('UC_DBTABLEPRE', '`$ucdbname`.$uctablepre');");
	$s = insertconfig($s, "/define\('UC_DBCONNECT',\s*'.*?'\);/i", "define('UC_DBCONNECT', '0');");
	$s = insertconfig($s, "/define\('UC_KEY',\s*'.*?'\);/i", "define('UC_KEY', '$appauthkey');");
	$s = insertconfig($s, "/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$ucapi');");
	$s = insertconfig($s, "/define\('UC_CHARSET',\s*'.*?'\);/i", "define('UC_CHARSET', '$uccharset');");
	$s = insertconfig($s, "/define\('UC_IP',\s*'.*?'\);/i", "define('UC_IP', '$ucip');");
	$s = insertconfig($s, "/define\('UC_APPID',\s*'?.*?'?\);/i", "define('UC_APPID', '$appid');");
	$s = insertconfig($s, "/define\('UC_PPP',\s*'?.*?'?\);/i", "define('UC_PPP', '20');");
	//$s = insertconfig($s, "/define\('UC_LINK',\s*'?.*?'?\);/i", "define('UC_LINK', ".($samelink ? 'TRUE' : 'FALSE').");");

	if(!($fp = @fopen($discuzconfig, 'w'))) {
		instmsg('�����ļ�д��ʧ�ܣ��뷵�ؼ�� ./config.inc.php ��Ȩ���Ƿ�Ϊ0777 ');
	}

	@fwrite($fp, $s);
	@fclose($fp);
	instmsg("���������ļ����", '?step=4&urladd='.$urladd, $uchidden);

} elseif($step == 4) {

	echo "<h4>��Ա���ݵ��뵽 UCenter</h4>";

	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);
	if(empty($_POST['ucsubmit']) && !getgpc('start')) {

		if(file_exists(DISCUZ_ROOT.'forumdata/upgrademaxuid.log')) {
			$maxuid = file(DISCUZ_ROOT.'forumdata/upgrademaxuid.log');
			$maxuid = $maxuid[0];
			instmsg('��Ա���ݵ�����ϡ�', '?step='.($maxuid > 0 ? 'merge' : '5').'&urladd='.$urladd.'&maxuid='.$maxuid, $uchidden);
		}

		$maxuid = getmaxuid();

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>?step=4">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" colspan="2">��Ա ID ��ʼֵ</td></tr>
<tr>
<td class="altbg1">��Ա ID ��ʼֵ:</td>
<td class="altbg2"><input class="txt" type="text" name="maxuidset" value="<?=$maxuid?>" size="10">
��ʼ��Ա ID ������ڵ��� <?=$maxuid?>������д 10000 ��ô��ԭ ID Ϊ 888 �Ļ�Ա����Ϊ 10888��</td>
</tr>
</table>
<center>
<input type="button" name="ucsubmit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="history.back()">&nbsp;
<input type="submit" name="ucsubmit" value=" <?=$lang['new_step']?> " style="height: 25"></center>
</center>
</form>
<?

	instfooter();
	exit;
	}

	$start = intval(getgpc('start'));
	$limit = 5000;
	$total = intval(getgpc('total'));
	$maxuid = intval(getgpc('maxuid'));
	$lastuid = intval(getgpc('lastuid'));
	if(!$total) {
		$maxuid = getmaxuid();
		$maxuidset = intval($_POST['maxuidset']);
		if($maxuidset < $maxuid) {
			@unlink(DISCUZ_ROOT.'forumdata/upgrademaxuid.log');
			instmsg('��ʼ��Ա ID ������ڵ��� '.$maxuid.'���뷵��������д��', '?step=4&urladd='.$urladd, $uchidden);
		} else {
			$maxuid = $maxuidset;
		}
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}members");
		$total = $db->result($query, 0);
		$fp = @fopen(DISCUZ_ROOT.'forumdata/upgrademaxuid.log', 'w');
		@fwrite($fp, $maxuid."\r\n���ļ��ṩ����̳��������������֮�ã�������ֵΪ����̳�� Discuz! 6.0.0 ������ Discuz! 6.1.0 �� UID ��ƫ����");
		@fclose($fp);

		if(!empty($forumfounders)) {
			$discuzconfig = DISCUZ_ROOT.'./config.inc.php';
			$s = file_get_contents($discuzconfig);
			$s = trim($s);
			$s = substr($s, -2) == '?>' ? substr($s, 0, -2) : $s;

			$forumfounderarray = explode(',', $forumfounders);
			foreach($forumfounderarray as $k => $u) {
				$forumfounderarray[$k] = is_numeric($u) ? $u + $maxuid : $u;
			}
			$forumfounders = implode(',', $forumfounderarray);

			$s = insertconfig($s, "/[$]forumfounders\s*\=\s*[\"'].*?[\"'];/is", "\$forumfounders = '$forumfounders';");

			if(!($fp = @fopen($discuzconfig, 'w'))) {
				instmsg('�����ļ�д��ʧ�ܣ��뷵�ؼ�� ./config.inc.php ��Ȩ���Ƿ�Ϊ0777 ');
			}

			@fwrite($fp, $s);
			@fclose($fp);
		}
	}
	if($total == 0 || $total <= $start) {
		$ucdb->query("ALTER TABLE ".UC_DBTABLEPRE."members AUTO_INCREMENT=".($lastuid + 1));
		instmsg('��Ա���ݵ�����ϡ�', '?step='.($maxuid > 0 ? 'merge' : '5').'&urladd='.$urladd.'&maxuid='.$maxuid, $uchidden);
	}

	$query = $db->query("SELECT * FROM {$tablepre}members LIMIT $start, $limit");
	if($ucdb->version() > '4.1' && $ucdb == $db && $dbname != UC_DBNAME) {
		$ucdb->query("SET NAMES ".UC_DBCHARSET);
	}
	while($data = $db->fetch_array($query)) {
		$salt = rand(100000, 999999);
		$password = md5($data['password'].$salt);
		$data['username'] = addslashes($data['username']);
		$lastuid = $data['uid'] += $maxuid;
		$queryuc = $ucdb->query("SELECT count(*) FROM ".UC_DBTABLEPRE."members WHERE username='$data[username]'");
		$userexist = $ucdb->result($queryuc, 0);
		if(!$userexist) {
			$ucdb->query("INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."members SET uid='$data[uid]', username='$data[username]', password='$password',
				email='$data[email]', regip='$data[regip]', regdate='$data[regdate]', salt='$salt'", 'SILENT');
			$ucdb->query("INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."memberfields SET uid='$data[uid]'",'SILENT');
		} else {
			$ucdb->query("REPLACE INTO ".UC_DBTABLEPRE."mergemembers SET appid='".UC_APPID."', username='$data[username]'", 'SILENT');
		}
	}

	$end = $start + $limit;
	instmsg("��Ա���ݵ��뵽 UCenter $start / $total ...", '?step=4&'.$urladd.'&start='.$end.'&total='.$total.'&maxuid='.$maxuid.'&lastuid='.$lastuid, $uchidden);

	instfooter();

} elseif($step == 'merge') {

	echo "<h4>�ϲ���Ա����</h4>";
	$maxuid = intval(getgpc('maxuid'));

	$uidfields = array(
		'members',
		'memberfields',
		'access',
		'activities',
		'activityapplies',
		'attachments',
		'attachpaymentlog',
		'buddys|uid,buddyid',
		'creditslog',
		'debateposts',
		'debates',
		'favorites',
		'forumrecommend|authorid,moderatorid',
		'invites',
		'magiclog',
		'magicmarket',
		'membermagics',
		'memberspaces',
		'moderators',
		'modworks',
		'myposts',
		'mythreads',
		'onlinetime',
		'orders',
		'paymentlog|uid,authorid',
		'pms|msgtoid,msgfromid|pmid',
		'posts|authorid|pid',
		'promotions',
		'ratelog',
		'rewardlog|authorid,answererid',
		'searchindex|uid',
		'spacecaches',
		'subscriptions',
		'threads|authorid|tid',
		'threadsmod',
		'tradecomments|raterid,rateeid',
		'tradelog|sellerid,buyerid',
		'trades|sellerid',
		'validating',
		'videos',
	);

	$start = intval(getgpc('start'));
	$end = $start + 1;
	$total = count($uidfields);
	if($total == 0 || $total <= $start) {
		instmsg('��Ա���ݺϲ���ϡ�', '?step=5&urladd='.$urladd, $uchidden);
	}

	$value = $uidfields[$start];
	list($table, $field, $stepfield) = explode('|', $value);
	$logs = array();
	$logs = explode('|', @file_get_contents(DISCUZ_ROOT.'forumdata/upgrade.log'));
	if(!in_array($table, $logs)) {
		$fields = !$field ? array('uid') : explode(',', $field);
		if($stepfield) {
			$mlimit = 5000;
			$mstart = intval(getgpc('mstart'));
			$mtotal = intval(getgpc('total'));
			if(!$mtotal) {
				$query = $db->query("SELECT `$stepfield` FROM `{$tablepre}$table` ORDER BY `$stepfield` DESC LIMIT 1");
				$mtotal = $db->result($query, 0);
			}

			if($mtotal != 0 && $mtotal > $mstart) {
				$mend = $mstart + $mlimit;
				$urladd = 'mstart='.$mend;

				foreach($fields as $field) {
					$db->query("UPDATE `{$tablepre}$table` SET `$field`=`$field`+$maxuid WHERE `$stepfield` >= $mstart AND `$stepfield` < $mend ORDER BY `$field` DESC");
				}

				instmsg("���ڴ����Ա�ϲ����� {$tablepre}$table $mstart / $mtotal ...", '?step=merge&'.$urladd.'&start='.$start.'&maxuid='.$maxuid, $uchidden);
			} else {
				$fp = fopen(DISCUZ_ROOT.'forumdata/upgrade.log', 'a+');
				fwrite($fp, $table."|");
				fclose($fp);
			}

		} else {
			foreach($fields as $field) {
				$db->query("UPDATE `{$tablepre}$table` SET `$field`=`$field`+$maxuid ORDER BY `$field` DESC");
			}
			$fp = fopen(DISCUZ_ROOT.'forumdata/upgrade.log', 'a+');
			fwrite($fp, $table."|");
			fclose($fp);
		}
	}
	instmsg("{$tablepre}$table ��ϲ���ϡ�", '?step=merge&'.$urladd.'&start='.$end.'&maxuid='.$maxuid, $uchidden);

} elseif($step == 5) {

	echo "<h4>�����������</h4>";

	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);

	$start = intval(getgpc('start'));
	$limit = 5000;
	$total = intval(getgpc('total'));
	if(!$total) {
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}buddys");
		$total = $db->result($query, 0);
	}
	if($total == 0 || $total <= $start) {
		instmsg('�������������ϡ�', '?step=6&urladd='.$urladd, $uchidden);
	}

	$query = $db->query("SELECT * FROM {$tablepre}buddys LIMIT $start, $limit");
	if($ucdb->version() > '4.1' && $ucdb == $db && $dbname != UC_DBNAME) {
		$ucdb->query("SET NAMES ".UC_DBCHARSET);
	}
	while($data = $db->fetch_array($query)) {
		$ucdb->query("INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."friends SET uid='$data[uid]', friendid='$data[buddyid]', direction='1',
			version='0', delstatus='0', comment='$data[description]'", 'SILENT');
	}
	$end = $start + $limit;
	instmsg("���ڵ���������� $start / $total ...", '?step=5&'.$urladd.'&start='.$end.'&total='.$total, $uchidden);

	instfooter();

} elseif($step == 6) {

	$ucdbhost = $_POST['ucdbhost'];
	$ucdbuser = $_POST['ucdbuser'];
	$ucdbpw = $_POST['ucdbpw'];
	$ucdbname = $_POST['ucdbname'];
	$ucdbcharset = $_POST['ucdbcharset'];
	$uctablepre = $_POST['uctablepre'];

	echo "<h4>�������Ϣ����</h4>";

	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);

	$commonpm = getgpc('commonpm');
	if(!$commonpm) {
		$arr = $db->fetch_first("SELECT uid, username FROM {$tablepre}members WHERE adminid='1' LIMIT 1");
		$query = $db->query("SELECT * FROM {$tablepre}announcements WHERE type='2'");
		while($data = $db->fetch_array($query)) {
			$data['subject'] = addslashes($data['subject']);
			$data['message'] = addslashes($data['message']);
			$ucdb->query("INSERT INTO ".UC_DBTABLEPRE."pms SET msgfrom='$arr[username]', msgfromid='$arr[uid]', msgtoid='0', folder='inbox', subject='$data[subject]', message='$data[message]', dateline='$data[dateline]'");
		}
	}

	$start = intval(getgpc('start'));
	$limit = 5000;
	$total = intval(getgpc('total'));
	if(!$total) {
		$total = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms");
	}
	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);

	//mysql_query("SET character_set_connection=latin1, character_set_results=binary, character_set_client=latin1", $ucdb->link);

	$query = $db->query("SELECT * FROM {$tablepre}pms LIMIT $start, $limit");
	if($total == 0 || $total <= $start || $db->errno() == 1146) {
		instmsg(' �������Ϣ��ϡ�', '?step=7');
	}
	if($ucdb->version() > '4.1' && $ucdb == $db && $dbname != UC_DBNAME) {
		$ucdb->query("SET NAMES ".UC_DBCHARSET);
	}
	while($data = $db->fetch_array($query)) {
		$data['subject'] = addslashes($data['subject']);
		$data['message'] = addslashes($data['message']);
		$ucdb->query("INSERT INTO ".UC_DBTABLEPRE."pms SET msgfrom='$data[msgfrom]',
			msgfromid='$data[msgfromid]',msgtoid='$data[msgtoid]',folder='$data[folder]',new='$data[new]',subject='$data[subject]',
			dateline='$data[dateline]',message='$data[message]',delstatus='$data[delstatus]',related='0'", 'SILENT');
	}
	$end = $start + $limit;
	instmsg("���ڴ������Ϣ $start / $total", '?step=6&commonpm=1&start='.$end.'&total='.$total, $uchidden);
	instfooter();

} elseif($step == 7) {

	echo "<h4>�������ݱ�</h4>";
	$sql = str_replace("\r\n", "\n", $sql);

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	runquery($upgrade1);
	instmsg("�������ݱ�����ϡ�", '?step=8');
	instfooter();

} elseif($step == 8) {

	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

	echo "<h4>������̳���ݱ�ṹ</h4>";
	if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

		echo "�������ݱ� [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:";
		$successed = upgradetable($upgradetable[$start]);

		if($successed === TRUE) {
			echo ' <font color=green>OK</font><br />';
		} elseif($successed === FALSE) {
			//echo ' <font color=red>ERROR</font><br />';
		} elseif($successed == 'TABLE NOT EXISTS') {
			echo '<span class=red>���ݱ�����</span>�����޷���������ȷ��������̳�汾�Ƿ���ȷ!</font><br />';
			instfooter();
			exit;
		}
	}

	$start ++;
	if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {
		instmsg("��ȴ� ...", "?step=8&start=$start");
	}
	instmsg("��̳���ݱ�ṹ������ϡ�", "?step=9");
	instfooter();

} elseif($step == 9) {

	echo "<h4>���²�������</h4>";
	runquery($upgrade3);
	upg_adminactions();
	upg_insenz();
	upg_js();
	instmsg("�������ݸ�����ϡ�", "?step=10");
	instfooter();

} else {

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	$uc_input = uc_api_input("action=updatecache");

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	echo '<br />��ϲ����̳���������ɹ���������������<ol><li><b>��ɾ��������</b>'.
		'<li>ʹ�ù���Ա��ݵ�¼��̳�������̨�����»���'.
		'<li>������̳ע�ᡢ��¼�������ȳ�����ԣ����������Ƿ�����'.
		'<li>�����������̳������û���κ������������ɾ�� forumdata Ŀ¼�µ� upgrade.log �ļ�<br /><br />'.
		'<b>��л��ѡ�����ǵĲ�Ʒ��</b><a href="index.php" target="_blank">�����ڿ��Է�����̳���鿴�������</a><iframe width="0" height="0" src="index.php"></iframe>';
	instfooter();

}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		// ���뵽���һ��
		$s .= "\r\n".$replace;
	}
	return $s;
}

function instheader() {
	global $charset, $version;

	echo "<html><head>".
		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">".
		"<title>$version[old] &gt;&gt; $version[new] ������</title>".
		"<style type=\"text/css\">
		a {
			color: #3A4273;
			text-decoration: none
		}

		a:hover {
			color: #3A4273;
			text-decoration: underline
		}

		body, table, td {
			color: #3A4273;
			font-family: Tahoma, Verdana, Arial;
			font-size: 12px;
			line-height: 20px;
			scrollbar-base-color: #E3E3EA;
			scrollbar-arrow-color: #5C5C8D
		}

		input {
			color: #085878;
			font-family: Tahoma, Verdana, Arial;
			font-size: 12px;
			background-color: #3A4273;
			color: #FFFFFF;
			scrollbar-base-color: #E3E3EA;
			scrollbar-arrow-color: #5C5C8D
		}

		.install {
			font-family: Arial, Verdana;
			font-size: 20px;
			font-weight: bold;
			color: #000000
		}

		.message {
			background: #E3E3EA;
			padding: 20px;
		}

		.altbg1 {
			background: #E3E3EA;
		}

		.altbg2 {
			background: #EEEEF6;
		}

		.header td {
			color: #FFFFFF;
			background-color: #3A4273;
			text-align: center;
		}

		.option td {
			text-align: center;
		}

		.redfont {
			color: #FF0000;
		}
		</style>
		<script type=\"text/javascript\">
		function redirect(url) {
			window.location=url;
		}
		function $(id) {
			return document.getElementById(id);
		}
		</script>
		</head>".
		"<body bgcolor=\"#3A4273\" text=\"#000000\"><div id=\"append_parent\"></div>".
		"<table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\" align=\"center\"><tr><td>".
      		"<table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tr>".
          	"<td class=\"install\" height=\"30\" valign=\"bottom\"><font color=\"#FF0000\">&gt;&gt;</font> $version[old] &gt;&gt; $version[new] ������ ".
          	"</td></tr><tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr><tr><td colspan=\"2\">";
}

function instfooter() {
	global $version;

	echo "</td></tr><tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr>".
        	"<tr><td align=\"center\">".
            	"<b style=\"font-size: 11px\">Powered by <a href=\"http://discuz.net\" target=\"_blank\">Discuz!".
          	"</a> &nbsp; Copyright &copy; <a href=\"http://www.comsenz.com\" target=\"_blank\">Comsenz Inc.</a> 2001-2008</b><br /><br />".
          	"</td></tr></table></td></tr></table>".
		"</body></html>";
}

function instmsg($message, $url_forward = '', $postdata = '') {
	global $lang, $msglang;
	$message = $msglang[$message] ? $msglang[$message] : $message;
	if($postdata) {
		$message .= "<br /><br /><br /><a href=\"###\" onclick=\"document.getElementById('postform').submit();\">$msglang[redirect_msg]</a>";
		echo '<form action="'.$url_forward.'" method="post" id="postform">';
		echo $postdata;
		echo	"<tr><td style=\"padding-top:50px; padding-bottom:100px\"><table width=\"560\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">".
			"<tr bgcolor=\"#3A4273\"><td width=\"20%\" style=\"color: #FFFFFF; padding-left: 10px\">$lang[error_message]</td></tr>".
	  		"<tr align=\"center\" bgcolor=\"#E3E3EA\"><td class=\"message\">$message</td></tr></table></td></tr>";
		echo '</form><script>setTimeout("document.getElementById(\'postform\').submit()", 1250);</script>';
		instfooter();
	} else {
		if($url_forward) {
			$message .= "<br /><br /><br /><a href=\"$url_forward\">$msglang[redirect_msg]</a>";
			$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
		} elseif(strpos($message, $lang['return'])) {
			$message .= "<br /><br /><br /><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a>";
		}

		echo 	"<tr><td style=\"padding-top:50px; padding-bottom:100px\"><table width=\"560\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">".
			"<tr bgcolor=\"#3A4273\"><td width=\"20%\" style=\"color: #FFFFFF; padding-left: 10px\">$lang[error_message]</td></tr>".
	  		"<tr align=\"center\" bgcolor=\"#E3E3EA\"><td class=\"message\">$message</td></tr></table></td></tr>";
		instfooter();
	}
	exit;
}


function getgpc($k, $var='G') {
	switch($var) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_REQUEST; break;
	}
	return isset($var[$k]) ? $var[$k] : NULL;
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

function var_to_hidden($k, $v) {
	return "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
}

function getmaxuid() {
	global $ucdb;
	$query = $ucdb->query("SHOW CREATE TABLE ".UC_DBTABLEPRE."members");
	$data = $ucdb->fetch_array($query);
	$data = $data['Create Table'];
	if(preg_match('/AUTO_INCREMENT=(\d+?)[\s|$]/i', $data, $a)) {
		return $a[1] - 1;
	} else {
		return 0;
	}
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].'?'.$matches['query'].'#'.$matches['fragment'] : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
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

	$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable like 'jswizard_%'");
	while($data = $db->fetch_array($query)) {
		$variable = substr($data['variable'], 9);
		$value = unserialize($data['value']);
		$type = $value['type'];
		$value = addslashes(serialize($value));

		$db->query("INSERT INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}

	foreach($request_data as $k => $v) {
		$variable = $k;
		$type = $v['type'];
		$value = addslashes(serialize($v));

		$db->query("REPLACE INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}
}

?>
