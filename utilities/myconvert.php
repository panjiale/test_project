<?php

// Upgrade Discuz! Board from 4.1.0 to 5.0.0

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("�����ϴ������°汾�ĳ����ļ��������б���������");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

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
$start = $_GET['start'];

$upgrademsg = array(
	1 => '��̳������ 1 ��: �����ҵ�����<br>',
	2 => '��̳������ 2 ��: �����ҵĻظ�<br>',
	3 => '��̳������ 3 ��: ����ҵĻظ�<br>',
	4 => '��̳������ 4 ��: ȫ���������<br>',
);

if(!$action) {
	echo 	"���������ڵ��� Discuz!5.0.0 �ҵ����⹦��, ��ȷ��֮ǰ�Ѿ�˳�������� Discuz!5.0.0 <br><br>",
		"<b><font color=\"red\">�������������� Discuz!5.0.0 �ҵ����⹦��</font></b><br>",
		"<b><font color=\"red\">��ȷ���Ѿ��ϴ� Discuz!5.0.0 ��ȫ���ļ���Ŀ¼�������ɹ�</font></b><br>",
		"<b><font color=\"red\">����ǰ�������� JavaScript ֧��,�����������Զ���ɵ�,�����˹�����͸�Ԥ.<br>����֮ǰ��ر������ݿ�����,������ܲ����޷��ָ��ĺ��!</font></b><br><br>",
		"��ȷ����������Ϊ:<br><ol><li>ȷ����̳�����ɹ����ر���̳<li>�ϴ�������������̳��װĿ¼��<li>���б�����,ֱ������������ɵ���ʾ</ol><br><br>",
		"<a href=\"$PHP_SELF?action=upgrade&step=1\">�������ȷ���������Ĳ���,�����������</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$step = intval($step);
	echo $upgrademsg[$step];
	flush();

	$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable IN ('myrecorddays')");
	$myrecorddays = $db->result($query, 0);
	$converttimp = time() - $myrecorddays * 86400;

	if($step == 1) {

		$limit = 500; // ÿ�ε����Ա��
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE lastactivity>$converttimp AND posts>0 LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$authorid[] = $data;
		}

		if(is_array($authorid)) {
			foreach($authorid as $author) {
				$query = $db->query("SELECT tid,dateline,authorid FROM {$tablepre}threads WHERE authorid=$author[uid] AND dateline>$converttimp");
				while($data = $db->fetch_array($query)) {
					$threads[] = $data;
				}
			}
		}

		if(is_array($threads)) {
			foreach($threads as $thread) {
				$db->query("REPLACE INTO {$tablepre}mythreads (uid, tid, dateline) VALUES ('$thread[authorid]', '$thread[tid]', '$thread[dateline]')", 'UNBUFFERED');
			}
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "�� $step �������ɹ�<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 2) {

		$limit = 500; // ÿ�ε����Ա��
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE lastactivity>$converttimp AND posts>0 LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$authorid[] = $data;
		}

		if(is_array($authorid)) {
			foreach($authorid as $author) {
				$query = $db->query("SELECT pid,tid,dateline,authorid FROM {$tablepre}posts WHERE authorid=$author[uid] AND dateline>$converttimp AND first!='1'");
				while($data = $db->fetch_array($query)) {
					$posts[] = $data;
				}
			}
		}

		if(is_array($posts)) {
			foreach($posts as $post) {
				$db->query("REPLACE INTO {$tablepre}myposts (uid, tid, pid, position, dateline) VALUES ('$post[authorid]', '$post[tid]', '$post[pid]', '".($thread['replies'] + 1)."', '$post[dateline]')", 'UNBUFFERED');
			}
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "�� $step �������ɹ�<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 3) {

		$limit = 500; // ÿ�ε����Ա��
		$next = FALSE;
		$start = $start ? intval($start) : 0;

		$query = $db->query("SELECT tid FROM {$tablepre}myposts LIMIT $start, $limit");
		while($data = $db->fetch_array($query)) {
			$next = TRUE;
			$threadid[] = $data;
		}

		if(is_array($threadid)) {
			foreach($threadid as $thread) {
				$query = $db->query("SELECT tid,authorid,replies FROM {$tablepre}threads WHERE tid=$thread[tid]");
				while($data = $db->fetch_array($query)) {
					$threads[] = $data;
				}
			}
		}

		if(is_array($threads)) {
			foreach($threads as $thread) {
				$position = $thread['replies'] + 1;
				$db->query("UPDATE {$tablepre}myposts SET position='$position' WHERE tid='$thread[tid]' AND uid='$thread[authorid]'", 'UNBUFFERED');
			}
		}

		if($next) {
			redirect("?action=upgrade&step=$step&start=".($start + $limit));
		} else {
			echo "�� $step �������ɹ�<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} else {

		echo 	'<br>��ϲ����̳���ݵ���ɹ�����������������ɾ��������<br><br>'.
			'<b>��л��ѡ�����ǵĲ�Ʒ��</b>';
		exit;
	}
}

function redirect($url) {

	echo 	"<script>",
		"function redirect() {window.location.replace('$url');}\n",
		"setTimeout('redirect();', 500);\n",
		"</script>",
		"<br><br><a href=\"$url\">��������Զ���תҳ�棬�����˹���Ԥ��<br>���ǵ����������û���Զ���תʱ����������</a>";
	flush();

}

?>