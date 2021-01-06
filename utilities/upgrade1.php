<?php

/*
	[DISCUZ!] upgrade1.php - upgrade 3.0RC1 to Discuz! 1.0/1.01
	This is NOT a freeware, use is subject to license terms

	Version: 1.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2002/10/6 17:00
*/



header("Content-Type: text/html; charset=gb2312");
define("IN_CDB", TRUE);
require "./config.php";
require "./functions.php";
require "./lib/$database.php";

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

$action = ($HTTP_POST_VARS["action"]) ? $HTTP_POST_VARS["action"] : $HTTP_GET_VARS["action"];

$upgrade = <<<EOT

UPDATE cdb_settings SET version='1.0';
ALTER TABLE cdb_themes DROP top;
UPDATE cdb_themes SET headercolor='header_bg.gif' WHERE themename='��׼����';
UPDATE cdb_members SET status='��ʽ��Ա' WHERE status='�ο�';

ALTER TABLE cdb_settings ADD postcredits1 tinyint(3) NOT NULL AFTER smcols, ADD digistcredits1 tinyint(3) NOT NULL AFTER postcredits1;
UPDATE cdb_settings SET postcredits1=postcredits, digistcredits1=digistcredits;
ALTER TABLE cdb_settings DROP postcredits, DROP digistcredits, CHANGE postcredits1 postcredits tinyint(3) NOT NULL, CHANGE digistcredits1 digistcredits tinyint(3) NOT NULL;
ALTER TABLE cdb_settings ADD karmactrl smallint(6) UNSIGNED NOT NULL AFTER floodctrl;
UPDATE cdb_settings SET karmactrl='600';


ALTER TABLE cdb_posts DROP INDEX tid, ADD INDEX tid (tid, dateline);
ALTER TABLE cdb_threads DROP hide;

ALTER TABLE cdb_attachments CHANGE filename filename VARCHAR(255) NOT NULL;
ALTER TABLE cdb_attachments CHANGE attachment attachment VARCHAR(255) NOT NULL;

EOT;


if(!$action) {
	echo"�������������� CDB 3.0 RC1 �� Discuz! 1.0,��ȷ��֮ǰ�Ѿ�˳����װ CDB 3.0 RC1<br><br><br>";
	echo"<b><font color=\"red\">���б���������֮ǰ,��ȷ���Ѿ��ϴ� 2.0.0 RC1 ��ȫ���ļ���Ŀ¼</font></b><br><br>";
	echo"<b><font color=\"red\">������ֻ�ܴ� CDB 3.0 RC1 ������ Discuz! 1.0,����ʹ�ñ�����������汾����,������ܻ��ƻ������ݿ�����.<br><br>ǿ�ҽ���������֮ǰ�������ݿ�����!</font></b><br><br>";
	echo"��ȷ����������Ϊ:<br>1. �ϴ� Discuz! 1.0 ���ȫ���ļ���Ŀ¼,���Ƿ������ϵ� CDB 3.0 RC1;<br>2. �ϴ�������($PHP_SELF)�� Discuz! Ŀ¼��;<br>3. ���б�����,ֱ������������ɵ���ʾ;<br>4. �� Discuz! ϵͳ�����лָ�Ĭ��ģ��,���»���,�������.<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade\">�������ȷ���������Ĳ���,�����������</a>";
} else {

	$tables = array('attachments', 'announcements', 'banned', 'caches', 'favorites', 'forumlinks', 'forums', 'members', 'memo',
		'news', 'posts', 'searchindex', 'sessions', 'settings', 'smilies', 'stats', 'subscriptions', 'templates', 'themes',
		'threads', 'u2u', 'usergroups', 'words', 'buddys');
	foreach($tables as $tablename) {
		${"table_".$tablename} = $tablepre.$tablename;
	}
	unset($tablename);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	runquery($upgrade);
	echo "�������,��ָ�Ĭ��ģ��,���»����Ա��������.";

}

function runquery($query) {
	global $tablepre;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql != "" && $sql[0] != "#") {
			mysql_query(str_replace("cdb_", $tablepre, $sql)) or die(mysql_error());
		}
	}
}

?>