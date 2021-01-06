<?php

// Upgrade Discuz! Board from 6.0.0RC1 to 6.0.0 final

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 6.0.0 RC1';
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

REPLACE INTO cdb_settings (variable, value) VALUES ('baidusitemap', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('baidusitemap_life', '12');
REPLACE INTO cdb_settings (variable, value) VALUES ('google', '');

UPDATE cdb_stylevars SET variable='stypeid', substitute='1' WHERE variable='smdir';

CREATE TABLE IF NOT EXISTS cdb_videotags (
  tagname char(10) NOT NULL DEFAULT '',
  vid char(16) NOT NULL DEFAULT '',
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY tagname (tagname,vid),
  KEY tid (tid)
) TYPE=MyISAM;

EOT;

$upgradetable = array(

	array('typevars', 'ADD', 'required', "TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER available"),
	array('typevars', 'ADD', 'unchangeable', " TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER required"),
	array('typevars', 'ADD', 'search', " TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `unchangeable`"),

	array('threadtypes', 'ADD', 'modelid', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0' DEFAULT '0' AFTER `special`"),
	array('threadtypes', 'ADD', 'expiration', "TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER modelid"),

	array('typeoptionvars', 'ADD', 'expiration', "INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `optionid`"),

	array('searchindex', 'ADD', 'threadtypeid', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `threads`"),
	array('searchindex', 'MODIFY', 'searchstring', "searchstring TEXT  NOT NULL"),

	array('forumfields', 'ADD', 'tradetypes', "TEXT NOT NULL"),
	array('forumfields', 'ADD', 'typemodels', "MEDIUMTEXT NOT NULL"),

	array('promotions', 'MODIFY', 'uid', "uid MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0'"),
	array('smilies', 'MODIFY', 'displayorder', "displayorder TINYINT( 3 ) NOT NULL DEFAULT '0'"),
	array('smilies', 'ADD', 'typeid', "SMALLINT( 6 ) UNSIGNED NOT NULL AFTER id"),

	array('caches', 'MODIFY', 'data', "data MEDIUMTEXT NOT NULL"),
	array('usergroups', 'ADD', 'allowpostvideo', "TINYINT (1) DEFAULT '0' NOT NULL  AFTER allowpostactivity"),
	array('usergroups', 'MODIFY', 'tradestick', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'"),

	array('videos', 'MODIFY', 'vid', "VARCHAR(16)  NOT NULL DEFAULT ''"),
	array('videotags', 'ADD', 'tid', "MEDIUMINT(8) UNSIGNED  DEFAULT '0' NOT NULL  AFTER vid"),
	array('videotags', 'INDEX', '', "ADD INDEX tid (tid)"),

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

	array('forums', 'MODIFY', 'allowpostspecial', "SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0'"),

);

$upgrade3 = <<<EOT

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

DROP TABLE IF EXISTS cdb_imagetypes;
CREATE TABLE cdb_imagetypes (
  typeid smallint(6) unsigned NOT NULL auto_increment,
  name char(20) NOT NULL,
  type enum('smiley', 'icon', 'avatar') NOT NULL DEFAULT 'smiley',
  displayorder tinyint(3) NOT NULL default '0',
  directory char(100) NOT NULL,
  PRIMARY KEY  (typeid)
) TYPE=MyISAM;
INSERT INTO cdb_imagetypes VALUES (1, 'Ĭ�ϱ���', 'smiley', 1, 'default');
UPDATE cdb_smilies SET typeid=1 WHERE type='smiley';

EOT;

$upgrade4 = <<<EOT

EOT;

$upgrade6 = <<<EOT

EOT;

$upgrade7 = <<<EOT

INSERT INTO cdb_bbcodes VALUES ({bbcodeid,1}, '0', 'sup', 'bb_sup.gif', '<sup>{1}</sup>', 'X[sup]2[/sup]', '�ϱ�', 1, '�������ϱ����֣�', '1');
INSERT INTO cdb_bbcodes VALUES ({bbcodeid,2}, '0', 'sub', 'bb_sub.gif', '<sub>{1}</sub>', 'X[sub]2[/sub]', '�±�', 1, '�������±����֣�', '1');

INSERT INTO cdb_templates (templateid, name, directory, copyright) VALUES ({templateid,1}, '�Ȳʰ���', './templates/Beijing2008', '��ʢ���루�������Ƽ����޹�˾');
INSERT INTO cdb_templates (templateid, name, directory, copyright) VALUES ({templateid,2}, '��������', './templates/Overcast', '��ʢ���루�������Ƽ����޹�˾');
INSERT INTO cdb_templates (templateid, name, directory, copyright) VALUES ({templateid,3}, '��ױ����', './templates/PinkDresser', '��ʢ���루�������Ƽ����޹�˾');

INSERT INTO cdb_styles (styleid, name, available, templateid) VALUES ({styleid,1}, '�Ȳʰ���', 1, {templateid,1});
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  ({styleid,1}, 'available', ''),
  ({styleid,1}, 'bgcolor', '#FFF'),
  ({styleid,1}, 'altbg1', '#FFF'),
  ({styleid,1}, 'altbg2', '#F7F7F3'),
  ({styleid,1}, 'link', '#262626'),
  ({styleid,1}, 'bordercolor', '#C1C1C1'),
  ({styleid,1}, 'headercolor', '#FFF forumbox_head.gif'),
  ({styleid,1}, 'headertext', '#D00'),
  ({styleid,1}, 'catcolor', '#F90 cat_bg.gif'),
  ({styleid,1}, 'tabletext', '#535353'),
  ({styleid,1}, 'text', '#535353'),
  ({styleid,1}, 'borderwidth', '1px'),
  ({styleid,1}, 'tablespace', '1px'),
  ({styleid,1}, 'fontsize', '12px'),
  ({styleid,1}, 'msgfontsize', '14px'),
  ({styleid,1}, 'msgbigsize', '16px'),
  ({styleid,1}, 'msgsmallsize', '12px'),
  ({styleid,1}, 'font', 'Arial,Helvetica,sans-serif'),
  ({styleid,1}, 'smfontsize', '11px'),
  ({styleid,1}, 'smfont', 'Arial,Helvetica,sans-serif'),
  ({styleid,1}, 'boardimg', 'logo.gif'),
  ({styleid,1}, 'imgdir', './images/Beijing2008'),
  ({styleid,1}, 'maintablewidth', '98%'),
  ({styleid,1}, 'bgborder', '#C1C1C1'),
  ({styleid,1}, 'catborder', '#E2E2E2'),
  ({styleid,1}, 'inputborder', '#D7D7D7'),
  ({styleid,1}, 'lighttext', '#535353'),
  ({styleid,1}, 'headermenu', '#FFF menu_bg.gif'),
  ({styleid,1}, 'headermenutext', '#54564C'),
  ({styleid,1}, 'framebgcolor', ''),
  ({styleid,1}, 'noticebg', ''),
  ({styleid,1}, 'commonboxborder', '#F0F0ED'),
  ({styleid,1}, 'tablebg', '#FFF'),
  ({styleid,1}, 'highlightlink', '#535353'),
  ({styleid,1}, 'commonboxbg', '#F5F5F0'),
  ({styleid,1}, 'boxspace', '8px'),
  ({styleid,1}, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  ({styleid,1}, 'noticeborder', ''),
  ({styleid,1}, 'noticetext', '#DD0000'),
  ({styleid,1}, 'stypeid', '1');

INSERT INTO cdb_styles (styleid, name, available, templateid) VALUES ({styleid,2}, '��������', 1, {templateid,2});
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  ({styleid,2}, 'available', ''),
  ({styleid,2}, 'bgcolor', '#222D2D'),
  ({styleid,2}, 'altbg1', '#3E4F4F'),
  ({styleid,2}, 'altbg2', '#384747'),
  ({styleid,2}, 'link', '#CEEBEB'),
  ({styleid,2}, 'bordercolor', '#1B2424'),
  ({styleid,2}, 'headercolor', '#1B2424'),
  ({styleid,2}, 'headertext', '#94B3C5'),
  ({styleid,2}, 'catcolor', '#293838'),
  ({styleid,2}, 'tabletext', '#CEEBEB'),
  ({styleid,2}, 'text', '#999'),
  ({styleid,2}, 'borderwidth', '6px'),
  ({styleid,2}, 'tablespace', '0'),
  ({styleid,2}, 'fontsize', '12px'),
  ({styleid,2}, 'msgfontsize', '14px'),
  ({styleid,2}, 'msgbigsize', '16px'),
  ({styleid,2}, 'msgsmallsize', '12px'),
  ({styleid,2}, 'font', 'Arial'),
  ({styleid,2}, 'smfontsize', '11px'),
  ({styleid,2}, 'smfont', 'Arial,sans-serif'),
  ({styleid,2}, 'boardimg', 'logo.gif'),
  ({styleid,2}, 'imgdir', './images/Overcast'),
  ({styleid,2}, 'maintablewidth', '98%'),
  ({styleid,2}, 'bgborder', '#384747'),
  ({styleid,2}, 'catborder', '#1B2424'),
  ({styleid,2}, 'inputborder', '#EEE'),
  ({styleid,2}, 'lighttext', '#74898E'),
  ({styleid,2}, 'headermenu', '#3E4F4F'),
  ({styleid,2}, 'headermenutext', '#CEEBEB'),
  ({styleid,2}, 'framebgcolor', '#222D2D'),
  ({styleid,2}, 'noticebg', '#3E4F4F'),
  ({styleid,2}, 'commonboxborder', '#384747'),
  ({styleid,2}, 'tablebg', '#3E4F4F'),
  ({styleid,2}, 'highlightlink', '#9CB2A0'),
  ({styleid,2}, 'commonboxbg', '#384747'),
  ({styleid,2}, 'boxspace', '6px'),
  ({styleid,2}, 'portalboxbgcode', '#293838'),
  ({styleid,2}, 'noticeborder', '#384747'),
  ({styleid,2}, 'noticetext', '#C7E001'),
  ({styleid,2}, 'stypeid', '1');

INSERT INTO cdb_styles (styleid, name, available, templateid) VALUES ({styleid,3}, '��ױ����', 1, {templateid,3});
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  ({styleid,3}, 'noticetext', '#C44D4D'),
  ({styleid,3}, 'noticeborder', '#D6D6D6'),
  ({styleid,3}, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  ({styleid,3}, 'boxspace', '6px'),
  ({styleid,3}, 'commonboxbg', '#FAFAFA'),
  ({styleid,3}, 'highlightlink', '#C44D4D'),
  ({styleid,3}, 'tablebg', '#FFF'),
  ({styleid,3}, 'commonboxborder', '#DEDEDE'),
  ({styleid,3}, 'noticebg', '#FAFAFA'),
  ({styleid,3}, 'framebgcolor', '#FFECF9'),
  ({styleid,3}, 'headermenu', 'transparent'),
  ({styleid,3}, 'headermenutext', ''),
  ({styleid,3}, 'lighttext', '#999'),
  ({styleid,3}, 'catborder', '#D7D7D7'),
  ({styleid,3}, 'inputborder', ''),
  ({styleid,3}, 'bgborder', '#CECECE'),
  ({styleid,3}, 'stypeid', '1'),
  ({styleid,3}, 'maintablewidth', '920px'),
  ({styleid,3}, 'imgdir', 'images/PinkDresser'),
  ({styleid,3}, 'boardimg', 'logo.gif'),
  ({styleid,3}, 'smfont', 'Arial,Helvetica,sans-serif'),
  ({styleid,3}, 'smfontsize', '12px'),
  ({styleid,3}, 'font', 'Arial,Helvetica,sans-serif'),
  ({styleid,3}, 'msgsmallsize', '12px'),
  ({styleid,3}, 'msgbigsize', '16px'),
  ({styleid,3}, 'msgfontsize', '14px'),
  ({styleid,3}, 'fontsize', '12px'),
  ({styleid,3}, 'tablespace', '0'),
  ({styleid,3}, 'borderwidth', '1px'),
  ({styleid,3}, 'text', '#666'),
  ({styleid,3}, 'tabletext', '#666'),
  ({styleid,3}, 'catcolor', '#FAFAFA category_bg.gif'),
  ({styleid,3}, 'headertext', '#FFF'),
  ({styleid,3}, 'headercolor', '#E7BFC9 forumbox_head.gif'),
  ({styleid,3}, 'bordercolor', '#D88E9D'),
  ({styleid,3}, 'link', '#C44D4D'),
  ({styleid,3}, 'altbg2', '#F1F1F1'),
  ({styleid,3}, 'available', ''),
  ({styleid,3}, 'altbg1', '#FBFBFB'),
  ({styleid,3}, 'bgcolor', '#FBF4F5 bg.gif');

INSERT INTO cdb_styles (styleid, name, available, templateid) VALUES ({styleid,4}, 'ʫ����԰', 1, 1);
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  ({styleid,4}, 'available', ''),
  ({styleid,4}, 'bgcolor', '#FFF'),
  ({styleid,4}, 'altbg1', '#FFFBF8'),
  ({styleid,4}, 'altbg2', '#FBF6F1'),
  ({styleid,4}, 'link', '#54564C'),
  ({styleid,4}, 'bordercolor', '#D7B094'),
  ({styleid,4}, 'headercolor', '#BE6A2D forumbox_head.gif'),
  ({styleid,4}, 'headertext', '#FFF'),
  ({styleid,4}, 'catcolor', '#E9E9E9 cat_bg.gif'),
  ({styleid,4}, 'tabletext', '#7B7D72'),
  ({styleid,4}, 'text', '#535353'),
  ({styleid,4}, 'borderwidth', '1px'),
  ({styleid,4}, 'tablespace', '1px'),
  ({styleid,4}, 'fontsize', '12px'),
  ({styleid,4}, 'msgfontsize', '14px'),
  ({styleid,4}, 'msgbigsize', '16px'),
  ({styleid,4}, 'msgsmallsize', '12px'),
  ({styleid,4}, 'font', 'Arial, sans-serif'),
  ({styleid,4}, 'smfontsize', '11px'),
  ({styleid,4}, 'smfont', 'Arial, sans-serif'),
  ({styleid,4}, 'boardimg', 'logo.gif'),
  ({styleid,4}, 'imgdir', './images/Picnicker'),
  ({styleid,4}, 'maintablewidth', '98%'),
  ({styleid,4}, 'bgborder', '#E8C9B7'),
  ({styleid,4}, 'catborder', '#E6E6E2'),
  ({styleid,4}, 'inputborder', ''),
  ({styleid,4}, 'lighttext', '#878787'),
  ({styleid,4}, 'headermenu', '#FFF menu_bg.gif'),
  ({styleid,4}, 'headermenutext', '#54564C'),
  ({styleid,4}, 'framebgcolor', 'frame_bg.gif'),
  ({styleid,4}, 'noticebg', '#FAFAF7'),
  ({styleid,4}, 'commonboxborder', '#E6E6E2'),
  ({styleid,4}, 'tablebg', '#FFF'),
  ({styleid,4}, 'highlightlink', ''),
  ({styleid,4}, 'commonboxbg', '#F5F5F0'),
  ({styleid,4}, 'boxspace', '6px'),
  ({styleid,4}, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  ({styleid,4}, 'noticeborder', '#E6E6E2'),
  ({styleid,4}, 'noticetext', '#FF3A00'),
  ({styleid,4}, 'stypeid', '1');

INSERT INTO cdb_styles (styleid, name, available, templateid) VALUES ({styleid,5}, '���ⰻȻ', 1, 1);
INSERT INTO cdb_stylevars (styleid, variable, substitute) VALUES
  ({styleid,5}, 'available', ''),
  ({styleid,5}, 'bgcolor', '#FFF'),
  ({styleid,5}, 'altbg1', '#F5F5F0'),
  ({styleid,5}, 'altbg2', '#F9F9F9'),
  ({styleid,5}, 'link', '#54564C'),
  ({styleid,5}, 'bordercolor', '#D9D9D4'),
  ({styleid,5}, 'headercolor', '#80A400 forumbox_head.gif'),
  ({styleid,5}, 'headertext', '#FFF'),
  ({styleid,5}, 'catcolor', '#F5F5F0 cat_bg.gif'),
  ({styleid,5}, 'tabletext', '#7B7D72'),
  ({styleid,5}, 'text', '#535353'),
  ({styleid,5}, 'borderwidth', '1px'),
  ({styleid,5}, 'tablespace', '1px'),
  ({styleid,5}, 'fontsize', '12px'),
  ({styleid,5}, 'msgfontsize', '14px'),
  ({styleid,5}, 'msgbigsize', '16px'),
  ({styleid,5}, 'msgsmallsize', '12px'),
  ({styleid,5}, 'font', 'Arial,sans-serif'),
  ({styleid,5}, 'smfontsize', '11px'),
  ({styleid,5}, 'smfont', 'Arial,sans-serif'),
  ({styleid,5}, 'boardimg', 'logo.gif'),
  ({styleid,5}, 'imgdir', './images/GreenPark'),
  ({styleid,5}, 'maintablewidth', '98%'),
  ({styleid,5}, 'bgborder', '#D9D9D4'),
  ({styleid,5}, 'catborder', '#D9D9D4'),
  ({styleid,5}, 'inputborder', '#D9D9D4'),
  ({styleid,5}, 'lighttext', '#878787'),
  ({styleid,5}, 'headermenu', '#FFF menu_bg.gif'),
  ({styleid,5}, 'headermenutext', '#262626'),
  ({styleid,5}, 'framebgcolor', ''),
  ({styleid,5}, 'noticebg', '#FAFAF7'),
  ({styleid,5}, 'commonboxborder', '#E6E6E2'),
  ({styleid,5}, 'tablebg', '#FFF'),
  ({styleid,5}, 'highlightlink', '#535353'),
  ({styleid,5}, 'commonboxbg', '#F9F9F9'),
  ({styleid,5}, 'boxspace', '6px'),
  ({styleid,5}, 'portalboxbgcode', '#FFF portalbox_bg.gif'),
  ({styleid,5}, 'noticeborder', '#E6E6E2'),
  ({styleid,5}, 'noticetext', '#FF3A00'),
  ({styleid,5}, 'stypeid', '1');

EOT;

$insenz_upgrade = <<<EOT


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

		$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 6)), 8, 6).random(10);

		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$siteuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$timestamp), 0, 4).random(6);

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('authkey', '$authkey')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('siteuniqueid', '$siteuniqueid')");

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
		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 6) {

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 7) {

		$lastid = $db->result($db->query("SELECT id FROM {$tablepre}bbcodes ORDER BY id DESC"), 0);
		$upgrade7 = preg_replace('/\{bbcodeid,(\d)\}/e', "\$lastid + \\1", $upgrade7);
		$lastid = $db->result($db->query("SELECT templateid FROM {$tablepre}templates ORDER BY templateid DESC"), 0);
		$upgrade7 = preg_replace('/\{templateid,(\d)\}/e', "\$lastid + \\1", $upgrade7);
		$lastid = $db->result($db->query("SELECT styleid FROM {$tablepre}styles ORDER BY styleid DESC"), 0);
		$upgrade7 = preg_replace('/\{styleid,(\d)\}/e', "\$lastid + \\1", $upgrade7);
		runquery($upgrade7);
		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 8) {

		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='insenz'");
		$insenz = ($insenz = $db->result($query, 0)) ? unserialize($insenz) : array();

		if(is_array($insenz['member_masks'])) {
			foreach($insenz['member_masks'] AS $uid => $username) {
				$db->query("UPDATE {$tablepre}members m, {$tablepre}usergroups u SET m.adminid=0, m.groupid=u.groupid WHERE m.uid='$uid' AND m.groupid='$insenz[groupid]' AND m.credits>u.creditshigher AND m.credits <=u.creditslower");
			}
		}

		$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid='$insenz[groupid]' AND type='special'");

		unset($insenz['groupid'], $insenz['lastmodified'], $insenz['forums']);

		$insenz['jsurl'] = !empty($insenz['jsurl']) ? preg_replace("/http:\/\/a0(\d{1})\.insenz\.com\/adv\?sid=(\d+)/e", "'http://a0'.('\\2' % 8 + 1).'.insenz.com/adv?sid=\\2'", $insenz['jsurl']) : '';

		$insenz['topicstatus'] = 1;

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");

		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='videoinfo'");
		$settings = unserialize($db->result($query, 0));
		$settings['vpassword'] = $settings['vsitecode'];
		$settings['vkey'] = $settings['vauthcode'];
		$settings['sitetype'] = "����\t����\t����\tӰ��\t����\t��Ϸ\t��Ů\t����\t����\t����\t����\tѧ��\t����\t����\t����\t����\tʱ��\t����\t����\t�ֻ�\t��Ӱ\tϷ��\t����\t����\tУ԰\t����\t����\t��ʷ\t����\t����\t�ƾ�\t����\t����\t����\t����\t�ۺ�";
		$settings['vclasses'] = array (
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
		);
		$settings['vclassesable'] = array (22, 15, 27, 28, 26, 1, 29, 18, 12, 8, 7, 24, 11, 19, 5, 21, 23, 25, 14, 30, 16, 31);
		unset($settings['vsitecode']);
		unset($settings['vauthcode']);
		$settings = addslashes(serialize($settings));
		$db->query("UPDATE {$tablepre}settings SET value='$settings' WHERE variable='videoinfo'");

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 9) {

		echo "�� $step �������ɹ�<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} else {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

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



?>