<?
header('Content-Type: text/html; charset=gbk');

$msg = '';

if($_POST['action'] == 'save') {

	if(is_writeable('mail_config.inc.php')) {

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

		} elseif($_POST['mailsend_new'] == 3) {

$savedata .= <<<EOF

\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
\$mailcfg['port'] = '{$_POST[mailcfg_new][port]}';

EOF;

		}

		setcookie('mail_cfg', base64_encode(serialize($_POST['mailcfg_new'])), time() + 86400);

$savedata .= <<<EOF

?>
EOF;

		$fp = fopen('mail_config.inc.php', 'w');
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

		$msg = '�޷�д���ʼ������ļ� mail_config.inc.php��Ҫʹ�ñ����������ô��ļ��Ŀ�д��Ȩ�ޡ�';

	}

}

include './mail_config.inc.php';

?>
<html>
<head>
<title>Discuz! Board Mail Config and Test Tools</title>
<style>
body,table,td	{COLOR: #3A4273; FONT-FAMILY: Tahoma, Verdana, Arial; FONT-SIZE: 12px; LINE-HEIGHT: 20px; scrollbar-base-color: #E3E3EA; scrollbar-arrow-color: #5C5C8D}
tr		{background-color: #E3E3EA}
th		{background-color: #3A4273; color: #FFFFFF}
input		{border: 1px solid #CCCCCC}
.button 	{background-color: #3A4273; color: #FFFFFF}
.text		{width: 100%}
.checkbox,.radio{border: 0px}
</style>
<script>
function $(id) {
	return document.getElementById(id);
}
</script>
</head>

<body>
<?

if($msg) {
	echo '<center><font color="#FF0000">'.$msg.'</font></center>';
}

?>
<table width="60%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<form method="post">
<input type="hidden" name="action" value="save"><input type="hidden" name="sendtest" value="0">
<tr><th colspan="2">�ʼ�����/���Թ���</th></tr>
<?

$saved_mailcfg = empty($_COOKIE['mail_cfg']) ? array(
	'server' => 'smtp.21cn.com',
	'port' => '25',
	'auth' => 1,
	'from' => 'Discuz <username@21cn.com>',
	'auth_username' => 'username@21cn.com',
	'auth_password' => 'password',
	'test_from' => 'user <my@mydomain.com>',
	'test_to' => 'user1 <test1@test1.com>, user2 <test2@test2.net>'
) : unserialize(base64_decode($_COOKIE['mail_cfg']));

echo '<tr><td width="40%">�����ʼ������е�ȫ��������ʾ</td><td>';
echo ' <input class="checkbox" type="checkbox" name="sendmail_silent_new" value="1"'.($sendmail_silent ? ' checked' : '').'><br>';
echo '</tr>';
echo '<tr><td>�ʼ�ͷ�ķָ���</td><td>';
echo ' <input class="radio" type="radio" name="maildelimiter_new" value="1"'.($maildelimiter ? ' checked' : '').'> ʹ�� CRLF ��Ϊ�ָ���<br>';
echo ' <input class="radio" type="radio" name="maildelimiter_new" value="0"'.(!$maildelimiter ? ' checked' : '').'> ʹ�� LF ��Ϊ�ָ���<br>';
echo '</tr>';
echo '<tr><td>�ռ����а����û���</td><td>';
echo ' <input class="checkbox" type="checkbox" name="mailusername_new" value="1"'.($mailusername ? ' checked' : '').'><br>';
echo '</tr>';

echo '<tr><td>�ʼ����ͷ�ʽ</td><td>';
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
echo '<tr><td>SMTP ������</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[server]" value="'.$mailcfg['server'].'"><br>';
echo '</tr>';
echo '<tr><td>SMTP �˿�, Ĭ�ϲ����޸�</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[port]" value="'.$mailcfg['port'].'"><br>';
echo '</tr>';
echo '</tbody>';
echo '<tbody id="hidden2" style="display:'.($mailsend != 2 ? ' none' : '').'">';
echo '<tr><td>�Ƿ���Ҫ AUTH LOGIN ��֤</td><td>';
echo ' <input class="checkbox" type="checkbox" name="mailcfg_new[auth]" value="1"'.($mailcfg['auth'] ? ' checked' : '').'><br>';
echo '</tr>';
echo '<tr><td>�����˵�ַ (�����Ҫ��֤,����Ϊ����������ַ)</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[from]" value="'.$mailcfg['from'].'"><br>';
echo '</tr>';
echo '<tr><td>��֤�û���</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[auth_username]" value="'.$mailcfg['auth_username'].'"><br>';
echo '</tr>';
echo '<tr><td>��֤����</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[auth_password]" value="'.$mailcfg['auth_password'].'"><br>';
echo '</tr>';
echo '</tbody>';

?>
<tr><td colspan="2" align="center">
<input class="button" type="submit" name="submit" value="��������">
</td></tr>
<?

echo '<tr><td>���Է�����</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[test_from]" value="'.$saved_mailcfg['test_from'].'"><br>';
echo '</tr>';
echo '<tr><td>�����ռ���</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[test_to]" value="'.$saved_mailcfg['test_to'].'"><br>';
echo '</tr>';

?>
<tr><td colspan="2" align="center">
<input class="button" type="submit" name="submit" onclick="this.form.sendtest.value = 1" value="�������ò����Է���">
</td></tr>
</form>
</table>

</body>