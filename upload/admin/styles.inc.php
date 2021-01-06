<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: styles.inc.php 13486 2008-04-18 04:32:01Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

if($operation == 'export' && $id) {

	$stylearray = $db->fetch_first("SELECT s.name, s.templateid, t.name AS tplname, t.directory, t.copyright FROM {$tablepre}styles s LEFT JOIN {$tablepre}templates t ON t.templateid=s.templateid WHERE styleid='$id'");
	if(!$stylearray) {
		cpheader();
		cpmsg('styles_export_invalid', '', 'error');
	}

	$stylearray['version'] = strip_tags($version);
	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$query = $db->query("SELECT * FROM {$tablepre}stylevars WHERE styleid='$id'");
	while($style = $db->fetch_array($query)) {
		$stylearray['style'][$style['variable']] = $style['substitute'];
	}

	$style_export = "# Discuz! Style Dump\n".
			"# Version: Discuz! $version\n".
			"# Time: $time\n".
			"# From: $bbname ($boardurl)\n".
			"#\n".
			"# This file was BASE64 encoded\n".
			"#\n".
			"# Discuz! Community: http://www.Discuz.net\n".
			"# Please visit our website for latest news about Discuz!\n".
			"# --------------------------------------------------------\n\n\n".
			wordwrap(base64_encode(serialize($stylearray)), 50, "\n", 1);

	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($style_export));
	dheader('Content-Disposition: attachment; filename=discuz_style_'.$stylearray['name'].'.txt');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));

	echo $style_export;
	dexit();

}

cpheader();

$predefinedvars = array('available', 'bgcolor', 'altbg1', 'altbg2', 'link', 'bordercolor', 'headercolor', 'headertext', 'catcolor',
	'tabletext', 'text', 'borderwidth', 'tablespace', 'fontsize', 'msgfontsize', 'msgbigsize', 'msgsmallsize',
	'font', 'smfontsize', 'smfont', 'boardimg', 'imgdir', 'maintablewidth', 'stypeid', 'bgborder',
	'catborder', 'inputborder', 'lighttext', 'headermenu', 'headermenutext', 'framebgcolor',
	'noticebg', 'commonboxborder', 'tablebg', 'highlightlink', 'commonboxbg', 'boxspace', 'portalboxbgcode',
	'noticeborder', 'noticetext'
);

if(!$operation) {

	if(!submitcheck('stylesubmit')) {

		$defaultstyleid = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='styleid'");
		$styleselect = '';
		$query = $db->query("SELECT s.styleid, s.available, s.name, t.name AS tplname, t.copyright FROM {$tablepre}styles s LEFT JOIN {$tablepre}templates t ON t.templateid=s.templateid");
		while($styleinfo = $db->fetch_array($query)) {
			$styleselect .= "<tr><td>".($styleinfo['styleid'] != $defaultstyleid ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$styleinfo[styleid]\">" : NULL)."</td>\n".
				"<td><input type=\"text\" class=\"txt\" name=\"namenew[$styleinfo[styleid]]\" value=\"$styleinfo[name]\" size=\"18\"></td>\n".
				"<td>".($styleinfo['styleid'] != $defaultstyleid ? "<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$styleinfo[styleid]]\" value=\"1\" ".($styleinfo['available'] ? 'checked' : NULL).">" : "<input class=\"checkbox\" type=\"hidden\" name=\"availablenew[$styleinfo[styleid]]\" value=\"1\">")."</td>\n".
				"<td>$styleinfo[styleid]</td>\n".
				"<td>$styleinfo[tplname]</td>\n".
				"<td><a href=\"admincp.php?action=styles&operation=export&id=$styleinfo[styleid]\" class=\"act\">$lang[export]</a></td>\n".
				"<td><a href=\"admincp.php?action=styles&operation=edit&id=$styleinfo[styleid]\" class=\"act\">$lang[detail]</a></td></tr>\n";
		}

		shownav('forum', 'nav_styles');
		showsubmenu('nav_styles', array(
			array('config', 'styles&operation=config', '0'),
			array('admin', 'styles', '1'),
			array('import', 'styles&operation=import', '0')
		));
		showtips('styles_tips');
		showformheader('styles');
		showhiddenfields(array('updatecsscache' => 0));
		showtableheader();
		showsubtitle(array('', 'styles_name', 'available', 'styleID', 'styles_template', '', ''));
		echo $styleselect;
		echo '<tr><td>'.$lang['add_new'].'</td><td><input type="text" class="txt" name="newname" size="18"></td><td colspan="6">&nbsp;</td></tr>';
		showsubmit('stylesubmit', 'submit', 'del', '<input onclick="this.form.updatecsscache.value=1" type="submit" class="btn" name="stylesubmit" value="'.lang('styles_csscache_update').'">');
		showtablefooter();
		showformfooter();

	} else {

		if($updatecsscache) {
			updatecache('styles');
			cpmsg('csscache_update', 'admincp.php?action=styles', 'succeed');
		} else {
			if(is_array($namenew)) {
				foreach($namenew as $id => $val) {
					$db->query("UPDATE {$tablepre}styles SET name='$namenew[$id]', available='$availablenew[$id]' WHERE styleid='$id'");
				}
			}

			if($ids = implodeids($delete)) {
				if($db->result_first("SELECT COUNT(*) FROM {$tablepre}settings WHERE variable='styleid' AND value IN ($ids)")) {
					cpmsg('styles_delete_invalid', '', 'error');
				}

				$db->query("DELETE FROM {$tablepre}styles WHERE styleid IN ($ids)");
				$db->query("DELETE FROM {$tablepre}stylevars WHERE styleid IN ($ids)");
				$db->query("UPDATE {$tablepre}members SET styleid='0' WHERE styleid IN ($ids)");
				$db->query("UPDATE {$tablepre}forums SET styleid='0' WHERE styleid IN ($ids)");
				$db->query("UPDATE {$tablepre}sessions SET styleid='$_DCACHE[settings][styleid]' WHERE styleid IN ($ids)");
			}

			if($newname) {
				$db->query("INSERT INTO {$tablepre}styles (name, templateid) VALUES ('$newname', '1')");
				$styleidnew = $db->insert_id();
				foreach($predefinedvars as $variable) {
					$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable)
						VALUES ('$styleidnew', '$variable')");
				}
			}

			updatecache('settings');
			updatecache('styles');
			cpmsg('styles_edit_succeed', 'admincp.php?action=styles', 'succeed');
		}

	}

} elseif($operation == 'import') {

	if(!submitcheck('importsubmit')) {

		shownav('forum', 'nav_styles');
		showsubmenu('nav_styles', array(
			array('config', 'styles&operation=config', '0'),
			array('admin', 'styles', '0'),
			array('import', 'styles&operation=import', '1')
		));
		showformheader('styles&operation=import', 'enctype');
		showtableheader('styles_import');
		showtablerow('', '', '<input type="file" name="importfile" size="40" class="uploadbtn marginbot" />');
		showtablerow('', '', '<input class="checkbox" type="checkbox" name="ignoreversion" id="ignoreversion" value="1" /><label for="ignoreversion"> '.lang('styles_import_ignore_version').'</label>');
		showsubmit('importsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$styledata = preg_replace("/(#.*\s+)*/", '', @implode('', file($_FILES['importfile']['tmp_name'])));
		@unlink($_FILES['importfile']['tmp_name']);
		$stylearray = daddslashes(unserialize(base64_decode($styledata)), 1);

		if(!is_array($stylearray)) {
			cpmsg('styles_import_data_invalid', '', 'error');
		} elseif(empty($ignoreversion) && strip_tags($stylearray['version']) != strip_tags($version)) {
			cpmsg('styles_import_version_invalid', '', 'error');
		}

		$renamed = 0;
		if($stylearray['templateid'] != 1) {
			$templatedir = DISCUZ_ROOT.'./'.$stylearray['directory'];
			if(!is_dir($templatedir)) {
				if(!@mkdir($templatedir, 0777)) {
					$basedir = dirname($stylearray['directory']);
					cpmsg('styles_import_directory_invalid', '', 'error');
				}
			}

			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}templates WHERE name='$stylearray[tplname]'")) {
				$stylearray['tplname'] .= '_'.random(4);
				$renamed = 1;
			}
			$db->query("INSERT INTO {$tablepre}templates (name, directory, copyright)
				VALUES ('$stylearray[tplname]', '$stylearray[directory]', '$stylearray[copyright]')");
			$templateid = $db->insert_id();
		} else {
			$templateid = 1;
		}

		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}styles WHERE name='$stylearray[name]'")) {
			$stylearray['name'] .= '_'.random(4);
			$renamed = 1;
		}
		$db->query("INSERT INTO {$tablepre}styles (name, templateid)
			VALUES ('$stylearray[name]', '$templateid')");
		$styleidnew = $db->insert_id();

		foreach($stylearray['style'] as $variable => $substitute) {
			$substitute = @htmlspecialchars($substitute);
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
				VALUES ('$styleidnew', '$variable', '$substitute')");
		}

		updatecache('styles');
		updatecache('settings');
		cpmsg($renamed ? 'styles_import_succeed_renamed' : 'styles_import_succeed', 'admincp.php?action=styles', 'succeed');

	}

} elseif($operation == 'edit') {

	if(!submitcheck('editsubmit')) {

		$style = $db->fetch_first("SELECT name, templateid FROM {$tablepre}styles WHERE styleid='$id'");
		if(!$style) {
			cpmsg('undefined_action', '', 'error');
		}

		$stylecustom = '';
		$stylestuff = $existvars = array();
		$query = $db->query("SELECT * FROM {$tablepre}stylevars WHERE styleid='$id'");
		while($stylevar = $db->fetch_array($query)) {
			if(in_array($stylevar['variable'], $predefinedvars)) {
				$stylestuff[$stylevar['variable']] = array('id' => $stylevar['stylevarid'], 'subst' => $stylevar['substitute']);
				$existvars[] = $stylevar['variable'];
			} else {
				$stylecustom .= showtablerow('', array('class="td25"', 'class="td24 bold"', 'class="td26"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$stylevar[stylevarid]\">",
					'{'.strtoupper($stylevar[variable]).'}',
					"<textarea name=\"stylevar[$stylevar[stylevarid]]\" style=\"height: 45px\" cols=\"50\" rows=\"2\">$stylevar[substitute]</textarea>",
					
				), TRUE);
			}
		}
		if($diffvars = array_diff($predefinedvars, $existvars)) {
			foreach($diffvars as $variable) {
				$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
					VALUES ('$id', '$variable', '')");
				$stylestuff[$variable] = array('id' => $db->insert_id(), 'subst' => '');
			}
		}

		$tplselect = array();
		$query = $db->query("SELECT templateid, name FROM {$tablepre}templates");
		while($template = $db->fetch_array($query)) {
			$tplselect[] = array($template['templateid'], $template['name']);
		}

		$smileytypes = array();
		$query = $db->query("SELECT typeid, name FROM {$tablepre}imagetypes");
		while($type = $db->fetch_array($query)) {
			$smileytypes[] = array($type['typeid'], $type['name']);
		}

		shownav('forum', 'styles_edit');
		showsubmenu('nav_styles', array(
			array('config', 'styles&operation=config', '0'),
			array('admin', 'styles', '0'),
			array('import', 'styles&operation=import', '0')
		));
		showformheader("styles&operation=edit&id=$id");
		showtableheader($lang['styles_edit'].' - '.$style['name'], 'nobottom');
		showsetting('styles_edit_name', 'namenew', $style['name'], 'text');
		showsetting('styles_edit_tpl', array('templateidnew', $tplselect), $style['templateid'], 'select');
		showsetting('styles_edit_smileytype', array("stylevar[{$stylestuff[stypeid][id]}]", $smileytypes), $stylestuff['stypeid']['subst'], 'select');
		showsetting('styles_edit_logo', "stylevar[{$stylestuff[boardimg][id]}]", $stylestuff['boardimg']['subst'], 'text');
		showsetting('styles_edit_imgdir', "stylevar[{$stylestuff[imgdir][id]}]", $stylestuff['imgdir']['subst'], 'text');

		showtitle('styles_edit_font_color');
		showsetting('styles_edit_font', "stylevar[{$stylestuff[font][id]}]", $stylestuff['font']['subst'], 'text');
		showsetting('styles_edit_fontsize', "stylevar[{$stylestuff[fontsize][id]}]", $stylestuff['fontsize']['subst'], 'text');
		showsetting('styles_edit_msgfontsize', "stylevar[{$stylestuff[msgfontsize][id]}]", $stylestuff['msgfontsize']['subst'], 'text');
		showsetting('styles_edit_msgbigsize', "stylevar[{$stylestuff[msgbigsize][id]}]", $stylestuff['msgbigsize']['subst'], 'text');
		showsetting('styles_edit_msgsmallsize', "stylevar[{$stylestuff[msgsmallsize][id]}]", $stylestuff['msgsmallsize']['subst'], 'text');
		showsetting('styles_edit_smfont', "stylevar[{$stylestuff[smfont][id]}]", $stylestuff['smfont']['subst'], 'text');
		showsetting('styles_edit_smfontsize', "stylevar[{$stylestuff[smfontsize][id]}]", $stylestuff['smfontsize']['subst'], 'text');
		showsetting('styles_edit_link', "stylevar[{$stylestuff[link][id]}]", $stylestuff['link']['subst'], 'color');
		showsetting('styles_edit_highlightlink', "stylevar[{$stylestuff[highlightlink][id]}]", $stylestuff['highlightlink']['subst'], 'color','55%');
		showsetting('styles_edit_headertext', "stylevar[{$stylestuff[headertext][id]}]", $stylestuff['headertext']['subst'], 'color');
		showsetting('styles_edit_tabletext', "stylevar[{$stylestuff[tabletext][id]}]", $stylestuff['tabletext']['subst'], 'color');
		showsetting('styles_edit_text', "stylevar[{$stylestuff[text][id]}]", $stylestuff['text']['subst'], 'color');
		showsetting('styles_edit_lighttext', "stylevar[{$stylestuff[lighttext][id]}]", $stylestuff['lighttext']['subst'], 'color');

		showtitle('styles_edit_table');
		showsetting('styles_edit_maintablewidth', "stylevar[{$stylestuff[maintablewidth][id]}]", $stylestuff['maintablewidth']['subst'], 'text');
		showsetting('styles_edit_tablespace', "stylevar[{$stylestuff[tablespace][id]}]", $stylestuff['tablespace']['subst'],   'text');
		showsetting('styles_edit_tablebg', "stylevar[{$stylestuff[tablebg][id]}]", $stylestuff['tablebg']['subst'], 'color');
		showsetting('styles_edit_borderwidth', "stylevar[{$stylestuff[borderwidth][id]}]", $stylestuff['borderwidth']['subst'], 'text');
		showsetting('styles_edit_bordercolor', "stylevar[{$stylestuff[bordercolor][id]}]", $stylestuff['bordercolor']['subst'], 'color');
		showsetting('styles_edit_bgcolor', "stylevar[{$stylestuff[bgcolor][id]}]", $stylestuff['bgcolor']['subst'], 'color');
		showsetting('styles_edit_headercolor', "stylevar[{$stylestuff[headercolor][id]}]", $stylestuff['headercolor']['subst'], 'color');
		showsetting('styles_edit_catcolor', "stylevar[{$stylestuff[catcolor][id]}]", $stylestuff['catcolor']['subst'], 'color');
		showsetting('styles_edit_catborder', "stylevar[{$stylestuff[catborder][id]}]", $stylestuff['catborder']['subst'], 'color');
		showsetting('styles_edit_portalboxbgcode', "stylevar[{$stylestuff[portalboxbgcode][id]}]", $stylestuff['portalboxbgcode']['subst'], 'color');
		showsetting('styles_edit_altbg1', "stylevar[{$stylestuff[altbg1][id]}]", $stylestuff['altbg1']['subst'], 'color');
		showsetting('styles_edit_altbg2', "stylevar[{$stylestuff[altbg2][id]}]", $stylestuff['altbg2']['subst'], 'color');
		showsetting('styles_edit_bgborder', "stylevar[{$stylestuff[bgborder][id]}]", $stylestuff['bgborder']['subst'], 'color');
		showsetting('styles_edit_noticebg', "stylevar[{$stylestuff[noticebg][id]}]", $stylestuff['noticebg']['subst'], 'color');
		showsetting('styles_edit_noticeborder', "stylevar[{$stylestuff[noticeborder][id]}]", $stylestuff['noticeborder']['subst'], 'color');
		showsetting('styles_edit_noticetext', "stylevar[{$stylestuff[noticetext][id]}]", $stylestuff['noticetext']['subst'], 'color');
		showsetting('styles_edit_commonboxborder', "stylevar[{$stylestuff[commonboxborder][id]}]", $stylestuff['commonboxborder']['subst'], 'color');
		showsetting('styles_edit_commonboxbg', "stylevar[{$stylestuff[commonboxbg][id]}]", $stylestuff['commonboxbg']['subst'], 'color');
		showsetting('styles_edit_boxspace', "stylevar[{$stylestuff[boxspace][id]}]", $stylestuff['boxspace']['subst'], 'text');

		showtitle('styles_other_table');
		showsetting('styles_edit_inputborder', "stylevar[{$stylestuff[inputborder][id]}]", $stylestuff['inputborder']['subst'], 'color');
		showsetting('styles_edit_headermenu', "stylevar[{$stylestuff[headermenu][id]}]", $stylestuff['headermenu']['subst'], 'color');
		showsetting('styles_edit_headermenutext', "stylevar[{$stylestuff[headermenutext][id]}]", $stylestuff['headermenutext']['subst'], 'color');
		showsetting('styles_edit_framebgcolor', "stylevar[{$stylestuff[framebgcolor][id]}]", $stylestuff['framebgcolor']['subst'], 'color');
		showtablefooter();

		showtableheader('styles_edit_customvariable', 'notop');
		showsubtitle(array('', 'styles_edit_variable', 'styles_edit_subst'));
		echo $stylecustom;
		showtablerow('', array('class="td25"', 'class="td24 bold"', 'class="td26"'), array(
			lang('add_new'),
			'<input type="text" class="txt" name="newcvar">',
			'<textarea name="newcsubst" class="tarea" style="height: 45px" cols="50" rows="2"></textarea>'
			
		));
		showsubmit('editsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($newcvar && $newcsubst) {
			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}stylevars WHERE variable='$newcvar' AND styleid='$id'")) {
				cpmsg('styles_edit_variable_duplicate', '', 'error');
			} elseif(!preg_match("/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/", $newcvar)) {
				cpmsg('styles_edit_variable_illegal', '', 'error');
			}
			$newcvar = strtolower($newcvar);
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
				VALUES ('$id', '$newcvar', '$newcsubst')");
		}

		$db->query("UPDATE {$tablepre}styles SET name='$namenew', templateid='$templateidnew' WHERE styleid='$id'");
		foreach($stylevar as $varid => $substitute) {
			$substitute = @htmlspecialchars($substitute);
			$db->query("UPDATE {$tablepre}stylevars SET substitute='$substitute' WHERE stylevarid='$varid' AND styleid='$id'");
		}

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}stylevars WHERE stylevarid IN ($ids) AND styleid='$id'");
		}

		updatecache('styles');
		cpmsg('styles_edit_succeed', 'admincp.php?action=styles'.($newcvar && $newcsubst ? '&operation=edit&id='.$id : ''), 'succeed');

	}

} elseif($operation == 'config') {

	if(!submitcheck('configsubmit')) {

		$settings = array();
		$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable IN ('styleid', 'stylejump')");
		while($setting = $db->fetch_array($query)) {
			$settings[$setting['variable']] = $setting['value'];
		}

		$stylelist = "<select name=\"settingsnew[styleid]\">\n";
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$selected = $style['styleid'] == $settings['styleid'] ? 'selected="selected"' : NULL;
			$stylelist .= "<option value=\"$style[styleid]\" $selected>$style[name]</option>\n";
		}
		$stylelist .= '</select>';

		shownav('forum', 'nav_styles');
		showsubmenu('nav_styles', array(
			array('config', 'styles&operation=config', '1'),
			array('admin', 'styles', '0'),
			array('import', 'styles&operation=import', '0')
		));
		showformheader('styles&operation=config');
		showtableheader();
		showsetting('settings_styleid', '', '', $stylelist);
		showsetting('settings_stylejump', 'settingsnew[stylejump]', $settings['stylejump'], 'radio');
		showsubmit('configsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('styleid', '$settingsnew[styleid]')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('stylejump', '$settingsnew[stylejump]')");
		updatecache('settings');
		cpmsg('settings_update_succeed', 'admincp.php?action=styles&operation=config', 'succeed');

	}

}

?>