<?php

/*
	[Discuz!] Tools (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tools.php 377 2007-05-31 06:33:51Z kimi $
*/

foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = $_value;
	}
}

$tool_password = ''; //��������һ�����߰��ĸ�ǿ�����룬����Ϊ�գ�
$lockfile = 'forumdata/tool.lock';

define('DISCUZ_ROOT', dirname(__FILE__).'/');
define('VERSION', '1.300');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(0);


if(file_exists($lockfile)) {
	errorpage("�������ѹرգ�����ʹ����ͨ��FTP������");
} elseif ($tool_password == ''){
	errorpage('���벻��Ϊ�գ����޸ı��ļ���$tool_password�������룡');
}

if($_POST['action'] == 'login') {
	setcookie('toolpassword', $_POST['toolpassword'], 0);
	echo '<meta http-equiv="refresh" content="2 url=?">';
	errorpage("���Եȣ������¼�У�");
}

if(isset($_COOKIE['toolpassword'])) {
	if($_COOKIE['toolpassword'] != $tool_password) {
		errorpage("login");
	}
} else {
		errorpage("login");
}

$action = $_GET['action'];

if($action == 'repair') {
	$check = $_GET['check'];
	$nohtml = $_GET['nohtml'];
	$iterations = $_GET['iterations'];
	$simple = $_GET['simple'];

	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			exit("�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�");
		}
	}
	mysql_connect($dbhost, $dbuser, $dbpw);
	mysql_select_db($dbname);
	$counttables = $oktables = $errortables = $rapirtables = 0;

	if($check) {

	$tables=mysql_query("SHOW TABLES");

	if(!$nohtml) {
		echo "<HTML><HEAD></HEAD><BODY><table border=1 cellspacing=0 cellpadding=4 STYLE=\"font-family: Tahoma, Verdana; font-size: 11px\">";
	}

	if($iterations) {
		$iterations --;
	}
	while($table=mysql_fetch_row($tables)) {
		if(substr($table[0], -8) != 'sessions') {
			$counttables += 1;
			$answer=checktable($table[0],$iterations);
			if(!$nohtml) {
			echo "<tr><td colspan=4>&nbsp;</td></tr>";
			} elseif (!$simple) {
			flush();
			}
		}
	}

	if(!$nohtml) {
		echo "</table></BODY></HTML>";
	}

	if($simple) {
	htmlheader();
	echo '<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr><td>
		<p class="subtitle">Discuz! ����޸����ݿ� <ul>
		<center><p class="subtitle">�����
		<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;">
			<table width="100%" cellpadding="6" cellspacing="0" border="0">
				<tr align="center" class="header"><td width="25%">����(��)</td><td width="25%">������(��)</td><td width="25%">�����(��)</td><td width="25%">������(��)</td></tr>
				<tr align="center"><td width="25%"><?=$counttables?></td><td width="25%"><?=$oktables?></td><td width="25%"><?=$rapirtables?></td><td width="25%"><?=$errortables?></td></tr>
			</table>
		</div><br>�����û�д�����뷵�ع�������ҳ��֮������޸�<p><b><a href="?action=repair">�����޸�</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="?">������ҳ</a></b></center>

		<br><br>
		<p><font color="red">ע�⣺
		<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
		<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
		</td></tr></table>';
	}
	} else {
		htmlheader();
		echo '<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr><td>
				<p class="subtitle">Discuz! ����޸����ݿ� <ul>
				<p class="subtitle">˵����<p style="text-indent: 3em; margin: 0;">������ͨ������ķ�ʽ�޸��Ѿ��𻵵����ݿ⡣����������ĵȴ��޸������
				<p style="text-indent: 3em; margin: 0;">����������޸����������ݿ���󣬵��޷���֤�����޸����е����ݿ����(��Ҫ MySQL 3.23+)
				<br><br>
				<ul>
				<li> <a href="?action=repair&check=1&nohtml=1&simple=1">��鲢�����޸����ݿ�1��</a>
				<li> <a href="?action=repair&check=1&iterations=5&nohtml=1&simple=1">��鲢�����޸����ݿ�5��</a> (��Ϊ���ݿ��д��ϵ������ʱ��Ҫ���޸����β�����ȫ�޸��ɹ�)
				</ul>
				<p><font color="red">ע�⣺
				<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
				<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
				</td></tr></table>';
	}
	htmlfooter();
} elseif ($action == 'check') {
	htmlheader();
	//6.У�黷���Ƿ�֧��DZ/SS���鿴���ݿ�ͱ���ַ�����������Ϣ    charset,dbcharset, php,mysql,zend,php�̱��
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			exit("�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�");
		}
	}
	$curr_os = PHP_OS;

	if(!function_exists('mysql_connect')) {
		$curr_mysql = '��֧��';
		$msg .= "<li>���ķ�������֧��MySql���ݿ⣬�޷���װ��̳����</li>";
		$quit = TRUE;
	} else {
		if(@mysql_connect($dbhost, $dbuser, $dbpw)) {
			$curr_mysql =  mysql_get_server_info();
		} else {
			$curr_mysql = '֧��';
		}
	}

	$curr_php_version = PHP_VERSION;
	if($curr_php_version < '4.0.6') {
		$msg .= "<li>���� PHP �汾С�� 4.0.6, �޷�ʹ�� Discuz! / SuperSite��</li>";
	}
	if(!ini_get('short_open_tag')) {
		$curr_short_tag = '�ر�';
		$msg .='<li>�뽫 php.ini �е� short_open_tag ����Ϊ On�������޷�ʹ����̳��</li>';
	} else {
		$curr_short_tag = '����';
	}
	if(@ini_get(file_uploads)) {
		$max_size = @ini_get(upload_max_filesize);
		$curr_upload_status = '�������ϴ����������ߴ�: '.$max_size;
	} else {
		$msg .= "<li>�����ϴ�����ز�������������ֹ��</li>";
	}

	if(OPTIMIZER_VERSION < 3.0) {
		$msg .="<li>����ZEND�汾����3.x,���޷�ʹ��SuperSite.</li>";
	}
	

	$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';
	?>
	<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;">
	<table width="100%" border="0" cellpadding="6" cellspacing="0" align="center">
	<tr class="header"><td></td><td>Discuz! / SuperSite ��������</td><td>��ǰ������</td>
	</tr><tr class="option">
	<td class="altbg2">����ϵͳ</td>
	<td class="altbg1">����</td>
	<td class="altbg2"><?=$curr_os?></td>
	</tr><tr class="option">
	<td class="altbg2">PHP �汾</td>
	<td class="altbg1">4.0.6+</td>
	<td class="altbg2"><?=$curr_php_version?></td>
	</tr>
	<tr class="option">
	<td class="altbg2">�̱��״̬</td>
	<td class="altbg1">����</td>
	<td class="altbg2"><?=$curr_short_tag?></td></tr>
	<tr class="option">
	<td class="altbg2">MySQL ֧��</td>
	<td class="altbg1">֧��</td>
	<td class="altbg2"><?=$curr_mysql?></td></tr>
	<tr class="option">
	<td class="altbg2">ZEND ֧��</td>
	<td class="altbg1">֧��</td>
	<td class="altbg2"><?=OPTIMIZER_VERSION?></td>
	
	</tr><tr class="option">
	<td class="altbg2">���̿ռ�</td>
	<td class="altbg1">10M+</td>
	<td class="altbg2"><?=$curr_disk_space?></td>
	</tr><tr class="option">
	<td class="altbg2">�����ϴ�</td>
	<td class="altbg1">����</td>
	<td class="altbg2"><?=$curr_upload_status?></td>
	</tr>
	<?
	echo '<tr class="option"><td colspan="3" class="altbg2">';
	$msg == '' && $msg = '����������,û�з�������.';
	echo '<br>&nbsp;&nbsp;<font color="red">'.$msg.'</font></td></tr>';
	?>
	
	</table></div>
	<?
	htmlfooter();
} elseif ($action == 'filecheck') {
	require_once './include/common.inc.php';

	@set_time_limit(0);

	$do = isset($do) ? $do : 'advance';

	$lang = array(
		'filecheck_fullcheck' => '����δ֪�ļ�',
		'filecheck_fullcheck_select' => '����δ֪�ļ� - ѡ����Ҫ������Ŀ¼',
		'filecheck_fullcheck_selectall' => '[����ȫ��Ŀ¼]',
		'filecheck_fullcheck_start' => '��ʼʱ��:',
		'filecheck_fullcheck_current' => '��ǰʱ��:',
		'filecheck_fullcheck_end' => '����ʱ��:',
		'filecheck_fullcheck_file' => '��ǰ�ļ�:',
		'filecheck_fullcheck_foundfile' => '����δ֪�ļ���: ',
		'filecheck_fullcheck_nofound' => 'û�з����κ�δ֪�ļ�'
	);

	if(!$discuzfiles = @file('admin/discuzfiles.md5')) {
		cpmsg('filecheck_nofound_md5file');
	}
	htmlheader();
	if($do == 'advance') {
		$dirlist = array();
		$starttime = date('Y-m-d H:i:s');
		$cachelist = $templatelist = array();
		if(empty($checkdir)) {
			checkdirs('./');
		} elseif($checkdir == 'all') {
			echo "\n<script>var dirlist = ['./'];var runcount = 0;var foundfile = 0</script>";
		} else {
			$checkdir = str_replace('..', '', $checkdir);
			$checkdir = $checkdir{0} == '/' ? '.'.$checkdir : $checkdir;
			checkdirs($checkdir.'/');
			echo "\n<script>var dirlist = ['$checkdir/'];var runcount = 0;var foundfile = 0</script>";
		}

		echo '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>
			<p class="subtitle">����δ֪�ļ�<ul>
			<center><div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;">
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<tr><th colspan="2" class="header">'.(empty($checkdir) ? '<a href="tools.php?action=filecheck&do=advance&start=yes&checkdir=all">'.$lang['filecheck_fullcheck_selectall'].'</a>' : $lang['filecheck_fullcheck'].($checkdir != 'all' ? ' - '.$checkdir : '')).'</th></tr>
			<script language="JavaScript" src="include/javascript/common.js"></script>';
		if(empty($checkdir)) {
			echo '<tr><td class="altbg1"><br><ul>';
			foreach($dirlist as $dir) {
				$subcount = count(explode('/', $dir));
				echo '<li>'.str_repeat('-', ($subcount - 2) * 4);
				echo '<a href="tools.php?action=filecheck&do=advance&start=yes&checkdir='.rawurlencode($dir).'">'.basename($dir).'</a></li>';
			}
			echo '</ul></td></tr></table>';
		} else {
			echo '<tr><td class="altbg1">'.$lang['filecheck_fullcheck_start'].' '.$starttime.'<br><span id="msg"></span></td></tr><tr><td class="altbg2"><div id="checkresult"></div></td></tr></table>
				<iframe name="checkiframe" id="checkiframe" style="display: none"></iframe>';
			echo "<script>checkiframe.location = 'tools.php?action=filecheck&do=advancenext&start=yes&dir=' + dirlist[runcount];</script>";
		}
		htmlfooter();
		exit;
	} elseif($do == 'advancenext') {
		$nopass = 0;
		foreach($discuzfiles as $line) {
			$md5files[] = trim(substr($line, 34));
		}
		$foundfile = checkfullfiles($dir);

		echo "<script>";
		if($foundfile) {
			echo "parent.foundfile += $foundfile;";
		}
		echo "parent.runcount++;
		if(parent.dirlist.length > parent.runcount) {
			parent.checkiframe.location = 'tools.php?action=filecheck&do=advancenext&start=yes&dir=' + parent.dirlist[parent.runcount];
		} else {
			var msg = '';
			msg = '$lang[filecheck_fullcheck_end] ".addslashes(date('Y-m-d H:i:s'))."';
			if(parent.foundfile) {
				msg += '<br>$lang[filecheck_fullcheck_foundfile] ' + parent.foundfile;
			} else {
				msg += '<br>$lang[filecheck_fullcheck_nofound]';
			}
			parent.$('msg').innerHTML = msg;
		}</script>";
		exit;
	}
} elseif ($action == 'logout') {
	setcookie('toolpassword', '', -86400 * 365);
	errorpage("�˳��ɹ���");
} elseif ($action == 'mysqlclear') {
	ob_implicit_flush();

	define('IN_DISCUZ', TRUE);

	require './config.inc.php';
	require './include/db_'.$database.'.class.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

	if(!get_cfg_var('register_globals')) {
		@extract($_GET, EXTR_SKIP);
	}

	$rpp			=	"1000"; //ÿ�δ������������
	$totalrows		=	isset($totalrows) ? $totalrows : 0;
	$convertedrows	=	isset($convertedrows) ? $convertedrows : 0;
	$start			=	isset($start) && $start > 0 ? $start : 0;
	$sqlstart		=	isset($start) && $start > $convertedrows ? $start - $convertedrows : 0;
	$end			=	$start + $rpp - 1;
	$stay			=	isset($stay) ? $stay : 0;
	$converted		=	0;
	$step			=	isset($step) ? $step : 0;
	$info			=	isset($info) ? $info : '';
	$action			=	array(
						'1'=>'����ظ���������',
						'2'=>'���฽����������',
						'3'=>'�����Ա��������',
						'4'=>'��������������',
						'5'=>'���������������',
						'6'=>'������Ϣ����',
						'7'=>'���������������'
					);
	$steps			=	count($action);
	$actionnow		=	isset($action[$step]) ? $action[$step] : '����';
	$maxid			=	isset($maxid) ? $maxid : 0;
	$tableid		=	isset($tableid) ? $tableid : 1;

	htmlheader();
	if($step==0){
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td>
	<p class="subtitle">���ݿ������������� <ul>
	<center><div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<tr class="header"><td colspan="9">���ݿ���������������Ŀ��ϸ��Ϣ</td></tr>
	<tr align="center" style="background: #FFFFD9;">
	<td>Posts�������</td><td>Attachments�������</td>
	<td>Members�������</td><td>Forums�������</td>
	<td>Pms�������</td><td>Threads�������</td><td>���б������</td></tr><tr align="center">
	<td class="altbg2">[<a href="?action=mysqlclear&step=1&stay=1">��������</a>]</td>
	<td class="altbg1">[<a href="?action=mysqlclear&step=2&stay=1">��������</a>]</td>
	<td class="altbg2">[<a href="?action=mysqlclear&step=3&stay=1">��������</a>]</td>
	<td class="altbg1">[<a href="?action=mysqlclear&step=4&stay=1">��������</a>]</td>
	<td class="altbg2">[<a href="?action=mysqlclear&step=5&stay=1">��������</a>]</td>
	<td class="altbg2">[<a href="?action=mysqlclear&step=6&stay=1">��������</a>]</td>
	<td class="altbg1">[<a href="?action=mysqlclear&step=1&stay=0">ȫ������</a>]</td>
	</tr>
	</center></table></div>
	<p><font color="red">ע�⣺
	<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
	<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
	</td></tr></table>
	<?php
	} elseif ($step=='1'){

		$query = "SELECT pid,tid FROM {$tablepre}posts LIMIT ".$sqlstart.", $rpp";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT tid FROM {$tablepre}threads WHERE tid='".$post['tid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}posts WHERE pid='".$post['pid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='2'){

		$query = "SELECT aid,pid,attachment FROM {$tablepre}attachments LIMIT ".$sqlstart.", $rpp";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT pid FROM {$tablepre}posts WHERE pid='".$post['pid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}attachments WHERE aid='".$post['aid']."'");
						$attachmentdir = DISCUZ_ROOT.'./attachments/';
						@unlink($attachmentdir.$post['attachment']);
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='3'){

		$query = "SELECT uid FROM {$tablepre}memberfields LIMIT ".$sqlstart.", $rpp";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE uid='".$post['uid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}memberfields WHERE uid='".$post['uid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='4'){

		$query = "SELECT fid FROM {$tablepre}forumfields LIMIT ".$sqlstart.", $rpp";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE fid='".$post['fid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='".$post['fid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='5'){

		$query = "SELECT msgfromid,msgtoid FROM {$tablepre}pms LIMIT ".$sqlstart.", $rpp";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE uid='".$post['msgtoid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}pms WHERE msgtoid='".$post['msgtoid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='6'){

		$query = "SELECT tid FROM {$tablepre}threads LIMIT ".$sqlstart.", $rpp";
		$posts=$db->query($query);
			while ($threads = $db->fetch_array($posts)){
				$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0'");
				$replynum = $db->result($query, 0) - 1;
				if ($replynum < 0) {
					$db->query("DELETE FROM {$tablepre}threads WHERE tid='".$threads['tid']."'");
				} else {
					$query = $db->query("SELECT a.aid FROM {$tablepre}posts p, {$tablepre}attachments a WHERE a.tid='".$threads['tid']."' AND a.pid=p.pid AND p.invisible='0' LIMIT 1");
					$attachment = $db->num_rows($query) ? 1 : 0;//�޸�����
					$query  = $db->query("SELECT pid, subject, rate FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0' ORDER BY dateline LIMIT 1");
					$firstpost = $db->fetch_array($query);
					$firstpost['subject'] = addslashes($firstpost['subject']);
					@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);//�޸�����
					$query  = $db->query("SELECT author, dateline FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0' ORDER BY dateline DESC LIMIT 1");
					$lastpost = $db->fetch_array($query);//�޸������
					$db->query("UPDATE {$tablepre}threads SET subject='".$firstpost['subject']."', replies='$replynum', lastpost='".$lastpost['dateline']."', lastposter='".addslashes($lastpost['author'])."', rate='".$firstpost['rate']."', attachment='$attachment' WHERE tid='".$threads['tid']."'", 'UNBUFFERED');
					$db->query("UPDATE {$tablepre}posts SET first='1', subject='".$firstpost['subject']."' WHERE pid='".$firstpost['pid']."'", 'UNBUFFERED');
					$db->query("UPDATE {$tablepre}posts SET first='0' WHERE tid='".$threads['tid']."' AND pid<>'".$firstpost['pid']."'", 'UNBUFFERED');
				}
				$converted = 1;
				$totalrows ++;
			}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='7'){

		echo '<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<tr class="header"><td colspan="9">���������������</td></tr><tr align="center" class="category">
	<td>������������������.<br><br><font color="red">������ʹ�ñ������ʱ��,��ע��������ɾ�����ļ�!</font><br></td></tr></table></div>';

	}
	htmlfooter();
} elseif ($action == 'repair_auto') {
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			exit("�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�");
		}
	}
	mysql_connect($dbhost, $dbuser, $dbpw);
	mysql_select_db($dbname);
	@set_time_limit(0);
	$querysql = array(
		'activityapplies' => 'applyid',
		'adminnotes' => 'id',
		'advertisements' => 'advid',
		'announcements' => 'id',
		'attachments' => 'aid',
		'attachtypes' => 'id',
		'banned' => 'id',
		'bbcodes' => 'id',
		'crons' => 'cronid',
		'faqs' => 'id',
		'forumlinks' => 'id',
		'forums' => 'fid',
		'itempool' => 'id',
		'magicmarket' => 'mid',
		'magics' => 'magicid',
		'medals' => 'medalid',
		'members' => 'uid',
		'pluginhooks' => 'pluginhookid',
		'plugins' => 'pluginid',
		'pluginvars' => 'pluginvarid',
		'pms' => 'pmid',
		'pmsearchindex' => 'searchid',
		'polloptions' => 'polloptionid',
		'posts' => 'pid',
		'profilefields' => 'fieldid',
		'projects' => 'id',
		'ranks' => 'rankid',
		'searchindex' => 'searchid',
		'smilies' => 'id',
		'styles' => 'styleid',
		'stylevars' => 'stylevarid',
		'templates' => 'templateid',
		'threads' => 'tid',
		'threadtypes' => 'typeid',
		'tradecomments' => 'id',
		'typeoptions' => 'optionid',
		'words' => 'id'
	);

	htmlheader();
	echo '<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr><td>
		<p class="subtitle">Discuz! �������ֶ��޸� <ul>
		<center><p class="subtitle">�����
		<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;">
		<table width="100%" cellpadding="6" cellspacing="0" border="0">
		<tr align="center" class="header"><td width="25%">���ݱ���</td><td width="25%">�ֶ���</td><td width="25%">�Ƿ�����</td><td width="25%">������״̬</td></tr>';
	foreach($querysql as $key => $keyfield) {
		echo '<tr align="center"><td width="25%"  class="altbg2" align="left">'.$tablepre.$key.'</td><td width="25%" class="altbg1">'.$keyfield.'</td>';
		if($query = @mysql_query("Describe $tablepre$key $keyfield")) {
			$istableexist = '����';
			$field = @mysql_fetch_array($query);
			if(empty($field[5]) &&  $field[0] == $keyfield) {
				mysql_query("ALTER TABLE $tablepre$key CHANGE $keyfield $keyfield $field[1] NOT NULL AUTO_INCREMENT");
				$tablestate = '<font color="red">�Ѿ��޸�</font>';
			} else {
				$tablestate = '����';
			}
		} else {
			$istableexist = '������';
			$tablestate = '----';
		}
		echo '<td width="25%" class="altbg2">'.$istableexist.'</td><td width="25%" class="altbg1">'.$tablestate.'</td></tr>';
	}
	echo '</table>
		</div><br></center>

		<br><br>
		<p><font color="red">ע�⣺
		<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
		<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
		</td></tr></table>';
	htmlfooter();
} elseif ($action == 'restore') {
	ob_implicit_flush();

	define('IN_DISCUZ', TRUE);

	if(@(!include("./config.inc.php")) || @(!include('./include/db_'.$database.'.class.php'))) {
		if(@(!include("./config.php")) || @(!include('./include/db_'.$database.'.class.php'))) {
			exit("�����ϴ������°汾�ĳ����ļ��������б���������");
		}
	}

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

	if(!get_cfg_var('register_globals')) {
		@extract($HTTP_GET_VARS);
	}

	$sqldump = '';
	htmlheader();
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td>
	<p class="subtitle">���ݿ�ָ�ʵ�ù��� <ul>

	<?php
	echo "���������ڻָ��� Discuz! ���ݵ������ļ�,�� Discuz! ���������޷����кͻָ�����,<br>".
		"�� phpMyAdmin �ֲ��ָܻ����ļ�ʱ,�ɳ���ʹ�ô˹���.<br><br>".
		"ע��:<ul>".
		"<li>ֻ�ָܻ�����ڷ�����(Զ�̻򱾵�)�ϵ������ļ�,����������ݲ��ڷ�������,���� FTP �ϴ�</li>".
		"<li>�����ļ�����Ϊ Discuz! ������ʽ,��������Ӧ����ʹ PHP �ܹ���ȡ</li>".
		"<li>�뾡��ѡ�����������ʱ�β���,�Ա��ⳬʱ.����򳤾�(���� 10 ����)����Ӧ,��ˢ��</b></li></ul>";

	if($file) {
		if(strtolower(substr($file, 0, 7)) == "http://") {
			echo "��Զ�����ݿ�ָ����� - ��ȡԶ������:<br><br>";
			echo "��Զ�̷�������ȡ�ļ� ... ";

			$sqldump = @fread($fp, 99999999);
			@fclose($fp);
			if($sqldump) {
				echo "�ɹ�<br><br>";
			} elseif (!$multivol) {
				cexit("ʧ��<br><br><b>�޷��ָ�����</b>");
			}
		} else {
			echo "�ӱ��ػָ����� - ��������ļ�:<br><br>";
			if(file_exists($file)) {
				echo "�����ļ� $file ���ڼ�� ... �ɹ�<br><br>";
			} elseif (!$multivol) {
				cexit("�����ļ� $file ���ڼ�� ... ʧ��<br><br><br><b>�޷��ָ�����</b>");
			}

			if(is_readable($file)) {
				echo "�����ļ� $file �ɶ���� ... �ɹ�<br><br>";
				@$fp = fopen($file, "r");
				@flock($fp, 3);
				$sqldump = @fread($fp, filesize($file));
				@fclose($fp);
				echo "�ӱ��ض�ȡ���� ... �ɹ�<br><br>";
			} elseif (!$multivol) {
				cexit("�����ļ� $file �ɶ���� ... ʧ��<br><br><br><b>�޷��ָ�����</b>");
			}
		}

		if($multivol && !$sqldump) {
			cexit("�־��ݷ�Χ��� ... �ɹ�<br><br><b>��ϲ��,�����Ѿ�ȫ���ɹ��ָ�!��ȫ���,�����ɾ��������.</b>");
		}

		echo "�����ļ� $file ��ʽ��� ... ";
		@list(,,,$method, $volume) = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", preg_replace("/^(.+)/", "\\1", substr($sqldump, 0, 256)))));
		if($method == 'multivol' && is_numeric($volume)) {
			echo "�ɹ�<br><br>";
		} else {
			cexit("ʧ��<br><br><b>���ݷ� Discuz! �־��ݸ�ʽ,�޷��ָ�</b>");
		}

		if($onlysave == "yes") {
			echo "�������ļ����浽���ط����� ... ";
			$filename = DISCUZ_ROOT.'./forumdata'.strrchr($file, "/");
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
		if($auto == 'off'){
			$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);
			cexit("�����ļ� <b>$volume#</b> �ָ��ɹ�,�������Ҫ������ָ������������ļ�<br>����<b><a href=\"?action=restore&file=$nextfile&multivol=yes\">ȫ���ָ�</a></b>	�������ָ���һ�������ļ�<b><a href=\"?action=restore&file=$nextfile&multivol=yes&auto=off\">�����ָ���һ�����ļ�</a></b>");
		} else {
			$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);
			echo "�����ļ� <b>$volume#</b> �ָ��ɹ�,���ڽ��Զ����������־�������.<br><b>����ر���������жϱ���������</b>";
			redirect("?action=restore&file=$nextfile&multivol=yes");
		}
		}
	} else {
			$exportlog = array();
			if(is_dir(DISCUZ_ROOT.'./forumdata')) {
				$dir = dir(DISCUZ_ROOT.'./forumdata');
				while($entry = $dir->read()) {
					$entry = "./forumdata/$entry";
					if(is_file($entry) && preg_match("/\.sql/i", $entry)) {
						$filesize = filesize($entry);
						$fp = fopen($entry, 'rb');
						$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						fclose ($fp);
							if(preg_match("/\-1.sql/i", $entry) || $identify[3] == 'shell'){
								$exportlog[$identify[0]] = array(	'version' => $identify[1],
													'type' => $identify[2],
													'method' => $identify[3],
													'volume' => $identify[4],
													'filename' => $entry,
													'size' => $filesize);
							}
					} elseif (is_dir($entry) && preg_match("/backup\_/i", $entry)) {
						$bakdir = dir($entry);
							while($bakentry = $bakdir->read()) {
								$bakentry = "$entry/$bakentry";
								if(is_file($bakentry)){
									$fp = fopen($bakentry, 'rb');
									$bakidentify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
									fclose ($fp);
									if(preg_match("/\-1\.sql/i", $bakentry) || $bakidentify[3] == 'shell') {
										$identify['bakentry'] = $bakentry;
									}
								}
							}
							if(preg_match("/backup\_/i", $entry)){
								$exportlog[filemtime($entry)] = array(	'version' => $bakidentify[1],
													'type' => $bakidentify[2],
													'method' => $bakidentify[3],
													'volume' => $bakidentify[4],
													'bakentry' => $identify['bakentry'],
													'filename' => $entry);
							}
					}
				}
				$dir->close();
			} else {
				echo 'error';
			}
			krsort($exportlog);
			reset($exportlog);

			$exportinfo = '<br><center><div style="margin-top: 4px; border-top: 1px solid #7AC4EA; border-right: 1px solid #7AC4EA; border-left: 1px solid #7AC4EA; width: 80%;float:center;"><table width="100%" border="0" cellpadding="6" cellspacing="0">
	<tr class="header"><td colspan="9">������ݱ��ݼ�¼��ϸ��Ϣ</td></tr>
	<tr align="center" style="background: #FFFFD9;">
	<td>������Ŀ</td><td>�汾</td>
	<td>ʱ��</td><td>����</td>
	<td>�鿴</td><td>����</td></tr>';
			foreach($exportlog as $dateline => $info) {
				$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : 'δ֪';
					switch($info['type']) {
						case 'full':
							$info['type'] = 'ȫ������';
							break;
						case 'standard':
							$info['type'] = '��׼����(�Ƽ�)';
							break;
						case 'mini':
							$info['type'] = '��С����';
							break;
						case 'custom':
							$info['type'] = '�Զ��屸��';
							break;
					}
				//$info['size'] = sizecount($info['size']);
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['method'] == 'multivol' ? '���' : 'shell';
				$info['url'] = str_replace(".sql", '', str_replace("-$info[volume].sql", '', substr(strrchr($info['filename'], "/"), 1)));
				$exportinfo .= "<tr align=\"center\">\n".
					"<td class=\"altbg2\" align=\"left\">".$info['url']."</td>\n".
					"<td class=\"altbg1\">$info[version]</td>\n".
					"<td class=\"altbg2\">$info[dateline]</td>\n".
					"<td class=\"altbg1\">$info[type]</td>\n";
				if($info['bakentry']){
				$exportinfo .= "<td class=\"altbg2\"><a href=\"?action=restore&bakdirname=".$info['url']."\">�鿴</a></td>\n".
					"<td class=\"altbg1\"><a href=\"?action=restore&file=$info[bakentry]&importsubmit=yes\">[ȫ������]</a></td>\n</tr>\n";
				} else {
				$exportinfo .= "<td class=\"altbg2\"><a href=\"?action=restore&filedirname=".$info['url']."\">�鿴</a></td>\n".
					"<td class=\"altbg1\"><a href=\"?action=restore&file=$info[filename]&importsubmit=yes\">[ȫ������]</a></td>\n</tr>\n";
				}
			}
		$exportinfo .= '</center></table></div>';
		echo $exportinfo;
		unset($exportlog);
		unset($exportinfo);
		echo "<br>";
	//��ǰ�汾�����õ��ı������
	if(!empty($filedirname)){
			$exportlog = array();
			if(is_dir(DISCUZ_ROOT.'./forumdata')) {
					$dir = dir(DISCUZ_ROOT.'./forumdata');
					while($entry = $dir->read()) {
						$entry = "./forumdata/$entry";
						if(is_file($entry) && preg_match("/\.sql/i", $entry) && preg_match("/$filedirname/i", $entry)) {
							$filesize = filesize($entry);
							$fp = fopen($entry, 'rb');
							$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							fclose ($fp);

							$exportlog[$identify[0]] = array(	'version' => $identify[1],
												'type' => $identify[2],
												'method' => $identify[3],
												'volume' => $identify[4],
												'filename' => $entry,
												'size' => $filesize);
						}
					}
					$dir->close();
				} else {
				}
				krsort($exportlog);
				reset($exportlog);

				$exportinfo = '<br><center><div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;"><table	width="100%" border="0" cellpadding="6" cellspacing="0">
								<tr class="header"><td colspan="9">���ݱ��ݼ�¼</td></tr>
								<tr align="center" class="category">
								<td>�ļ���</td><td>�汾</td>
								<td>ʱ��</td><td>����</td>
								<td>��С</td><td>��ʽ</td>
								<td>���</td><td>����</td></tr>';
				foreach($exportlog as $dateline => $info) {
					$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : 'δ֪';
						switch($info['type']) {
							case 'full':
								$info['type'] = 'ȫ������';
								break;
							case 'standard':
								$info['type'] = '��׼����(�Ƽ�)';
								break;
							case 'mini':
								$info['type'] = '��С����';
								break;
							case 'custom':
								$info['type'] = '�Զ��屸��';
								break;
						}
					//$info['size'] = sizecount($info['size']);
					$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
					$info['method'] = $info['method'] == 'multivol' ? '���' : 'shell';
					$exportinfo .= "<tr align=\"center\">\n".
						"<td class=\"altbg2\" align=\"left\"><a href=\"$info[filename]\" name=\"".substr(strrchr($info['filename'], "/"), 1)."\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
						"<td class=\"altbg1\">$info[version]</td>\n".
						"<td class=\"altbg2\">$info[dateline]</td>\n".
						"<td class=\"altbg1\">$info[type]</td>\n".
						"<td class=\"altbg2\">".get_real_size($info[size])."</td>\n".
						"<td class=\"altbg1\">$info[method]</td>\n".
						"<td class=\"altbg2\">$info[volume]</td>\n".
						"<td class=\"altbg1\"><a href=\"?action=restore&file=$info[filename]&importsubmit=yes&auto=off\">[����]</a></td>\n</tr>\n";
				}
			$exportinfo .= '</center></table></div>';
			echo $exportinfo;
		}
	// 5.5�汾�õ�����ϸ�������
	if(!empty($bakdirname)){
			$exportlog = array();
			$filedirname = DISCUZ_ROOT.'./forumdata/'.$bakdirname;
			if(is_dir($filedirname)) {
					$dir = dir($filedirname);
					while($entry = $dir->read()) {
						$entry = $filedirname.'/'.$entry;
						if(is_file($entry) && preg_match("/\.sql/i", $entry)) {
							$filesize = filesize($entry);
							$fp = fopen($entry, 'rb');
							$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							fclose ($fp);

							$exportlog[$identify[0]] = array(	'version' => $identify[1],
												'type' => $identify[2],
												'method' => $identify[3],
												'volume' => $identify[4],
												'filename' => $entry,
												'size' => $filesize);
						}
					}
					$dir->close();
			} else {
				}
			krsort($exportlog);
			reset($exportlog);

			$exportinfo = '<br><center><div style="margin-top: 4px; border-top: 1px solid #7AC4EA; border-right: 1px solid #7AC4EA; border-left: 1px solid #7AC4EA; width: 80%;float:center;"><table width="100%" border="0" cellpadding="6" cellspacing="0">
					<tr class="header"><td colspan="9">���ݱ��ݼ�¼</td></tr>
					<tr align="center" style="background: #FFFFD9;">
					<td>�ļ���</td><td>�汾</td>
					<td>ʱ��</td><td>����</td>
					<td>��С</td><td>��ʽ</td>
					<td>���</td><td>����</td></tr>';
			foreach($exportlog as $dateline => $info) {
				$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : 'δ֪';
				switch($info['type']) {
					case 'full':
						$info['type'] = 'ȫ������';
						break;
					case 'standard':
						$info['type'] = '��׼����(�Ƽ�)';
						break;
					case 'mini':
						$info['type'] = '��С����';
						break;
					case 'custom':
						$info['type'] = '�Զ��屸��';
						break;
				}
				//$info['size'] = sizecount($info['size']);
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['method'] == 'multivol' ? '���' : 'shell';
				$exportinfo .= "<tr align=\"center\">\n".
						"<td class=\"altbg2\" align=\"left\"><a href=\"$info[filename]\" name=\"".substr(strrchr($info['filename'], "/"), 1)."\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
						"<td class=\"altbg1\">$info[version]</td>\n".
						"<td class=\"altbg2\">$info[dateline]</td>\n".
						"<td class=\"altbg1\">$info[type]</td>\n".
						"<td class=\"altbg2\">".get_real_size($info[size])."</td>\n".
						"<td class=\"altbg1\">$info[method]</td>\n".
						"<td class=\"altbg2\">$info[volume]</td>\n".
						"<td class=\"altbg1\"><a href=\"?action=restore&file=$info[filename]&importsubmit=yes&auto=off\">[����]</a></td>\n</tr>\n";
			}
			$exportinfo .= '</center></table></div>';
			echo $exportinfo;
		}
		echo "<br>";
		cexit("");
	}
} elseif ($action == 'replace') {
	htmlheader();
	$rpp			=	"500"; //ÿ�δ������������
	$totalrows		=	isset($totalrows) ? $totalrows : 0;
	$convertedrows	=	isset($convertedrows) ? $convertedrows : 0;
	$start			=	isset($start) && $start > 0 ? $start : 0;
	$end			=	$start + $rpp - 1;
	$converted		=	0;
	$maxid			=	isset($maxid) ? $maxid : 0;
	$threads_mod	=	isset($threads_mod) ? $threads_mod : 0;
	$threads_banned =	isset($threads_banned) ? $threads_banned : 0;
	$posts_mod		=	isset($posts_mod) ? $posts_mod : 0;
	ob_implicit_flush();
	define('IN_DISCUZ', TRUE);
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			exit("�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�");
		}
	}
	require './include/db_'.$database.'.class.php';
	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	if(isset($replacesubmit) || $start > 0) {
		$array_find = $array_replace = $array_findmod = $array_findbanned = array();
		$query = $db->query("SELECT find,replacement from {$tablepre}words");//������й���{BANNED}�Ż���վ {MOD}�Ž�����б�
		while($row = $db->fetch_array($query)) {
			$find = $row['find'];
			$replacement = $row['replacement'];
			if($replacement == '{BANNED}') {
				$array_findbanned[] = $find;
			} elseif($replacement == '{MOD}') {
				$array_findmod[] = $find;
			} else {
				$array_find[] = $find;
				$array_replace[] = $replacement;
			}

		}
		function topattern_array($source_array) { //����������
			$source_array = preg_replace("/\{(\d+)\}/",".{0,\\1}",$source_array);
			foreach($source_array as $key => $value) {
				$source_array[$key] = '/'.$value.'/i';
			}
			return $source_array;
		}
		$array_find = topattern_array($array_find);
		$array_findmod = topattern_array($array_findmod);
		$array_findbanned = topattern_array($array_findbanned);

		//��ѯposts��׼���滻
		$sql = "SELECT pid, tid, first, subject, message from {$tablepre}posts where pid > $start and pid < $end";
		$query = $db->query($sql);
		while($row = $db->fetch_array($query)) {
			$pid = $row['pid'];
			$tid = $row['tid'];
			$subject = $row['subject'];
			$message = $row['message'];
			$first = $row['first'];
			$displayorder = 0;//  -2��� -1����վ
			if(count($array_findmod) > 0) {
				foreach($array_findmod as $value){
					if(preg_match($value,$subject.$message)){
						$displayorder = '-2';
						break;
					}
				}
			} 
			if(count($array_findbanned) > 0) {
				foreach($array_findbanned as $value){
					if(preg_match($value,$subject.$message)){
						$displayorder = '-1';
						break;
					}
				}
			}
			if($displayorder < 0) {
				if($displayorder == '-2' && $first == 0) {//��������Ƶ���˻ظ�
					$posts_mod ++;
					$db->query("UPDATE {$tablepre}posts SET invisible = '$displayorder' WHERE pid = $pid");
				} else {
					if($db->affected_rows($db->query("UPDATE {$tablepre}threads SET displayorder = '$displayorder' WHERE tid = $tid and displayorder >= 0")) > 0) {
						$displayorder == '-2' && $threads_mod ++;
						$displayorder == '-1' && $threads_banned ++;
					}
				}
			}

			$subject = preg_replace($array_find,$array_replace,addslashes($subject));
			$message = preg_replace($array_find,$array_replace,addslashes($message));
			if($subject != addslashes($row['subject']) || $message != addslashes($row['message'])) {
				if($db->query("UPDATE {$tablepre}posts SET subject = '$subject', message = '$message' WHERE pid = $pid")) {
					$convertedrows ++;
				}
			}
			
			$converted = 1;
		}
		if($converted) {
			continue_redirect('replace',"&replacesubmit=1&threads_banned=$threads_banned&threads_mod=$threads_mod&posts_mod=$posts_mod");
		} else {
			echo "	<table width=\"80%\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">
						<tr class=\"header\">
							<td>�����滻���</td>
						</tr>";
			$threads_banned > 0 && print("<tr class=\"altbg1\"><td><br><li><font color=\"red\">".$threads_banned."</font>�����ⱻ�������վ.</li><center><br></td></tr>");
			$threads_mod > 0 && print("<tr class=\"altbg1\"><td><br><li><font color=\"red\">".$threads_mod."</font>�����ⱻ��������б�.</li><br></td></tr>");
			$posts_mod > 0 && print("<tr class=\"altbg1\"><td><br><li><font color=\"red\">".$posts_mod."</font>���ظ�����������б�.</li><br></td></tr>");
			echo "<tr class=\"altbg1\"><td><br><li>�滻��<font color=\"red\">".$convertedrows."</font>������</li><br></td></tr>";
			echo "</table>";
		}
	} else {
		$query = $db->query("select * from {$tablepre}words");
		$i = 1;
		if($db->num_rows($query) < 1) {
			echo "<center><br><br><font color=\"red\">�Բ���,���ڻ�û�й��˹���,�������̳��̨����.</font><br><br></center>";
			htmlfooter();
			exit;
		}
	?>
		<form method="post" action="tools.php?action=replace">
				<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 60%;float:center;">
				<table width="100%" border="0" cellpadding="6" cellspacing="0" align="center">
					<tr class="header"><td colspan="3">�����滻��������</td></tr>
					<tr align="center" style="background: #FFFFD9;">
						<td align="center" width="30">���</td>
						<td align="center">��������</td>
						<td align="center">�滻Ϊ</td></tr>
					<?
						while($row = $db->fetch_array($query)) {
					?>
					<tr>
						<td align="center" class="altbg2"><?=$i++?></td>
						<td align="center" class="altbg1"><?=$row['find']?></td>
						<td align="center" class="altbg2"><?=$row['replacement']?></td>
					</tr>
					<?}?>
				</table></div><br><br>
				<center><input type="submit" name=replacesubmit value="��ʼ�滻"></center><br>
		</form>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td>
	<p><font color="red">ע�⣺
	<br><p style="text-indent: 3em; margin: 0;">������ᰴ����̳���й��˹������������������.�����޸������̳��̨��
	<br><p style="text-indent: 3em; margin: 0;">�ϱ��г�������̳��ǰ�Ĺ��˴���.</p></font>
	</td></tr></table>
	<?
	}
	htmlfooter();
} elseif ($action == 'runquery') {
	define('IN_DISCUZ',TRUE);
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			exit("�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�");
		}
	}
	if($admincp['runquery'] != 1) {
		errorpage('ʹ�ô˹�����Ҫ�� config.inc.php ���е� $admincp[\'runquery\'] �����޸�Ϊ 1��');
	} else {
		if(!empty($_POST['sqlsubmit']) && $_POST['queries']) {
			mysql_connect($dbhost, $dbuser, $dbpw);
			mysql_select_db($dbname);
			if(mysql_query(stripslashes($_POST[queries]))) {
				errorpage("���ݿ������ɹ�,Ӱ������ &nbsp;".mysql_affected_rows());
				if(strpos($_POST[queries],'settings')) {
					require_once './include/common.inc.php';
					require_once './include/cache.func.php';
					updatecache('settings');
				}
			} else {
				errorpage("���ݿ�����ʧ��,mysql������ʾ:.<br />".mysql_error().'<br><br><br><a href="javascript:history.go(-1);" >[ ������ﷵ����һҳ ]</a>');
			}
		}
		htmlheader();
		echo "<form method=\"post\" action=\"tools.php?action=runquery\">
		<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
		<tr class=\"header\"><td colspan=2>Discuz! ���ݿ����� - �뽫���ݿ��������ճ��������</td></tr>
		<tr class=\"altbg1\">
		<td valign=\"top\">
		<div align=\"center\">
		<br /><select name=\"queryselect\" style=\"width:35%\" onChange=\"queries.value = this.value\">
			<option value = ''>��ѡ��TOOLS�����������</option>
			<option value = \"REPLACE INTO ".$tablepre."settings (variable, value) VALUES ('seccodestatus', '0')\">�ر�������֤�빦��</option>
			<option value = \"REPLACE INTO ".$tablepre."settings (variable, value) VALUES ('supe_status', '1')\">�ر���̳�е�supersite����</option>
		</select>
		<br />
		<br /><textarea cols=\"85\" rows=\"10\" name=\"queries\">$queries</textarea><br />
		<br /></div>
		<br /><center><input class=\"button\" type=\"submit\" name=\"sqlsubmit\" value=\"�ύ\"></center><br>
		</td></tr></table>
		</form>";	
	}
	htmlfooter();
} elseif ($action == 'setadmin') {
	$info = "������Ҫ���óɹ���Ա���û���";
	htmlheader();
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td>
	<p class="subtitle">Discuz! admin�û������һغ����� <ul>

	<?php

	if(!empty($_POST['loginsubmit'])){
		require './config.inc.php';
		mysql_connect($dbhost, $dbuser, $dbpw);
		mysql_select_db($dbname);
		$passwordsql = empty($_POST['password']) ? '' : ', password = \''.md5($_POST['password']).'\'';
		$passwordsql .= empty($_POST['issecques']) ? '' : ', secques = \'\'';
		$passwordinfo = empty($_POST['password']) ? '���뱣�ֲ���' : '���������޸�Ϊ'.$_POST['password'].'';
		$query = "UPDATE {$tablepre}members SET adminid='1', groupid='1' $passwordsql WHERE $_POST[loginfield] = '$_POST[username]' limit 1";
			if(mysql_query($query)){
				$mysql_affected_rows = mysql_affected_rows();
				if($mysql_affected_rows == 0){
				$info = '<font color="red">�޴��û��������û����Ƿ���ȷ<br><br>��������ע�ᣬ����</font><a href="?action=setadmin">��������</a>';
				} elseif ($mysql_affected_rows > 0){
				$info = "��$_POST[loginfield]Ϊ$_POST[username]���û��Ѿ����óɹ���Ա��$passwordinfo";
				}
			} else {
			$info = '<font color="red">ʧ������Mysql����config.inc.php</font>';
			}
	?>
	<center><p class="subtitle">
	<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;">
	<form action="?action=setadmin" method="post"><input type="hidden" name="action" value="login" />
		<table width="100%" cellpadding="6" cellspacing="0" border="0">
			<tr align="left" class="header"><td><center>��ʾ��Ϣ</center></td></tr>
			<tr align="center"><td><br><?=$info?></td></tr>
		</table>
	</form>
	<?php
	} else {?>
	<center><p class="subtitle"><?=$info?>
	<div style="margin-top: 4px; width: 80%;">
	<form action="?action=setadmin" method="post">
		<table width="100%" cellpadding="6" cellspacing="0" border="0" style="border: 1px solid #7AC4EA;">
			<tr colspan="1" class="header"><td width="30%">��Ϣ</td><td width="70%">��д</td></tr>
			<tr align="left"><td class="altbg1" width="30%"><span class="bold">	<input type="radio" name="loginfield" value="username" checked class="radio">�û���<input type="radio" name="loginfield" value="uid" class="radio">UID</span></td><td class="altbg2" width="70%"><input type="text" name="username" size="25" maxlength="40"></td></tr>
			<tr align="left"><td class="altbg1" width="30%"><div style="padding-left: 5px;">����������</div></td><td class="altbg2" width="70%"><input type="text" name="password" size="25"></td></tr>
			<tr align="left"><td class="altbg1" width="30%"><div style="padding-left: 5px;">�Ƿ������ȫ����</div></td><td class="altbg2" width="70%"><span class="bold"><input type="radio" name="issecques" value="1" checked class="radio">��<input type="radio" name="issecques" value="" class="radio">��</span></td></tr>
			<th colspan="2" class="altbg1" align="center"><input type="submit" name="loginsubmit" value="�� &nbsp; ��"></th>
		</table>
	</form>
	<?php
	}?>
	</div></center>
	<br><br>
	<p><font color="red">ע�⣺
	<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
	<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
	</td></tr></table>
	<?php
	htmlfooter();
} elseif ($action == 'setlock') {
	touch($lockfile);
	if(file_exists($lockfile)) {
		echo '<meta http-equiv="refresh" content="3 url=?">';
		errorpage("�ɹ��رչ����䣡<br>ǿ�ҽ������ڲ���Ҫ�������ʱ��ʱ����ɾ��");
	} else {
		errorpage('ע������Ŀ¼û��д��Ȩ�ޣ������޷������ṩ��ȫ���ϣ���ɾ����̳��Ŀ¼�µ�tool.php�ļ���');
	}
} elseif ($action == 'testmail') {
	$msg = '';

	if($_POST['action'] == 'save') {

		if(is_writeable('./mail_config.inc.php')) {

			$_POST['sendmail_silent_new'] = intval($_POST['sendmail_silent_new']);
			$_POST['mailsend_new'] = intval($_POST['mailsend_new']);
			$_POST['maildelimiter_new'] = intval($_POST['maildelimiter_new']);
			$_POST['mailusername_new'] = intval($_POST['mailusername_new']);
			$_POST['mailcfg_new']['server'] = addslashes($_POST['mailcfg_new']['server']);
			$_POST['mailcfg_new']['port'] = intval($_POST['mailcfg_new']['port']);
			$_POST['mailcfg_new']['auth'] = intval($_POST['mailcfg_new']['auth']);
			$_POST['mailcfg_new']['from'] = addslashes($_POST['mailcfg_new']['from']);
			$_POST['mailcfg_new']['auth_username'] = addslashes($_POST['mailcfg_new']['auth_username']);
			$_POST['mailcfg_new']['auth_password'] = addslashes($_POST['mailcfg_new']['auth_password']);

	$savedata = <<<EOF
	<?php

	\$sendmail_silent = $_POST[sendmail_silent_new];
	\$maildelimiter = $_POST[maildelimiter_new];
	\$mailusername = $_POST[mailusername_new];
	\$mailsend = $_POST[mailsend_new];

EOF;

			if($_POST['mailsend_new'] == 2) {

	$savedata .= <<<EOF

	\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
	\$mailcfg['port'] = {$_POST[mailcfg_new][port]};
	\$mailcfg['auth'] = {$_POST[mailcfg_new][auth]};
	\$mailcfg['from'] = '{$_POST[mailcfg_new][from]}';
	\$mailcfg['auth_username'] = '{$_POST[mailcfg_new][auth_username]}';
	\$mailcfg['auth_password'] = '{$_POST[mailcfg_new][auth_password]}';

EOF;

			} elseif ($_POST['mailsend_new'] == 3) {

	$savedata .= <<<EOF

	\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
	\$mailcfg['port'] = '{$_POST[mailcfg_new][port]}';

EOF;

			}

			setcookie('mail_cfg', base64_encode(serialize($_POST['mailcfg_new'])), time() + 86400);

	$savedata .= <<<EOF

	?>
EOF;

			$fp = fopen('./mail_config.inc.php', 'w');
			fwrite($fp, $savedata);
			fclose($fp);

			$msg = '���ñ�����ϣ�';

			if($_POST['sendtest']) {

				define('IN_DISCUZ', true);

				define('DISCUZ_ROOT', './');
				define('TPLDIR', './templates/default');
				require './include/global.func.php';

				$test_tos = explode(',', $_POST['mailcfg_new']['test_to']);
				$date = date('Y-m-d H:i:s');

				switch($_POST['mailsend_new']) {
					case 1:
						$title = '��׼��ʽ���� Email';
						$message = "ͨ�� PHP ������ UNIX sendmail ����\n\n���� {$_POST['mailcfg_new']['test_from']}\n\n����ʱ�� ".$date;
						break;
					case 2:
						$title = 'ͨ�� SMTP ������(SOCKET)���� Email';
						$message = "ͨ�� SOCKET ���� SMTP ����������\n\n���� {$_POST['mailcfg_new']['test_from']}\n\n����ʱ�� ".$date;
						break;
					case 3:
						$title = 'ͨ�� PHP ���� SMTP ���� Email';
						$message = "ͨ�� PHP ���� SMTP ���� Email\n\n���� {$_POST['mailcfg_new']['test_from']}\n\n����ʱ�� ".$date;
						break;
				}

				$bbname = '�ʼ���������';
				sendmail($test_tos[0], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);
				$bbname = '�ʼ�Ⱥ������';
				sendmail($_POST['mailcfg_new']['test_to'], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);

				$msg = '���ñ�����ϣ�<br>����Ϊ��'.$title.' @ '.$date.'���Ĳ����ʼ��Ѿ�������';

			}

		} else {

			$msg = '�޷�д���ʼ������ļ� ./mail_config.inc.php��Ҫʹ�ñ����������ô��ļ��Ŀ�д��Ȩ�ޡ�';

		}

	}

	define('IN_DISCUZ', TRUE);
	htmlheader();
	
	if(@include("./discuz_version.php")) {
		if(substr(DISCUZ_VERSION, 0, 1) >= 6) {
			echo '<br>�������Ѿ��ƶ���Disuz!��̳��̨�����е��ʼ�����';
			htmlfooter();
			exit;
		}
	}

	@include './mail_config.inc.php';
	?>
	<script>
	function $(id) {
		return document.getElementById(id);
	}
	</script>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td>
	<p class="subtitle">Discuz! �ʼ�����/���Թ���<ul>
	<center><p class="subtitle">

	<?

	if($msg) {
		echo '<font color="#FF0000">'.$msg.'</font>';
	}

	?><div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;">
	<table width="100%" cellpadding="6" cellspacing="0" border="0">
	<form method="post">
	<input type="hidden" name="action" value="save"><input type="hidden" name="sendtest" value="0">
	<tr><th colspan="2" class="header">�ʼ�����/���Թ���</th></tr>
	<?

	$saved_mailcfg = empty($_COOKIE['mail_cfg']) ? array(
		'server' => 'smtp.21cn.com',
		'port' => '25',
		'auth' => 1,
		'from' => 'Discuz <username@21cn.com>',
		'auth_username' => 'username@21cn.com',
		'auth_password' => '2678hn',
		'test_from' => 'user <my@mydomain.com>',
		'test_to' => 'user1 <test1@test1.com>, user2 <test2@test2.net>'
	) : unserialize(base64_decode($_COOKIE['mail_cfg']));

	echo '<tr><td width="30%" class="altbg1">�����ʼ������е�ȫ��������ʾ</td><td class="altbg2">';
	echo ' <input class="checkbox" type="checkbox" name="sendmail_silent_new" value="1"'.($sendmail_silent ? ' checked' : '').'><br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">�ʼ�ͷ�ķָ���</td><td class="altbg2">';
	echo ' <input class="radio" type="radio" name="maildelimiter_new" value="1"'.($maildelimiter ? ' checked' : '').'> ʹ�� CRLF ��Ϊ�ָ���<br>';
	echo ' <input class="radio" type="radio" name="maildelimiter_new" value="0"'.(!$maildelimiter ? ' checked' : '').'> ʹ�� LF ��Ϊ�ָ���<br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">�ռ����а����û���</td><td class="altbg2">';
	echo ' <input class="checkbox" type="checkbox" name="mailusername_new" value="1"'.($mailusername ? ' checked' : '').'><br>';
	echo '</tr>';

	echo '<tr><td class="altbg1">�ʼ����ͷ�ʽ</td><td class="altbg2">';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="1"'.($mailsend == 1 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'none\';$(\'hidden2\').style.display=\'none\'"> ͨ�� PHP ������ UNIX sendmail ����(�Ƽ��˷�ʽ)<br>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="2"'.($mailsend == 2 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'\'"> ͨ�� SOCKET ���� SMTP ����������(֧�� ESMTP ��֤)<br>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="3"'.($mailsend == 3 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'none\'"> ͨ�� PHP ���� SMTP ���� Email(�� win32 ����Ч, ��֧�� ESMTP)<br>';
	echo '</tr>';

	$mailcfg['server'] = $mailcfg['server'] == '' ? $saved_mailcfg['server'] : $mailcfg['server'];
	$mailcfg['port'] = $mailcfg['port'] == '' ? $saved_mailcfg['port'] : $mailcfg['port'];
	$mailcfg['auth'] = $mailcfg['auth'] == '' ? $saved_mailcfg['auth'] : $mailcfg['auth'];
	$mailcfg['from'] = $mailcfg['from'] == '' ? $saved_mailcfg['from'] : $mailcfg['from'];
	$mailcfg['auth_username'] = $mailcfg['auth_username'] == '' ? $saved_mailcfg['auth_username'] : $mailcfg['auth_username'];
	$mailcfg['auth_password'] = $mailcfg['auth_password'] == '' ? $saved_mailcfg['auth_password'] : $mailcfg['auth_password'];

	echo '<tbody id="hidden1" style="display:'.($mailsend == 1 ? ' none' : '').'">';
	echo '<tr><td class="altbg1">SMTP ������</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[server]" value="'.$mailcfg['server'].'"><br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">SMTP �˿�, Ĭ�ϲ����޸�</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[port]" value="'.$mailcfg['port'].'"><br>';
	echo '</tr>';
	echo '</tbody>';
	echo '<tbody id="hidden2" style="display:'.($mailsend != 2 ? ' none' : '').'">';
	echo '<tr><td class="altbg1">�Ƿ���Ҫ AUTH LOGIN ��֤</td><td class="altbg2">';
	echo ' <input class="checkbox" type="checkbox" name="mailcfg_new[auth]" value="1"'.($mailcfg['auth'] ? ' checked' : '').'><br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">�����˵�ַ (�����Ҫ��֤,����Ϊ����������ַ)</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[from]" value="'.$mailcfg['from'].'"><br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">��֤�û���</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[auth_username]" value="'.$mailcfg['auth_username'].'"><br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">��֤����</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[auth_password]" value="'.$mailcfg['auth_password'].'"><br>';
	echo '</tr>';
	echo '</tbody>';

	?>
	<tr><td colspan="2" align="center" class="altbg2">
	<input class="button" type="submit" name="submit" value="��������">
	</td></tr>
	<?

	echo '<tr><td class="altbg1">���Է�����</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[test_from]" value="'.$saved_mailcfg['test_from'].'" size="30"><br>';
	echo '</tr>';
	echo '<tr><td class="altbg1">�����ռ���</td><td class="altbg2">';
	echo ' <input class="text" type="text" name="mailcfg_new[test_to]" value="'.$saved_mailcfg['test_to'].'" size="45"><br>';
	echo '</tr>';

	?>
	<tr><td colspan="2" align="center" class="altbg2">
	<input class="button" type="submit" name="submit" onclick="this.form.sendtest.value = 1" value="�������ò����Է���">
	</td></tr>
	</form>
	</table></div>
	<?php
	htmlfooter();
} else {
	htmlheader();
	?>

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td class="title">��ӭ��ʹ�� Discuz! ϵͳά��������<?=VERSION?></td></tr>
	<tr><td><br>

	<p class="subtitle">Discuz! ϵͳά�������书�ܽ���<ul>
	<p><ul>
	<li>�����޸�Discuz!���ݿ�
	<li>�Ż�����Discuz!���ݿ������Ƭ
	<li>����Discuz!���ݿⱸ���ļ�����ǰ������
	<li>�ָ���̳����ԱȨ��
	<li>���ݿ�������������
	<li>�����ʼ����ͷ�ʽ
	</ul>
	<p><font color="red">ע�⣺
	<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
	<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
	</td></tr></table>
	<?
	htmlfooter();}

function cexit($message){
	echo $message;
	echo '<br><br>
			<p><font color="red">ע�⣺
			<br><p style="text-indent: 3em; margin: 0;">�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������
			<br><p style="text-indent: 3em; margin: 0;">����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</p></font>
			</td></tr></table>';
	htmlfooter();
	exit();
}

function checktable($table, $loops = 0) {
	global $db, $nohtml, $simple, $counttables, $oktables, $errortables, $rapirtables;

	$result = mysql_query("CHECK TABLE $table");
	if(!$nohtml) {
		echo "<tr bgcolor='#CCCCCC'><td colspan=4 align='center'>������ݱ� Checking table $table</td></tr>";
		echo "<tr><td>Table</td><td>Operation</td><td>Type</td><td>Text</td></tr>";
	} else {
	if(!$simple) {
		echo "\n>>>>>>>>>>>>>Checking Table $table\n";
		echo "---------------------------------<br>\n";
	}
	}
	$error = 0;
	while($r = mysql_fetch_row($result)) {
	if($r[2] == 'error') {
		if($r[3] == "The handler for the table doesn't support check/repair") {
		$r[2] = 'status';
		$r[3] = 'This table does not support check/repair/optimize';
		unset($bgcolor);
		$nooptimize = 1;
		} else {
		$error = 1;
		$bgcolor = 'red';
		unset($nooptimize);
		}
		$view = '����';
		$errortables += 1;
	} else {
		unset($bgcolor);
		unset($nooptimize);
		$view = '����';
		if($r[3] == 'OK') {
		$oktables += 1;
		}
	}
	if(!$nohtml) {
		echo "<tr><td>$r[0]</td><td>$r[1]</td><td bgcolor='$bgcolor'>$r[2]</td><td>$r[3] / $view </td></tr>";
	} else {
		if(!$simple) {
		echo "$r[0] | $r[1] | $r[2] | $r[3]<br>\n";
		}
	}
	}

	if($error) {
	if(!$nohtml) {
		echo "<tr><td colspan=4 align='center'>�����޸��� / Repairing table $table</td></tr>";
	} else {
		if(!$simple) {
		echo ">>>>>>>>�����޸��� / Repairing Table $table<br>\n";
		}
	}
	$result2=mysql_query("REPAIR TABLE $table");
	while($r2 = mysql_fetch_row($result2)) {
	if($r2[3] == 'OK') {
		$bgcolor='blue';
		$rapirtables += 1;
	} else {
		unset($bgcolor);
	}
	if(!$nohtml) {
		echo "<tr><td>$r2[0]</td><td>$r2[1]</td><td>$r2[2]</td><td bgcolor='$bgcolor'>$r2[3]</td></tr>";
	} else {
		if(!$simple) {
			echo "$r2[0] | $r2[1] | $r2[2] | $r2[3]<br>\n";
		}
	}
	}
	}
	if(($result2[3]=='OK'||!$error)&&!$nooptimize) {
	if(!$nohtml) {
		echo "<tr><td colspan=4 align='center'>�Ż����ݱ� Optimizing table $table</td></tr>";
	} else {
		if(!$simple) {
		echo ">>>>>>>>>>>>>Optimizing Table $table<br>\n";
		}
	}
	$result3=mysql_query("OPTIMIZE TABLE $table");
	$error=0;
	while($r3=mysql_fetch_row($result3)) {
		if($r3[2]=='error') {
		$error=1;
		$bgcolor='red';
		} else {
		unset($bgcolor);
		}
		if(!$nohtml) {
		echo "<tr><td>$r3[0]</td><td>$r3[1]</td><td bgcolor='$bgcolor'>$r3[2]</td><td>$r3[3]</td></tr>";
		} else {
		if(!$simple) {
			echo "$r3[0] | $r3[1] | $r3[2] | $r3[3]<br><br>\n";
		}
		}
	}
	}
	if($error && $loops) {
		checktable($table,($loops-1));
	}
}


function checkfullfiles($currentdir) {
	global $db, $tablepre, $md5files, $cachelist, $templatelist, $lang, $nopass;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		$file = $currentdir != './' ? preg_replace('/^\.\//', '', $file) : $file;
		$mainsubdir = substr($file, 0, strpos($file, '/'));
		if($entry != '.' && $entry != '..') {
			echo "<script>parent.$('msg').innerHTML = '$lang[filecheck_fullcheck_current] ".addslashes(date('Y-m-d H:i:s')."<br>$lang[filecheck_fullcheck_file] $file")."';</script>\r\n";
			if(is_dir($file)) {
    				checkfullfiles($file.'/');
			} elseif(is_file($file) && !in_array($file, $md5files)) {
				$pass = FALSE;
				if(in_array($file, array('./favicon.ico', './config.inc.php', './mail_config.inc.php', './robots.txt'))) {
					$pass = TRUE;
				}
				if($entry == 'index.htm' && filesize($file) < 5) {
					$pass = TRUE;
				}

				switch($mainsubdir) {
					case 'attachments' :
						if(!preg_match('/\.(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'images' :
						if(preg_match('/\.(gif|jpg|jpeg|png|ttf|wav|css)$/i', $entry)) {
							$pass = TRUE;
						}
					case 'customavatars' :
						if(preg_match('/\.(gif|jpg|jpeg|png)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'mspace' :
						if(preg_match('/\.(gif|jpg|jpeg|png|css|ini)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'forumdata' :
						$forumdatasubdir = str_replace('forumdata', '', dirname($file));
						if(substr($forumdatasubdir, 0, 8) == '/backup_') {
							if(preg_match('/\.(zip|sql)$/i', $entry)) {
								$pass = TRUE;
							}
						} else {
							switch ($forumdatasubdir) {
								case '' :
									if(in_array($entry, array('dberror.log', 'install.lock'))) {
										$pass = TRUE;
									}
								break;
								case '/templates':
									if(empty($templatelist)) {
										$query = $db->query("SELECT templateid, directory FROM {$tablepre}templates");
										while($template = $db->fetch_array($query)) {
											$templatelist[$template['templateid']] = $template['directory'];
										}
									}
									$tmp = array();
									$entry = preg_replace('/(\d+)\_(\w+)\.tpl\.php/ie', '$tmp = array(\1,"\2");', $entry);
									if(!empty($tmp) && file_exists($templatelist[$tmp[0]].'/'.$tmp[1].'.htm')) {
										$pass = TRUE;
									}

								break;
								case '/logs':
									if(preg_match('/(runwizardlog|\_cplog|\_errorlog|\_banlog|\_illegallog|\_modslog|\_ratelog|\_medalslog)\.php$/i', $entry)) {
										$pass = TRUE;
									}
								break;
								case '/cache':
									if(preg_match('/\.php$/i', $entry)) {
										if(empty($cachelist)) {
											$cachelist = checkcachefiles('forumdata/cache/');
											foreach($cachelist[1] as $nopassfile => $value) {
												$nopass++;
												echo "<script>parent.$('checkresult').innerHTML += '$nopassfile<br>';</script>\r\n";
											}
										}
										$pass = TRUE;
									} elseif(preg_match('/\.(css|log)$/i', $entry)) {
										$pass = TRUE;
									}
								break;
								case '/threadcaches':
									if(preg_match('/\.htm$/i', $entry)) {
										$pass = TRUE;
									}
								break;
							}
						}

					break;
					case 'templates' :
						if(preg_match('/\.(lang\.php|htm)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'include' :
						if(preg_match('/\.table$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'ipdata' :
						if($entry == 'wry.dat' || preg_match('/\.txt$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'admin' :
						if(preg_match('/\.md5$/i', $entry)) {
							$pass = TRUE;
						}
					break;
				}

				if(!$pass) {
					$nopass++;
					echo "<script>parent.$('checkresult').innerHTML += '$file<br>';</script>\r\n";
				}
			}
			ob_flush();
    			flush();
		}
	}
	return $nopass;
}

function checkdirs($currentdir) {
	global $dirlist;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..') {
			if(is_dir($file)) {
				$dirlist[] = $file;
				checkdirs($file.'/');
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $authkey;
	$dir = opendir($currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = array();
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			$fp = fopen($file, "rb");
			$cachedata = fread($fp, filesize($file));
			fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Created: [\w\s,:]+\n\/\/Identify: (\w{32})\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$authkey) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = $addlist[$file] = '';
			}
		}

	}

	return array($showlist, $modifylist, $addlist);
}

function continue_redirect($action = 'mysqlclear', $extra = '') {
	global $scriptname, $step, $actionnow, $start, $end, $stay, $convertedrows, $totalrows, $maxid;
	$url = "?action=$action&step=".$step."&start=".($end + 1)."&stay=$stay&totalrows=$totalrows&convertedrows=$convertedrows&maxid=$maxid".$extra;
	$timeout = $GLOBALS['debug'] ? 5000 : 2000;
	echo "<script>\r\n";
	echo "<!--\r\n";
	echo "function redirect() {\r\n";
	echo "	window.location.replace('".$url."');\r\n";
	echo "}\r\n";
	echo "setTimeout('redirect();', $timeout);\r\n";
	echo "-->\r\n";
	echo "</script>\r\n";
	echo '<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<tr class="header"><td colspan="9">���ڽ���'.$actionnow.'</td></tr><tr align="center" class="category"><td>';
	echo "���ڼ�� $start ---- $end �� <div style=\"float: right;\">[<a href='?action=mysqlclear' style='color:red'>ֹͣ����</a>]</div>";
	echo "<br><br><a href=\"".$url."\">��������������ʱ��û���Զ���ת���������</a>";
	echo '</td></tr></table></div>';
}

function dirsize($dir) {
	$dh = @opendir($dir);
	$size = 0;
	while($file = @readdir($dh)) {
		if ($file != '.' && $file != '..') {
			$path = $dir.'/'.$file;
			if (@is_dir($path)) {
				$size += dirsize($path);
			} else {
				$size += @filesize($path);
			}
		}
	}
	@closedir($dh);
	return $size;
}

function get_real_size($size) {

	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;

	if($size < $kb) {
		return $size.' Byte';
	} else if($size < $mb) {
		return round($size/$kb,2).' KB';
	} else if($size < $gb) {
		return round($size/$mb,2).' MB';
	} else if($size < $tb) {
		return round($size/$gb,2).' GB';
	} else {
		return round($size/$tb,2).' TB';
	}
}

function htmlheader(){
	echo '<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		<title>Discuz! ϵͳά��������</title>
		<style type="text/css">
		<!--
		body {
			margin: 0px;
			scrollbar-base-color: #F5FBFF;
			scrollbar-arrow-color: #7AC4EA;
			font: 12px Tahoma, Verdana;
			background-color: #FFFFFF;
			color: #333333;
		}
		td {
			font: 12px Tahoma, Verdana;
		}
		a {
			text-decoration: none;
			color: #154BA0;
		}
		a:hover {
			text-decoration: underline;
		}
		.header {
			font: 12px Arial, Tahoma !important;
			font-weight: bold !important;
			font: 11px Arial, Tahoma;
			font-weight: bold;
			color: #154BA0;
			background: #C0E4F7;
			height: 30px;
			padding-left: 10px;
		}
		.header td {
			padding-left: 10px;
		}
		.header a {
			color: #154BA0;
		}
		.mainborder {
			clear: both;
			height: 8px;
			font-size: 0px;
			line-height: 0px;
			padding: 0px;
			background-color: #154BA0;
		}
		.headerline {
			font-size: 0px;
			line-height: 0px;
			padding: 0px;
			background: #F5FBFF;
		}
		.footerline div {
			background-color: #FFFFFF;
			position: relative;
			float: right;
			right: 40px;
		}

		.spaceborder {
			width: 100%;
			border: 1px solid #7AC4EA;
			padding: 1px;
			clear: both;
		}
		.maintable{
			width: 95%;
			font: 12px Tahoma, Verdana;
		}
		ul {
			font-size: 12px;
			color: #666666;
			margin-left: 12px;
		}
		li {
			margin-left: 22px;
		}
		pre {
			font-size: 12px;
			font-family: Courier, Courier New;
			font-weight: normal;
			color:#000000;
		}
		.code {
			background: #EFEFEF;
			border: 1px solid #CCCCCC;
		}
		.title {
			font-size: 16px;
			border-bottom: 1px dashed #999999;
			font-weight:bold; color:#333399;
		}
		.subtitle {
			font-size: 14px;
			font-weight: bold;
			color: #000000;
		}
		input, select, textarea {
		font: 12px Tahoma, Verdana;
		color: #333333;
		font-weight: normal;
		background-color: #F5FBFF;
		border: 1px solid #7AC4EA;
		}
		.checkbox, .radio {
		border: 0px;
		background: none;
		vertical-align: middle;
		height: 16px;
		}
		input {
		height: 21px;
		}
		.submitbutton {
		margin-top: 8px !important;
		margin-top: 6px;
		margin-bottom: 5px;
		text-align: left;
		}
		.bold {
		font-weight: bold;
		}
		.altbg1	{
		background: #F5FBFF;
		font: 12px Tahoma, Verdana;
		}
		td.altbg1 {
		border-bottom: 1px solid #BBE9FF;
		}
		.altbg2 {
		background: #FFFFFF;
		font: 12px Tahoma, Verdana;
		}
		td.altbg2 {
		border-bottom: 1px solid #BBE9FF;
		}
		-->
		</style>
		</head>

		<body leftmargin="0" rightmargin="0" topmargin="0">
		<div class="mainborder"></div>
		<div class="headerline" style="height: 6px"></div>
		<center><div class="maintable">
		<br><div class="spaceborder"><table cellspacing="0" cellpadding="4" width="100%" align="center">
		<tr><td class="header" colspan="2">Discuz! ϵͳά��������</td></tr><tr><td bgcolor="#F8F8F8" align="left">
		[ <b><a href="?" target="_self">��������ҳ</a></b> ]
		[ <b><a href="?action=setlock" target="_self"><font color="red">����������</font></a></b> ] &nbsp; &raquo; &nbsp;
		</td><td bgcolor="#F8F8F8" align="center">
		[ <b><a href="?action=repair" target="_self">�����޸����ݿ�</a></b> ]
		[ <b><a href="?action=restore" target="_self">�������ݿⱸ��</a></b> ]
		[ <b><a href="?action=setadmin" target="_self">���ù���Ա�ʺ�</a></b> ]
		[ <b><a href="?action=testmail" target="_self">�ʼ����ò���</a></b> ]
		[ <b><a href="?action=mysqlclear" target="_self">���ݿ�������������</a></b> ]
		<br>
		[ <b><a href="?action=filecheck" target="_self">Discuz!δ֪�ļ�����</a></b> ]
		[ <b><a href="?action=runquery" target="_self">Mysql�������ݿ�</a></b> ]
		[ <b><a href="?action=replace" target="_self">�������������滻</a></b> ]
		[ <b><a href="?action=check" target="_self">ϵͳ�������</a></b> ]
		[ <b><a href="tools.php?action=repair_auto">�ֶ��������޸�</a></b> ]
		[ <b><a href="?action=logout" target="_self">�˳�</a></b> ]
		</td></tr></table></div><br><br>';
}

function htmlfooter(){
	echo '
		</div></center><br><br><br><br></td></tr><tr><td colspan="3" style="padding: 1">
		<table cellspacing="0" cellpadding="4" width="100%"><tr bgcolor="#F5FBFF">
		<td align="center" class="smalltxt"><font color="#666666">Discuz! Board ϵͳά�������� &nbsp;
		��Ȩ���� &copy;2001-2007 <a href="http://www.comsenz.com" style="color: #888888; text-decoration: none">
		��ʢ����(����)�Ƽ����޹�˾ Comsenz Inc</a>.</font></td></tr><tr style="font-size: 0px; line-height: 0px; spacing: 0px; padding: 0px; background-color: #698CC3">
		</table></td></tr></table><div class="mainborder" style="height: 6px"></div>
		</body>
		</html>';
}

function errorpage($message){
	htmlheader();
	if($message == 'login'){
		$message ='������Disucz!���߰��ĵ�¼���룡<div style="margin-top: 4px; width: 80%;">
				<form action="?" method="post">
					<input type="password" name="toolpassword"></input>
					<input type="submit" value="submit"></input>
					<input type="hidden" name="action" value="login">
				</form>
				</div>';
	}
	echo "<br><br><br><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
		<tr><td>
		<center><p class=\"subtitle\"><font color=\"red\">$message</font></center>
		</td></tr></table>";
	htmlfooter();
	exit();
}

function redirect($url) {
	echo "<script>";
	echo "function redirect() {window.location.replace('$url');}\n";
	echo "setTimeout('redirect();', 2000);\n";
	echo "</script>";
	echo "<br><br><a href=\"$url\">������������û���Զ���ת����������</a>";
	cexit("");
}

function splitsql($sql){
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

function stay_redirect() {
	global $action, $actionnow, $step, $stay;
	$nextstep = $step + 1;
	echo '<div style="margin-top: 4px; border: 1px solid #7AC4EA; width: 80%;float:center;">
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<tr class="header"><td colspan="9">���ڽ���'.$actionnow.'</td></tr><tr align="center" class="category">
			<td>';
	if($stay) {
		$actions = isset($action[$nextstep]) ? $action[$nextstep] : '����';
		echo "$actionnow �������.".($stay == 1 ? "&nbsp;&nbsp;&nbsp;&nbsp;" : '').'<br><br>';
		echo "<a href='?action=mysqlclear&step=".$nextstep."&stay=1'>���������һ������( $actions )���������</a><br>";
	} else {
		if(isset($action[$nextstep])) {
			echo '�������룺'.$action[$nextstep].'......';
		}
		$timeout = $GLOBALS['debug'] ? 5000 : 2000;
		echo "<script>\r\n";
		echo "<!--\r\n";
		echo "function redirect() {\r\n";
		echo "	window.location.replace('?action=mysqlclear&step=".$nextstep."');\r\n";
		echo "}\r\n";
		echo "setTimeout('redirect();', $timeout);\r\n";
		echo "-->\r\n";
		echo "</script>\r\n";
		echo "<div style=\"float: right;\">[<a href='?action=mysqlclear' style='color:red'>ֹͣ����</a>]</div><br><br><a href=\"".$scriptname."?step=".$nextstep."\">��������������ʱ��û���Զ���ת���������</a>";
	}

	echo '</td></tr></table></div>';
}
?>