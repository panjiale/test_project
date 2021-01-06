<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: google.inc.php 13176 2008-03-28 09:56:14Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$google = ($google = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='google'")) ? unserialize($google) : array();

if($operation == 'config') {

	if(!submitcheck('googlesubmit')) {

		$checks = array();
		$checkstatus = array($google['status'] => 'checked');
		$checklocation = array($google['location'] => 'checked');
		$checkrelatedsort = array($google['relatedsort'] => 'checked');
		$google['searchbox'] = sprintf('%03b', $google['searchbox']);
		for($i = 1; $i <= 3; $i++) {
			$checks[$i] = $google['searchbox'][3 - $i] ? 'checked' : '';
		}

		shownav('extended', 'google');
		showsubmenu('google');

		showformheader('google&operation=config');
		showtableheader();
		showsetting('google_status', 'googlenew[status]', $google['status'], 'radio');
		showsetting('google_searchbox', '', '', '<ul class="nofloat" onmouseover="altStyle(this);"><li'.($checks[1] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="googlenew[searchbox][1]" value="1" '.$checks[1].'> '.$lang['google_searchbox_index'].'</li><li'.($checks[2] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="googlenew[searchbox][2]" value="1" '.$checks[2].'> '.$lang['google_searchbox_forumdisplay'].'</li><li'.($checks[3] ? ' class="checked"' : '').'><input class="checkbox" type="checkbox" name="googlenew[searchbox][3]" value="1" '.$checks[3].'> '.$lang['google_searchbox_viewthread'].'</li></ul>');
		showsetting('google_lang', array('googlenew[lang]', array(
			array('', $lang['google_lang_any']),
			array('en', $lang['google_lang_en']),
			array('zh-CN', $lang['google_lang_zh-CN']),
			array('zh-TW', $lang['google_lang_zh-TW']))), $google['lang'], 'mradio');
		showsubmit('googlesubmit');
		showtablefooter();
		showformfooter();

	} else {

		$googlenew['searchbox'] = bindec(intval($googlenew['searchbox'][3]).intval($googlenew['searchbox'][2]).intval($googlenew['searchbox'][1]));

		$db->query("UPDATE {$tablepre}settings SET value='".addslashes(serialize($googlenew))."' WHERE variable='google'");
		updatecache('settings');
		updatecache('google');
		cpmsg('google_succeed', 'admincp.php?action=google&operation=config', 'succeed');

	}

}

?>