<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: checktools.inc.php 13488 2008-04-18 06:05:55Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

if($operation == 'filecheck') {

	$step = max(1, intval($step));
	shownav('tools', 'nav_filecheck');
	showsubmenusteps('nav_filecheck', array(
		array('nav_filecheck_confirm', $step == 1),
		array('nav_filecheck_verify', $step == 2),
		array('nav_filecheck_completed', $step == 3)
	));
	if($step == 1) {
		cpmsg($lang[filecheck_tips_step1], 'admincp.php?action=checktools&operation=filecheck&step=2', 'button', '', FALSE);
	} elseif($step == 2) {
		cpmsg(lang('filecheck_verifying'), "admincp.php?action=checktools&operation=filecheck&step=3", 'loading', '', FALSE);
	} elseif($step == 3) {

		if(!$discuzfiles = @file('admin/discuzfiles.md5')) {
			cpmsg('filecheck_nofound_md5file', '', 'error');
		}

		$md5data = array();
		$cachelist = checkcachefiles('forumdata/cache/');
		checkfiles('./', '\.php', 0, 'config.inc.php');
		checkfiles('include/', '\.php|\.htm|\.js');
		checkfiles('templates/default/', '\.php|\.htm');
		checkfiles('wap/', '\.php');
		checkfiles('archiver/', '\.php');
		checkfiles('api/', '\.php');
		checkfiles('plugins/', '\.php');
		checkfiles('admin/', '\.php');
		checkfiles('modcp/', '\.php');
		checkfiles('uc_client/', '\.php', 0);
		checkfiles('uc_client/control/', '\.php');
		checkfiles('uc_client/model/', '\.php');
		checkfiles('uc_client/lib/', '\.php');

		foreach($discuzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if($md5datanew[$file] != $md5data[$file]) {
				$modifylist[$file] = $md5data[$file];
			}
			$md5datanew[$file] = $md5data[$file];
		}

		$weekbefore = $timestamp - 604800;
		$addlist = @array_merge(@array_diff_assoc($md5data, $md5datanew), $cachelist[2]);
		$dellist = @array_diff_assoc($md5datanew, $md5data);
		$modifylist = @array_merge(@array_diff_assoc($modifylist, $dellist), $cachelist[1]);
		$showlist = @array_merge($md5data, $md5datanew, $cachelist[0]);

		$dirlist = $dirlog = array();
		foreach($showlist as $file => $md5) {
			$dir = dirname($file);
			$statusf = $statust = 1;
			if(@array_key_exists($file, $modifylist)) {
				$status = '<em class="edited">'.$lang['filecheck_modify'];
				$dirlog[$dir]['modify']++;
				$statusmodify = 1;
			} elseif(@array_key_exists($file, $dellist)) {
				$status = '<em class="del">'.$lang['filecheck_delete'];
				$dirlog[$dir]['del']++;
			} elseif(@array_key_exists($file, $addlist)) {
				$status = '<em class="unknown">'.$lang['filecheck_unknown'];
				$dirlog[$dir]['add']++;
			} else {
				$status = '<em class="correct">'.$lang['filecheck_check_ok'];
				$statusf = 0;
			}

			$filemtime = @filemtime($file);
			if($filemtime > $weekbefore) {
				$filemtime = '<b>'.date("$dateformat $timeformat", $filemtime).'</b>';
			} else {
				$filemtime = date("$dateformat $timeformat", $filemtime);
				$statust = 0;
			}

			if($statusf || $statust) {
				$status .= '</em>';
				$filelist = '<tr><td><em class="bold files">'.basename($file).'</em></td>';
				if(file_exists($file)) {
					$filelist .= '<td style="text-align: right">'.number_format(filesize($file)).' Bytes&nbsp;&nbsp;</td><td>'.$filemtime.'</td>';
				} else {
					$filelist .= '<td></td><td></td>';
				}
				$filelist .= '<td>'.$status.'</td></tr>';
				$dirlist[$dir] .= $filelist;
			}
		}
		$result = '';
		$dirnum = 0;
		foreach($dirlist as $dirname => $filelist) {
			$dirnum++;
			$result .= '<tr><td colspan="4"><div class="left"><a href="#dir" class="ofolder" onclick="$(\'dir_'.$dirnum.'\').style.display=$(\'dir_'.$dirnum.'\').style.display==\'none\'?\'\':\'none\';this.className=this.className==\'ofolder\'?\'cfolder\':\'ofolder\'">'.$dirname.'/</a></div><div class="lightfont filenum left">'.
				($dirlog[$dirname]['modify'] ? $lang['filecheck_modify'].': '.$dirlog[$dirname]['modify'].' &nbsp; ' : '').
				($dirlog[$dirname]['del'] ? $lang['filecheck_delete'].': '.$dirlog[$dirname]['del'].' &nbsp; ' : '').
				($dirlog[$dirname]['add'] ? $lang['filecheck_unknown'].': '.$dirlog[$dirname]['add'].' &nbsp; ' : '').
				'</div></td></tr><tbody id="dir_'.$dirnum.'"'.(!$dirlog[$dirname]['modify'] && !$dirlog[$dirname]['del'] && !$dirlog[$dirname]['add'] ? ' style="display: none"' : '').'>'.$filelist.'</tbody>';
		}
		$modifiedfiles = count($modifylist);
		$deletedfiles = count($dellist);
		$unknownfiles = count($addlist);

		showtips('filecheck_tips');
		showtableheader('filecheck_completed');
		showtablerow('', 'colspan="4"', "<div class=\"lightfont filenum left\">$lang[filecheck_modify]: $modifiedfiles , $lang[filecheck_delete]: $deletedfiles , $lang[filecheck_unknown]: $unknownfiles</div>");
		showsubtitle(array('filename', '', 'lastmodified', 'filecheck_status'));
		echo $result;
		showtablefooter();

	}

} elseif($operation == 'ftpcheck') {

	$alertmsg = '';
	$testdir = substr(md5('Discuz!' + $timestamp), 12, 8);
	$testfile = 'discuztest.txt';
	$attach_dir = $attachdir;
	if($attachsave) {
		$attach_dir .= '/'.$testdir;
		if(!@mkdir($attach_dir, 0777)) {
			$alertmsg = lang('settings_local_mderr');
		}
	}
	if(!$alertmsg) {
		if(!@fclose(fopen($attach_dir.'/'.$testfile, 'w'))) {
			$alertmsg = lang('settings_local_uperr');
		} else {
			@unlink($attach_dir.'/'.$testfile);
		}
		$attachsave && @rmdir($attach_dir);
	}

	if(!$alertmsg) {
		require_once './include/ftp.func.php';
		if(!empty($settingsnew['ftp']['password'])) {
			$settings['ftp'] = unserialize($db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='ftp'"));
			$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($authkey));
			$pwlen = strlen($settingsnew['ftp']['password']);
			if($settingsnew['ftp']['password']{0} == $settings['ftp']['password']{0} && $settingsnew['ftp']['password']{$pwlen - 1} == $settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} && substr($settingsnew['ftp']['password'], 1, $pwlen - 2) == '********') {
				$settingsnew['ftp']['password'] = $settings['ftp']['password'];
			}
		}
		$ftp['pasv'] = intval($settingsnew['ftp']['pasv']);
		$ftp_conn_id = dftp_connect($settingsnew['ftp']['host'], $settingsnew['ftp']['username'], $settingsnew['ftp']['password'], $settingsnew['ftp']['attachdir'], $settingsnew['ftp']['port'], $settingsnew['ftp']['ssl'], 1);
		switch($ftp_conn_id) {
			case '-1':
				$alertmsg = $lang['settings_remote_conerr'];
				break;
			case '-2':
				$alertmsg = $lang['settings_remote_logerr'];
				break;
			case '-3':
				$alertmsg = $lang['settings_remote_pwderr'];
				break;
			case '-4':
				$alertmsg = $lang['settings_remote_ftpoff'];
				break;
			default:
				$alertmsg = '';
		}
	}

	if(!$alertmsg) {
		if(!dftp_mkdir($ftp_conn_id, $testdir)) {
			$alertmsg = $lang['settings_remote_mderr'];
		} else {
			if(!(function_exists('ftp_chmod') && dftp_chmod($ftp_conn_id, 0777, $testdir)) && !dftp_site($ftp_conn_id, "'CHMOD 0777 $testdir'") && !@ftp_exec($ftp_conn_id, "SITE CHMOD 0777 $testdir")) {
				$alertmsg = $lang['settings_remote_chmoderr'].'\n';
			}
			$testfile = $testdir.'/'.$testfile;
			if(!dftp_put($ftp_conn_id, $testfile, DISCUZ_ROOT.'./robots.txt', FTP_BINARY)) {
				$alertmsg .= $lang['settings_remote_uperr'];
				dftp_delete($ftp_conn_id, $testfile);
				dftp_delete($ftp_conn_id, $testfile.'.uploading');
				dftp_delete($ftp_conn_id, $testfile.'.abort');
				dftp_rmdir($ftp_conn_id, $testdir);
			} else {
				if(!@readfile($settingsnew['ftp']['attachurl'].'/'.$testfile)) {
					$alertmsg .= $lang['settings_remote_geterr'];
					dftp_delete($ftp_conn_id, $testfile);
					dftp_rmdir($ftp_conn_id, $testdir);
				} else {
					if(!dftp_delete($ftp_conn_id, $testfile)) {
						$alertmsg .= $lang['settings_remote_delerr'];
					} else {
						dftp_rmdir($ftp_conn_id, $testdir);
						$alertmsg = $lang['settings_remote_ok'];
					}
				}
			}
		}
	}
	echo '<script language="javascript">alert(\''.str_replace('\'', '\\\'', $alertmsg).'\');parent.$(\'cpform\').action=\'admincp.php?action=settings&edit=yes\';parent.$(\'cpform\').target=\'_self\'</script>';

} elseif($operation == 'mailcheck') {

	$mail = serialize($settingsnew['mail']);
	$test_tos = explode(',', $test_to);
	$date = date('Y-m-d H:i:s');
	$alertmsg = '';

	$title = $lang['settings_mailcheck_title_'.$settingsnew['mail']['mailsend']];
	$message = $lang['settings_mailcheck_message_'.$settingsnew['mail']['mailsend']].' '.$test_from.$lang['settings_mailcheck_date'].' '.$date;

	$bbname = $lang['settings_mailcheck_method_1'];
	sendmail($test_tos[0], $title.' @ '.$date, "$bbname\n\n\n$message", $test_from);
	$bbname = $lang['settings_mailcheck_method_2'];
	sendmail($test_to, $title.' @ '.$date, "$bbname\n\n\n$message", $test_from);

	if(!$alertmsg) {
		$alertmsg = $lang['settings_mailcheck_success_1']."$title @ $date".$lang['settings_mailcheck_success_2'];
	} else {
		$alertmsg = $lang['settings_mailcheck_error'].$alertmsg;
	}

	echo '<script language="javascript">alert(\''.str_replace(array('\'', "\n", "\r"), array('\\\'', '\n', ''), $alertmsg).'\');parent.$(\'cpform\').action=\'admincp.php?action=settings&edit=yes\';parent.$(\'cpform\').target=\'_self\'</script>';

} elseif($operation == 'imagepreview') {

	if(!empty($previewthumb)) {
		$thumbstatus = $settingsnew['thumbstatus'];
		if(!$thumbstatus) {
			cpmsg('thumbpreview_error', '', 'error');
		}
		$imagelib = $settingsnew['imagelib'];
		$imageimpath = $settingsnew['imageimpath'];
		$thumbwidth = $settingsnew['thumbwidth'];
		$thumbheight = $settingsnew['thumbheight'];
		$thumbquality = $settingsnew['thumbquality'];

		require_once DISCUZ_ROOT.'./include/image.class.php';
		@unlink(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg');
		$image = new Image('images/admincp/watermarkpreview.jpg', 'images/admincp/watermarkpreview.jpg');
		$image->Thumb($thumbwidth, $thumbheight, 1);
		if(file_exists(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg')) {
			showsubmenu('imagepreview_thumb');
			$sizesource = filesize('images/admincp/watermarkpreview.jpg');
			$sizetarget = filesize(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg');
			echo '<img src="forumdata/watermark_temp.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget).' Bytes ('.
				(sprintf("%2.1f", $sizetarget / $sizesource * 100)).'%)';
		} else {
			cpmsg('thumbpreview_createerror', '', 'error');
		}
	} else {
		$watermarkstatus = $settingsnew['watermarkstatus'];
		if(!$watermarkstatus) {
			cpmsg('watermarkpreview_error', '', 'error');
		}
		$imagelib = $settingsnew['imagelib'];
		$imageimpath = $settingsnew['imageimpath'];
		$watermarktype = $settingsnew['watermarktype'];
		$watermarktrans = $settingsnew['watermarktrans'];
		$watermarkquality = $settingsnew['watermarkquality'];
		$watermarkminwidth = $settingsnew['watermarkminwidth'];
		$watermarkminheight = $settingsnew['watermarkminheight'];
		$settingsnew['watermarktext']['size'] = intval($settingsnew['watermarktext']['size']);
		$settingsnew['watermarktext']['angle'] = intval($settingsnew['watermarktext']['angle']);
		$settingsnew['watermarktext']['shadowx'] = intval($settingsnew['watermarktext']['shadowx']);
		$settingsnew['watermarktext']['shadowy'] = intval($settingsnew['watermarktext']['shadowy']);
		$settingsnew['watermarktext']['translatex'] = intval($settingsnew['watermarktext']['translatex']);
		$settingsnew['watermarktext']['translatey'] = intval($settingsnew['watermarktext']['translatey']);
		$settingsnew['watermarktext']['skewx'] = intval($settingsnew['watermarktext']['skewx']);
		$settingsnew['watermarktext']['skewy'] = intval($settingsnew['watermarktext']['skewy']);
		$settingsnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $settingsnew['watermarktext']['fontpath']);
		$settingsnew['watermarktext']['color'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $settingsnew['watermarktext']['color']);
		$settingsnew['watermarktext']['shadowcolor'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $settingsnew['watermarktext']['shadowcolor']);

		if($watermarktype == 2) {
			if($settingsnew['watermarktext']['fontpath']) {
				$fontpath = $settingsnew['watermarktext']['fontpath'];
				$fontpathnew = 'ch/'.$fontpath;
				$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
				if(!$settingsnew['watermarktext']['fontpath']) {
					$fontpathnew = 'en/'.$fontpath;
					$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
				}
				if(!$settingsnew['watermarktext']['fontpath']) {
					cpmsg('watermarkpreview_fontpath_error', '', 'error');
				}
				$settingsnew['watermarktext']['fontpath'] = 'images/fonts/'.$settingsnew['watermarktext']['fontpath'];
			}

			if($settingsnew['watermarktext']['text'] && strtoupper($charset) != 'UTF-8') {
				include DISCUZ_ROOT.'include/chinese.class.php';
				$c = new Chinese($charset, 'utf8');
				$settingsnew['watermarktext']['text'] = $c->Convert($settingsnew['watermarktext']['text']);
			}
			$settingsnew['watermarktext']['text'] = bin2hex($settingsnew['watermarktext']['text']);
			$watermarktext = $settingsnew['watermarktext'];
		}

		require_once DISCUZ_ROOT.'./include/image.class.php';
		@unlink(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg');
		$image = new Image('images/admincp/watermarkpreview.jpg', 'images/admincp/watermarkpreview.jpg');
		$image->Watermark(1);
		if(file_exists(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg')) {
			showsubmenu('imagepreview_watermark');
			$sizesource = filesize('images/admincp/watermarkpreview.jpg');
			$sizetarget = filesize(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg');
			echo '<img src="forumdata/watermark_temp.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget).' Bytes ('.
				(sprintf("%2.1f", $sizetarget / $sizesource * 100)).'%)';
		} else {
			cpmsg('watermarkpreview_createerror', '', 'error');
		}
	}

}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $md5data;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (preg_match($exts, $entry) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
			if($sub && is_dir($file)) {
				checkfiles($file.'/', $ext, $sub, $skip);
			} else {
				$md5data[$file] = md5_file($file);
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

function checkmailerror($type, $error) {
	global $alertmsg;
	$alertmsg .= !$alertmsg ? $error : '';
}

?>