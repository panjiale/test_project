<?php

// Upgrade Discuz! Board from 5.5.0 to 6.0.0 Final

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 5.5.0';
$version_new = 'Discuz! 6.0.0 ��ʽ��';
$timestamp = time();

@include(DISCUZ_ROOT."./config.inc.php");
@include(DISCUZ_ROOT."./include/db_mysql.class.php");

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
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

$upgrade1 = <<<EOT
REPLACE INTO cdb_settings (variable, value) VALUES ('tagstatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('hottags', 20);
REPLACE INTO cdb_settings (variable, value) VALUES ('viewthreadtags', 100);

UPDATE cdb_settings SET value=value*12 WHERE variable='maxsigrows';

REPLACE INTO cdb_settings (variable, value) VALUES ('mail', 'a:10:{s:8:"mailsend";s:1:"1";s:6:"server";s:13:"smtp.21cn.com";s:4:"port";s:2:"25";s:4:"auth";s:1:"1";s:4:"from";s:26:"Discuz <username@21cn.com>";s:13:"auth_username";s:17:"username@21cn.com";s:13:"auth_password";s:8:"password";s:13:"maildelimiter";s:1:"0";s:12:"mailusername";s:1:"1";s:15:"sendmail_silent";s:1:"1";}');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarktext', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarkminwidth', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarkminheight', '0');

DELETE FROM cdb_settings WHERE variable='ec_id';
DELETE FROM cdb_settings WHERE variable='ec_securitycode';
DELETE FROM cdb_settings WHERE variable='ec_commision';

REPLACE INTO cdb_settings (variable, value) VALUES ('inviteconfig', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('rewritecompatible', '');
REPLACE INTO cdb_settings (variable, value) values ('userdateformat','Y-n-j\r\nY/n/j\r\nj-n-Y\r\nj/n/Y');
REPLACE INTO cdb_settings (variable, value) VALUES ('regname', 'register.php');
REPLACE INTO cdb_settings (variable, value) VALUES ('reglinkname', 'ע��');
REPLACE INTO cdb_settings (variable, value) VALUES ('activitytype', '���Ѿۻ�\n���⽼��\n�Լݳ���\n����\n���ϻ');

REPLACE INTO cdb_settings (variable, value) VALUES ('tradeimagewidth', 200);
REPLACE INTO cdb_settings (variable, value) VALUES ('tradeimageheight', 150);
REPLACE INTO cdb_settings (variable, value) VALUES ('customauthorinfo', 'a:1:{i:0;a:9:{s:3:\"uid\";a:1:{s:4:\"menu\";s:1:\"1\";}s:5:\"posts\";a:1:{s:4:\"menu\";s:1:\"1\";}s:6:\"digest\";a:1:{s:4:\"menu\";s:1:\"1\";}s:7:\"credits\";a:1:{s:4:\"menu\";s:1:\"1\";}s:8:\"readperm\";a:1:{s:4:\"menu\";s:1:\"1\";}s:8:\"location\";a:1:{s:4:\"menu\";s:1:\"1\";}s:6:\"oltime\";a:1:{s:4:\"menu\";s:1:\"1\";}s:7:\"regtime\";a:1:{s:4:\"menu\";s:1:\"1\";}s:8:\"lastdate\";a:1:{s:4:\"menu\";s:1:\"1\";}}}');
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_credit', 'a:2:{s:18:"maxcreditspermonth";i:6;s:4:"rank";a:15:{i:1;i:4;i:2;i:11;i:3;i:41;i:4;i:91;i:5;i:151;i:6;i:251;i:7;i:501;i:8;i:1001;i:9;i:2001;i:10;i:5001;i:11;i:10001;i:12;i:20001;i:13;i:50001;i:14;i:100001;i:15;i:200001;}}');

REPLACE INTO cdb_settings (variable, value) VALUES ('imagelib', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('imageimpath', '');

REPLACE INTO cdb_settings (variable, value) VALUES ('historyposts', '0	0');

REPLACE INTO cdb_settings (variable, value) VALUES ('zoomstatus', '1');

REPLACE INTO cdb_settings (variable, value) VALUES ('postno', '#');
REPLACE INTO cdb_settings (variable, value) VALUES ('postnocustom', '');

REPLACE INTO cdb_settings (variable, value) VALUES ('maxbiotradesize', '400');
REPLACE INTO cdb_settings (variable, value) VALUES ('tradetypes', '');

REPLACE INTO cdb_settings (variable, value) VALUES ('baidusitemap', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('baidusitemap_life', '12');
REPLACE INTO cdb_settings (variable, value) VALUES ('google', '');


DELETE FROM cdb_crons WHERE filename='pushthreads_weekly.inc.php';
EOT;

$upgradetable = array(

	array('forums', 'ADD', 'allowmediacode', "TINYINT(1) NOT NULL DEFAULT '0'"),
	array('forums', 'MODIFY', 'allowpostspecial', "TINYINT(1) NOT NULL DEFAULT '63'"),
	array('forums', 'MODIFY', 'allowpostspecial', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0'"),

	array('forumfields', 'ADD', 'keywords', "TEXT NOT NULL AFTER postattachperm"),
	array('forumfields', 'ADD', 'formulaperm', "TEXT NOT NULL"),
	array('forumfields', 'ADD', 'modrecommend', "TEXT NOT NULL"),
	array('forumfields', 'ADD', 'tradetypes', "TEXT NOT NULL"),
	array('forumfields', 'ADD', 'typemodels', "MEDIUMTEXT NOT NULL"),

	array('myposts', 'ADD', 'special', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'"),

	array('mythreads', 'ADD', 'special', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'"),

	array('threadtypes', 'ADD', 'special', "SMALLINT(6) NOT NULL default '0'"),
	array('threadtypes', 'ADD', 'template', "TEXT NOT NULL"),

	array('smilies', 'CHANGE', 'displayorder', "displayorder TINYINT( 3 ) NOT NULL DEFAULT '0'"),
	array('smilies', 'ADD', 'typeid', "SMALLINT( 6 ) UNSIGNED NOT NULL AFTER id"),

	array('usergroups', 'ADD', 'allowpostdebate', "TINYINT(1) NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'tradestick', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'allowinvite', "TINYINT(1) NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'allowmailinvite', "TINYINT(1) NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'maxinvitenum', "TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'maxinviteday', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'inviteprice', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0'"),
	array('usergroups', 'ADD', 'allowpostvideo', "TINYINT (1) DEFAULT '0' NOT NULL  AFTER allowpostactivity"),
	array('usergroups', 'DROP', 'tradetaxtype', ""),
	array('usergroups', 'DROP', 'tradetaxs', ""),

	array('trades', 'ADD', 'pid', "INT(10) UNSIGNED NOT NULL AFTER tid"),
	array('trades', 'ADD', 'typeid', "SMALLINT(6) UNSIGNED NOT NULL AFTER pid"),
	array('trades', 'ADD', 'aid', "MEDIUMINT(8) UNSIGNED NOT NULL"),
	array('trades', 'ADD', 'displayorder', " TINYINT(1) NOT NULL"),
	array('trades', 'ADD', 'costprice', "DECIMAL(8,2) NOT NULL"),
	array('trades', 'MODIFY', 'price', "DECIMAL(8,2) NOT NULL"),
	array('trades', 'INDEX', '', "DROP PRIMARY KEY"),
	array('trades', 'INDEX', '', "ADD PRIMARY KEY (tid, pid)"),
	array('trades', 'INDEX', '', "ADD INDEX displayorder (tid, displayorder)"),
	array('trades', 'INDEX', '', "ADD INDEX sellertrades (sellerid, tradesum, totalitems)"),
	array('trades', 'INDEX', '', "ADD INDEX typeid (typeid)"),

	array('tradelog', 'ADD', 'pid', "INT(10) UNSIGNED NOT NULL AFTER tid"),
	array('tradelog', 'ADD', 'offline', "TINYINT(1) NOT NULL default '0'"),
	array('tradelog', 'ADD', 'buyername', "CHAR(50) NOT NULL"),
	array('tradelog', 'ADD', 'buyerzip', "CHAR(10) NOT NULL"),
	array('tradelog', 'ADD', 'buyerphone', "CHAR(20) NOT NULL"),
	array('tradelog', 'ADD', 'buyermobile', "CHAR(20) NOT NULL"),
	array('tradelog', 'ADD', 'transport', "TINYINT(1) NOT NULL default '0'"),
	array('tradelog', 'ADD', 'transportfee', "smallint(6) unsigned NOT NULL"),
	array('tradelog', 'ADD', 'baseprice', "decimal(8,2) NOT NULL"),
	array('tradelog', 'ADD', 'discount', "TINYINT(1) NOT NULL default '0'"),
	array('tradelog', 'ADD', 'ratestatus', "TINYINT(1) NOT NULL default '0'"),
	array('tradelog', 'MODIFY', 'price', "DECIMAL(8,2) NOT NULL"),
	array('tradelog', 'INDEX', '', "DROP INDEX tid"),
	array('tradelog', 'INDEX', '', "ADD INDEX tid (tid, pid)"),
	array('tradelog', 'INDEX', '', "ADD INDEX pid (pid)"),

	array('memberfields', 'ADD', 'buyercredit', "SMALLINT( 6 ) NOT NULL default '0'"),
	array('memberfields', 'ADD', 'sellercredit', "SMALLINT( 6 ) NOT NULL default '0'"),

	array('profilefields', 'INDEX', '', "ADD INDEX available (available,required,displayorder)"),

	array('posts', 'ADD', 'status', "TINYINT(1) NOT NULL DEFAULT '0'"),

	array('admingroups', 'ADD', 'allowbanpost', "TINYINT(1) NOT NULL DEFAULT '0'"),

	array('members', 'MODIFY', 'extgroupids', "char (20) NOT NULL DEFAULT ''"),
	array('members', 'MODIFY', 'email', "char (40) NOT NULL DEFAULT ''"),
	array('members', 'MODIFY', 'dateformat', "TINYINT(1) NOT NULL DEFAULT '0'"),
	array('members', 'INDEX', '', "ADD INDEX groupid (groupid)"),

	array('promotions', 'CHANGE', 'uid', "uid MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0'"),

	array('relatedthreads', 'ADD', 'type', "ENUM( 'general', 'trade' ) NOT NULL DEFAULT 'general' AFTER tid"),
	array('relatedthreads', 'INDEX', '', "DROP PRIMARY KEY , ADD PRIMARY KEY ( tid , type )"),

	array('threadtypes', 'ADD', 'modelid', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0' DEFAULT '0' AFTER `special`"),
	array('threadtypes', 'ADD', 'expiration', "TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER modelid"),

        array('tradelog', 'MODIFY', 'orderid', "varchar(32) NOT NULL"),
        array('tradelog', 'MODIFY', 'tradeno', "varchar(32) NOT NULL"),
        array('tradelog', 'MODIFY', 'subject', "varchar(100) NOT NULL"),
        array('tradelog', 'MODIFY', 'locus', "varchar(100) NOT NULL"),
        array('tradelog', 'MODIFY', 'seller', "varchar(15) NOT NULL"),
        array('tradelog', 'MODIFY', 'selleraccount', "varchar(50) NOT NULL"),
        array('tradelog', 'MODIFY', 'buyer', "varchar(15) NOT NULL"),
        array('tradelog', 'MODIFY', 'buyercontact', "varchar(50) NOT NULL"),
        array('tradelog', 'MODIFY', 'buyermsg', "varchar(200) default NULL"),
        array('tradelog', 'MODIFY', 'buyername', "varchar(50) NOT NULL"),
        array('tradelog', 'MODIFY', 'buyerzip', " varchar(10) NOT NULL"),
        array('tradelog', 'MODIFY', 'buyerphone', "varchar(20) NOT NULL"),
        array('tradelog', 'MODIFY', 'buyermobile', "varchar(20) NOT NULL"),
        array('tradelog', 'ADD', 'message', "text NOT NULL"),
	array('tradelog', 'MODIFY', 'transportfee', "smallint(6) unsigned NOT NULL DEFAULT '0'"),


);

$upgrade3 = <<<EOT

DROP TABLE IF EXISTS cdb_blogcaches;

DROP TABLE IF EXISTS cdb_imagetypes;
CREATE TABLE cdb_imagetypes (
  typeid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL,
  `type` enum('smiley','icon','avatar') NOT NULL DEFAULT 'smiley',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  `directory` char(100) NOT NULL,
  PRIMARY KEY (typeid)
) TYPE=MyISAM;

INSERT INTO cdb_imagetypes VALUES ('1','Ĭ�ϱ���','smiley','1','default');
UPDATE cdb_smilies SET typeid=1 WHERE type='smiley';

DROP TABLE IF EXISTS cdb_threadtags;
CREATE TABLE cdb_threadtags (
  `tagname` char(20) NOT NULL,
  `tid` int(10) unsigned NOT NULL,
  KEY `tagname` (`tagname`),
  KEY `tid` (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_tags;
CREATE TABLE cdb_tags (
  `tagname` char(20) NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `total` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`tagname`),
  KEY `total` (`total`),
  KEY `closed` (`closed`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_debates;
CREATE TABLE cdb_debates (
  tid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  uid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  starttime INT(10) UNSIGNED NOT NULL DEFAULT '0',
  endtime INT(10) UNSIGNED NOT NULL DEFAULT '0',
  affirmdebaters MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  negadebaters MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  affirmvotes MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  negavotes MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  umpire VARCHAR(15) NOT NULL DEFAULT '',
  winner TINYINT(1) NOT NULL DEFAULT '0',
  bestdebater VARCHAR(50) NOT NULL DEFAULT '',
  affirmpoint TEXT NOT NULL,
  negapoint TEXT NOT NULL,
  umpirepoint TEXT NOT NULL,
  affirmvoterids TEXT NOT NULL,
  negavoterids TEXT NOT NULL,
  affirmreplies MEDIUMINT(8) UNSIGNED NOT NULL,
  negareplies MEDIUMINT(8) UNSIGNED NOT NULL,
  PRIMARY KEY  (tid),
  KEY uid (uid,starttime)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_debateposts;
CREATE TABLE cdb_debateposts (
  pid INT(10) UNSIGNED NOT NULL DEFAULT '0',
  stand TINYINT(1) NOT NULL DEFAULT '0',
  tid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  uid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  dateline INT(10) UNSIGNED NOT NULL DEFAULT '0',
  voters MEDIUMINT(10) UNSIGNED NOT NULL DEFAULT '0',
  voterids TEXT NOT NULL ,
  PRIMARY KEY  (pid),
  KEY pid (pid,stand),
  KEY tid (tid,uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_typeoptions;
CREATE TABLE cdb_typeoptions (
  optionid smallint(6) unsigned NOT NULL auto_increment,
  classid smallint(6) unsigned NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(100) NOT NULL ,
  description varchar(255) NOT NULL,
  identifier varchar(40) NOT NULL,
  `type` varchar(20) NOT NULL,
  rules mediumtext NOT NULL,
  PRIMARY KEY  (optionid),
  KEY classid (classid)
) TYPE=MyISAM;

INSERT INTO cdb_typeoptions VALUES (1, 0, 0, 'ͨ����', '', '', '', '');
INSERT INTO cdb_typeoptions VALUES (2, 0, 0, '������', '', '', '', '');
INSERT INTO cdb_typeoptions VALUES (3, 0, 0, '������', '', '', '', '');
INSERT INTO cdb_typeoptions VALUES (4, 0, 0, '��ְ��Ƹ��', '', '', '', '');
INSERT INTO cdb_typeoptions VALUES (5, 0, 0, '������', '', '', '', '');
INSERT INTO cdb_typeoptions VALUES (6, 0, 0, '��������', '', '', '', '');
REPLACE INTO cdb_typeoptions VALUES (7, 1, 0, '����', '', 'name', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (9, 1, 0, '����', '', 'age', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (10, 1, 0, '��ַ', '', 'address', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (11, 1, 0, 'QQ', '', 'qq', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (12, 1, 0, '����', '', 'mail', 'email', '');
REPLACE INTO cdb_typeoptions VALUES (13, 1, 0, '�绰', '', 'phone', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (14, 5, 0, '��ѵ����', '', 'teach_pay', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (15, 5, 0, '��ѵʱ��', '', 'teach_time', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (20, 2, 0, '¥��', '', 'floor', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (21, 2, 0, '��ͨ״��', '', 'traf', 'textarea', '');
REPLACE INTO cdb_typeoptions VALUES (22, 2, 0, '��ͼ', '', 'images', 'image', '');
REPLACE INTO cdb_typeoptions VALUES (24, 2, 0, '�۸�', '', 'price', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (26, 5, 0, '��ѵ����', '', 'teach_name', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (28, 3, 0, '���', '', 'heighth', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (29, 3, 0, '����', '', 'weighth', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (33, 1, 0, '��Ƭ', '', 'photo', 'image', '');
REPLACE INTO cdb_typeoptions VALUES (35, 5, 0, '����ʽ', '', 'service_type', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (36, 5, 0, '����ʱ��', '', 'service_time', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (37, 5, 0, '�������', '', 'service_pay', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (39, 6, 0, '��ַ', '', 'site_url', 'url', '');
REPLACE INTO cdb_typeoptions VALUES (40, 6, 0, '�����ʼ�', '', 'site_mail', 'email', '');
REPLACE INTO cdb_typeoptions VALUES (42, 6, 0, '��վ����', '', 'site_name', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (46, 4, 0, 'ְλ', '', 'recr_intend', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (47, 4, 0, '�����ص�', '', 'recr_palce', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (49, 4, 0, '��Ч����', '', 'recr_end', 'calendar', '');
REPLACE INTO cdb_typeoptions VALUES (51, 4, 0, '��˾����', '', 'recr_com', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (52, 4, 0, '����Ҫ��', '', 'recr_age', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (54, 4, 0, 'רҵ', '', 'recr_abli', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (55, 5, 0, 'ʼ��', '', 'leaves', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (56, 5, 0, '�յ�', '', 'boundfor', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (57, 6, 0, 'Alexa����', '', 'site_top', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (58, 5, 0, '����/����', '', 'train_no', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (59, 5, 0, '����', '', 'trade_num', 'number', '');
REPLACE INTO cdb_typeoptions VALUES (60, 5, 0, '�۸�', '', 'trade_price', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (61, 5, 0, '��Ч����', '', 'trade_end', 'calendar', '');
REPLACE INTO cdb_typeoptions VALUES (63, 1, 0, '��ϸ����', '', 'detail_content', 'textarea', '');
REPLACE INTO cdb_typeoptions VALUES (64, 1, 0, '����', '', 'born_place', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (65, 2, 0, '���', '', 'money', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (66, 2, 0, '���', '', 'acreage', 'text', '');
REPLACE INTO cdb_typeoptions VALUES (67, 5, 0, '����ʱ��', '', 'time', 'calendar', 'N;');
REPLACE INTO cdb_typeoptions VALUES (68, 1, 0, '���ڵ�', '', 'now_place', 'text', '');

ALTER TABLE cdb_typeoptions AUTO_INCREMENT =3000;

DROP TABLE IF EXISTS cdb_typeoptionvars;
CREATE TABLE cdb_typeoptionvars (
  typeid smallint(6) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  optionid smallint(6) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  KEY typeid (typeid),
  KEY tid (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_tradeoptionvars;
CREATE TABLE cdb_tradeoptionvars (
  typeid smallint(6) unsigned NOT NULL default '0',
  pid mediumint(8) unsigned NOT NULL default '0',
  optionid smallint(6) unsigned NOT NULL default '0',
  `value` mediumtext NOT NULL,
  KEY typeid (typeid),
  KEY pid (pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_typevars;
CREATE TABLE cdb_typevars (
  typeid smallint(6) NOT NULL default '0',
  optionid smallint(6) NOT NULL default '0',
  available tinyint(1) NOT NULL default '0' ,
  required tinyint(1) NOT NULL DEFAULT '0',
  unchangeable tinyint(1) NOT NULL DEFAULT '0',
  search tinyint(1) NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL default '0',
  KEY typeid (typeid),
  UNIQUE optionid (typeid, optionid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_tradecomments;
CREATE TABLE cdb_tradecomments (
  id mediumint(8) NOT NULL auto_increment,
  orderid char(32) NOT NULL,
  pid int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL,
  raterid mediumint(8) unsigned NOT NULL,
  rater char(15) NOT NULL,
  rateeid mediumint(8) unsigned NOT NULL,
  ratee char(15) NOT NULL,
  message char(200) NOT NULL,
  explanation char(200) NOT NULL,
  score tinyint(1) NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  KEY raterid (raterid,`type`,dateline),
  KEY rateeid (rateeid,`type`,dateline),
  KEY orderid (orderid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_invites;
CREATE TABLE cdb_invites (
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0' ,
  expiration int(10) unsigned NOT NULL default '0',
  inviteip char(15) NOT NULL,
  invitecode char(16) NOT NULL ,
  reguid mediumint(8) unsigned NOT NULL default '0',
  regdateline int(10) unsigned NOT NULL default '0' ,
  `status` tinyint(1) NOT NULL default '1',
  KEY uid (uid,`status`),
  KEY invitecode (invitecode)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_forumrecommend;
CREATE TABLE cdb_forumrecommend (
  fid smallint(6) unsigned NOT NULL,
  tid mediumint(8) unsigned NOT NULL,
  displayorder tinyint(1) NOT NULL,
  subject char(80) NOT NULL,
  author char(15) NOT NULL,
  authorid mediumint(8) NOT NULL,
  moderatorid mediumint(8) NOT NULL,
  expiration int(10) unsigned NOT NULL,
  PRIMARY KEY  (tid),
  KEY displayorder (fid,displayorder)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_caches;
CREATE TABLE cdb_caches (
  cachename varchar(32) NOT NULL,
  type tinyint(3) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  expiration int(10) unsigned NOT NULL,
  data mediumtext NOT NULL,
  PRIMARY KEY (cachename),
  KEY expiration (type,expiration)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_videos;
CREATE TABLE cdb_videos (
  vid varchar(16) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  vtype tinyint(1) unsigned NOT NULL default '0',
  vview mediumint(8) unsigned NOT NULL default '0',
  vtime smallint(6) unsigned NOT NULL default '0',
  visup tinyint(1) unsigned NOT NULL default '0',
  vthumb varchar(128) NOT NULL default '',
  vtitle varchar(64) NOT NULL default '',
  vclass varchar(32) NOT NULL default '',
  vautoplay tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (vid),
  UNIQUE KEY uid (vid,uid),
  KEY dateline (dateline)
) Type=MyISAM;

DROP TABLE IF EXISTS cdb_videotags;
CREATE TABLE cdb_videotags (
  tagname char(10) NOT NULL DEFAULT '',
  vid char(16) NOT NULL DEFAULT '',
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY tagname (tagname,vid),
  KEY tid (tid)
) TYPE=MyISAM;


EOT;

$upgrade4 = <<<EOT

DROP TABLE IF EXISTS cdb_searchindex;
CREATE TABLE cdb_searchindex (
  searchid int(10) unsigned NOT NULL AUTO_INCREMENT,
  keywords varchar(255) NOT NULL DEFAULT '',
  searchstring text NOT NULL,
  useip varchar(15) NOT NULL DEFAULT '',
  uid mediumint(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  expiration int(10) unsigned NOT NULL DEFAULT '0',
  threads smallint(6) unsigned NOT NULL DEFAULT '0',
  threadtypeid smallint(6) unsigned NOT NULL DEFAULT '0',
  tids text NOT NULL,
  PRIMARY KEY (searchid)
) TYPE=MyISAM AUTO_INCREMENT=1;

DROP TABLE IF EXISTS cdb_typemodels;
CREATE TABLE cdb_typemodels (
  id smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  `type` tinyint(1) NOT NULL default '0',
  options mediumtext NOT NULL,
  customoptions mediumtext NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

INSERT INTO cdb_typemodels (id, name, displayorder, type, options, customoptions) VALUES (1, '���ݽ�����Ϣ', 0, 1, '7	10	13	65	66	68', '');
INSERT INTO cdb_typemodels (id, name, displayorder, type, options, customoptions) VALUES (2, '��Ʊ������Ϣ', 0, 1, '55	56	58	67	7	13	68', '');
INSERT INTO cdb_typemodels (id, name, displayorder, type, options, customoptions) VALUES (3, '��Ȥ������Ϣ', 0, 1, '8	9	31', '');
INSERT INTO cdb_typemodels (id, name, displayorder, type, options, customoptions) VALUES (4, '��˾��Ƹ��Ϣ', 0, 1, '34	48	54	51	47	46	44	45	52	53', '');

ALTER TABLE cdb_typemodels AUTO_INCREMENT=101;

UPDATE cdb_admingroups SET allowbanpost=1;

DELETE FROM cdb_crons WHERE filename='tags_daily.inc.php';
INSERT INTO cdb_crons (`available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ('1','system','ÿ�ձ�ǩ����','tags_daily.inc.php','0','1170600452','-1','-1','0','0');

UPDATE cdb_members SET dateformat=0;

EOT;

$upgrade6 = <<<EOT

EOT;

$upgrade7 = <<<EOT

INSERT INTO cdb_bbcodes VALUES ({bbcodeid,1}, '0', 'sup', 'bb_sup.gif', '<sup>{1}</sup>', 'X[sup]2[/sup]', '�ϱ�', 1, '�������ϱ����֣�', '1');
INSERT INTO cdb_bbcodes VALUES ({bbcodeid,2}, '0', 'sub', 'bb_sub.gif', '<sub>{1}</sub>', 'X[sub]2[/sub]', '�±�', 1, '�������±����֣�', '1');

DROP TABLE IF EXISTS cdb_styles;
CREATE TABLE cdb_styles (
  styleid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  available tinyint(1) NOT NULL DEFAULT '1',
  templateid smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (styleid)
) TYPE=MyISAM AUTO_INCREMENT=3;

INSERT INTO cdb_styles VALUES ('1','Ĭ�Ϸ��','1','1');
INSERT INTO cdb_styles VALUES ('2','�Ȳʰ���','1','2');
INSERT INTO cdb_styles VALUES ('3','��������','1','3');
INSERT INTO cdb_styles VALUES ('4','��ױ����','1','4');
INSERT INTO cdb_styles VALUES ('5','ʫ����԰','1','1');
INSERT INTO cdb_styles VALUES ('6','���ⰻȻ','1','1');

DROP TABLE IF EXISTS cdb_stylevars;
CREATE TABLE cdb_stylevars (
  stylevarid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  styleid smallint(6) unsigned NOT NULL DEFAULT '0',
  variable text NOT NULL,
  substitute text NOT NULL,
  PRIMARY KEY (stylevarid),
  KEY styleid (styleid)
) TYPE=MyISAM AUTO_INCREMENT=42;

INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  (1, 'available', ''),
  (1, 'commonboxborder', '#E8E8E8'),
  (1, 'noticebg', '#FFFFF2'),
  (1, 'tablebg', '#FFF'),
  (1, 'highlightlink', '#069'),
  (1, 'commonboxbg', '#F7F7F7'),
  (1, 'bgcolor', '#FFF'),
  (1, 'altbg1', '#F5FAFE'),
  (1, 'altbg2', '#E8F3FD'),
  (1, 'link', '#000'),
  (1, 'bordercolor', '#9DB3C5'),
  (1, 'headercolor', '#2F589C header_bg.gif'),
  (1, 'headertext', '#FFF'),
  (1, 'tabletext', '#000'),
  (1, 'text', '#666'),
  (1, 'catcolor', '#E8F3FD cat_bg.gif'),
  (1, 'borderwidth', '1px'),
  (1, 'fontsize', '12px'),
  (1, 'tablespace', '1px'),
  (1, 'msgfontsize', '14px'),
  (1, 'msgbigsize', '16px'),
  (1, 'msgsmallsize', '12px'),
  (1, 'font', 'Helvetica, Arial, sans-serif'),
  (1, 'smfontsize', '0.83em'),
  (1, 'smfont', 'Verdana, Arial, Helvetica, sans-serif'),
  (1, 'bgborder', '#CAD9EA'),
  (1, 'maintablewidth', '98%'),
  (1, 'imgdir', 'images/default'),
  (1, 'boardimg', 'logo.gif'),
  (1, 'inputborder', '#DDD'),
  (1, 'catborder', '#CAD9EA'),
  (1, 'lighttext', '#999'),
  (1, 'framebgcolor', 'frame_bg.gif'),
  (1, 'headermenu', '#FFF menu_bg.gif'),
  (1, 'headermenutext', '#333'),
  (1, 'boxspace', '10px'),
  (1, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  (1, 'noticeborder', '#EDEDCE'),
  (1, 'noticetext', '#090'),
  (1, 'stypeid', '1');
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  (2, 'available', ''),
  (2, 'bgcolor', '#FFF'),
  (2, 'altbg1', '#FFF'),
  (2, 'altbg2', '#F7F7F3'),
  (2, 'link', '#262626'),
  (2, 'bordercolor', '#C1C1C1'),
  (2, 'headercolor', '#FFF forumbox_head.gif'),
  (2, 'headertext', '#D00'),
  (2, 'catcolor', '#F90 cat_bg.gif'),
  (2, 'tabletext', '#535353'),
  (2, 'text', '#535353'),
  (2, 'borderwidth', '1px'),
  (2, 'tablespace', '1px'),
  (2, 'fontsize', '12px'),
  (2, 'msgfontsize', '14px'),
  (2, 'msgbigsize', '16px'),
  (2, 'msgsmallsize', '12px'),
  (2, 'font', 'Arial,Helvetica,sans-serif'),
  (2, 'smfontsize', '11px'),
  (2, 'smfont', 'Arial,Helvetica,sans-serif'),
  (2, 'boardimg', 'logo.gif'),
  (2, 'imgdir', './images/Beijing2008'),
  (2, 'maintablewidth', '98%'),
  (2, 'bgborder', '#C1C1C1'),
  (2, 'catborder', '#E2E2E2'),
  (2, 'inputborder', '#D7D7D7'),
  (2, 'lighttext', '#535353'),
  (2, 'headermenu', '#FFF menu_bg.gif'),
  (2, 'headermenutext', '#54564C'),
  (2, 'framebgcolor', ''),
  (2, 'noticebg', ''),
  (2, 'commonboxborder', '#F0F0ED'),
  (2, 'tablebg', '#FFF'),
  (2, 'highlightlink', '#535353'),
  (2, 'commonboxbg', '#F5F5F0'),
  (2, 'boxspace', '8px'),
  (2, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  (2, 'noticeborder', ''),
  (2, 'noticetext', '#DD0000'),
  (2, 'stypeid', '1');
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  (3, 'available', ''),
  (3, 'bgcolor', '#222D2D'),
  (3, 'altbg1', '#3E4F4F'),
  (3, 'altbg2', '#384747'),
  (3, 'link', '#CEEBEB'),
  (3, 'bordercolor', '#1B2424'),
  (3, 'headercolor', '#1B2424'),
  (3, 'headertext', '#94B3C5'),
  (3, 'catcolor', '#293838'),
  (3, 'tabletext', '#CEEBEB'),
  (3, 'text', '#999'),
  (3, 'borderwidth', '6px'),
  (3, 'tablespace', '0'),
  (3, 'fontsize', '12px'),
  (3, 'msgfontsize', '14px'),
  (3, 'msgbigsize', '16px'),
  (3, 'msgsmallsize', '12px'),
  (3, 'font', 'Arial'),
  (3, 'smfontsize', '11px'),
  (3, 'smfont', 'Arial,sans-serif'),
  (3, 'boardimg', 'logo.gif'),
  (3, 'imgdir', './images/Overcast'),
  (3, 'maintablewidth', '98%'),
  (3, 'bgborder', '#384747'),
  (3, 'catborder', '#1B2424'),
  (3, 'inputborder', '#EEE'),
  (3, 'lighttext', '#74898E'),
  (3, 'headermenu', '#3E4F4F'),
  (3, 'headermenutext', '#CEEBEB'),
  (3, 'framebgcolor', '#222D2D'),
  (3, 'noticebg', '#3E4F4F'),
  (3, 'commonboxborder', '#384747'),
  (3, 'tablebg', '#3E4F4F'),
  (3, 'highlightlink', '#9CB2A0'),
  (3, 'commonboxbg', '#384747'),
  (3, 'boxspace', '6px'),
  (3, 'portalboxbgcode', '#293838'),
  (3, 'noticeborder', '#384747'),
  (3, 'noticetext', '#C7E001'),
  (3, 'stypeid', '1');
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  (4, 'noticetext', '#C44D4D'),
  (4, 'noticeborder', '#D6D6D6'),
  (4, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  (4, 'boxspace', '6px'),
  (4, 'commonboxbg', '#FAFAFA'),
  (4, 'highlightlink', '#C44D4D'),
  (4, 'tablebg', '#FFF'),
  (4, 'commonboxborder', '#DEDEDE'),
  (4, 'noticebg', '#FAFAFA'),
  (4, 'framebgcolor', '#FFECF9'),
  (4, 'headermenu', 'transparent'),
  (4, 'headermenutext', ''),
  (4, 'lighttext', '#999'),
  (4, 'catborder', '#D7D7D7'),
  (4, 'inputborder', ''),
  (4, 'bgborder', '#CECECE'),
  (4, 'stypeid', '1'),
  (4, 'maintablewidth', '920px'),
  (4, 'imgdir', 'images/PinkDresser'),
  (4, 'boardimg', 'logo.gif'),
  (4, 'smfont', 'Arial,Helvetica,sans-serif'),
  (4, 'smfontsize', '12px'),
  (4, 'font', 'Arial,Helvetica,sans-serif'),
  (4, 'msgsmallsize', '12px'),
  (4, 'msgbigsize', '16px'),
  (4, 'msgfontsize', '14px'),
  (4, 'fontsize', '12px'),
  (4, 'tablespace', '0'),
  (4, 'borderwidth', '1px'),
  (4, 'text', '#666'),
  (4, 'tabletext', '#666'),
  (4, 'catcolor', '#FAFAFA category_bg.gif'),
  (4, 'headertext', '#FFF'),
  (4, 'headercolor', '#E7BFC9 forumbox_head.gif'),
  (4, 'bordercolor', '#D88E9D'),
  (4, 'link', '#C44D4D'),
  (4, 'altbg2', '#F1F1F1'),
  (4, 'available', ''),
  (4, 'altbg1', '#FBFBFB'),
  (4, 'bgcolor', '#FBF4F5 bg.gif');
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  (5, 'available', ''),
  (5, 'bgcolor', '#FFF'),
  (5, 'altbg1', '#FFFBF8'),
  (5, 'altbg2', '#FBF6F1'),
  (5, 'link', '#54564C'),
  (5, 'bordercolor', '#D7B094'),
  (5, 'headercolor', '#BE6A2D forumbox_head.gif'),
  (5, 'headertext', '#FFF'),
  (5, 'catcolor', '#E9E9E9 cat_bg.gif'),
  (5, 'tabletext', '#7B7D72'),
  (5, 'text', '#535353'),
  (5, 'borderwidth', '1px'),
  (5, 'tablespace', '1px'),
  (5, 'fontsize', '12px'),
  (5, 'msgfontsize', '14px'),
  (5, 'msgbigsize', '16px'),
  (5, 'msgsmallsize', '12px'),
  (5, 'font', 'Arial, sans-serif'),
  (5, 'smfontsize', '11px'),
  (5, 'smfont', 'Arial, sans-serif'),
  (5, 'boardimg', 'logo.gif'),
  (5, 'imgdir', './images/Picnicker'),
  (5, 'maintablewidth', '98%'),
  (5, 'bgborder', '#E8C9B7'),
  (5, 'catborder', '#E6E6E2'),
  (5, 'inputborder', ''),
  (5, 'lighttext', '#878787'),
  (5, 'headermenu', '#FFF menu_bg.gif'),
  (5, 'headermenutext', '#54564C'),
  (5, 'framebgcolor', 'frame_bg.gif'),
  (5, 'noticebg', '#FAFAF7'),
  (5, 'commonboxborder', '#E6E6E2'),
  (5, 'tablebg', '#FFF'),
  (5, 'highlightlink', ''),
  (5, 'commonboxbg', '#F5F5F0'),
  (5, 'boxspace', '6px'),
  (5, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  (5, 'noticeborder', '#E6E6E2'),
  (5, 'noticetext', '#FF3A00'),
  (5, 'stypeid', '1');
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  (6, 'available', ''),
  (6, 'bgcolor', '#FFF'),
  (6, 'altbg1', '#F5F5F0'),
  (6, 'altbg2', '#F9F9F9'),
  (6, 'link', '#54564C'),
  (6, 'bordercolor', '#D9D9D4'),
  (6, 'headercolor', '#80A400 forumbox_head.gif'),
  (6, 'headertext', '#FFF'),
  (6, 'catcolor', '#F5F5F0 cat_bg.gif'),
  (6, 'tabletext', '#7B7D72'),
  (6, 'text', '#535353'),
  (6, 'borderwidth', '1px'),
  (6, 'tablespace', '1px'),
  (6, 'fontsize', '12px'),
  (6, 'msgfontsize', '14px'),
  (6, 'msgbigsize', '16px'),
  (6, 'msgsmallsize', '12px'),
  (6, 'font', 'Arial,sans-serif'),
  (6, 'smfontsize', '11px'),
  (6, 'smfont', 'Arial,sans-serif'),
  (6, 'boardimg', 'logo.gif'),
  (6, 'imgdir', './images/GreenPark'),
  (6, 'maintablewidth', '98%'),
  (6, 'bgborder', '#D9D9D4'),
  (6, 'catborder', '#D9D9D4'),
  (6, 'inputborder', '#D9D9D4'),
  (6, 'lighttext', '#878787'),
  (6, 'headermenu', '#FFF menu_bg.gif'),
  (6, 'headermenutext', '#262626'),
  (6, 'framebgcolor', ''),
  (6, 'noticebg', '#FAFAF7'),
  (6, 'commonboxborder', '#E6E6E2'),
  (6, 'tablebg', '#FFF'),
  (6, 'highlightlink', '#535353'),
  (6, 'commonboxbg', '#F9F9F9'),
  (6, 'boxspace', '6px'),
  (6, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  (6, 'noticeborder', '#E6E6E2'),
  (6, 'noticetext', '#FF3A00'),
  (6, 'stypeid', '1');

DROP TABLE IF EXISTS cdb_templates;
CREATE TABLE cdb_templates (
  templateid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `directory` varchar(100) NOT NULL DEFAULT '',
  copyright varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (templateid)
) TYPE=MyISAM AUTO_INCREMENT=3;

INSERT INTO cdb_templates VALUES ('1','Ĭ��ģ����ϵ','./templates/default','��ʢ���루�������Ƽ����޹�˾');
INSERT INTO cdb_templates VALUES ('2','�Ȳʰ���','./templates/Beijing2008','��ʢ���루�������Ƽ����޹�˾');
INSERT INTO cdb_templates VALUES ('3','��������','./templates/Overcast','��ʢ���루�������Ƽ����޹�˾');
INSERT INTO cdb_templates VALUES ('4','��ױ����','./templates/PinkDresser','��ʢ���루�������Ƽ����޹�˾');

REPLACE INTO cdb_settings (variable, value) VALUES ('styleid','1');

EOT;

$insenz_upgrade = <<<EOT

DROP TABLE IF EXISTS cdb_pushedthreads;
DROP TABLE IF EXISTS cdb_campaigns;
CREATE TABLE cdb_campaigns (
  id mediumint(8) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  fid smallint(6) unsigned NOT NULL,
  tid mediumint(8) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  begintime int(10) unsigned NOT NULL,
  starttime int(10) unsigned NOT NULL,
  endtime int(10) unsigned NOT NULL,
  expiration int(10) unsigned NOT NULL,
  nextrun int(10) unsigned NOT NULL,
  PRIMARY KEY  (id,`type`),
  KEY tid (tid),
  KEY nextrun (nextrun)
) TYPE=MyISAM;

EOT;

$upgrademsg = array(

	1 => '��̳������ 1 ��: ���ӻ�������<br /><br />',
	2 => '��̳������ 2 ��: ������̳���ݱ�ṹ<br /><br />',
	3 => '��̳������ 3 ��: �������ݱ�<br /><br />',

	4 => '��̳������ 4 ��: ���²�������<br /><br />',
	5 => '��̳������ 5 ��: �����ʼ�����<br /><br />',
	6 => '��̳������ 6 ��: ����������������<br /><br />',
	7 => '��̳������ 7 ��: ������̳���<br /><br />',

	8 => '��̳������ 8 ��: Insenz�����������<br /><br />',
	9 => '��̳������ 9 ��: ���������������<br /><br />',
	10 => '��̳������ 10 ��: ����ȫ�����<br /><br />',
);

$errormsg = '';
if(!isset($dbhost)) {
	showerror("<span class=error>û���ҵ� config.inc.php �ļ�!</span><br />��ȷ�����Ѿ��ϴ������� $version_new �ļ�");
} elseif(!isset($cookiepre)) {
	showerror("<span class=error>config.inc.php �汾����!</span><br />���ϴ� $version_new �� config.inc.php�������������ݿ�����Ȼ�����½�������");
} elseif(!$dblink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
	showerror("<span class=error>config.inc.php ���ô���!</span><br />���޸� config.inc.php ���й������ݿ�����ã�Ȼ���ϴ�����̳Ŀ¼�����¿�ʼ����");
}

@mysql_close($dblink);
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$action) {

	if(!$tableinfo = loadtable('threads')) {
		showerror("<span class=error>�޷��ҵ� Discuz! ��̳���ݱ�!</span><br />���޸� config.inc.php ���й������ݿ�����ã�Ȼ���ϴ�����̳Ŀ¼�����¿�ʼ����");
	} elseif($db->version() > '4.1') {
		$old_dbcharset = substr($tableinfo['subject']['Collation'], 0, strpos($tableinfo['subject']['Collation'], '_'));
		if($old_dbcharset <> $dbcharset) {
			showerror("<span class=error>config.inc.php ���ݿ��ַ������ô���!</span><br />".
				"<li>ԭ�����ַ�������Ϊ��$old_dbcharset".
				"<li>��ǰʹ�õ��ַ���Ϊ��$dbcharset".
				"<li>���飺�޸� config.inc.php�� �����е� <b>\$dbcharset = ''</b> ���� <b>\$dbcharset = '$dbcharset'</b> �޸�Ϊ�� <b>\$dbcharset = '$old_dbcharset'</b>".
				"<li>�޸���Ϻ��ϴ� config.inc.php��Ȼ�����½�������"
			);
		}
	}

	echo <<< EOT
<span class="red">
����ǰ�������� JavaScript ֧��,�����������Զ���ɵ�,�����˹�����͸�Ԥ.<br />
����֮ǰ��ر������ݿ����ϣ���������ʧ���޷��ָ�<br /></span><br />
��ȷ����������Ϊ:
<ol>
	<li>�ر�ԭ����̳,�ϴ� $version_new ��ȫ���ļ���Ŀ¼, ���Ƿ������ϵ� $version_old
	<li>�ϴ�����������̳Ŀ¼�У�<b>�������ú� config.inc.php</b>
	<li>���б�����,ֱ������������ɵ���ʾ
	<li>�����;ʧ�ܣ���ʹ��Discuz!�����䣨./utilities/tools.php����������ݻָ����߻ָ�����, ȥ��������������б�����
</ol>
<a href="$PHP_SELF?action=upgrade&step=1"><font size="2" color="red"><b>&gt;&gt;&nbsp;�������ȷ���������Ĳ���,�����������</b></font></a>
<br /><br />
EOT;
	showfooter();

} else {

	$step = intval($step);
	echo '&gt;&gt;'.$upgrademsg[$step];
	flush();

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		runquery($upgrade1);

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

			echo "�������ݱ� [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:";
			$successed = upgradetable($upgradetable[$start]);

			if($successed === TRUE) {
				echo ' <font color=green>OK</font><br />';
			} elseif($successed === FALSE) {
				//echo ' <font color=red>ERROR</font><br />';
			} elseif($successed == 'TABLE NOT EXISTS') {
				showerror('<span class=red>���ݱ�����</span>�����޷���������ȷ��������̳�汾�Ƿ���ȷ!</font><br />');
			}
		}

		$start ++;
		if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {
			redirect("?action=upgrade&step=$step&start=$start");
		}

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade3);

		$optionlist = array (
			  8 => array (
			    	'classid' => '1',
			   	'displayorder' => '2',
			    	'title' => '�Ա�',
			    	'identifier' => 'gender',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=��\r\n2=Ů",
			    		),
			  	),
			  16 => array (
			    	'classid' => '2',
			    	'displayorder' => '0',
			    	'title' => '��������',
			    	'identifier' => 'property',
			    	'type' => 'select',
			    	'rules' => array (
			      			'choices' => "1=д��¥\r\n2=��Ԣ\r\n3=С��\r\n4=ƽ��\r\n5=����\r\n6=������",
			    		),
			  	),
			  17 => array (
			    	'classid' => '2',
			    	'displayorder' => '0',
			    	'title' => '����',
			    	'identifier' => 'face',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=����\r\n2=����\r\n3=����\r\n4=����",
			    		),
			  	),
			  18 => array (
			    	'classid' => '2',
			    	'displayorder' => '0',
			    	'title' => 'װ�����',
			    	'identifier' => 'makes',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=��װ��\r\n2=��װ��\r\n3=��װ��",
			    		),
			  	),
			  19 => array (
			    	'classid' => '2',
			    	'displayorder' => '0',
			    	'title' => '����',
			    	'identifier' => 'mode',
			    	'type' => 'select',
			    	'rules' => array (
			      			'choices' => "1=����\r\n2=������\r\n3=������\r\n4=�ľ���\r\n5=����",
			    		),
			  	),
			  23 => array (
			    	'classid' => '2',
			    	'displayorder' => '0',
			    	'title' => '������ʩ',
			    	'identifier' => 'equipment',
			    	'type' => 'checkbox',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=ˮ��\r\n2=���\r\n3=�ܵ���\r\n4=���ߵ���\r\n5=����\r\n6=�绰\r\n7=����\r\n8=ϴ�»�\r\n9=��ˮ��\r\n10=�յ�\r\n11=ů��\r\n12=΢��¯\r\n13=���̻�\r\n14=��ˮ��",
			   		),
			  	),
			  25 => array (
			    	'classid' => '2',
			    	'displayorder' => '0',
			    	'title' => '�Ƿ��н�',
			    	'identifier' => 'bool',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=��\r\n2=��",
			    		),
			  	),
			  27 => array (
			    	'classid' => '3',
			   	'displayorder' => '0',
			    	'title' => '����',
			    	'identifier' => 'Horoscope',
			    	'type' => 'select',
			    	'rules' => array (
			      			'choices' => "1=������\r\n2=��ţ��\r\n3=˫����\r\n4=��з��\r\n5=ʨ����\r\n6=��Ů��\r\n7=�����\r\n8=��Ы��\r\n9=������\r\n10=Ħ����\r\n11=ˮƿ��\r\n12=˫����",
			    		),
			  	),
			  30 => array (
			    	'classid' => '3',
			    	'displayorder' => '0',
			    	'title' => '����״��',
			    	'identifier' => 'marrige',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'choices' => "1=�ѻ�\r\n2=δ��",
			    		),
			  	),
			  31 => array (
			    	'classid' => '3',
			    	'displayorder' => '0',
			    	'title' => '����',
			    	'identifier' => 'hobby',
			    	'type' => 'checkbox',
			    	'rules' => array (
			      			'choices' => "1=��ʳ\r\n2=����\r\n3=����\r\n4=��Ӱ\r\n5=����\r\n6=Ϸ��\r\n7=����\r\n8=����\r\n9=����\r\n10=����\r\n11=��Ϸ\r\n12=�滭\r\n13=�鷨\r\n14=����\r\n15=����\r\n16=�Ķ�\r\n17=�˶�\r\n18=����\r\n19=����\r\n20=����\r\n21=׬Ǯ\r\n22=����\r\n23=��Ӱ",
			    		),
			  	),
			  32 => array (
			    	'classid' => '3',
			    	'displayorder' => '0',
			    	'title' => '���뷶Χ',
			    	'identifier' => 'salary',
			    	'type' => 'select',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=����\r\n2=800Ԫ����\r\n3=1500Ԫ����\r\n4=2000Ԫ����\r\n5=3000Ԫ����\r\n6=5000Ԫ����\r\n7=8000Ԫ����",
			    		),
			  	),
			  34 => array (
			    	'classid' => '1',
			    	'displayorder' => '0',
			    	'title' => 'ѧ��',
			    	'identifier' => 'education',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=��ä\r\n2=Сѧ\r\n3=����\r\n4=����\r\n5=��ר\r\n6=��ר\r\n7=����\r\n8=�о���\r\n9=��ʿ",
			    		),
			  	),
			  38 => array (
			    	'classid' => '5',
			    	'displayorder' => '0',
			    	'title' => 'ϯ��',
			    	'identifier' => 'seats',
			    	'type' => 'select',
			    	'rules' => array (
			      			'choices' => "1=վƱ\r\n2=Ӳ��\r\n3=����\r\n4=Ӳ��\r\n5=����",
			    		),
			  	),
			  44 => array (
			    	'classid' => '4',
			    	'displayorder' => '0',
			    	'title' => '�Ƿ�Ӧ��',
			    	'identifier' => 'recr_term',
			    	'type' => 'radio',
			    	'rules' => array (
					      	'required' => '0',
					      	'unchangeable' => '0',
					      	'choices' => "1=Ӧ��\r\n2=��Ӧ��",
			    		),
			  	),
			  48 => array (
			    	'classid' => '4',
			    	'displayorder' => '0',
			    	'title' => 'н��',
			    	'identifier' => 'recr_salary',
			    	'type' => 'select',
			    	'rules' => array (
			      			'choices' => "1=����\r\n2=1000����\r\n3=1000~1500\r\n4=1500~2000\r\n5=2000~3000\r\n6=3000~4000\r\n7=4000~6000\r\n8=6000~8000\r\n9=8000����",
			    		),
			  	),
			  50 => array (
			    	'classid' => '4',
			    	'displayorder' => '0',
			    	'title' => '��������',
			    	'identifier' => 'recr_work',
			    	'type' => 'radio',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=ȫְ\r\n2=��ְ",
			    		),
			  	),
			  53 => array (
			    	'classid' => '4',
			    	'displayorder' => '0',
			    	'title' => '�Ա�Ҫ��',
			    	'identifier' => 'recr_sex',
			    	'type' => 'checkbox',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=��\r\n2=Ů",
			    		),
			  	),
			  62 => array (
			    	'classid' => '5',
			    	'displayorder' => '0',
			    	'title' => '���ʽ',
			    	'identifier' => 'pay_type',
			    	'type' => 'checkbox',
			    	'rules' => array (
			      			'required' => '0',
			      			'unchangeable' => '0',
			      			'choices' => "1=���\r\n2=֧����\r\n3=�ֽ�\r\n4=����",
			    		),
			  	),
			);

		foreach($optionlist as $optionid => $option) {
			$db->query("REPLACE INTO {$tablepre}typeoptions VALUES ('$optionid', '$option[classid]', '$option[displayorder]', '$option[title]', '', '$option[identifier]', '$option[type]', '".addslashes(serialize($option['rules']))."');");
		}

		$db->query("ALTER TABLE {$tablepre}typeoptions AUTO_INCREMENT=3001");

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 4) {

		runquery($upgrade4);

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 5) {
		upg_mail();
		upg_spaces();
		upg_seccode();
		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 6) {

		$endupg = upg_ec($start);
		if($endupg != '-1') {
			echo "�� $step �������ɹ�<br /><br />";
			redirect("?action=upgrade&step=$step&start=$endupg");
		} else {
			echo "�� $step �������ɹ�<br /><br />";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 7) {

		$lastid = $db->result($db->query("SELECT id FROM {$tablepre}bbcodes ORDER BY id DESC"), 0);
		$upgrade7 = preg_replace('/\{bbcodeid,(\d)\}/e', "\$lastid + \\1", $upgrade7);
		runquery($upgrade7);
		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 8) {

		$query = $db->query("RENAME TABLE {$tablepre}campaigns TO {$tablepre}campaigns_bak", 'SILENT');
		runquery($insenz_upgrade);
		$query = $db->query("SELECT * FROM {$tablepre}campaigns_bak", 'SILENT');
		while(@$campaign = $db->fetch_array($query)) {
			$c_id = $campaign['id'];
			$c_type = $campaign['type'];;
			$fid = $campaign['fid'];
			$tid = $campaign['tid'];
			$status = $campaign['status'];
			$begintime = $campaign['begintime'];
			$endtime = $campaign['endtime'];
			$expiration = $campaign['expiration'];
			$nextrun = $campaign['nextrun'];
			$db->query("INSERT INTO {$tablepre}campaigns (id, type, fid, tid, status, begintime, endtime, expiration,nextrun) VALUES ('$c_id', '$c_type', '$fid', '$tid', '$status', '$begintime', '$endtime', '$expiration', '$nextrun')");
		}

		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='insenz'");
		$insenz = ($insenz = $db->result($query, 0)) ? unserialize($insenz) : array();
		if(isset($insenz['version']) && $insenz['version'] == '0.2') {
			$query = $db->query("SELECT it.id, it.tid, it.status, it.dateline, tm.expiration, t.fid, t.displayorder
				FROM {$tablepre}insenzthreads it
				LEFT JOIN {$tablepre}threadsmod tm ON tm.tid=it.tid AND tm.status=it.status
				LEFT JOIN {$tablepre}threads t ON t.tid=it.tid", 'SILENT');
			while(@$campaign = $db->fetch_array($query)) {
				$c_id = $campaign['id'];
				$c_type = in_array($campaign['displayorder'], array(1, 2, -121, -122)) ? 2 : 1;
				$fid = $campaign['fid'];
				$tid = $campaign['tid'];
				$status = $campaign['status'] - 120;
				$begintime = $campaign['dateline'];
				$endtime = $campaign['expiration'];
				$expiration = $endtime + 60 * 86400;
				$nextrun = $status == 1 ? $begintime : ($status == 2 ? $endtime : $expiration);
				$db->query("INSERT INTO {$tablepre}campaigns (id, type, fid, tid, status, begintime, endtime, expiration, nextrun) VALUES ('$c_id', '$c_type', '$fid', '$tid', '$status', '$begintime', '$endtime', '$expiration', '$nextrun')", 'SILENT');
				$newdisplayorder = in_array($campaign['displayorder'], array(-120, -121, -122)) ? $campaign['displayorder'] + 110 : $campaign['displayorder'];
				$db->query("UPDATE {$tablepre}threads SET digest=-1, displayorder='$newdisplayorder' WHERE tid='$tid'", 'UNBUFFERED');
			}
		}
		if($insenz) {
			$insenz['status'] = isset($insenz['status']) ? intval($insenz['status']) : 1;
			$insenz['softadstatus'] = isset($insenz['softadstatus']) ? intval($insenz['softadstatus']) : 2;
		}

		unset($insenz['lastmodified'], $insenz['forums'], $insenz['version']);

		$insenz['topicstatus'] = 1;

		$insenz['host'] = $insenz['host'] ? $insenz['host'] : 'api.insenz.com';
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
		$db->query("DROP TABLE IF EXISTS {$tablepre}campaigns_bak");
		$db->query("DROP TABLE IF EXISTS {$tablepre}insenzthreads");
		$db->query("DELETE FROM {$tablepre}crons WHERE filename IN ('insenz_onlinestats.inc.php', 'insenz_pushthreads.inc.php', 'insenz_forumstats.inc.php')");

		$settings = array(
			'open' => 0,
			'bbname' => '',
			'url' => '',
			'email' => '',
			'logo' => '',
			'sitetype' => "����\t����\t����\tӰ��\t����\t��Ϸ\t��Ů\t����\t����\t����\t����\tѧ��\t����\t����\t����\t����\tʱ��\t����\t����\t�ֻ�\t��Ӱ\tϷ��\t����\t����\tУ԰\t����\t����\t��ʷ\t����\t����\t�ƾ�\t����\t����\t����\t����\t�ۺ�",
			'vsiteid' => '',
			'vpassword' => '',
			'vkey' => '',
			'vclasses' => array (
				22 => '����',
				15 => '����',
				27 => '����',
				28 => '����',
				26 => '��ɫ',
				1 => '��Ц',
				29 => '����',
				18 => 'Ӱ��',
				12 => '����',
				8 => '����',
				7 => '��Ϸ',
				24 => '����',
				11 => '���',
				19 => '����',
				5 => 'ʱ��',
				21 => '�Ӽ�',
				23 => '����',
				25 => '����',
				14 => '����',
				30 => '����',
				16 => '�Ƽ�',
				31 => '����'
			),
			'vclassesable' => array (22, 15, 27, 28, 26, 1, 29, 18, 12, 8, 7, 24, 11, 19, 5, 21, 23, 25, 14, 30, 16, 31),
		);
		
		$settings = addslashes(serialize($settings));
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '$settings')");

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 9) {

		$qihoo_items = "'qihoo_adminemail', 'qihoo_jammer', 'qihoo_keywords', 'qihoo_location', 'qihoo_maxtopics', 'qihoo_relatedthreads', 'qihoo_relatedsort', 'qihoo_searchbox', 'qihoo_status', 'qihoo_summary', 'qihoo_topics', 'qihoo_validity'";
		$qihoo = $settings = array();
		$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable IN ($qihoo_items)");
		while($setting = $db->fetch_array($query)) {
			$settings[$setting['variable']] = $setting['value'];
		}
		$settings['qihoo_topics'] = !empty($settings['qihoo_topics']) ? unserialize($settings['qihoo_topics']) : array();
		$settings['qihoo_relatedthreads'] = !empty($settings['qihoo_relatedthreads']) ? unserialize($settings['qihoo_relatedthreads']) : array();
		foreach($settings AS $variable => $value) {
			$qihoo[substr($variable, 6)] = $value;
		}
		unset($qihoo['validity']);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('qihoo', '".addslashes(serialize($qihoo))."')");
		$db->query("DELETE FROM {$tablepre}settings WHERE variable IN ($qihoo_items)");

		$rewritestatus = intval($db->result($db->query("SELECT value FROM {$tablepre}settings WHERE variable='rewritestatus'"), 0));
		if($rewritestatus == 1) {
			$rewritestatus = 16;
		} elseif($rewritestatus == 2) {
			$rewritestatus = 7;
		} elseif($rewritestatus == 3) {
			$rewritestatus = 23;
		}
		$query = $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('rewritestatus', '$rewritestatus')");

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} else {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 6)), 8, 6).random(10);

		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$siteuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$timestamp), 0, 4).random(6);

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('authkey', '$authkey')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('siteuniqueid', '$siteuniqueid')");

		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='insenz'");
		$insenz = unserialize($db->result($query, 0));

		$insenz_message = '';
		if(empty($insenz['authkey'])) {
			$insenz_message = '<li><font color="red">'.$version_new.' Ϊ���ṩ�� Insenz ����Ӫ�����񣬰���վ���������棬����������ֵ����¼��̳��̨���������Ӫ�����˵�����ע��</font>';
		} elseif($insenz['softadstatus'] != 2) {
			$insenz_message = '<li><font color="red">��������Ӫ����δ�����Զ����ܻ���ܣ�Ϊ�˱����������棬�������Ľӵ���������ҵõ����ߵĹ����ã�������������¼��̳��̨���ڡ�����Ӫ�� -- Ӫ������ -- �������á��������Զ����ܻ</font>';
		}

		echo '<br />��ϲ����̳���������ɹ���������������<ol><li><b>��ɾ��������</b>'.$insenz_message.
		'<li>ʹ�ù���Ա��ݵ�¼��̳�������̨�����»���'.
		'<li>������̳ע�ᡢ��¼�������ȳ�����ԣ����������Ƿ�����'.
		'<li>�����ϣ������ <b>'.$version_new.'</b> �ṩ���¹��ܣ��㻹��Ҫ������̳�������á���Ŀ����Ա��ȵȽ�����������</ol><br />'.
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

	$successed = TRUE;

	if(is_array($updatesql) && !empty($updatesql[0])) {

		list($table, $action, $field, $sql) = $updatesql;

		if(empty($field) && !empty($sql)) {

			$query = "ALTER TABLE {$tablepre}{$table} ";
			if($action == 'INDEX') {
				$successed = $db->query("$query $sql", "SILENT");
			} elseif ($action == 'UPDATE') {
				$successed = $db->query("UPDATE {$tablepre}{$table} SET $sql", 'SILENT');
			}

		} elseif($tableinfo = loadtable($table)) {

			$fieldexist = isset($tableinfo[$field]) ? 1 : 0;

			$query = "ALTER TABLE {$tablepre}{$table} ";

			if($action == 'MODIFY') {

				$query .= $fieldexist ? "MODIFY $field $sql" : "ADD $field $sql";
				$successed = $db->query($query, 'SILENT');

			} elseif($action == 'CHANGE') {

				$field2 = trim(substr($sql, 0, strpos($sql, ' ')));
				$field2exist = isset($tableinfo[$field2]);

				if($fieldexist && ($field == $field2 || !$field2exist)) {
					$query .= "CHANGE $field $sql";
				} elseif($fieldexist && $field2exist) {
					$db->query("ALTER TABLE {$tablepre}{$table} DROP $field2", 'SILENT');
					$query .= "CHANGE $field $sql";
				} elseif(!$fieldexist && $fieldexist2) {
					$db->query("ALTER TABLE {$tablepre}{$table} DROP $field2", 'SILENT');
					$query .= "ADD $sql";
				} elseif(!$fieldexist && !$field2exist) {
					$query .= "ADD $sql";
				}
				$successed = $db->query($query);

			} elseif($action == 'ADD') {

				$query .= $fieldexist ? "CHANGE $field $field $sql" :  "ADD $field $sql";
				$successed = $db->query($query);

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
<b>����������ֻ�ܴ� $version_old ������ $version_new ������֮ǰ����ȷ���Ѿ��ϴ������ļ������������ݱ���<br />
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
	echo '<br /><br />'.$message.'<br /><br />';
	if($break) showfooter();
}

function redirect($url) {

	$url = $url.(strstr($url, '&') ? '&' : '?').'t='.time();

	echo <<< EOT
<hr size=1>
<script language="JavaScript">
	function redirect() {
		window.location.replace('$url');
	}
	setTimeout('redirect();', 1000);
</script>
<br /><br />
&gt;&gt;<a href="$url">��������Զ���תҳ�棬�����˹���Ԥ�����ǵ������������ʱ��û���Զ���תʱ����������</a>
<br /><br />
EOT;
	showfooter();
}


//upgrade trade and tradelogs
function upg_ec($start) {
	global $db, $tablepre;

	$ppp = 100;
	$startlimit = $start * $ppp;

	$num = 0;

	$query = $db->query("SELECT t.tid, p.pid FROM {$tablepre}trades t LEFT JOIN {$tablepre}posts p ON (p.tid = t.tid AND p.first='1') WHERE t.pid=0 LIMIT $startlimit, $ppp");
	while($trade = $db->fetch_array($query)) {
		$num ++;
		$db->query("UPDATE {$tablepre}trades SET pid='$trade[pid]' WHERE tid='$trade[tid]'", 'UNBUFFERED');
		$db->query("UPDATE {$tablepre}tradelog SET pid='$trade[pid]}' WHERE tid='$trade[tid]'", 'UNBUFFERED');
	}

	$num = $num < $ppp ? '-1' : ($start + $num);
	return $num;
}

//upgrade mail config
function upg_mail() {
	global $db, $tablepre;
	@include DISCUZ_ROOT.'./mail_config.inc.php';
	$mail = array (
	    'mailsend' => $mailsend,
	    'server' => $mailcfg['server'],
	    'port' => $mailcfg['port'],
	    'auth' => $mailcfg['auth'],
	    'from' => $mailcfg['from'],
	    'auth_username' => $mailcfg['auth_username'],
	    'auth_password' => $mailcfg['auth_password'],
	    'maildelimiter' => $maildelimiter,
	    'mailusername' => $mailusername,
	    'sendmail_silent' => $sendmail_silent
	);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('mail', '".addslashes(serialize($mail))."')");
}

function upg_spaces() {
	global $db, $tablepre;

	$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable like 'space%'");
	$spacedata = array();
	while($data = $db->fetch_array($query)) {
		$data['value'] = intval($data['value']);
		switch($data['variable']) {
			case 'spacecachelife'		: $spacedata['cachelife'] = $data['value'];break;
			case 'spacelimitmythreads'	: $spacedata['limitmythreads'] = $data['value'];break;
			case 'spacelimitmyreplies'	: $spacedata['limitmyreplies'] = $data['value'];break;
			case 'spacelimitmyrewards'	: $spacedata['limitmyrewards'] = $data['value'];break;
			case 'spacelimitmytrades'	: $spacedata['limitmytrades'] = $data['value'];break;
			case 'spacelimitmyblogs'	: $spacedata['limitmyblogs'] = $data['value'];break;
			case 'spacelimitmyfriends'	: $spacedata['limitmyfriends'] = $data['value'];break;
			case 'spacelimitmyfavforums'	: $spacedata['limitmyfavforums'] = $data['value'];break;
			case 'spacelimitmyfavthreads'	: $spacedata['limitmyfavthreads'] = $data['value'];break;
			case 'spacetextlength'		: $spacedata['textlength'] = $data['value'];break;
		}
	}

	$spacedata['limitmyvideos'] = 0;
	$spacedata = serialize($spacedata);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('spacedata', '".$spacedata."')");
	$db->query("DELETE FROM {$tablepre}settings WHERE variable IN ('spacecachelife', 'spacelimitmythreads', 'spacelimitmyreplies', 'spacelimitmyrewards', 'spacelimitmytrades', 'spacelimitmyblogs', 'spacelimitmyfriends', 'spacelimitmyfavforums', 'spacelimitmyfavthreads', 'spacetextlength')");
}

function upg_seccode() {
	global $db, $tablepre;

	$seccodedata = unserialize($db->result($db->query("SELECT value FROM {$tablepre}settings WHERE variable like 'seccodedata'"), 0));
	$seccodedatanew = array(
	    'minposts' => '',
	    'loginfailedcount' => $seccodedata['loginfailedcount'],
	    'width' => 150,
	    'height' => 60,
	    'type' => 0,
	    'background' => $seccodedata['background'],
	    'adulterate' => 1,
	    'ttf' => $seccodedata['ttf'],
	    'angle' => 0,
	    'color' => 1,
	    'size' => 0,
	    'shadow' => 1,
	    'animator' => $seccodedata['animator']
	);
	$seccodedatanew = serialize($seccodedatanew);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('seccodedata', '".$seccodedatanew."')");
}

?>