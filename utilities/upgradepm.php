<?

define('IN_DISCUZ', TRUE);

define('DISCUZ_ROOT', './');

include 'config.inc.php';
require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';

if(UC_CONNECT != 'mysql') {
	echo "����UC_Server���ڷ�����ִ�б���������";
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
		echo '�Ѵ��� '.$affected_rows.' ����Ϣ<br /><a href="upgradepm.php?start=yes&affected_rows_prev='.$affected_rows.'">����</a>';
		echo '<script>setTimeout("redirect(\'upgradepm.php?start=yes&affected_rows_prev='.$affected_rows.'\')", 1250);</script>';

	} else {

		$db->query("UPDATE ".UC_DBTABLEPRE."pms SET related=0");
		$affected_rows_prev += $db->affected_rows();

		echo 'ת����ϣ��ܹ������� '.$affected_rows_prev.' ����Ϣ';

	}

} else {

	echo 'Discuz! 6.0.1 UC ����Ϣ��������<br /><a href="upgradepm.php?start=yes">������￪ʼ</a>';

}