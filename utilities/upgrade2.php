<?php
//-----------------------------------------------------------------------------
//    Discuz! Board 1.0 Standard - Discuz! ������̳ (PHP & MySQL) 1.0 ��׼��
//-----------------------------------------------------------------------------
//    Copyright(C) Dai Zhikang, Crossday Studio, 2002. All rights reserved
//
//    Crossday ������ www.crossday.com    *Discuz! ����֧�� www.Discuz.net
//-----------------------------------------------------------------------------
//  ����ϸ�Ķ� Discuz! ��ȨЭ��,�鿴��ʹ�� Discuz! ���κβ�����ζ����ȫͬ��
//  Э���е�ȫ������,�����֮��֧�ֹ��������ҵ,�Ͻ�һ��Υ��Э�����Ȩ��Ϊ.
//-----------------------------------------------------------------------------
// Discuz! רע���ṩ��Чǿ�����̳�������,��������ҵ��;,�����빺��ʹ����Ȩ!
//-----------------------------------------------------------------------------


header("Content-Type: text/html; charset=gb2312");
set_time_limit(1000);

require "./config.php";
require "./include/global.php";
require "./include/db_mysql.php";
require "./include/discuzcode.php";

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

$action = ($HTTP_POST_VARS[action]) ? $HTTP_POST_VARS[action] : $HTTP_GET_VARS[action];
$step = $HTTP_GET_VARS[step];
$start = $HTTP_GET_VARS[start];

$upgrade1 = <<<EOT

DROP TABLE IF EXISTS cdb_caches, cdb_news, cdb_karmalog, cdb_pm;
DROP TABLE IF EXISTS cdb_styles, cdb_stylevars, cdb_sessions, cdb_templates;
CREATE TABLE cdb_sessions (
	sid varchar(8) BINARY NOT NULL,
	ip varchar(15) NOT NULL,
	ipbanned tinyint(1) NOT NULL,
	status enum('Guest', 'Member', 'Admin', 'SuperMod', 'Moderator', 'Banned', 'IPBanned', 'PostBanned', 'Inactive') NOT NULL,
	username varchar(15) NOT NULL,
	lastactivity int(10) UNSIGNED NOT NULL,
	groupid smallint(6) UNSIGNED NOT NULL,
	styleid smallint(6) UNSIGNED NOT NULL,
	action tinyint(1) UNSIGNED NOT NULL,
	fid smallint(6) UNSIGNED NOT NULL,
	tid mediumint(8) UNSIGNED NOT NULL,
	KEY  (sid)
) TYPE=heap MAX_ROWS=1000;

#ALTER TABLE cdb_sessions MAX_ROWS=1000;

CREATE TABLE cdb_templates (
	templateid smallint(6) UNSIGNED NOT NULL auto_increment,
	name varchar(30) NOT NULL,
	directory varchar(100) NOT NULL,
	copyright varchar(100) NOT NULL,
	PRIMARY KEY (templateid)
);

INSERT INTO cdb_templates VALUES (1, 'Default', './templates/default', 'Designed by Crossday Studio');

CREATE TABLE cdb_stylevars (
	stylevarid smallint(6) UNSIGNED NOT NULL auto_increment,
	styleid smallint(6) UNSIGNED NOT NULL,
	variable text NOT NULL,
	substitute text NOT NULL,
	PRIMARY KEY (stylevarid),
	KEY (styleid)
);
	
ALTER TABLE cdb_settings ADD styleid smallint(6) UNSIGNED NOT NULL AFTER moddisplay;

ALTER TABLE cdb_themes ADD smfont varchar(255) NOT NULL, ADD smfontsize VARCHAR(255) NOT NULL;
UPDATE cdb_themes SET smfont=font, smfontsize=fontsize;;
ALTER TABLE cdb_themes RENAME cdb_styles;


ALTER TABLE cdb_styles CHANGE themeid styleid smallint(6) UNSIGNED auto_increment NOT NULL, CHANGE themename name varchar(20) NOT NULL;
ALTER TABLE cdb_styles ADD templateid smallint(6) UNSIGNED NOT NULL AFTER name;
UPDATE cdb_styles SET templateid='1';

ALTER TABLE cdb_members CHANGE theme styleid smallint(6) UNSIGNED NOT NULL;
ALTER TABLE cdb_settings ADD totalmembers smallint(6) UNSIGNED NOT NULL AFTER onlinerecord;

ALTER TABLE cdb_banned CHANGE admin admin VARCHAR(15) NOT NULL;
ALTER TABLE cdb_buddys CHANGE username username VARCHAR(15) NOT NULL;
ALTER TABLE cdb_buddys CHANGE buddyname buddyname VARCHAR(15) NOT NULL;
ALTER TABLE cdb_favorites CHANGE username username VARCHAR(15) NOT NULL;
ALTER TABLE cdb_members CHANGE username username VARCHAR(15) NOT NULL;
ALTER TABLE cdb_memo CHANGE username username VARCHAR(15) NOT NULL;
ALTER TABLE cdb_posts CHANGE author author VARCHAR(15) NOT NULL;
ALTER TABLE cdb_posts CHANGE useip useip VARCHAR(15) NOT NULL;
ALTER TABLE cdb_members CHANGE regip regip VARCHAR(15) NOT NULL;
ALTER TABLE cdb_subscriptions CHANGE username username VARCHAR(15) NOT NULL;
ALTER TABLE cdb_threads CHANGE lastposter lastposter VARCHAR(15) NOT NULL;
ALTER TABLE cdb_threads CHANGE author author VARCHAR(15) NOT NULL;
ALTER TABLE cdb_u2u CHANGE msgto msgto VARCHAR(15) NOT NULL;
ALTER TABLE cdb_u2u CHANGE msgfrom msgfrom VARCHAR(15) NOT NULL;
ALTER TABLE cdb_announcements CHANGE author author VARCHAR(15) NOT NULL;
ALTER TABLE cdb_settings CHANGE lastmember lastmember VARCHAR(15) NOT NULL;
ALTER TABLE cdb_settings ADD searchctrl smallint(6) UNSIGNED NOT NULL AFTER karmactrl;
ALTER TABLE cdb_settings DROP credittitle, DROP creditunit;
EOT;

$upgrade2 = <<<EOT
UPDATE cdb_members SET status=REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(status, '��̳����Ա', 'Admin'), '��������', 'SuperMod'), '����', 'Moderator'), '��ʽ��Ա', 'Member'), '��ֹ����', 'Banned'), '��ֹ����', 'PostBanned'), '�ο�', 'Guest'), '��ֹIP', 'IPBanned'), '�ȴ���֤', 'Inactive');
UPDATE cdb_usergroups SET status=REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(status, '��̳����Ա', 'Admin'), '��������', 'SuperMod'), '����', 'Moderator'), '��ʽ��Ա', 'Member'), '��ֹ����', 'Banned'), '��ֹ����', 'PostBanned'), '�ο�', 'Guest'), '��ֹIP', 'IPBanned'), '�ȴ���֤', 'Inactive');
UPDATE cdb_threads SET author=REPLACE(author, '�ο�', 'Guest'), lastposter=REPLACE(lastposter, '�ο�', 'Guest');
UPDATE cdb_posts SET author=REPLACE(author, '�ο�', 'Guest');
UPDATE cdb_forums SET lastpost=REPLACE(lastpost, '\t�ο�', '\tGuest');

ALTER TABLE cdb_members CHANGE status status enum('Member', 'Admin', 'SuperMod', 'Moderator', 'Banned', 'PostBanned', 'Inactive') NOT NULL;
ALTER TABLE cdb_usergroups CHANGE status status enum('Guest', 'Member', 'Admin', 'SuperMod', 'Moderator', 'Banned', 'IPBanned', 'PostBanned', 'Inactive') NOT NULL DEFAULT 'Member';

ALTER TABLE cdb_settings CHANGE version version varchar(100) NOT NULL;
UPDATE cdb_settings SET version='2.0 <b style=\'color: #FF9900\'>COML</b>', searchctrl='5';
ALTER TABLE cdb_searchindex CHANGE num results INT(10) UNSIGNED DEFAULT '0' NOT NULL;

UPDATE cdb_members SET tpp='0', ppp='0';

ALTER TABLE cdb_u2u  CHANGE folder folder enum('inbox', 'outbox') NOT NULL;

ALTER TABLE cdb_posts CHANGE message message mediumtext NOT NULL;
ALTER TABLE cdb_settings CHANGE maxpostsize maxpostsize mediumint(8) UNSIGNED NOT NULL;

CREATE TABLE cdb_karmalog (
	username varchar(15) NOT NULL default '',
	pid int(10) UNSIGNED NOT NULL default 0,
	dateline int(10) UNSIGNED NOT NULL default 0,
	score tinyint(3) UNSIGNED NOT NULL default 0
);

ALTER TABLE cdb_settings DROP karmactrl;
ALTER TABLE cdb_usergroups CHANGE maxkarmavote maxkarmarate tinyint(3) UNSIGNED NOT NULL;
ALTER TABLE cdb_usergroups CHANGE maxattachsize maxattachsize int(10) UNSIGNED NOT NULL;
ALTER TABLE cdb_usergroups ADD maxrateperday smallint(6) UNSIGNED NOT NULL AFTER maxkarmarate;
UPDATE cdb_usergroups SET maxrateperday='10';
ALTER TABLE cdb_posts ADD rate smallint(6) NOT NULL, ADD ratetimes tinyint(3) UNSIGNED NOT NULL;

ALTER TABLE cdb_settings CHANGE timeoffset timeoffset1 char(3);
ALTER TABLE cdb_settings ADD timeoffset varchar(5) NOT NULL AFTER attachimgpost;
UPDATE cdb_settings SET timeoffset=timeoffset1;
ALTER TABLE cdb_settings DROP timeoffset1;

ALTER TABLE cdb_settings ADD modshortcut tinyint(1) NOT NULL AFTER fastpost;
ALTER TABLE cdb_settings ADD logincredits tinyint(3) UNSIGNED NOT NULL AFTER smcols;
ALTER TABLE cdb_settings CHANGE postcredits postcredits tinyint(3) UNSIGNED NOT NULL;
ALTER TABLE cdb_settings CHANGE digistcredits digistcredits tinyint(3) UNSIGNED NOT NULL;

ALTER TABLE cdb_settings ADD attachsave tinyint(1) NOT NULL AFTER dotfolders;
ALTER TABLE cdb_settings DROP chcode;
ALTER TABLE cdb_settings ADD maxonlines smallint(6) UNSIGNED NOT NULL AFTER styleid;
UPDATE cdb_settings SET maxonlines='1000';

ALTER TABLE cdb_posts ADD INDEX (dateline);
ALTER TABLE cdb_forums ADD styleid smallint(6) UNSIGNED NOT NULL AFTER moderator;

ALTER TABLE cdb_u2u RENAME cdb_pm;
ALTER TABLE cdb_usergroups CHANGE maxu2unum maxpmnum smallint(6) UNSIGNED NOT NULL;
ALTER TABLE cdb_members CHANGE newu2u newpm tinyint(1) NOT NULL, CHANGE ignoreu2u ignorepm text NOT NULL;
ALTER TABLE cdb_pm CHANGE u2uid pmid INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE cdb_members CHANGE credit credit INT(10) DEFAULT '0' NOT NULL;

ALTER TABLE cdb_settings CHANGE digistcredits digestcredits tinyint(3) unsigned NOT NULL default '0';
ALTER TABLE cdb_threads CHANGE digist digest tinyint(1) NOT NULL default '0';
ALTER TABLE cdb_settings CHANGE emailcheck regverify TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE cdb_styles ADD available TINYINT(1) DEFAULT '1' NOT NULL AFTER name;
EOT;

if(!$action) {
	echo"�������������� Discuz! 1.01 �� Discuz! 2.0,��ȷ��֮ǰ�Ѿ�˳����װ Discuz! 1.01<br><br><br>";
	echo"<b><font color=\"red\">���б���������֮ǰ,��ȷ���Ѿ��ϴ� 2.0 COML ��ȫ���ļ���Ŀ¼</font></b><br><br>";
	echo"<b><font color=\"red\">������ֻ�ܴ� Discuz! 1.01 ������ Discuz! 2.0 COML,����ʹ�ñ�����������汾����,������ܻ��ƻ������ݿ�����.<br><br>����֮ǰ��ر������ݿ�����,������ܲ����޷��ָ��ĺ��!</font></b><br><br>";
	echo"��ȷ����������Ϊ:<br>1. �ϴ� Discuz! 2.0 ���ȫ���ļ���Ŀ¼,���Ƿ������ϵ� 1.01<br>2. �ϴ�������($PHP_SELF)�� Discuz! Ŀ¼��;<br>3. ���б�����,ֱ������������ɵ���ʾ;<br>4. �� Discuz! ϵͳ�����и��»���,�������.<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">�������ȷ���������Ĳ���,�����������</a>";
} else {
	$tables = array('attachments', 'announcements', 'banned', 'caches', 'favorites', 'forumlinks', 'forums', 'members', 'memo',
	'news', 'posts', 'searchindex', 'sessions', 'settings', 'styles', 'smilies', 'stats', 'subscriptions', 'templates', 'themes',
	'threads', 'u2u', 'usergroups', 'words', 'buddys', 'stylevars');
	foreach($tables as $tablename) {
		${"table_".$tablename} = $tablepre.$tablename;
	}
	unset($tablename);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		runquery($upgrade1);

		echo "<a href=\"$PHP_SELF?action=upgrade&step=2\">�� 1 �������ɹ�,������������һ��.</a>";

	} elseif($step == 2) {

		runquery($upgrade2);

		$query = $db->query("SELECT $table_styles.styleid FROM $table_settings, $table_styles WHERE $table_styles.name=$table_settings.theme");
		$styleid = $db->result($query, 0);
		$db->query("UPDATE $table_settings SET styleid='$styleid'");
		$db->query("ALTER TABLE $table_settings DROP theme");

		$query = $db->query("SELECT * FROM $table_styles");
		while($style = $db->fetch_array($query)) {
			foreach($style as $key => $val) {
				if($key != 'styleid' && $key != 'name' && $key != 'templateid') {
					$db->query("INSERT INTO $table_stylevars VALUES ('', $style[styleid], '".addslashes($key)."', '".addslashes($val)."')");
				}
			}
		}
		$db->query("ALTER TABLE $table_styles DROP bgcolor, DROP altbg1, DROP altbg2, DROP link, DROP bordercolor, DROP headercolor, DROP headertext, DROP catcolor, DROP tabletext, DROP text, DROP borderwidth, DROP tablewidth, DROP tablespace, DROP font, DROP fontsize, DROP nobold, DROP boardimg, DROP imgdir, DROP smdir, DROP cattext, DROP smfont, DROP smfontsize");

		$query = $db->query("SELECT COUNT(*) FROM $table_members");
		$db->query("UPDATE $table_settings SET totalmembers='".$db->result($query, 0)."'");
		$db->query("ALTER TABLE $table_templates ADD charset varchar(30) NOT NULL AFTER name");
		$db->query("UPDATE $table_templates SET charset='gb2312'");

		$query = $db->query("SELECT * FROM $table_usergroups WHERE groupid='1' OR groupid='2'");
		while($group = $db->fetch_array($query)) {
			$sqlquery[] = join("','", $group);
		}
		foreach($sqlquery as $sql) {
			if($sql) {
				$sql = preg_replace("/^\'(\d{1})\'/", "''", "'$sql'");
				$db->query("INSERT INTO $table_usergroups VALUES ($sql)");
			}
		}
		$db->query("DELETE FROM $table_usergroups WHERE groupid='1' OR groupid='2'");
		$db->query("UPDATE $table_usergroups SET groupid='1' WHERE groupid='14'");
		$db->query("UPDATE $table_usergroups SET groupid='2' WHERE groupid='16'");

		echo "<a href=\"$PHP_SELF?action=upgrade&step=3\">�� 2 �������ɹ�,������������һ��.</a>";

	} elseif($step == 3) {

		$many = 3000;
		if(!$start) {
			$start = 0;
		}
		$end = $start + $many;
		$converted = 0;

		echo"<font color=\"red\"><b>���ڽ��������� 3 �� ����������ݺܶ�,����һ����Ҫʱ��ϳ�</b></font><br><br>\n";
		echo"����ת������. ��ת�����ܾ��������ת, �벻Ҫ�ر������.<br><br>\n";
		echo"���ڿ�ʼת�� id �� $start �� $end ������.<br>";

		$query = $db->query("SELECT pid, message FROM $table_posts WHERE parseurloff='0' LIMIT $start, $many");
		while($post = $db->fetch_array($query)) {
			$post[message] = trim(addslashes($post[message]));
			$post[message1] = trim(parseurl($post[message]));
			if($post[message] != $post[message1]) {
				$db->query("UPDATE $table_posts SET message='$post[message1]' WHERE pid='$post[pid]'");
			}
			$converted = 1;
		}

		if($converted) {
			$end ++;
			redirect("$PHP_SELF?action=upgrade&step=3&start=$end");
		} else {
			redirect("$PHP_SELF?action=upgrade&step=4");
		}

	} elseif($step == 4) {

		$query = $db->query("SELECT tid, pollopts FROM $table_threads WHERE pollopts<>''");
		while($thread = $db->fetch_array($query)) {
			if(strpos($thread['pollopts'], '#|#')) {
				$pollarray = array('multiple' => 0, 'max' => 0, 'total' => 0);
				$votersorig = explode(' ', substr($thread['pollopts'], strrpos($thread['pollopts'], '#|#') + 2));
				foreach($votersorig as $voter) {
					$voter = trim($voter);
					if($voter) {
						$pollarray['voters'][] = $voter;
					}
				}
				foreach(explode('#|#', $thread['pollopts']) as $pollorig) {
					$optionorig = explode('||~|~||', addslashes($pollorig));
					if(count($optionorig) == 2) {
						$pollarray['options'][] = array($optionorig[0], $optionorig[1]);
						if($optionorig[1] > $pollarray['max']) {
							$pollarray['max'] = $optionorig[1];
						}
						$pollarray['total'] += $optionorig[1];
					}
				}
				$pollopts = serialize($pollarray);
				$db->query("UPDATE $table_threads SET pollopts='$pollopts' WHERE tid='$thread[tid]'");
			}
		}

		echo "��ϲ�������ɹ�,��ɾ��������.";
	}
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
	echo"setTimeout('redirect();', 2000);\n";
	echo"</script>";
	echo"<br><br><a href=\"$url\">������������û���Զ���ת����������</a>";

}

// DELETE FROM cdb_stylevars WHERE variable IN ('credittitle', 'creditunit', 'moved', 'sticky', 'digist', 'poll');

?>