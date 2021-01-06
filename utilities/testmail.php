<?php

/*
	[DISCUZ!] utilities/testmail.php - test email sending module of Discuz!
	This is NOT a freeware, use is subject to license terms

	Version: 2.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2002/12/6 17:00
*/

error_reporting(7);
define('IN_DISCUZ', true);

define('DISCUZ_ROOT', './');
define('TPLDIR', './templates/default');

// Please modify the following 3 variables to fit your situations
$from = 'my@mydomain.com';			// mail from(�������ʼ���ַ)
$to1 = 'test@test.com';				// mail to(���Ե�һ�ʼ����͵�ַ)
$to2 = 'test1@test1.com, test2@test2.net';	// mail to for Bcc(�����ʼ�Ⱥ�巢�͵�ַ)

require './include/global.func.php';

$mailsend = 1;
sendmail($to1, '��׼��ʽ���� Email(����)', "ͨ�� PHP ������ UNIX sendmail ����\n\n��һ�ʼ������� $to1\n\n���� $from", $from);
sendmail($to2, '��׼��ʽ���� Email(Ⱥ��)', "ͨ�� PHP ������ UNIX sendmail ����\n\nȺ�巢���ʼ������� $to2\n\n���� $from", $from);

$mailsend = 2;
sendmail($to1, 'ͨ�� SMTP ������(SOCKET)���� Email(����)', "ͨ�� SOCKET ���� SMTP ����������\n\n��һ�ʼ������� $to1\n\n���� $from", $from);
sendmail($to2, 'ͨ�� SMTP ������(SOCKET)���� Email(Ⱥ��)', "ͨ�� SOCKET ���� SMTP ����������\n\nȺ�巢���ʼ������� $to2\n\n���� $from", $from);

$mailsend = 3;
sendmail($to1, 'ͨ�� PHP ���� SMTP ���� Email(����)', "ͨ�� PHP ���� SMTP ���� Email\n\n��һ�ʼ������� $to1\n\n���� $from", $from);
sendmail($to2, 'ͨ�� PHP ���� SMTP ���� Email(Ⱥ��)', "ͨ�� PHP ���� SMTP ����\n\nȺ�巢���ʼ������� $to2\n\n���� $from", $from);

?>