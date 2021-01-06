<?php

/*
	[DISCUZ!] utilities/restore.php - Discuz! database importing utilities
	This is NOT a freeware, use is subject to license terms

	Version: 2.0.1
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/9/10 10:05
*/


error_reporting(7);
@set_time_limit(1000);
ob_implicit_flush();

define('DISCUZ_ROOT', './');
define('IN_DISCUZ', TRUE);

require './config.inc.php';
require './include/db_'.$database.'.class.php';

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
$db->select_db($dbname);

if(!get_cfg_var('register_globals')) {
	@extract($HTTP_GET_VARS);
}

$sqldump = '';

echo "<HTML><HEAD></HEAD><BODY STYLE=\"font-family: Tahoma, Verdana, ����; font-size: 11px\"><b>���ݿ�ָ�ʵ�ù��� RESTORE for Discuz!</b><br><br>".
	"���������ڻָ��� Discuz! ���ݵ������ļ�,�� Discuz! ���������޷����кͻָ�����,<br>".
	"�� phpMyAdmin �ֲ��ָܻ����ļ�ʱ,�ɳ���ʹ�ô˹���.<br><br>".
	"��Ȩ����(C) ��ʢ����(����)�Ƽ����޹�˾, 2002, 2003, 2004<br><br>".
	"ע��:<br><br>".
	"<b>����������� Discuz! Ŀ¼�в���ʹ��<br><br>".
	"ֻ�ָܻ�����ڷ�����(Զ�̻򱾵�)�ϵ������ļ�,����������ݲ��ڷ�������,���� FTP �ϴ�<br><br>".
	"�����ļ�����Ϊ Discuz! ������ʽ,��������Ӧ����ʹ PHP �ܹ���ȡ<br><br>".
	"�뾡��ѡ�����������ʱ�β���,�Ա��ⳬʱ.����򳤾�(���� 10 ����)����Ӧ,��ˢ��</b><br><br>";

if($file) {
	if(strtolower(substr($file, 0, 7)) == "http://") {
		echo "��Զ�����ݿ�ָ����� - ��ȡԶ������:<br><br>";
		echo "��Զ�̷�������ȡ�ļ� ... ";

		$sqldump = @fread($fp, 99999999);
		@fclose($fp);
		if($sqldump) {
			echo "�ɹ�<br><br>";
		} elseif(!$multivol) {
			exit("ʧ��<br><br><b>�޷��ָ�����</b>");
		}
	} else {
		echo "�ӱ��ػָ����� - ��������ļ�:<br><br>";
		if(file_exists($file)) {
			echo "�����ļ� $file ���ڼ�� ... �ɹ�<br><br>";
		} elseif(!$multivol) {
			exit("�����ļ� $file ���ڼ�� ... ʧ��<br><br><br><b>�޷��ָ�����</b>");
		}

		if(is_readable($file)) {
			echo "�����ļ� $file �ɶ���� ... �ɹ�<br><br>";
			@$fp = fopen($file, "r");
			@flock($fp, 3);
			$sqldump = @fread($fp, filesize($file));
			@fclose($fp);
			echo "�ӱ��ض�ȡ���� ... �ɹ�<br><br>";
		} elseif(!$multivol) {
			exit("�����ļ� $file �ɶ���� ... ʧ��<br><br><br><b>�޷��ָ�����</b>");
		}
	}

	if($multivol && !$sqldump) {
		exit("�־��ݷ�Χ��� ... �ɹ�<br><br><b>��ϲ��,�����Ѿ�ȫ���ɹ��ָ�!��ȫ���,�����ɾ��������.</b>");
	}

	echo "�����ļ� $file ��ʽ��� ... ";
	@list(,,,$method, $volume) = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", preg_replace("/^(.+)/", "\\1", substr($sqldump, 0, 256)))));
	if($method == 'multivol' && is_numeric($volume)) {
		echo "�ɹ�<br><br>";
	} else {
		exit("ʧ��<br><br><b>���ݷ� Discuz! �־��ݸ�ʽ,�޷��ָ�</b>");
	}

	if($onlysave == "yes") {
		echo "�������ļ����浽���ط����� ... ";
		$filename = "./forumdata".strrchr($file, "/");
		@$filehandle = fopen($filename, "w");
		@flock($filehandle, 3);
		if(@fwrite($filehandle, $sqldump)) {
			@fclose($filehandle);
			echo "�ɹ�<br><br>";
		} else {
			@fclose($filehandle);
			die("ʧ��<br><br><b>�޷���������</b>");
		}
		echo "�ɹ�<br><br><b>��ϲ��,�����Ѿ��ɹ����浽���ط����� <a href=\"".strstr($filename, "/")."\">$filename</a>.��ȫ���,�����ɾ��������.</b>";
	} else {
		$sqlquery = splitsql($sqldump);
		echo "��ֲ������ ... �ɹ�<br><br>";
		unset($sqldump);

		echo "���ڻָ�����,��ȴ� ... <br><br>";
		foreach($sqlquery as $sql) {
			if(trim($sql)) {
				$db->query($sql);
				//echo "$sql<br>";
			}
		}

		$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);

		echo "�����ļ� <b>$volume#</b> �ָ��ɹ�,���ڽ��Զ����������־�������.<br><b>����ر���������жϱ���������</b>";
		redirect("restore.php?file=$nextfile&multivol=yes");
	}
} else {
	echo "����:<br><br>".
		"<b>file=forumdata/dz_xxx.sql</b> (���ػָ�: forumdata/dz_xxx.sql �Ǳ��ط������������ļ���·��������)<br>".
		"<b>file=http://your.com/discuz/forumdata/dz_xxx.sql</b> (Զ�ָ̻�: http://... ��Զ�������ļ���·��������)<br><br>".
		"<b>onlysave=yes</b> (ֻ�������ļ�ת�浽���ط�����,�����ָ������ݿ�)<br><br>".
		"�÷�����:<br><br>".
		"<b><a href=\"restore.php?file=forumdata/discuz.sql\">restore.php?file=forumdata/discuz.sql</a></b> (�ָ� forumdata Ŀ¼�µ� discuz.sql �����ļ�)<br>".
		"<b><a href=\"restore.php?file=http://your.com/discuz/forumdata/dz_xxx.sql\">restore.php?file=http://your.com/discuz/forumdata/discuz_xxx.sql</a></b> (�ָ� your.com �ϵ���Ӧ�����ļ�)<br>".
		"<b><a href=\"restore.php?file=http://your.com/discuz/forumdata/dz_xxx.sql&onlysave=yes\">restore.php?file=http://your.com/discuz/forumdata/dz_xxx.sql&onlysave=yes</a></b> (ת�� your.com �ϵ���Ӧ�����ļ������ط�����)<br>".
		"</BODY></HTML>";
}

function splitsql($sql) {
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}			
	return($ret);
}

function redirect($url) {
	echo "<script>";
	echo "function redirect() {window.location.replace('$url');}\n";
	echo "setTimeout('redirect();', 2000);\n";
	echo "</script>";
	echo "<br><br><a href=\"$url\">������������û���Զ���ת����������</a>";
}

?>
