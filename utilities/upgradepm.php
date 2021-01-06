<?

define('IN_DISCUZ', TRUE);

define('DISCUZ_ROOT', './');

include 'config.inc.php';
require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';

if(UC_CONNECT != 'mysql') {
	echo "请在UC_Server所在服务器执行本升级程序！";
	exit;
}

if(!empty($start)) {

	$step = 50;
	$db = new dbstuff;
	$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, true, UC_DBCHARSET);

	$query = $db->query("SELECT * FROM ".UC_DBTABLEPRE."pms WHERE dateline = related LIMIT $step");
	if($db->num_rows($query)) {

		$affected_rows = !empty($affected_rows_prev) ? $affected_rows_prev : 0;
		if($pm = $db->fetch_array($query)) {
			$db->query("INSERT INTO ".UC_DBTABLEPRE."pms (msgfrom, msgfromid, msgtoid, folder, new, subject, dateline, message, delstatus, related)
				VALUES ('$pm[msgfrom]', '$pm[msgfromid]', '$pm[msgtoid]', '$pm[folder]', '$pm[new]', '$pm[subject]', '$pm[dateline]', '', '$pm[delstatus]', '0')");
			$pmid = $db->insert_id();
			$db->query("UPDATE ".UC_DBTABLEPRE."pms SET related='$pmid' WHERE related='$pm[related]'");
			$affected_rows += $db->affected_rows() + 1;
		}

		echo '<script type="text/javascript">function redirect(url) {window.location=url;}</script>';
		echo '已处理 '.$affected_rows.' 条消息<br /><a href="upgradepm.php?start=yes&affected_rows_prev='.$affected_rows.'">继续</a>';
		echo '<script>setTimeout("redirect(\'upgradepm.php?start=yes&affected_rows_prev='.$affected_rows.'\')", 1250);</script>';

	} else {

		$db->query("UPDATE ".UC_DBTABLEPRE."pms SET related=0");
		$affected_rows_prev += $db->affected_rows();

		echo '转换完毕！总共处理了 '.$affected_rows_prev.' 条消息';

	}

} else {

	echo 'Discuz! 6.0.1 UC 短消息升级程序<br /><a href="upgradepm.php?start=yes">点击这里开始</a>';

}