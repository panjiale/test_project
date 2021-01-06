<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: smilies.inc.php 13484 2008-04-18 03:11:29Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

if($operation == 'export' && $id) {
	$smileyarray = $db->fetch_first("SELECT name, directory FROM {$tablepre}imagetypes WHERE typeid='$id' AND type='smiley'");
	if(!$smileyarray) {
		cpheader();
		cpmsg('smilies_export_invalid', '', 'error');
	}

	$smileyarray['smilies'] = array();
	$query = $db->query("SELECT typeid, displayorder, code, url FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$id'");
	while($smiley = $db->fetch_array($query)) {
		$smileyarray['smilies'][] = $smiley;
	}

	$smileyarray['version'] = strip_tags($version);
	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$smiley_export = "# Discuz! Smilies Dump\n".
			"# Version: Discuz! $version\n".
			"# Time: $time\n".
			"# From: $bbname ($boardurl)\n".
			"#\n".
			"# This file was BASE64 encoded\n".
			"#\n".
			"# Discuz! Community: http://www.Discuz.net\n".
			"# Please visit our website for latest news about Discuz!\n".
			"# --------------------------------------------------------\n\n\n".
			wordwrap(base64_encode(serialize($smileyarray)), 50, "\n", 1);

	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($smiley_export));
	dheader('Content-Disposition: attachment; filename=discuz_smilies_'.$smileyarray['name'].'.txt');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));

	echo $smiley_export;
	dexit();
}

cpheader();

if(!$operation) {

	if(!submitcheck('smiliessubmit')) {

		shownav('topic', 'smilies_edit');
		showsubmenu('nav_smilies', array(
			array('smilies_type', 'smilies', 1),
			array('smilies_import', 'smilies&operation=import', 0),
		));
		showtips('smilies_tips_smileytypes');
		showformheader('smilies');
		showtableheader();
		showsubtitle(array('', 'display_order', 'smilies_type', 'dir', 'smilies_nums', '', ''));

		$dirfilter = array();
		$query = $db->query("SELECT * FROM {$tablepre}imagetypes WHERE type='smiley' ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$squery = $db->query("SELECT COUNT(*) FROM {$tablepre}smilies WHERE typeid='$type[typeid]'");
			$smiliesnum = $db->result($squery, 0);
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\" ".($smiliesnum ? 'disabled' : '').">",
				"<input type=\"text\" class=\"txt\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\" size=\"2\">",
				"<input type=\"text\" class=\"txt\" name=\"namenew[$type[typeid]]\" value=\"$type[name]\" size=\"15\">",
				"./images/smilies/$type[directory]",
				$smiliesnum,
				"<a href=\"admincp.php?action=smilies&operation=export&id=$type[typeid]\" class=\"act\">$lang[export]</a>",
				"<a href=\"admincp.php?action=smilies&operation=edit&id=$type[typeid]\" class=\"act\">$lang[detail]</a>"
			));
			$dirfilter[] = $type['directory'];
		}

		$smdir = DISCUZ_ROOT.'./images/smilies';
		$smiliesdir = dir($smdir);
		$dirnum = 0;
		while($entry = $smiliesdir->read()) {
			if($entry != '.' && $entry != '..' && !in_array($entry, $dirfilter) && preg_match("/^\w+$/", $entry) && strlen($entry) < 30 && is_dir($smdir.'/'.$entry)){
				showtablerow('', array('class="td25"', 'class="td28"'), array(
					($dirnum ? '&nbsp;' : $lang['add_new']),
					'<input type="text" class="txt" name="newdisplayorder[]" size="2">',
					'<input type="text" class="txt" name="newname[]" size="15">',
					'./images/smilies/'.$entry,
					'<input type="hidden" name="newdirectory[]" value="'.$entry.'">',
					''

				));
				$dirnum++;
			}
		}

		if(!$dirnum) {
			showtablerow('', array('', 'colspan="6"'), array(
				lang('add_new'),
				lang('smiliesupload_tips')
			));
		}

		showsubmit('smiliessubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($namenew)) {
			foreach($namenew as $id => $val) {
				$db->query("UPDATE {$tablepre}imagetypes SET name='".htmlspecialchars(trim($val))."', displayorder='$displayordernew[$id]' WHERE typeid='$id'");
			}
		}

		if($ids = implodeids($delete)) {
			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}smilies WHERE type='smiley' AND typeid IN ($ids)")) {
				cpmsg('smilies_delete_invalid', '', 'error');
			}
			$db->query("DELETE FROM {$tablepre}imagetypes WHERE typeid IN ($ids)");
		}

		if(is_array($newname)) {
			foreach($newname as $key => $val) {
				$val = trim($val);
				if($val) {
					$smurl = './images/smilies/'.$newdiredctory[$key];
					if(!is_dir(DISCUZ_ROOT.$smurl)) {
						cpmsg('smilies_directory_invalid', '', 'error');
					}
					$db->query("INSERT INTO {$tablepre}imagetypes (name, type, displayorder, directory) VALUES ('".htmlspecialchars($val)."', 'smiley', '$newdisplayorder[$key]', '$newdirectory[$key]')");
				}
			}
		}

		updatecache('smileytypes');
		cpmsg('smilies_edit_succeed', 'admincp.php?action=smilies', 'succeed');

	}

} elseif($operation == 'edit' && $id) {

	$type = $db->fetch_first("SELECT typeid, name, directory FROM {$tablepre}imagetypes WHERE typeid='$id'AND type='smiley'");
	$smurl = './images/smilies/'.$type['directory'];
	$smdir = DISCUZ_ROOT.$smurl;
	if(!is_dir($smdir)) {
		cpmsg('smilies_directory_invalid', '', 'error');
	}

	if(!$do) {

		if(!submitcheck('editsubmit')) {

			$smiliesperpage = 10;
			$page = max(1, intval($page));
			$start_limit = ($page - 1) * $smiliesperpage;

			$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$id'");
			$multipage = multi($num, $smiliesperpage, $page, 'admincp.php?action=smilies&operation=edit&id='.$id);

			$smileynum = 1;
			$smilies = '';
			$query = $db->query("SELECT * FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$id' ORDER BY displayorder LIMIT $start_limit, $smiliesperpage");
			while($smiley =	$db->fetch_array($query)) {
				$smilies .= showtablerow('', array('class="td25"', 'class="td28 td24"', 'class="td23"', 'class="td23"', 'class="td24"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
					"<img src=\"$smurl/$smiley[url]\" border=\"0\" onload=\"if(this.height>30) {this.resized=true; this.height=30;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\">",
					$smiley['id'],
					"<input type=\"text\" class=\"txt\" size=\"25\" name=\"code[$smiley[id]]\" value=\"".dhtmlspecialchars($smiley['code'])."\" id=\"code_$smileynum\" smileyid=\"$smiley[id]\">",
					"<input type=\"hidden\" value=\"$smiley[url]\" id=\"url_$smileynum\">$smiley[url]"
				), TRUE);
				$imgfilter[] = $smiley[url];
				$smileynum ++;
			}

			shownav('topic', 'nav_smilies');
			showsubmenu(lang('smilies_edit').' - '.$type[name], array(
				array('admin', "smilies&operation=edit&id=$id", !$do),
				array('add', "smilies&operation=edit&do=add&id=$id", $do == 'add')
			));
			showformheader("smilies&operation=edit&id=$id");
			showtableheader('', 'nobottom');
			showsubtitle(array('', 'display_order', 'smilies_edit_image', 'smilies_id', 'smilies_edit_code', 'smilies_edit_filename'));
			echo $smilies;
			showtablerow('', array('', 'colspan="5"'), array(
				'',
				$lang['smilies_edit_add_code'].' <input type="text" class="txt" style="margin-right:0;width:40px;" size="2" value="'.$lang['smilies_prefix'].'" id="prefix" onclick="clearinput(this, \''.$lang['smilies_prefix'].'\')" /> + <select id="middle"><option value="1">'.$lang['smilies_edit_order_file'].'</option><option value="2">'.$lang['smilies_edit_order_radom'].'</option><option value="3">'.$lang['smilies_id'].'</option></select> + <input type="text" class="txt" style="margin-right:0;width:40px;" size="2" value="'.$lang['smilies_suffix'].'" id="suffix" onclick="clearinput(this, \''.$lang['smilies_suffix'].'\')" /> <input type="button" class="btn" onclick="addsmileycodes(\''.$smileynum.'\', \'\');" value="'.$lang['apply'].'">'
			));
			showsubmit('editsubmit', 'submit', 'del', '<input type="button" class="btn" value="'.$lang['return'].'" onclick="window.location=\'admincp.php?action=smilies\'">', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM	{$tablepre}smilies WHERE id IN ($ids)");
			}

			if(is_array($displayorder)) {

				foreach($displayorder as $key => $val) {
					$displayorder[$key] = intval($displayorder[$key]);
					$code[$key] = trim($code[$key]);
					if(empty($code[$key])) {
						$db->query("DELETE FROM {$tablepre}smilies WHERE id='$key'");
					} else {
						$db->query("UPDATE {$tablepre}smilies SET displayorder='$displayorder[$key]', code='$code[$key]' WHERE id='$key'");
					}
				}
			}

			updatecache(array('smilies', 'smilies_display'));
			cpmsg('smilies_edit_succeed', "admincp.php?action=smilies&operation=edit&id=$id", 'succeed');

		}

	} elseif($do == 'add') {

		if(!submitcheck('editsubmit')) {

			shownav('topic', 'nav_smilies');
			showsubmenu(lang('smilies_edit').' - '.$type[name], array(
				array('admin', "smilies&operation=edit&id=$id", !$do),
				array('add', "smilies&operation=edit&do=add&id=$id", $do == 'add')
			));
			showtips('smilies_tips');
			showtagheader('div', 'addsmilies', TRUE);
			showtableheader('smilies_add', 'notop fixpadding');
			showtablerow('', '', "<span class=\"bold marginright\">$lang[smilies_type]:</span>$type[name]");
			showtablerow('', '', "<span class=\"bold marginright\">$lang[dir]:</span>$smurl $lang[smilies_add_search]");
			showtablerow('', '', '<input type="button" class="btn" value="'.$lang['search'].'" onclick="ajaxget(\'admincp.php?action=smilies&operation=edit&do=add&id='.$id.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);">');
			showtablefooter();
			showtagfooter('div');

			if($search) {

				$newid = 1;
				$newimages = '';
				$imgextarray = array('jpg', 'gif');
				$imgfilter =  array();
				$query = $db->query("SELECT url FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$id'");
				while($img = $db->fetch_array($query)) {
					$imgfilter[] = $img[url];
				}
				$smiliesdir = dir($smdir);
				while($entry = $smiliesdir->read()) {
					if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($entry, 0, strrpos($entry, '.'))) && strlen($entry) < 30 && is_file($smdir.'/'.$entry)) {
						$newimages .= showtablerow('', array('class="td25"', 'class="td28 td24"', 'class="td23"', 'class="td24"'), array(
							"<input class=\"checkbox\" type=\"checkbox\" name=\"add[$newid]\" value=\"\" checked=\"checked\">",
							"<input type=\"text\" class=\"txt\" size=\"2\" name=\"adddisplayorder[$newid]\" value=\"0\">",
							"<img src=\"$smurl/$entry\" border=\"0\" onload=\"if(this.height>30) {this.resized=true; this.height=30;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\">",
							"<input type=\"text\" class=\"txt\" size=\"25\" name=\"addcode[$newid]\" value=\"\" id=\"addcode_$newid\" smileyid=\"$smiley[id]\">",
							"<input type=\"hidden\" size=\"25\" name=\"addurl[$newid]\" value=\"$entry\" id=\"addurl_$newid\">$entry"
						), TRUE);
						$newid ++;
					}
				}
				$smiliesdir->close();

				ajaxshowheader();

				if($newimages) {

					showformheader("smilies&operation=edit&do=add&id=$id");
					showtableheader('smilies_add', 'notop fixpadding');
					showsubtitle(array('', 'display_order', 'smilies_edit_image', 'smilies_edit_code', 'smilies_edit_filename'));
					echo $newimages;
					showtablerow('', array('', 'colspan="4"'), array(
						'',
						$lang['smilies_edit_add_code'].' <input type="text" class="txt" style="margin-right:0;width:40px;" size="2" value="'.$lang['smilies_prefix'].'" id="addprefix" onclick="clearinput(this, \''.$lang['smilies_prefix'].'\')"> + <select id="addmiddle"><option value="1">'.$lang['smilies_edit_order_file'].'</option><option value="2">'.$lang['smilies_edit_order_radom'].'</option></select> + <input type="text" class="txt" style="margin-right:0;width:40px;" size="2" value="'.$lang['smilies_suffix'].'" id="addsuffix" onclick="clearinput(this, \''.$lang['smilies_suffix'].'\')" /> <input type="button" class="btn" onclick="addsmileycodes(\''.$newid.'\', \'add\');" value="'.$lang['apply'].'" />'
					));
					showtablerow('', array('', 'colspan="4"'), array(
						'<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'add\')" class="checkbox" checked="checked">'.$lang['enable'],
						'<input type="submit" class="btn" name="editsubmit" value="'.$lang['submit'].'"> &nbsp; <input type="button" class="btn" value="'.$lang['research'].'" onclick="ajaxget(\'admincp.php?action=smilies&operation=edit&do=add&id='.$id.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);">'
					));
					showtablefooter();
					showformfooter();

				} else {

					eval("\$lang[smilies_edit_add_tips] = \"".$lang['smilies_edit_add_tips']."\";");
					showtableheader('smilies_add', 'notop');
					showtablerow('', 'class="lineheight"', $lang['smilies_edit_add_tips']);
					showtablerow('', '', '<input type="button" class="btn" value="'.$lang['research'].'" onclick="ajaxget(\'admincp.php?action=smilies&operation=edit&do=add&id='.$id.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);">');
					showtablefooter();

				}

				ajaxshowfooter();
				exit;
			}

		} else {

			if(is_array($add)) {

				foreach($add as $k => $v) {
					$addcode[$k] = trim($addcode[$k]);
					if($addcode[$k] != '') {
						$db->query("INSERT INTO {$tablepre}smilies (type, displayorder, typeid, code, url)
							VALUES ('smiley', '{$adddisplayorder[$k]}', '$id', '$addcode[$k]', '$addurl[$k]')");
					}
				}

			}

			updatecache(array('smilies', 'smilies_display'));
			cpmsg('smilies_edit_succeed', "admincp.php?action=smilies&operation=edit&id=$id", 'succeed');
		}
	}

	echo <<<EOT
<script type="text/JavaScript">
	function addsmileycodes(smiliesnum, pre) {
		smiliesnum = parseInt(smiliesnum);
		if(smiliesnum > 1) {
			for(var i = 1; i < smiliesnum; i++) {
				var prefix = trim($(pre + 'prefix').value);
				var suffix = trim($(pre + 'suffix').value);
				var page = parseInt('$page');
				var middle = $(pre + 'middle').value == 1 ? $(pre + 'url_' + i).value.substr(0,$(pre + 'url_' + i).value.lastIndexOf('.')) : ($(pre + 'middle').value == 2 ? i + page * 10 : $(pre + 'code_'+ i).attributes['smileyid'].nodeValue);
				if(!prefix || prefix == '$lang[smilies_prefix]' || !suffix || suffix == '$lang[smilies_suffix]') {
					alert('$lang[smilies_prefix_tips]');
					return;
				}
				suffix = !suffix || suffix == '$lang[smilies_suffix]' ? '' : suffix;
				$(pre + 'code_' + i).value = prefix + middle + suffix;
			}
		}
	}
	function clearinput(obj, defaultval) {
		if(obj.value == defaultval) {
			obj.value = '';
		}
	}
</script>
EOT;

} elseif($operation == 'import') {

	if(!submitcheck('importsubmit')) {

		shownav('topic', 'smilies_edit');
		showsubmenu('nav_smilies', array(
			array('smilies_type', 'smilies', 0),
			array('smilies_import', 'smilies&operation=import', 1),
		));
		showtips('smilies_tips_import');
		showformheader('smilies&operation=import', 'enctype');
		showtableheader('smilies_import');
		showtablerow('', '', '<input type="file" name="importfile" size="40" class="uploadbtn marginbot" />');
		showsubmit('importsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$smiliesdata = preg_replace("/(#.*\s+)*/", '', @implode('', file($_FILES['importfile']['tmp_name'])));
		@unlink($_FILES['importfile']['tmp_name']);
		$smileyarray = daddslashes(unserialize(base64_decode($smiliesdata)), 1);

		if(!is_array($smileyarray) || !is_array($smileyarray['smilies'])) {
			cpmsg('smilies_import_data_invalid', '', 'error');
		}

		$renamed = 0;
		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}imagetypes WHERE type='smiley' AND name='$smileyarray[name]'")) {
			$smileyarray['name'] .= '_'.random(4);
			$renamed = 1;
		}
		$db->query("INSERT INTO {$tablepre}imagetypes (name, type, directory)
			VALUES ('$smileyarray[name]', 'smiley', '$smileyarray[directory]')");
		$typeid = $db->insert_id();

		foreach($smileyarray['smilies'] as $key => $smiley) {
			$substitute = @htmlspecialchars($substitute);
			$db->query("INSERT INTO {$tablepre}smilies (type, typeid, displayorder, code, url)
				VALUES ('smiley', '$typeid', '$smiley[displayorder]', '$smiley[code]', '$smiley[url]')");
		}

		updatecache(array('smileytypes', 'smilies', 'smilies_display'));
		cpmsg($renamed ? 'smilies_import_succeed_renamed' : 'smilies_import_succeed', 'admincp.php?action=smilies', 'succeed');

	}

}

?>