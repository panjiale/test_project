<?php

// Upgrade Discuz! Board from 5.0.0 to 5.5.0
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 5.0.0';
$version_new = 'Discuz! 5.5.0';
$timestamp = time();

@include("./config.inc.php");
@include("./include/db_mysql.class.php");

header("Content-Type: text/html; charset=$charset");
showheader();

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

$upgrade1 = <<<EOT

REPLACE INTO cdb_settings (variable, value) VALUES ('spacecachelife', 1800);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmythreads', 5);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyreplies', 5);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyrewards', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmytrades', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyblogs', 8);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyfriends', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyfavforums', 5);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacelimitmyfavthreads', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('spacetextlength', 300);
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbstatus', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbwidth', 400);
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbheight', 300);
REPLACE INTO cdb_settings (variable, value) VALUES ('forumlinkstatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('pluginjsmenu', '���');
REPLACE INTO cdb_settings (variable, value) VALUES ('magicstatus', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('magicmarket', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('maxmagicprice', '50');
REPLACE INTO cdb_settings (variable, value) VALUES ('jswizard', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('passport_shopex', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('seccodeanimator', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('welcomemsgtitle', '{username}�����ã���л����ע�ᣬ���Ķ��������ݡ�');
REPLACE INTO cdb_settings (variable, value) VALUES ('welcomemsgtxt', '�𾴵�{username}�����Ѿ�ע���Ϊ{sitename}�Ļ�Ա�������ڷ�������ʱ�����ص��ط��ɷ��档\r\n�������ʲô���ʿ�����ϵ����Ա��Email: {adminemail}��\r\n\r\n\r\n{bbname}\r\n{time}');
REPLACE INTO cdb_settings (variable, value) values ('cacheindexlife', '0');
REPLACE INTO cdb_settings (variable, value) values ('cachethreadlife', '0');
REPLACE INTO cdb_settings (variable, value) values ('cachethreaddir', 'forumdata/threadcaches');
REPLACE INTO cdb_settings (variable, value) values ('jsdateformat', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seccodedata', '');
REPLACE INTO cdb_settings (variable, value) values ('frameon', '0');
REPLACE INTO cdb_settings (variable, value) values ('framewidth', '180');
REPLACE INTO cdb_settings (variable, value) VALUES ('smrows', '4');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarktype', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('spacestatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('whosonline_contract', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('attachdir', './attachments');
REPLACE INTO cdb_settings (variable, value) VALUES ('attachurl', 'attachments');
REPLACE INTO cdb_settings (variable, value) VALUES ('onlinehold', '15');
REPLACE INTO cdb_settings (variable, value) VALUES ('wapregister', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('msgforward', 'a:3:{s:11:\"refreshtime\";i:1;s:5:\"quick\";i:1;s:8:\"messages\";a:13:{i:0;s:19:\"thread_poll_succeed\";i:1;s:19:\"thread_rate_succeed\";i:2;s:23:\"usergroups_join_succeed\";i:3;s:23:\"usergroups_exit_succeed\";i:4;s:25:\"usergroups_update_succeed\";i:5;s:20:\"buddy_update_succeed\";i:6;s:17:\"post_edit_succeed\";i:7;s:18:\"post_reply_succeed\";i:8;s:24:\"post_edit_delete_succeed\";i:9;s:22:\"post_newthread_succeed\";i:10;s:13:\"admin_succeed\";i:11;s:17:\"pm_delete_succeed\";i:12;s:15:\"search_redirect\";}}');
REPLACE INTO cdb_settings (variable, value) VALUES ('forumjump','0');
REPLACE INTO cdb_settings (variable, value) VALUES ('ftp', 'a:10:{s:2:\"on\";s:1:\"0\";s:3:\"ssl\";s:1:\"0\";s:4:\"host\";s:0:\"\";s:4:\"port\";s:2:\"21\";s:8:\"username\";s:0:\"\";s:8:\"password\";s:0:\"\";s:9:\"attachdir\";s:1:\".\";s:9:\"attachurl\";s:0:\"\";s:7:\"hideurl\";s:1:\"0\";s:7:\"timeout\";s:1:\"0\";}');
REPLACE INTO cdb_settings (variable, value) VALUES ('secqaa', 'a:2:{s:8:\"minposts\";s:1:\"1\";s:6:\"status\";i:0;}');
REPLACE INTO cdb_settings (variable, value) values ('smthumb','20');

DELETE FROM cdb_settings WHERE variable IN ('qihoo_searchboxtxt', 'qihoo_ustyle', 'qihoo_allsearch');
DELETE FROM cdb_settings WHERE variable='avatarshowwidth';
DELETE FROM cdb_settings WHERE variable='avatarshowstatus';
DELETE FROM cdb_settings WHERE variable='avatarshowpos';
DELETE FROM cdb_settings WHERE variable='avatarshowlink';
DELETE FROM cdb_settings WHERE variable='avatarshowheight';
DELETE FROM cdb_settings WHERE variable='avatarshowdefault';

EOT;

$upgradetable = array(

	array('usergroups', 'CHANGE', 'minrewardprice', "minrewardprice smallint(6) NOT NULL default '1'"),
	array('usergroups', 'CHANGE', 'maxrewardprice', "maxrewardprice smallint(6) NOT NULL default '0'"),
	array('usergroups', 'ADD', 'magicsdiscount', "tinyint(1) NOT NULL default '0'"),
	array('usergroups', 'ADD', 'allowmagics', "tinyint(1) unsigned NOT NULL default '1'"),
	array('usergroups', 'ADD', 'maxmagicsweight', "smallint(6) unsigned NOT NULL default '100'"),
	array('usergroups', 'ADD', 'allowbiobbcode', "tinyint(1) unsigned NOT NULL default '0'"),
	array('usergroups', 'ADD', 'allowbioimgcode', "tinyint(1) unsigned NOT NULL default '0'"),
	array('usergroups', 'ADD', 'maxbiosize', "smallint(6) unsigned NOT NULL default '0'"),

	array('forums', 'ADD', 'alloweditpost', "tinyint(1) unsigned NOT NULL default '1'"),
	array('forums', 'ADD', 'simple', "tinyint(1) unsigned NOT NULL default '0'"),
	array('forums', 'ADD', 'allowspecialonly', "tinyint(1) unsigned NOT NULL default '0' AFTER allowpostspecial"),

	array('attachments', 'ADD', 'thumb', "tinyint(1) unsigned NOT NULL default '0'"),
	array('attachments', 'ADD', 'price', "smallint(6) unsigned not NULL default '0' AFTER readperm"),
	array('attachments', 'ADD', 'remote', "tinyint(1) unsigned NOT NULL default '0'"),

	array('threadsmod', 'ADD', 'magicid', "smallint(6) unsigned NOT NULL"),
	array('threadsmod', 'CHANGE', 'action', "action CHAR(5) NOT NULL"),

	array('announcements', 'CHANGE', 'redirect', "type tinyint(1) NOT NULL default '0'"),
	array('announcements', 'ADD', 'groups', "text NOT NULL"),

	array('activityapplies', 'ADD', 'contact', "CHAR(200) NOT NULL"),

	array('forumlinks', 'CHANGE', 'note', "description mediumtext NOT NULL"),

	array('sessions', 'CHANGE', 'seccode', "seccode mediumint(6) unsigned NOT NULL default '0'"),

	array('bbcodes', 'ADD', 'prompt', "TEXT NOT NULL AFTER params"),

	array('memberfields', 'ADD', 'spacename', "varchar(40) NOT NULL"),
	array('memberfields', 'DROP', 'signature', ""),

	array('members', 'DROP', 'avatarshowid', ""),

	array('pms', 'ADD', 'delstatus', "tinyint(1) unsigned NOT NULL default '0'"),

);

$upgrade2 = <<<EOT

UPDATE cdb_forums SET alloweditpost=1;
UPDATE cdb_usergroups SET maxmagicsweight=100 WHERE groupid<4 OR groupid>9;

UPDATE cdb_bbcodes SET prompt = '�����������ʾ������:' WHERE `tag` ='fly';
UPDATE cdb_bbcodes SET prompt = '������ Flash ������ URL:' WHERE `tag` ='flash';
UPDATE cdb_bbcodes SET prompt = '��������ʾ����״̬ QQ ����:' WHERE `tag` ='qq';
UPDATE cdb_bbcodes SET prompt = '������ Real ��Ƶ�� URL:' WHERE `tag` ='ra';
UPDATE cdb_bbcodes SET prompt = '������ Real ��Ƶ����Ƶ�� URL:' WHERE `tag` ='rm';
UPDATE cdb_bbcodes SET prompt = '������ Windows media ��Ƶ�� URL:' WHERE `tag` ='wma';
UPDATE cdb_bbcodes SET prompt = '������ Windows media ��Ƶ����Ƶ�� URL:' WHERE `tag` ='wmv';
UPDATE cdb_bbcodes SET replacement='<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="400" height="30"><param name="src" value="{1}" /><param name="controls" value="controlpanel" /><param name="console" value="{RANDOM}" /><embed src="{1}" type="audio/x-pn-realaudio-plugin" console="{RANDOM}" controls="ControlPanel" width="400" height="30"></embed></object>' WHERE tag='ra';
UPDATE cdb_bbcodes SET replacement='<br /><object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="480" height="360"><param name="src" value="{1}" /><param name="controls" value="imagewindow" /><param name="console" value="{MD5}" /><embed src="{1}" type="audio/x-pn-realaudio-plugin" controls="IMAGEWINDOW" console="{MD5}" width="480" height="360"></embed></object><br /><object classid="CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="480" height="32"><param name="src" value="{1}" /><param name="controls" value="controlpanel" /><param name="console" value="{MD5}" /><embed src="{1}" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" console="{MD5}" width="480" height="32"></embed></object><br />'  WHERE tag='rm';
UPDATE cdb_bbcodes SET replacement='<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="260" height="64"><param name="autostart" value="0" /><param name="url" value="{1}" /><embed src="{1}" autostart="0" type="video/x-ms-wmv" width="260" height="42"></embed></object>'  WHERE tag='wma';
UPDATE cdb_bbcodes SET replacement='<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="500" height="400"><param name="autostart" value="0" /><param name="url" value="{1}" /><embed src="{1}" autostart="0" type="video/x-ms-wmv" width="500" height="400"></embed></object>'  WHERE tag='wmv';
UPDATE cdb_crons SET filename = 'threadexpiries_hourly.inc.php' WHERE filename = 'threadexpiries_daily.inc.php';

EOT;

$upgrade4 = <<<EOT

UPDATE cdb_posts SET invisible='-2' WHERE invisible='2';

EOT;

$upgrade6 = <<<EOT

DROP TABLE IF EXISTS cdb_spacecaches;
CREATE TABLE cdb_spacecaches (
  uid mediumint(8) unsigned NOT NULL default '0',
  variable varchar(20) NOT NULL,
  value text NOT NULL,
  expiration int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (uid, variable)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_memberspaces;
CREATE TABLE cdb_memberspaces (
  uid mediumint(8) unsigned NOT NULL default '0',
  style char(20) NOT NULL,
  description char(100) NOT NULL,
  layout char(200) NOT NULL,
  side tinyint(1) NOT NULL default '0',
  PRIMARY KEY (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_attachpaymentlog;
CREATE TABLE cdb_attachpaymentlog (
  uid mediumint(8) unsigned NOT NULL default '0',
  aid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  amount int(10) unsigned NOT NULL default '0',
  netamount int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (aid,uid),
  KEY uid (uid),
  KEY authorid (authorid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_magics;
CREATE TABLE cdb_magics (
  magicid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  type tinyint(3) NOT NULL default '0',
  name varchar(50) NOT NULL,
  identifier varchar(40) NOT NULL,
  description varchar(255) NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  price mediumint(8) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  salevolume smallint(6) unsigned NOT NULL default '0',
  supplytype tinyint(1) NOT NULL default '0',
  supplynum smallint(6) unsigned NOT NULL default '0',
  weight tinyint(3) unsigned NOT NULL default '1',
  filename varchar(50) NOT NULL,
  magicperm text NOT NULL,
  PRIMARY KEY  (magicid),
  UNIQUE KEY identifier (identifier),
  KEY displayorder (available,displayorder)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_magiclog;
CREATE TABLE cdb_magiclog (
  uid mediumint(8) unsigned NOT NULL default '0',
  magicid smallint(6) unsigned NOT NULL default '0',
  action tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  amount smallint(6) unsigned NOT NULL default '0',
  price mediumint(8) unsigned NOT NULL default '0',
  targettid mediumint(8) unsigned NOT NULL default '0',
  targetpid int(10) unsigned NOT NULL default '0',
  targetuid mediumint(8) unsigned NOT NULL default '0',
  KEY uid (uid,dateline),
  KEY targetuid (targetuid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_magicmarket;
CREATE TABLE cdb_magicmarket (
  mid smallint(6) unsigned NOT NULL auto_increment,
  magicid smallint(6) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL,
  price mediumint(8) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY (mid),
  KEY num (magicid,num),
  KEY price (magicid,price),
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_membermagics;
CREATE TABLE cdb_membermagics (
  uid mediumint(8) unsigned NOT NULL default '0',
  magicid smallint(6) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_projects;
CREATE TABLE cdb_projects (
  id smallint(6) unsigned NOT NULL auto_increment auto_increment,
  name varchar(50) NOT NULL,
  type varchar(10) NOT NULL,
  description varchar(255) NOT NULL,
  value mediumtext NOT NULL,
  PRIMARY KEY (id),
  KEY type (type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_itempool;
CREATE TABLE cdb_itempool (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  type tinyint(1) unsigned NOT NULL,
  question text NOT NULL,
  answer varchar(50) NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_faqs;
CREATE TABLE cdb_faqs (
  id smallint(6) NOT NULL auto_increment,
  fpid smallint(6) unsigned NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  identifier varchar(20) NOT NULL,
  keyword varchar(50) NOT NULL,
  title varchar(50) NOT NULL,
  message text NOT NULL,
  PRIMARY KEY (id),
  KEY displayplay (displayorder)
) TYPE=MyISAM  AUTO_INCREMENT=35;

EOT;

$upgrade7 = <<<EOT

INSERT INTO cdb_magics (magicid, available, type, name, identifier, description, displayorder, price, num, salevolume, supplytype, supplynum, weight, filename, magicperm) VALUES
	('1','1','1','��ɫ��','CCK','���Ա任�������ɫ,������24Сʱ','0','10','999','0','0','0','30','magic_color.inc.php',''),
	('2','1','3','��Ǯ��','MOK','����������һЩ���','0','10','999','0','0','0','30','magic_money.inc.php',''),
	('3','1','1','IP��','SEK','���Բ鿴�������ߵ�IP','0','15','999','0','0','0','30','magic_see.inc.php',''),
	('4','1','1','������','UPK','��������ĳ������','0','10','999','0','0','0','30','magic_up.inc.php',''),
	('5','1','1','�ö���','TOK','���Խ������ö�24Сʱ','0','20','999','0','0','0','40','magic_top.inc.php',''),
	('6','1','1','����','REK','����ɾ���Լ�������','0','10','999','0','0','0','30','magic_del.inc.php',''),
	('7','1','2','���п�','RTK','�鿴ĳ���û��Ƿ�����','0','15','999','0','0','0','30','magic_reporter.inc.php',''),
	('8','1','1','��Ĭ��','CLK','24Сʱ�ڲ��ܻظ�','0','15','999','0','0','0','30','magic_close.inc.php',''),
	('9','1','1','������','OPK','ʹ���ӿ��Իظ�','0','15','999','0','0','0','30','magic_open.inc.php',''),
	('10','1','1','����','YSK','���Խ��Լ�����������','0','20','999','0','0','0','30','magic_hidden.inc.php',''),
	('11','1','1','�ָ���','CBK','�������ָ�Ϊ������ʾ���û���,�����ս���','0','15','999','0','0','0','20','magic_renew.inc.php',''),
	('12','1','1','�ƶ���','MVK','�ɽ����ѵ������ƶ����������棨�����������޶�������⣩','0','50','989','0','0','0','50','magic_move.inc.php','');

INSERT INTO cdb_projects (name, type, description, value) VALUES
	('��������̳', 'extcredit', '�������ϣ����Աͨ����ˮ��ҳ����ʵȷ�ʽ�õ����֣�������Ҫ����һЩ�����Ե����ӻ�û��֡�', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:49:\"posts*0.5+digestposts*5+extcredits1*2+extcredits2\";s:13:\"creditspolicy\";s:299:\"a:12:{s:4:\"post\";a:0:{}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:10;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1444:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"��Ǯ\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
  	('��������̳', 'extcredit', '��������̳�Ļ�Ա����ͨ������һЩ���ۡ��ظ��Ȼ�û��֣�ͬʱ������̳�ķ�����������Ҫ����ϣ����Ա����һЩ�м�ֵ���������ŵȡ�', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:81:\"posts+digestposts*5+oltime*5+pageviews/1000+extcredits1*2+extcredits2+extcredits3\";s:13:\"creditspolicy\";s:315:\"a:12:{s:4:\"post\";a:1:{i:1;i:1;}s:5:\"reply\";a:1:{i:2;i:1;}s:6:\"digest\";a:1:{i:1;i:10;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1036:\"a:8:{i:1;a:6:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:2;a:6:{s:5:\"title\";s:4:\"��Ǯ\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:3;a:6:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:4;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:5;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:6;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:7;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}i:8;a:6:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;}}\";}'),
  	('��������Ӱ����̳', 'extcredit', '��������̳��Ҫ�����ͼƬ��������������Ա���������һ����չ���֣�������', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:86:\"posts+digestposts*2+pageviews/2000+extcredits1*2+extcredits2+extcredits3+extcredits4*3\";s:13:\"creditspolicy\";s:324:\"a:12:{s:4:\"post\";a:1:{i:2;i:1;}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:10;}s:10:\"postattach\";a:1:{i:4;i:3;}s:9:\"getattach\";a:1:{i:2;i:-2;}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1454:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"��Ǯ\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
 	('���¡�С˵����̳', 'extcredit', '�����͵���̳�����ӻ�Ա��ԭ�����»�����ת�������£��������һ����չ���֣��Ĳɡ�', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:57:\"posts+digestposts*8+extcredits2+extcredits3+extcredits4*2\";s:13:\"creditspolicy\";s:307:\"a:12:{s:4:\"post\";a:1:{i:2;i:1;}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:4;i:10;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1454:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"��Ǯ\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:4:\"�Ĳ�\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
	('��������̳', 'extcredit', '��������̳���������ǵõ���Ա�Ľ�����������Ҫ��ͨ��ͶƱ�ķ�ʽ���ֻ�Ա�Ľ��飬�������һ����ֲ���Ϊ���μ�ͶƱ������һ����չ����Ϊ�������ԡ�', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:63:\"posts*0.5+digestposts*2+extcredits1*2+extcredits3+extcredits4*2\";s:13:\"creditspolicy\";s:306:\"a:12:{s:4:\"post\";a:0:{}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:8;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:0:{}s:8:\"votepoll\";a:1:{i:4;i:5;}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1456:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"��Ǯ\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:6:\"������\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
	('ó������̳', 'extcredit', '��������̳��ע�ص��ǻ�Ա֮��Ľ��ף����ʹ�û��ֲ��ԣ����׳ɹ�������һ����չ���֣����Ŷȡ�', 'a:4:{s:10:\"savemethod\";a:2:{i:0;s:1:\"1\";i:1;s:1:\"2\";}s:14:\"creditsformula\";s:55:\"posts+digestposts+extcredits1*2+extcredits3+extcredits4\";s:13:\"creditspolicy\";s:306:\"a:12:{s:4:\"post\";a:0:{}s:5:\"reply\";a:0:{}s:6:\"digest\";a:1:{i:1;i:5;}s:10:\"postattach\";a:0:{}s:9:\"getattach\";a:0:{}s:2:\"pm\";a:0:{}s:6:\"search\";a:0:{}s:15:\"promotion_visit\";a:1:{i:3;i:2;}s:18:\"promotion_register\";a:1:{i:3;i:2;}s:13:\"tradefinished\";a:1:{i:4;i:6;}s:8:\"votepoll\";a:0:{}s:10:\"lowerlimit\";a:0:{}}\";s:10:\"extcredits\";s:1456:\"a:8:{i:1;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:2;a:8:{s:5:\"title\";s:4:\"��Ǯ\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:3;a:8:{s:5:\"title\";s:4:\"����\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:4;a:8:{s:5:\"title\";s:6:\"���Ŷ�\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";s:1:\"1\";s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:5;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:6;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:7;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}i:8;a:8:{s:5:\"title\";s:0:\"\";s:4:\"unit\";s:0:\"\";s:5:\"ratio\";i:0;s:9:\"available\";N;s:10:\"lowerlimit\";i:0;s:12:\"showinthread\";N;s:15:\"allowexchangein\";N;s:16:\"allowexchangeout\";N;}}\";}'),
	('̳����������', 'forum', '�ð�������˲���������ģ�鹲���Լ���������Ҫ�ܸߵ�Ȩ�޲�������ð�顣Ҳ�ʺ��ڱ����Ը߰�顣', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"0\";s:16:\"allowpostspecial\";s:1:\"0\";s:14:\"alloweditrules\";s:1:\"1\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"0\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:7:\"	1	2	3	\";s:8:\"postperm\";s:7:\"	1	2	3	\";s:9:\"replyperm\";s:7:\"	1	2	3	\";s:13:\"getattachperm\";s:7:\"	1	2	3	\";s:14:\"postattachperm\";s:7:\"	1	2	3	\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('������������', 'forum', '�����ÿ��������⻺��ϵ����������Ȩ�����ü���ϵ͡�', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:1:\"5\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:0:\"\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('������������', 'forum', '�����ÿ����˷�����ˣ����������������û��顣', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:1:\"1\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"1\";s:6:\"jammer\";s:1:\"1\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:1:\"0\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"0\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:7:\"	1	2	3	\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('��������', 'forum', '�����������﷢������һ����֮����Զ��ر����⡣', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:1:\"9\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"1\";s:9:\"autoclose\";s:2:\"30\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:22:\"	1	2	3	11	12	13	14	15	\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}'),
	('���ֹ�ˮ����', 'forum', '�����������⻺��ϵ�������������е��������ⰴť��', 'a:33:{s:7:\"styleid\";s:1:\"0\";s:12:\"allowsmilies\";s:1:\"1\";s:9:\"allowhtml\";s:1:\"0\";s:11:\"allowbbcode\";s:1:\"1\";s:12:\"allowimgcode\";s:1:\"1\";s:14:\"allowanonymous\";s:1:\"0\";s:10:\"allowshare\";s:1:\"1\";s:16:\"allowpostspecial\";s:2:\"15\";s:14:\"alloweditrules\";s:1:\"0\";s:10:\"recyclebin\";s:1:\"1\";s:11:\"modnewposts\";s:1:\"0\";s:6:\"jammer\";s:1:\"0\";s:16:\"disablewatermark\";s:1:\"0\";s:12:\"inheritedmod\";s:1:\"0\";s:9:\"autoclose\";s:1:\"0\";s:12:\"forumcolumns\";s:1:\"0\";s:12:\"threadcaches\";s:2:\"40\";s:16:\"allowpaytoauthor\";s:1:\"1\";s:13:\"alloweditpost\";s:1:\"1\";s:6:\"simple\";s:1:\"0\";s:11:\"postcredits\";s:0:\"\";s:12:\"replycredits\";s:0:\"\";s:16:\"getattachcredits\";s:0:\"\";s:17:\"postattachcredits\";s:0:\"\";s:13:\"digestcredits\";s:0:\"\";s:16:\"attachextensions\";s:0:\"\";s:11:\"threadtypes\";s:0:\"\";s:8:\"viewperm\";s:0:\"\";s:8:\"postperm\";s:0:\"\";s:9:\"replyperm\";s:0:\"\";s:13:\"getattachperm\";s:0:\"\";s:14:\"postattachperm\";s:0:\"\";s:16:\"supe_pushsetting\";s:0:\"\";}');

INSERT INTO cdb_faqs (id, fpid, displayorder, identifier, keyword, title, message) VALUES
	('1', '0', '1', '', '', '�û���֪', ''),
	('2', '1', '1', '', '', '�ұ���Ҫע����', '��ȡ���ڹ���Ա������� Discuz! ��̳���û���Ȩ��ѡ��������п��ܱ�����ע�����ʽ�û�������������ӡ���Ȼ����ͨ������£�������Ӧ������ʽ�û����ܷ������ͻظ��������ӡ��� <a href="register.php" target="_blank">�������</a> ���ע���Ϊ���ǵ����û���\r\n<br><br>ǿ�ҽ�����ע�ᣬ������õ��ܶ����ο�����޷�ʵ�ֵĹ��ܡ�'),
	('3', '1', '2', 'login', '��¼����', '����ε�¼��̳��', '������Ѿ�ע���Ϊ����̳�Ļ�Ա����ô��ֻҪͨ������ҳ�����ϵ�<a href="logging.php?action=login" target="_blank">��¼</a>�������½������д��ȷ���û��������루��������а�ȫ���ʣ���ѡ����ȷ�İ�ȫ���ʲ������Ӧ�Ĵ𰸣���������ύ��������ɵ�½�������δע���������<br><br>\r\n�����Ҫ���ֵ�¼����ѡ����Ӧ�� Cookie ʱ�䣬�ڴ�ʱ�䷶Χ�������Բ�����������������ϴεĵ�¼״̬��'),
	('4', '1', '3', '', '', '�����ҵĵ�¼���룬��ô�죿', '�����������û���¼�����룬������ͨ��ע��ʱ��д�ĵ���������������һ���µ����롣�����¼ҳ���е� <a href="member.php?action=lostpasswd" target="_blank">ȡ������</a>������Ҫ����д���ĸ�����Ϣ��ϵͳ���Զ���������������ʼ�����ע��ʱ��д�� Email �����С�������� Email ��ʧЧ���޷��յ��ż���������̳����Ա��ϵ��'),
	('5', '0', '2', '', '', '������ز���', ''),
	('6', '0', '3', '', '', '�������ܲ���', ''),
	('7', '0', '4', '', '', '�����������', ''),
	('8', '1', '4', '', '', '�����ʹ�ø��Ի�ͷ��', '��<a href="memcp.php" target="_blank">�������</a>�еġ��༭�������ϡ�����һ����ͷ�񡱵�ѡ�����ʹ����̳�Դ���ͷ������Զ����ͷ��'),
	('9', '1', '5', '', '', '������޸ĵ�¼����', '��<a href="memcp.php" target="_blank">�������</a>�еġ��༭�������ϡ�����д��ԭ���롱���������롱����ȷ�������롱��������ύ���������޸ġ�'),
	('10', '1', '6', '', '', '�����ʹ�ø��Ի�ǩ�����ǳ�', '��<a href="memcp.php" target="_blank">�������</a>�еġ��༭�������ϡ�����һ�����ǳơ��͡�����ǩ������ѡ������ڴ����á�'),
	('11', '5', '1', '', '', '����η���������', '����̳����У��㡰�������������Ȩ�ޣ������Կ����С�ͶƱ�����ͣ�������ס���������ɽ��빦����ȫ�ķ������档\r\n<br><br>ע�⣺һ����̳������Ϊ�߼�����û�����ܷ����������������⡣�緢����ͨ���⣬ֱ�ӵ��������������Ȼ��Ҳ����ʹ�ð������ġ����ٷ�������������(�����ѡ���)��һ����̳������Ϊ��Ҫ��¼����ܷ�����'),
	('12', '5', '2', '', '', '����η���ظ�', '�ظ��з����֣���һ���������·��Ŀ��ٻظ��� �ڶ���������ظ���¥�������·����ظ����� �����������ظ�ҳ�棬�����ҳ���������Աߵġ��ظ�����'),
	('13', '5', '3', '', '', '����α༭�Լ�������', '�����ӵ����½ǣ��б༭���ظ��������ѡ�����༭���Ϳ��Զ����ӽ��б༭��'),
	('14', '5', '4', '', '', '����γ��۹�������', '<li>�������⣺\r\n�������뷢���������������ڵ��û����з���������Ȩ�ޣ��ڡ��ۼ�(��Ǯ)��������д����ļ۸����������û��ڲ鿴������ӵ�ʱ�����Ҫ���뽻�ѵĹ��̲ſ��Բ鿴���ӡ�</li>\r\n<li>�������⣺\r\n�����׼����������ӣ������ӵ������Ϣ��������[�鿴�����¼] [��������] [������һҳ] \r\n�����ӣ�������������⡱���й���</li>'),
	('15', '5', '5', '', '', '����γ��۹��򸽼�', '<li>�ϴ�����һ���и��ۼ۵������������ۼ۸񼴿�ʵ����Ҫ֧���ſ����ظ����Ĺ��ܡ�</li>\r\n<li>���������[���򸽼�]��ť�����������������ӻ���ת����������ҳ�棬ȷ�ϸ���������Ϣ����ύ��ť�����ɵõ�����������Ȩ�ޡ�ֻ�蹺��һ�Σ����иø�������Զ����Ȩ�ޡ�</li>'),
	('16', '5', '6', '', '', '������ϴ�����', '<li>�����������ʱ���ϴ�����������Ϊ��д�����ӱ�������ݺ���ϴ������ҷ��������Ȼ���ڱ���ѡ��Ҫ�ϴ������ľ����ļ���������������⡣</li>\r\n<li>����ظ���ʱ���ϴ�����������Ϊ��д��ظ�¥�������ݣ�Ȼ����ϴ������ҷ���������ҵ���Ҫ�ϴ��ĸ������������ظ���</li>'),
	('17', '5', '7', '', '', '�����ʵ�ַ���ʱͼ�Ļ���Ч��', '<li>�Ȱ�����д�������Ȼ�����ص�ͼƬ�Ը�������ʽ�ϴ���</li>\r\n<li>�༭���ӣ��ҵ������·��ĸ�����Ϣ�����aid��Ŀ����Ӧ�����֣���̳���Զ��Ѹ�����������[attach]xx[/attach]����ʽ���뵽��ǰ������ڵ�λ�á�</li>'),
	('18', '5', '8', 'discuzcode', 'Discuz!����', '�����ʹ��Discuz!����', '<table width="100%" cellpadding="2" cellspacing="2">\r\n  <tr>\r\n    <th width="50%">Discuz!����</th>\r\n    <th width="402">Ч��</th>\r\n  </tr>\r\n  <tr>\r\n    <td>[b]�������� Abc[/b]</td>\r\n    <td><strong>�������� Abc</strong></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[i]б������ Abc[/i]</td>\r\n    <td><em>б������ Abc</em></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[u]�»������� Abc[/u]</td>\r\n    <td><u>�»������� Abc</u></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[color=red]����ɫ[/color]</td>\r\n    <td><font color="red">����ɫ</font></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[size=3]���ִ�СΪ 3[/size] </td>\r\n    <td><font size="3">���ִ�СΪ 3</font></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[font=����]����Ϊ����[/font] </td>\r\n    <td><font face"����">����Ϊ����</font></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[align=Center]���ݾ���[/align] </td>\r\n    <td><div align="center">���ݾ���</div></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[url]http://www.comsenz.com[/url]</td>\r\n    <td><a href="http://www.comsenz.com" target="_blank">http://www.comsenz.com</a>���������ӣ�</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[url=http://www.Discuz.net]Discuz! ��̳[/url]</td>\r\n    <td><a href="http://www.Discuz.net" target="_blank">Discuz! ��̳</a>���������ӣ�</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[email]myname@mydomain.com[/email]</td>\r\n    <td><a href="mailto:myname@mydomain.com">myname@mydomain.com</a>��E-mail���ӣ�</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[email=support@discuz.net]Discuz! ����֧��[/email]</td>\r\n    <td><a href="mailto:support@discuz.net">Discuz! ����֧�֣�E-mail���ӣ�</a></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[quote]Discuz! Board ���ɿ�ʢ���루�������Ƽ����޹�˾��������̳���[/quote] </td>\r\n    <td><div style="font-size: 12px"><br><br><div class="msgheader">QUOTE:</div><div class="msgborder">ԭ���� <i>admin</i> �� 2006-12-26 08:45 ����<br>Discuz! Board ���ɿ�ʢ���루�������Ƽ����޹�˾��������̳���</div></td>\r\n  </tr>\r\n   <tr>\r\n    <td>[code]Discuz! Board ���ɿ�ʢ���루�������Ƽ����޹�˾��������̳���[/code] </td>\r\n    <td><div style="font-size: 12px"><br><br><div class="msgheader">CODE:</div><div class="msgborder">Discuz! Board ���ɿ�ʢ���루�������Ƽ����޹�˾��������̳���</div></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[hide]�������� Abc[/hide]</td>\r\n    <td>Ч��:ֻ�е�����߻ظ�����ʱ������ʾ���е����ݣ�������ʾΪ��<b>**** ������Ϣ �����������ʾ *****</b>��</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[hide=20]�������� Abc[/hide]</td>\r\n    <td>Ч��:ֻ�е�����߻��ָ��� 20 ��ʱ������ʾ���е����ݣ�������ʾΪ��<b>**** ������Ϣ ���ָ��� 20 �������ʾ ****</b>��</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[list][*]�б��� #1[*]�б��� #2[*]�б��� #3[/list]</td>\r\n    <td><ul>\r\n      <li>�б��� ��1</li>\r\n      <li>�б��� ��2</li>\r\n      <li>�б��� ��3 </li>\r\n    </ul></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[fly]���е�Ч��[/fly]</td>\r\n    <td><marquee scrollamount="3" behavior="alternate" width="90%">���е�Ч��</marquee></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[flash]Flash��ҳ��ַ [/flash] </td>\r\n    <td>������Ƕ�� Flash ����</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[qq]123456789[/qq]</td>\r\n    <td>����������ʾ QQ ����״̬�������ͼ����Ժ�������������</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[ra]ra��ҳ��ַ[/ra]</td>\r\n    <td>������Ƕ�� Real ��Ƶ</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[rm]rm��ҳ��ַ[/rm] </td>\r\n    <td>������Ƕ�� Real ��Ƶ����Ƶ</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[wma]wma��ҳ��ַ[/wma] </td>\r\n    <td>������Ƕ�� Windows media ��Ƶ</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[wmv]wmv��ҳ��ַ[/wmv]</td>\r\n    <td>������Ƕ�� Windows media ��Ƶ����Ƶ</td>\r\n  </tr>\r\n  <tr>\r\n    <td>[img]http://www.discuz.net/images/default/logo.gif[/img] </td>\r\n    <td>��������ʾΪ��<img src="http://www.discuz.net/images/default/logo.gif"></td>\r\n  </tr>\r\n  <tr>\r\n    <td>[img=88,31]http://www.discuz.net/images/logo.gif[/img] </td>\r\n    <td>��������ʾΪ��<img src="http://www.discuz.net/images/logo.gif"></td>\r\n  </tr>\r\n</table>'),
	('19', '6', '1', '', '', '�����ʹ�ö���Ϣ����', '����¼�󣬵���������ϵĶ���Ϣ��ť�����ɽ������Ϣ����\r\n���[���Ͷ���Ϣ]��ť����"���͵�"�����������˵��û�������д���������ݣ����ύ(�� Ctrl+Enter ����)���ɷ�������Ϣ��\r\n<br><br>���Ҫ���浽�����䣬�����ύǰ��ѡ"���浽��������"ǰ�ĸ�ѡ��\r\n<ul>\r\n<li>����ռ���ɴ������ռ���鿴�յ��Ķ���Ϣ��</li>\r\n<li>���������ɲ鿴�����ڷ�������Ķ���Ϣ�� </li>\r\n<li>�����Ϣ�������鿴�Է��Ƿ��Ѿ��Ķ����Ķ���Ϣ�� </li>\r\n<li>�����������Ϣ�Ϳ�ͨ���ؼ��֣������ˣ������ˣ�������Χ���������͵�һϵ�������趨���ҵ�����Ҫ���ҵĶ���Ϣ�� </li>\r\n<li>�����������Ϣ���Խ��Լ��Ķ���Ϣ����htm�ļ��������Լ��ĵ���� </li>\r\n<li>��������б�����趨������Ա������Щ����ӵĺ����û��������Ͷ���Ϣʱ��������ա�</li>\r\n</ul>'),
	('20', '6', '2', '', '', '����������Ⱥ������Ϣ', '��¼��̳�󣬵������Ϣ��Ȼ��㷢�Ͷ���Ϣ������к��ѵĻ�������Ⱥ��������ȫѡ�����Ը����еĺ���Ⱥ������Ϣ��'),
	('21', '6', '3', '', '', '����β鿴��̳��Ա����', '�������������Ļ�Ա��Ȼ����ʾ���Ǵ���̳�Ļ�Ա���ݡ�ע����Ҫ��̳����Ա����������鿴��Ա���ϲſɿ�����'),
	('22', '6', '4', '', '', '�����ʹ������', '�����������������������������Ĺؼ��ֲ�ѡ��һ����Χ���Ϳ��Լ���������Ȩ�޷�����̳�е���ص����ӡ�'),
	('23', '6', '5', '', '', '�����ʹ�á��ҵġ�����', '<li>��Ա��������<a href="logging.php?action=login" target="_blank">��¼</a>��û���û���������<a href="register.php" target="_blank">ע��</a>��</li>\r\n<li>��¼֮������̳�����Ϸ������һ�����ҵġ��ĳ������ӣ�����������֮��Ϳɽ��뵽�й���������Ϣ��</li>'),
	('24', '7', '1', '', '', '����������Ա��������', '��һ�����ӣ������ӵ����½ǿ��Կ��������༭���������á��������桱�������֡������ظ����ȵȼ�����ť��������еġ����桱��ť���뱨��ҳ�棬��д�á��ҵ�����������������桱��ť������ɱ���ĳ�����ӵĲ�����'),
	('25', '7', '2', '', '', '����Ρ���ӡ�������Ƽ����������ġ������ղء�����', '�������һ������ʱ�����������Ͻǿ��Կ���������ӡ�������Ƽ����������ġ������ղء���������Ӧ���������Ӽ��������صĲ�����'),
	('26', '7', '3', '', '', '�����������̳����', '������̳������3�ּ򵥵ķ�����\r\n<ul>\r\n<li>����������ӵ�ʱ����Ե��������ʱ�䡱�Ҳ�ġ���Ϊ���ѡ�������̳���ѡ�</li>\r\n<li>�������ĳ�û��ĸ�������ʱ�����Ե��ͷ���·��ġ���Ϊ���ѡ�������̳���ѡ�</li>\r\n<li>��Ҳ�����ڿ�������еĺ����б�����������̳���ѡ�</li>\r\n<ul>'),
	('27', '7', '4', '', '', '�����ʹ��RSS����', '����̳����ҳ�ͽ������ҳ������ϽǾͻ����һ��rss���ĵ�Сͼ��<img src="images/common/xml.gif" border="0">�������֮�󽫳��ֱ�վ���rss��ַ������Խ���rss��ַ���뵽���rss�Ķ����н��ж��ġ�'),
	('28', '7', '5', '', '', '��������Cookies', 'cookie���������������ϵͳ�ڵģ�����̳�����½��ṩ��"��� Cookies"�Ĺ��ܣ�����󼴿ɰ������ϵͳ�ڴ洢��Cookies�� <br><br>\r\n���½���3�ֳ����������Cookies�������(ע���˷���Ϊ���ȫ����Cookies,�����ʹ��)\r\n<ul>\r\n<li>Internet Explorer: ���ߣ�ѡ��ڵ�Internetѡ�������ѡ��ڣ�IE6ֱ�ӿ��Կ���ɾ��Cookies�İ�ť������ɣ�IE7Ϊ��� ����ʷ��¼��ѡ���ڵ�ɾ������������Cookies������Maxthon,��ѶTT��IE���������һ�����á� </li>\r\n<li>FireFox:���ߡ�ѡ�����˽��Cookies����ʾCookie����Զ�Cookie���ж�Ӧ��ɾ�������� </li>\r\n<li>Opera:���ߡ���ѡ����߼���Cookies������Cookies���ɶ�Cookies����ɾ���Ĳ�����</li>\r\n</ul>'),
	('29', '7', '6', '', '', '�������ϵ����Ա', '������ͨ����̳�ײ����½ǵġ���ϵ���ǡ����ӿ��ٵķ����ʼ���������ϵ��Ҳ����ͨ�������Ŷ��е��û����Ϸ��Ͷ���Ϣ�����ǡ�'),
	('30', '7', '7', '', '', '����ο�ͨ���˿ռ�', '�������Ȩ�޿�ͨ���ҵĸ��˿ռ䡱�����û���¼��̳�Ժ�����̳��ҳ���û������ҷ������ͨ�ҵĸ��˿ռ䣬������˿ռ������ҳ�档'),
	('31', '7', '8', '', '', '����ν��Լ������������˿ռ�', '�������Ȩ�޿�ͨ���ҵĸ��˿ռ䡱����������������Ϸ������������˿ռ䡱��������������Լ��ظ�������뵽���ռ����־�'),
	('32', '5', '9', 'smilies', 'Smilies', '�����ʹ��Smilies����', 'Smilies��һЩ���ַ���ʾ�ı�����ţ������ Smilies ���ܣ�Discuz! ���һЩ����ת����Сͼ����ʾ�������У������������ˡ�Ŀǰ֧��������Щ Smilies��<br><br>\r\n<table cellspacing="0" cellpadding="4" width="30%" align="center">\r\n<tr><th width="25%" align="center">�������</td>\r\n<th width="75%" align="center">��Ӧͼ��</td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:)</td>\r\n<td width="75%" align="center"><img src="images/smilies/smile.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:(</td>\r\n<td width="75%" align="center"><img src="images/smilies/sad.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:D</td>\r\n<td width="75%" align="center"><img src="images/smilies/biggrin.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:\'(</td>\r\n<td width="75%" align="center"><img src="images/smilies/cry.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:@</td>\r\n<td width="75%" align="center"><img src="images/smilies/huffy.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:o</td>\r\n<td width="75%" align="center"><img src="images/smilies/shocked.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:P</td>\r\n<td width="75%" align="center"><img src="images/smilies/tongue.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:$</td>\r\n<td width="75%" align="center"><img src="images/smilies/shy.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">��P</td>\r\n<td width="75%" align="center"><img src="images/smilies/titter.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:L</td>\r\n<td width="75%" align="center"><img src="images/smilies/sweat.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:Q</td>\r\n<td width="75%" align="center"><img src="images/smilies/mad.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:lol</td>\r\n<td width="75%" align="center"><img src="images/smilies/lol.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:hug:</td>\r\n<td width="75%" align="center"><img src="images/smilies/hug.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:victory:</td>\r\n<td width="75%" align="center"><img src="images/smilies/victory.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:time:</td>\r\n<td width="75%" align="center"><img src="images/smilies/time.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:kiss:</td>\r\n<td width="75%" align="center"><img src="images/smilies/kiss.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:handshake</td>\r\n<td width="75%" align="center"><img src="images/smilies/handshake.gif" alt=""></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" align="center">:call:</td>\r\n<td width="75%" align="center"><img src="images/smilies/call.gif" alt=""></td>\r\n</tr>\r\n</table>\r\n</div></div>\r\n<br>'),
	('33','0','5','','','��̳�߼�����ʹ��',''),
	('34','33','0','forwardmessagelist','','��̳������ת�ؼ����б�','Discuz! ֧���Զ��������תҳ�棬��ĳЩ������ɺ󣬿��Բ���ʾ��ʾ��Ϣ��ֱ����ת���µ�ҳ�棬�Ӷ������û�������һ������������ȴ��� ��ʵ��ʹ�õ��У���������Ҫ���ѹؼ�����ӵ�������ת��������(��̨ -- �������� --  ��������ʾ��ʽ -- [<a href=\"admincp.php?action=settings&do=styles&frames=yes\" target=\"_blank\">��ʾ��Ϣ��ת����</a> ])����ĳЩ��Ϣ����ʾ��ʵ�ֿ�����ת�������� Discuz! ���е�һЩ������Ϣ�Ĺؼ���:\r\n</br></br>\r\n\r\n<table width=\"400\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"msgborder\" align=\"center\">\r\n  <tr class=\"msgheader\">\r\n    <td width=\"50%\">�ؼ���</td>\r\n    <td width=\"50%\">��ʾ��Ϣҳ���������</td>\r\n  </tr>\r\n  <tr>\r\n    <td>login_succeed</td>\r\n    <td>��¼�ɹ�</td>\r\n  </tr>\r\n  <tr>\r\n    <td>logout_succeed</td>\r\n    <td>�˳���¼�ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>thread_poll_succeed</td>\r\n    <td>ͶƱ�ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>thread_rate_succeed</td>\r\n    <td>���ֳɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>register_succeed</td>\r\n    <td>ע��ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>usergroups_join_succeed</td>\r\n    <td>������չ��ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td height=\"22\">usergroups_exit_succeed</td>\r\n    <td>�˳���չ��ɹ�</td>\r\n  </tr>\r\n  <tr>\r\n    <td>usergroups_update_succeed</td>\r\n    <td>������չ��ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>buddy_update_succeed</td>\r\n    <td>���Ѹ��³ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_edit_succeed</td>\r\n    <td>�༭���ӳɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_edit_delete_succeed</td>\r\n    <td>ɾ�����ӳɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_reply_succeed</td>\r\n    <td>�ظ��ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_newthread_succeed</td>\r\n    <td>����������ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_reply_blog_succeed</td>\r\n    <td>�ļ����۷���ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>post_newthread_blog_succeed</td>\r\n    <td>blog ����ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>&nbsp;profile_avatar_succeed</td>\r\n    <td>ͷ�����óɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>&nbsp;profile_succeed</td>\r\n    <td>�������ϸ��³ɹ�</td>\r\n  </tr>\r\n    <tr>\r\n    <td>pm_send_succeed</td>\r\n    <td>����Ϣ���ͳɹ�</td>\r\n  </tr>\r\n  </tr>\r\n    <tr>\r\n    <td>pm_delete_succeed</td>\r\n    <td>����Ϣɾ���ɹ�</td>\r\n  </tr>\r\n  </tr>\r\n    <tr>\r\n    <td>pm_ignore_succeed</td>\r\n    <td>����Ϣ�����б����</td>\r\n  </tr>\r\n    <tr>\r\n    <td>admin_succeed</td>\r\n    <td>��������ɹ���ע�⣺���ô˹ؼ��ֺ����й��������϶���ֱ����ת��</td>\r\n  </tr>\r\n    <tr>\r\n    <td>admin_succeed_next&nbsp;</td>\r\n    <td>����ɹ�������ת����һ��������</td>\r\n  </tr> \r\n    <tr>\r\n    <td>search_redirect</td>\r\n    <td>������ɣ�������������б�</td>\r\n  </tr>\r\n</table>');

EOT;

$upgrademsg = array(
	1 => '��̳������ 1 ��: ���ӻ�������<br><br>',
	2 => '��̳������ 2 ��: ������̳���ݱ�ṹ<br><br>',
	3 => '��̳������ 3 ��: ���²�������<br><br>',
	4 => '��̳������ 4 ��: ���������Ϣ<br><br>',
	5 => '��̳������ 5 ��: ������������<br><br>',
	6 => '��̳������ 6 ��: �������ݱ�<br><br>',
	7 => '��̳������ 7 ��: ������̳�������<br><br>',
	8 => '��̳������ 8 ��: SupeSite�����������<br><br>',
	9 => '��̳������ 9 ��: ���������������<br><br>',
	10 => '��̳������ 10 ��: ����ȫ�����<br><br>',
);

$errormsg = '';
if(!isset($dbhost)) {
	showerror("<span class=error>û���ҵ� config.inc.php �ļ�!</span><br>��ȷ�����Ѿ��ϴ������� $version_new �ļ�");
} elseif(!isset($cookiepre)) {
	showerror("<span class=error>config.inc.php �汾����!</span><br>���ϴ� $version_new �� config.inc.php�������������ݿ�����Ȼ�����½�������");
} elseif(!$dblink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
	showerror("<span class=error>config.inc.php ���ô���!</span><br>���޸� config.inc.php ���й������ݿ�����ã�Ȼ���ϴ�����̳Ŀ¼�����¿�ʼ����");
}

@mysql_close($dblink);
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$action) {

	if(!$tableinfo = loadtable('threads')) {
		showerror("<span class=error>�޷��ҵ� Discuz! ��̳���ݱ�!</span><br>���޸� config.inc.php ���й������ݿ�����ã�Ȼ���ϴ�����̳Ŀ¼�����¿�ʼ����");
	} elseif($db->version() > '4.1') {
		$old_dbcharset = substr($tableinfo['subject']['Collation'], 0, strpos($tableinfo['subject']['Collation'], '_'));
		if($old_dbcharset <> $dbcharset) {
			showerror("<span class=error>config.inc.php ���ݿ��ַ������ô���!</span><br>".
				"<li>ԭ�����ַ�������Ϊ��$old_dbcharset".
				"<li>��ǰʹ�õ��ַ���Ϊ��$dbcharset".
				"<li>���飺�޸� config.inc.php�� �����е� <b>\$dbcharset = ''</b> ���� <b>\$dbcharset = '$dbcharset'</b> �޸�Ϊ�� <b>\$dbcharset = '$old_dbcharset'</b>".
				"<li>�޸���Ϻ��ϴ� config.inc.php��Ȼ�����½�������"
			);
		}
	}

	echo <<< EOT
<span class="red">
����ǰ�������� JavaScript ֧��,�����������Զ���ɵ�,�����˹�����͸�Ԥ.<br>
����֮ǰ��ر������ݿ����ϣ���������ʧ���޷��ָ�<br></span><br>
��ȷ����������Ϊ:
<ol>
	<li>�ر�ԭ����̳,�ϴ� $version_new ��ȫ���ļ���Ŀ¼, ���Ƿ������ϵ� $version_old
	<li>�ϴ�����������̳Ŀ¼�У�<b>�������ú� config.inc.php</b>
	<li>���б�����,ֱ������������ɵ���ʾ
	<li>�����;ʧ�ܣ���ʹ��Discuz!�����䣨./utilities/tools.php����������ݻָ����߻ָ�����, ȥ��������������б�����
</ol>
<a href="$PHP_SELF?action=upgrade&step=1"><font size="2" color="red"><b>&gt;&gt;&nbsp;�������ȷ���������Ĳ���,�����������</b></font></a>
<br><br>
EOT;
	showfooter();

} else {

	$step = intval($step);
	echo '&gt;&gt;'.$upgrademsg[$step];
	flush();

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		if(!dir_writeable('./forumdata/logs')) {
			showerror('�������ʧ�ܣ��޷�����Ŀ¼ /forumdata/logs�����ֹ�������Ŀ¼��Ȼ������������������');
		} else {
			$logfilearray = array('cplog.php', 'illegallog.php', 'ratelog.php', 'medalslog.php', 'banlog.php', 'runwizardlog.php', 'errorlog.php', 'modslog.php', 'viewcount.log', 'dberror.log');
			foreach($logfilearray as $filename) {
				@copy('./forumdata/'.$filename, './forumdata/logs/'.$filename);
				@unlink('./forumdata/'.$filename);
			}
		}

		runquery($upgrade1);

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

		if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

			echo "�������ݱ� [ $start ] {$tablepre}{$upgradetable[$start][0]} :";
			$successed = upgradetable($upgradetable[$start]);

			if($successed === TRUE) {
				echo ' <font color=green>OK</font><br>';
			} elseif($successed === FALSE) {
				echo ' <font color=red>ERROR</font><br>';
			} elseif($successed == 'TABLE NOT EXISTS') {
				showerror('<span class=red>���ݱ�����</span>�����޷���������ȷ��������̳�汾�Ƿ���ȷ!</font><br>');
			}
		}

		$start ++;
		if(isset($upgradetable[$start])) {
			redirect("?action=upgrade&step=$step&start=$start");
		}

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade2);

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 4) {

		runquery($upgrade4);

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 5) {

		$db->query("DELETE FROM {$tablepre}crons WHERE type='system' AND filename='magics_daily.inc.php'", 'SILENT');
		$db->query("INSERT INTO {$tablepre}crons (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (1, 'system', '�����Զ�����', 'magics_daily.inc.php', $timestamp, $timestamp, -1, -1, 0, '0')", "SILENT");
		$db->query("INSERT INTO {$tablepre}crons (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (1, 'system', 'ÿ����֤�ʴ����', 'secqaa_daily.inc.php', 0, 0, -1, -1, 6, '0')", "SILENT");
		
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='msgbigsize'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='msgsmallsize'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='frameswitch'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='framebg'", 'SILENT');
		$db->query("DELETE FROM {$tablepre}stylevars WHERE variable='framebgcolor'", 'SILENT');

		$styleids = array();
		$query = $db->query("SELECT styleid FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$styleids[] = $style['styleid'];
		}

		foreach ($styleids as $styleid) {
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'msgbigsize', '')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'msgsmallsize', '')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'frameswitch', 'frame_switch.gif')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'framebg', 'frame_bg.gif')", 'SILENT');
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES ('$styleid', 'framebgcolor', '#E8F2F7')", 'SILENT');
		}

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 6) {

		runquery($upgrade6);

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 7) {

		runquery($upgrade7);

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 8) {

		$settings = array();
		$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable LIKE 'supe_%'");
		while($setting = $db->fetch_array($query)) {
			$settings[$setting['variable']] = $setting['value'];
		}
		$supe = array(
		'dbmode' => '0',
		'dbhost' => '',
		'dbuser' => '',
		'dbpw' => '',
		'dbname' => '',
		'status' => $settings['supe_status'],
		'tablepre' => $settings['supe_tablepre'],
		'siteurl' => $settings['supe_siteurl'],
		'sitename' => $settings['supe_sitename'],
		'maxupdateusers' => $settings['supe_maxupdateusers'],
		'items' => array(
		'status' => '1',
		'rows' => '4',
		'columns' => '3',
		'orderby' => '1'
		),
		'circlestatus' => '0'
		);

		$supe = addslashes(serialize($supe));

		$db->query("DELETE FROM {$tablepre}settings WHERE `variable` = 'supe_maxupdateusers'");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) values ('supe', '$supe')");

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 9) {

		$backupdir = random(6);
		@mkdir('forumdata/backup_'.$backupdir, 0777);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) values ('backupdir', '$backupdir')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('seccodedata', '".addslashes(serialize(array('loginfailedcount' => 0, 'animator' => 0, 'background' => 1, 'width' => mt_rand(70, 100), 'height' => mt_rand(25, 40))))."')");

		echo "�� $step �������ɹ�<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} else {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		echo '<br>��ϲ����̳���������ɹ���������������<ol><li><b>��ɾ��������</b>'.
		'<li>ʹ�ù���Ա��ݵ�¼��̳�������̨�����»���'.
		'<li><span class="red">���� Discuz! 5.5.0 ��ԭ config.inc.php ���й��ڸ��������ø�Ϊ���ݿ�洢������������ĸ���Ŀ¼�������Ĭ�����ã�������̨���������ã��������ã����и��ġ�������̳���������޷��ҵ�ԭ�еĸ���</span>'.
		'<li>������̳ע�ᡢ��¼�������ȳ�����ԣ����������Ƿ�����'.
		'<li><b>���������̳������ URL ��̬�����ܣ�����Ҫ�Ķ����û�ʹ��˵���顷���еĸ߼�Ӧ�ã����������� rewrite ���ã� ������̳����ҳ�������޷����ʵĴ���</b>'.
		'<li>�����ϣ������ <b>'.$version_new.'</b> �ṩ���¹��ܣ��㻹��Ҫ������̳�������á���Ŀ����Ա��ȵȽ�����������</ol><br>'.
		'<b>��л��ѡ�����ǵĲ�Ʒ��</b><a href="index.php" target="_blank">�����ڿ��Է�����̳���鿴�������</a><iframe width="0" height="0" src="index.php"></iframe>';
		showfooter();
	}
}

instfooter();

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	@touch($dir.'/index.htm');
	$directory->close();
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
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

function instfooter() {
	echo '</table></body></html>';
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function runquery($query) {
	global $db, $tablepre, $dbcharset;

	$query = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $query));
	$expquery = explode(";\n", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function loadtable($table, $force = 0) {
	global $db, $tablepre, $dbcharset;
	static $tables = array();

	if(!isset($tables[$table]) || $force) {
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM {$tablepre}$table", 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM {$tablepre}$table", 'SILENT');
		}
		while($field = @$db->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}

function upgradetable($updatesql) {
	global $db, $tablepre, $dbcharset;

	$successed = FALSE;

	if(is_array($updatesql) && !empty($updatesql[0])) {

		list($table, $action, $field, $sql) = $updatesql;

		if($tableinfo = loadtable($table)) {
			$fieldexist = isset($tableinfo[$field]) ? 1 : 0;

			$query = "ALTER TABLE {$tablepre}{$table} ";

			if($action == 'CHANGE') {

				$field2 = trim(substr($sql, 0, strpos($sql, ' ')));
				$field2exist = isset($tableinfo[$field2]);

				if($fieldexist && ($field == $field2 || !$field2exist)) {
					$query .= "CHANGE $field $sql";
				} elseif($fieldexist && $field2exist) {
					$db->query('ALTER TABLE {$tablepre}{$table} DROP $field2', 'SILENT');
					$query .= "CHANGE $field $sql";
				} elseif(!$fieldexist && $fieldexist2) {
					$db->query('ALTER TABLE {$tablepre}{$table} DROP $field2', 'SILENT');
					$query .= "ADD $sql";
				} elseif(!$fieldexist && !$field2exist) {
					$query .= "ADD $sql";
				}
				$successed = $db->query($query);

			} elseif($action == 'ADD') {

				$query .= $fieldexist ? "CHANGE $field $field $sql" :  "ADD $field $sql";
				if($successed = $db->query($query)) {
					$db->query("UPDATE LOW_PRIORITY IGNORE $tablepre{$table} SET $field=NULL", "UNBUFFERED");
				}

			} elseif($action == 'DROP') {
				if($fieldexist) {
					$successed = $db->query("$query DROP $field", "SILENT");
				}
				$successed = TRUE;
			}

		} else {

			$successed = 'TABLE NOT EXISTS';

		}
	}
	return $successed;
}

function showheader() {
	global $version_old, $version_new;

	print <<< EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Discuz! ��������( $version_old &gt;&gt; $version_new)</title>
<meta name="MSSmartTagsPreventParsing" content="TRUE">
<meta http-equiv="MSThemeCompatible" content="Yes">
<style>
a:visited	{color: #FF0000; text-decoration: none}
a:link		{color: #FF0000; text-decoration: none}
a:hover		{color: #FF0000; text-decoration: underline}
body,table,td	{color: #3a4273; font-family: Tahoma, verdana, arial; font-size: 12px; line-height: 20px; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
input		{color: #085878; font-family: Tahoma, verdana, arial; font-size: 12px; background-color: #3a4273; color: #ffffff; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
.install	{font-family: Arial, Verdana; font-size: 14px; font-weight: bold; color: #000000}
.header		{font: 12px Tahoma, Verdana; font-weight: bold; background-color: #3a4273 }
.header	td	{color: #ffffff}
.red		{color: red; font-weight: bold}
.bg1		{background-color: #e3e3ea}
.bg2		{background-color: #eeeef6}
</style>
</head>

<body bgcolor="#3A4273" text="#000000">
<table width="95%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
<tr>
<td>
<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="install" height="30" valign="bottom"><font color="#FF0000">&gt;&gt;</font>
Discuz! ��������( $version_old &gt;&gt; $version_new)</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr>
<td align="center">
<b>����������ֻ�ܴ� $version_old ������ $version_new ������֮ǰ����ȷ���Ѿ��ϴ������ļ������������ݱ���<br>
�����������κ���������ʼ���֧��վ�� <a href="http://www.discuz.net" target="_blank">http://www.discuz.net</a></b>
</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr><td>
EOT;
}

function showfooter() {
	echo <<< EOT
</td></tr></table></td></tr>
<tr><td height="100%">&nbsp;</td></tr>
</table>
</body>
</html>
EOT;
	exit();
}

function showerror($message, $break = 1) {
	echo '<br><br>'.$message.'<br><br>';
	if($break) showfooter();
}

function redirect($url) {

	echo <<< EOT
<hr size=1>
<script language="JavaScript">
	function redirect() {
		window.location.replace('$url');
	}
	setTimeout('redirect();', 1000);
</script>
<br><br>
&gt;&gt;<a href="$url">��������Զ���תҳ�棬�����˹���Ԥ�����ǵ������������ʱ��û���Զ���תʱ����������</a>
<br><br>
EOT;
	showfooter();
}
?>