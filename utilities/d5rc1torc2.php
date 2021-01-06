<?php

// Upgrade Discuz! Board from 5.0.0RC1 to 5.0.0RC2

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

if(!$action) {
	echo"�������������� Discuz! 5.0.0RC1 �� Discuz! 5.0.0RC2,��ȷ��֮ǰ�Ѿ�˳����װ Discuz! 5.0.0RC1<br><br><br>";
	echo"<b><font color=\"red\">����������ֻ�ܴ� 5.0.0RC1 ������ 5.0.0RC2,����֮ǰ,��ȷ���Ѿ��ϴ� 5.0.0RC2 ��ȫ���ļ���Ŀ¼</font></b><br>";
	echo"<b><font color=\"red\">����ǰ�������� JavaScript ֧��,�����������Զ���ɵ�,�����˹�����͸�Ԥ.<br>����֮ǰ��ر������ݿ�����,������ܲ����޷��ָ��ĺ��!<br></font></b><br><br>";
	echo"��ȷ����������Ϊ:<br>1. �ر�ԭ����̳,�ϴ� Discuz! 5.0.0RC2 ���ȫ���ļ���Ŀ¼,���Ƿ������ϵ� 5.0.0RC1<br>2. �ϴ������� Discuz! Ŀ¼��;<br>4. ���б�����,ֱ������������ɵ���ʾ;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">�������ȷ���������Ĳ���,�����������</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	$query = $db->query("SELECT styleid FROM {$tablepre}styles ORDER BY styleid DESC LIMIT 1");
	$styleid = intval($db->result($query, 0));
	$newstyleid = $styleid + 1;

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('indexname', 'index.php')");
	$db->query("REPLACE INTO {$tablepre}styles (styleid, name, available, templateid) VALUES ('$newstyleid', 'Discuz! 5 Default', '1', '1')");
	$db->query("REPLACE INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$newstyleid', 'lighttext', '#999999'),
			('$newstyleid', 'bgcolor', '#FFFFFF'),
			('$newstyleid', 'altbg1', '#F9FAFF'),
			('$newstyleid', 'altbg2', '#FFFFFF'),
			('$newstyleid', 'link', '#154BA0'),
			('$newstyleid', 'bordercolor', '#7AC4EA'),
			('$newstyleid', 'headercolor', 'header_bg.gif'),
			('$newstyleid', 'headertext', '#333333'),
			('$newstyleid', 'catcolor', '#F1F1F1'),
			('$newstyleid', 'tabletext', '#333333'),
			('$newstyleid', 'text', '#333333'),
			('$newstyleid', 'borderwidth', '1'),
			('$newstyleid', 'tablewidth', '98%'),
			('$newstyleid', 'tablespace', '4'),
			('$newstyleid', 'font', 'Tahoma, Verdana'),
			('$newstyleid', 'fontsize', '12px'),
			('$newstyleid', 'msgfontsize', '12px'),
			('$newstyleid', 'nobold', '0'),
			('$newstyleid', 'boardimg', 'logo.gif'),
			('$newstyleid', 'imgdir', 'images/default'),
			('$newstyleid', 'smdir', 'images/smilies'),
			('$newstyleid', 'cattext', '#339900'),
			('$newstyleid', 'smfontsize', '11px'),
			('$newstyleid', 'smfont', 'Arial, Tahoma'),
			('$newstyleid', 'maintablewidth', '98%'),
			('$newstyleid', 'maintablecolor', '#FFFFFF'),
			('$newstyleid', 'innerborderwidth', '0'),
			('$newstyleid', 'innerbordercolor', '#B6DFF6'),
			('$newstyleid', 'menubg', '#D9EEF9'),
			('$newstyleid', 'bgborder', '#B6DFF6'),
			('$newstyleid', 'inputborder', '#7AC4EA'),
			('$newstyleid', 'mainborder', '#154BA0'),
			('$newstyleid', 'catborder', '#E7E7E7'),
			('$newstyleid', 'headermenu', 'menu_bg.gif')");
	$db->query("UPDATE {$tablepre}members SET styleid = '0'");
	$db->query("UPDATE {$tablepre}settings SET value = '$newstyleid' WHERE variable = 'styleid'");
	$db->query("UPDATE {$tablepre}styles SET available = '0' WHERE styleid != '$newstyleid'");

	echo "��ϲ�������ɹ�,�����ɾ��������.";

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

?>