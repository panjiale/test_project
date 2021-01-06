<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: misc.inc.php 13486 2008-04-18 04:32:01Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'onlinelist') {

	if(!submitcheck('onlinesubmit')) {

		shownav('misc', 'onlinelist');
		showsubmenu('nav_misc_onlinelist');
		showtips('onlinelist_tips');
		showformheader('misc&operation=onlinelist&');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'display_order', 'usergroup', 'usergroups_title', 'onlinelist_image'));

		$listarray = array();
		$query = $db->query("SELECT * FROM {$tablepre}onlinelist");
		while($list = $db->fetch_array($query)) {
			$list['title'] = dhtmlspecialchars($list['title']);
			$listarray[$list['groupid']] = $list;
		}

		$onlinelist = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type<>'member'");
		$group = array('groupid' => 0, 'grouptitle' => 'Member');
		do {
			$id = $group['groupid'];
			showtablerow('', array('class="td25"', 'class="td23 td28"', 'class="td24"', 'class="td24"', 'class="td21 td26"'), array(
				$listarray[$id]['url'] ? " <img src=\"images/common/{$listarray[$id]['url']}\">" : '',
				'<input type="text" class="txt" name="displayordernew['.$id.']" value="'.$listarray[$id]['displayorder'].'" size="3" />',
				$group['groupid'] <= 8 ? lang('usergroups_system_'.$id) : $group['grouptitle'],
				'<input type="text" class="txt" name="titlenew['.$id.']" value="'.($listarray[$id]['title'] ? $listarray[$id]['title'] : $group['grouptitle']).'" size="15" />',
				'<input type="text" class="txt" name="urlnew['.$id.']" value="'.$listarray[$id]['url'].'" size="20" />'
			));;

		} while($group = $db->fetch_array($query));

		showsubmit('onlinesubmit', 'submit', 'td');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($urlnew)) {
			$db->query("DELETE FROM {$tablepre}onlinelist");
			foreach($urlnew as $id => $url) {
				$url = trim($url);
				if($id == 0 || $url) {
					$db->query("INSERT INTO {$tablepre}onlinelist (groupid, displayorder, title, url)
						VALUES ('$id', '$displayordernew[$id]', '$titlenew[$id]', '$url')");
				}
			}
		}

		updatecache('onlinelist');
		cpmsg('onlinelist_succeed', 'admincp.php?action=misc&operation=onlinelist', 'succeed');

	}

} elseif($operation == 'forumlinks') {

	if(!submitcheck('forumlinksubmit')) {

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
		[1,'<input type="text" class="txt" name="newname[]" size="15">'],
		[1,'<input type="text" class="txt" name="newurl[]" size="20">'],
		[1,'<input type="text" class="txt" name="newdescription[]" size="30">', 'td26'],
		[1,'<input type="text" class="txt" name="newlogo[]" size="20">']
	]
]
</script>
<?

		shownav('misc', 'forumlinks');
		showsubmenu('nav_misc_links');
		showtips('forumlinks_tips');
		showformheader('misc&operation=forumlinks');
		showtableheader();
		showsubtitle(array('', 'display_order', 'forumlinks_edit_name', 'forumlinks_edit_url', 'forumlinks_edit_description', 'forumlinks_edit_logo'));

		$query = $db->query("SELECT * FROM {$tablepre}forumlinks ORDER BY displayorder");
		while($forumlink = $db->fetch_array($query)) {
			showtablerow('', array('class="td25"', 'class="td28"', '', '', 'class="td26"'), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$forumlink['id'].'" />',
				'<input type="text" class="txt" name="displayorder['.$forumlink[id].']" value="'.$forumlink['displayorder'].'" size="3" />',
				'<input type="text" class="txt" name="name['.$forumlink[id].']" value="'.$forumlink['name'].'" size="15" />',
				'<input type="text" class="txt" name="url['.$forumlink[id].']" value="'.$forumlink['url'].'" size="20" />',
				'<input type="text" class="txt" name="description['.$forumlink[id].']" value="'.$forumlink['description'].'" size="30" />',
				'<input type="text" class="txt" name="logo['.$forumlink[id].']" value="'.$forumlink['logo'].'" size="20" />'
			));
		}

		echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['forumlinks_add'].'</a></div></td></tr>';
		showsubmit('forumlinksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	{$tablepre}forumlinks WHERE id IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}forumlinks SET displayorder='$displayorder[$id]', name='$name[$id]', url='$url[$id]',description='$description[$id]',logo='$logo[$id]' WHERE id='$id'");
			}
		}

		if(is_array($newname)) {
			foreach($newname as $key => $value) {
				if($value) {
					$db->query("INSERT INTO {$tablepre}forumlinks (displayorder, name, url, description, logo) VALUES ('$newdisplayorder[$key]', '$value', '$newurl[$key]', '$newdescription[$key]', '$newlogo[$key]')");
				}
			}
		}

		updatecache('forumlinks');
		cpmsg('forumlinks_succeed', 'admincp.php?action=misc&operation=forumlinks', 'succeed');

	}

} elseif($operation == 'discuzcodes') {

	if(!submitcheck('bbcodessubmit') && !$edit) {

		shownav('topic', 'nav_posting_discuzcodes');
		showsubmenu('nav_posting_discuzcodes');
		showtips('dzcode_edit_tips');
		showformheader('misc&operation=discuzcodes');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'dzcode_tag', 'available', 'dzcode_icon', 'dzcode_icon_file', ''));
		$query = $db->query("SELECT * FROM {$tablepre}bbcodes");
		while($bbcode = $db->fetch_array($query)) {
			showtablerow('', array('class="td25"', 'class="td21"', 'class="td25"', 'class="td25"', 'class="td21"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$bbcode[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"tagnew[$bbcode[id]]\" value=\"$bbcode[tag]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] ? 'checked' : NULL).">",
				$bbcode[icon] ? "<img src=\"images/common/$bbcode[icon]\" border=\"0\"" : ' ',
				"<input type=\"text\" class=\"txt\" size=\"25\" name=\"iconnew[$bbcode[id]]\" value=\"$bbcode[icon]\">",
				"<a href=\"admincp.php?action=misc&operation=discuzcodes&edit=$bbcode[id]\" class=\"act\">$lang[detail]</a>"
			));
		}
		showtablerow('', array('class="td25"', 'class="td21"', 'class="td25"', 'class="td25"', 'class="td21"'), array(
			lang('add_new'),
			'<input type="text" class="txt" size="15" name="newtag">',
			'',
			'',
			'<input type="text" class="txt" size="25" name="newicon">',
			''
		));
		showsubmit('bbcodessubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} elseif(submitcheck('bbcodessubmit')) {

		if(is_array($delete)) {
			$ids = '\''.implode('\',\'', $delete).'\'';
			$db->query("DELETE FROM	{$tablepre}bbcodes WHERE id IN ($ids)");
		}

		if(is_array($tagnew)) {
			foreach($tagnew as $id => $val) {
				if(!preg_match("/^[0-9a-z]+$/i", $tagnew[$id]) && strlen($tagnew[$id]) < 20) {
					cpmsg('dzcode_edit_tag_invalid', '', 'error');
				}
				$db->query("UPDATE {$tablepre}bbcodes SET tag='$tagnew[$id]', icon='$iconnew[$id]', available='$availablenew[$id]' WHERE id='$id'");
			}
		}

		if($newtag != '') {
			if(!preg_match("/^[0-9a-z]+$/i", $newtag && strlen($newtag) < 20)) {
				cpmsg('dzcode_edit_tag_invalid', '', 'error');
			}
			$db->query("INSERT INTO	{$tablepre}bbcodes (tag, icon, available, params, nest)
				VALUES ('$newtag', '$newicon', '0', '1', '1')");
		}

		updatecache(array('bbcodes', 'bbcodes_display'));
		cpmsg('dzcode_edit_succeed', 'admincp.php?action=misc&operation=discuzcodes', 'succeed');

	} elseif($edit) {

		$bbcode = $db->fetch_first("SELECT * FROM {$tablepre}bbcodes WHERE id='$edit'");
		if(!$bbcode) {
			cpmsg('undefined_action', '', 'error');
		}

		if(!submitcheck('editsubmit')) {
			$bbcode['prompt'] = str_replace("\t", "\n", $bbcode['prompt']);

			shownav('topic', 'nav_posting_discuzcodes');
			showsubmenu($lang['dzcode_edit'].' - '.$bbcode['tag']);
			showformheader("misc&operation=discuzcodes&edit=$edit");
			showtableheader();
			showsetting('dzcode_edit_tag', 'tagnew', $bbcode['tag'], 'text');
			showsetting('dzcode_edit_replacement', 'replacementnew', $bbcode['replacement'], 'textarea');
			showsetting('dzcode_edit_example', 'examplenew', $bbcode['example'], 'text');
			showsetting('dzcode_edit_explanation', 'explanationnew', $bbcode['explanation'], 'text');
			showsetting('dzcode_edit_params', 'paramsnew', $bbcode['params'], 'text');
			showsetting('dzcode_edit_prompt', 'promptnew', $bbcode['prompt'], 'textarea');
			showsetting('dzcode_edit_nest', 'nestnew', $bbcode['nest'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$tagnew = trim($tagnew);
			if(!preg_match("/^[0-9a-z]+$/i", $tagnew)) {
				cpmsg('dzcode_edit_tag_invalid', '', 'error');
			} elseif($paramsnew < 1 || $paramsnew > 3 || $nestnew < 1 || $nestnew > 3) {
				cpmsg('dzcode_edit_range_invalid', '', 'error');
			}
			$promptnew = trim(preg_replace("/\r\n|\r|\n/", "\t", str_replace("\t", '', $promptnew)));

			$db->query("UPDATE {$tablepre}bbcodes SET tag='$tagnew', replacement='$replacementnew', example='$examplenew', explanation='$explanationnew', params='$paramsnew', prompt='$promptnew', nest='$nestnew' WHERE id='$edit'");

			updatecache(array('bbcodes', 'bbcodes_display'));
			cpmsg('dzcode_edit_succeed', 'admincp.php?action=misc&operation=discuzcodes', 'succeed');

		}
	}

} elseif($operation == 'censor') {

	$page = max(1, intval($page));
	$ppp = 30;

	$addcensors = isset($addcensors) ? trim($addcensors) : '';

	if($do == 'export') {

		ob_end_clean();
		dheader('Cache-control: max-age=0');
		dheader('Expires: '.gmdate('D, d M Y H:i:s', $timestamp - 31536000).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename=CensorWords.txt');
		dheader('Content-Type: text/plain');

		$query = $db->query("SELECT find, replacement FROM {$tablepre}words ORDER BY find ASC");
		while($censor = $db->fetch_array($query)) {
			$censor['replacement'] = str_replace('*', '', $censor['replacement']) <> '' ? $censor['replacement'] : '';
			echo $censor['find'].($censor['replacement'] != '' ? '='.stripslashes($censor['replacement']) : '')."\n";
		}
		exit();

	} elseif(submitcheck('addcensorsubmit') && $addcensors != '') {
		$oldwords = array();
		if($adminid == 1 && $overwrite == 2) {
			$db->query("TRUNCATE {$tablepre}words");
		} else {
			$query = $db->query("SELECT find, admin FROM {$tablepre}words");
			while($censor = $db->fetch_array($query)) {
				$oldwords[md5($censor['find'])] = $censor['admin'];
			}
			$db->free_result($query);
		}

		$censorarray = explode("\n", $addcensors);
		$updatecount = $newcount = $ignorecount = 0;
		foreach($censorarray as $censor) {
			list($newfind, $newreplace) = array_map('trim', explode('=', $censor));
			$newreplace = $newreplace <> '' ? daddslashes(str_replace("\\\'", '\'', $newreplace), 1) : '**';
			if(strlen($newfind) < 3) {
				$ignorecount ++;
				continue;
			} elseif(isset($oldwords[md5($newfind)])) {
				if($overwrite && ($adminid == 1 || $oldwords[md5($newfind)] == $discuz_userss)) {
					$updatecount ++;
					$db->query("UPDATE {$tablepre}words SET replacement='$newreplace' WHERE `find`='$newfind'");
				} else {
					$ignorecount ++;
				}
			} else {
				$newcount ++;
				$db->query("INSERT INTO	{$tablepre}words (admin, find, replacement) VALUES
					('$discuz_user', '$newfind', '$newreplace')");
				$oldwords[md5($newfind)] = $discuz_userss;
			}
		}
		updatecache('censor');
		cpmsg('censor_batch_add_succeed', "admincp.php?action=misc&operation=censor&anchor=import", 'succeed');

	} elseif(!submitcheck('censorsubmit')) {

		$page = max(1, intval($page));
		$startlimit = ($page - 1) * $ppp;
		$totalcount = $db->result_first("SELECT count(*) FROM {$tablepre}words");
		$multipage = multi($totalcount, $ppp, $page, "admincp.php?action=misc&operation=censor");

		shownav('topic', 'nav_posting_censors');
		$anchor = in_array($anchor, array('list', 'import')) ? $anchor : 'list';
		showsubmenuanchors('nav_posting_censors', array(
			array('admin', 'list', $anchor == 'list'),
			array('censor_batch_add', 'import', $anchor == 'import')
		));
		showtips('censor_tips', 'list_tips', $anchor == 'list');
		showtips('censor_batch_add_tips', 'import_tips', $anchor == 'import');

		showtagheader('div', 'list', $anchor == 'list');
		showformheader("misc&operation=censor&page=$page", '', 'listform');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'censor_word', 'censor_replacement', 'operator'));

		$query = $db->query("SELECT * FROM {$tablepre}words ORDER BY find ASC LIMIT $startlimit, $ppp");
		while($censor =	$db->fetch_array($query)) {
			$censor['replacement'] = stripslashes($censor['replacement']);
			$disabled = $adminid != 1 && $censor['admin'] != $discuz_userss ? 'disabled' : NULL;
			showtablerow('', array('class="td25"', 'class="td24"', 'class="td24"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled>",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"replace[$censor[id]]\" value=\"$censor[replacement]\" $disabled>",
				$censor[admin]
			));
		}

		showtablerow('', array('class="td25"', 'class="td24"', 'class="td24"'), array(
			lang('add_new'),
			'<input type="text" class="txt" size="30" name="newfind">',
			'<input type="text" class="txt" size="30" name="newreplace">',
			''
		));
		showsubmit('censorsubmit', 'submit', 'del', '', $multipage);
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'import', $anchor == 'import');
		showformheader("misc&operation=censor&page=$page", 'fixpadding');
		showtableheader('', 'fixpadding', 'importform');
		showtablerow('', 'class="vtop rowform"', '<br /><textarea name="addcensors" class="tarea" rows="10" cols="80"></textarea><br /><br />'.mradio('overwrite', array(
				2 => lang('censor_batch_add_clear'),
				1 => lang('censor_batch_add_overwrite'),
				0 => lang('censor_batch_add_no_overwrite')
		)));
		showsubmit('addcensorsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}words WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if(is_array($find)) {
			foreach($find as $id =>	$val) {
				$find[$id]  = $val = trim(str_replace('=', '', $find[$id]));
				if(strlen($val) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				$replace[$id] = daddslashes(str_replace("\\\'", '\'', $replace[$id]), 1);
				$db->query("UPDATE {$tablepre}words SET find='$find[$id]', replacement='$replace[$id]' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
			}
		}

		$newfind = trim(str_replace('=', '', $newfind));
		$newreplace  = trim($newreplace);

		if($newfind != '') {
			if(strlen($newfind) < 3) {
				cpmsg('censor_keywords_tooshort', '', 'error');
			}
			$newreplace = daddslashes(str_replace("\\\'", '\'', $newreplace), 1);
			if($oldcenser = $db->fetch_first("SELECT admin FROM {$tablepre}words WHERE find='$newfind'")) {
				cpmsg('censor_keywords_existence', '', 'error');
			} else {
				$db->query("INSERT INTO	{$tablepre}words (admin, find, replacement) VALUES
					('$discuz_user', '$newfind', '$newreplace')");
			}
		}

		updatecache('censor');
		cpmsg('censor_succeed', "admincp.php?action=misc&operation=censor&page=$page", 'succeed');

	}

} elseif($operation == 'icons') {

	if(!submitcheck('iconsubmit')) {

		$anchor = in_array($anchor, array('list', 'add')) ? $anchor : 'list';
		shownav('topic', 'nav_thread_icon');
		showsubmenuanchors('nav_thread_icon', array(
			array('admin', 'list', $anchor == 'list'),
			array('add', 'add', $anchor == 'add')
		));

		showtagheader('div', 'list', $anchor == 'list');
		showformheader('misc&operation=icons');
		showtableheader();
		showsubtitle(array('', 'display_order', 'smilies_edit_image', 'smilies_edit_filename'));

		$imgfilter =  array();
		$query = $db->query("SELECT * FROM {$tablepre}smilies WHERE type='icon' ORDER BY displayorder");
		while($smiley =	$db->fetch_array($query)) {
			showtablerow('', array('class="td25"', 'class="td28 td24"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
				"<img src=\"images/icons/$smiley[url]\">",
				$smiley[url]
			));
			$imgfilter[] = $smiley[url];
		}

		showsubmit('iconsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'add', $anchor == 'add');
		showformheader('misc&operation=icons');
		showtableheader();
		showsubtitle(array('', 'display_order', 'smilies_edit_image', 'smilies_edit_filename'));

		$newid = 0;
		$imgextarray = array('jpg', 'gif');
		$iconsdir = dir(DISCUZ_ROOT.'./images/icons');
		while($entry = $iconsdir->read()) {
			if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && is_file(DISCUZ_ROOT.'./images/icons/'.$entry)) {
				showtablerow('', array('class="td25"', 'class="td28 td24"', 'class="td23"'), array(
					"<input type=\"checkbox\" name=\"addcheck[$newid]\" class=\"checkbox\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"adddisplayorder[$newid]\" value=\"0\">",
					"<img src=\"images/icons/$entry\">",
					"<input type=\"text\" class=\"txt\" size=\"35\" name=\"addurl[$newid]\" value=\"$entry\" readonly>"
				));
				$newid ++;
			}
		}
		$iconsdir->close();
		if(!$newid) {
			showtablerow('', array('class="td25"', 'colspan="3"'), array('', lang('icon_tips')));
		} else {
			showsubmit('iconsubmit', 'submit', '<input type="checkbox" class="checkbox" name="chkall2" onclick="checkAll(\'prefix\', this.form, \'addcheck\', \'chkall2\')">'.lang('enable'));
		}

		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}smilies WHERE id IN ($ids)");
		}

		if(is_array($displayorder)) {
			foreach($displayorder as $id => $val) {
				$displayorder[$id] = intval($displayorder[$id]);
				$db->query("UPDATE {$tablepre}smilies SET displayorder='$displayorder[$id]' WHERE id='$id'");
			}
		}

		if(is_array($addurl)) {
			foreach($addurl as $k => $v) {
				if($addcheck[$k]) {
					$query = $db->query("INSERT INTO {$tablepre}smilies (displayorder, type, url)
						VALUES ('{$adddisplayorder[$k]}', 'icon', '$addurl[$k]')");
				}
			}
		}

		updatecache('icons');

		cpmsg('thread_icon_succeed', "admincp.php?action=misc&operation=icons", 'succeed');
	}

} elseif($operation == 'attachtypes') {

	if(!submitcheck('typesubmit')) {

		$attachtypes = '';
		$query = $db->query("SELECT * FROM {$tablepre}attachtypes");
		while($type = $db->fetch_array($query)) {
			$attachtypes .= showtablerow('', array('class="td25"', 'class="td24"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\" />",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\" />",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\" />"
			), TRUE);
		}

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input name="newextension[]" type="text" class="txt" size="10">', 'td24'],
		[1,'<input name="newmaxsize[]" type="text" class="txt" size="15">']
	]
];
</script>
<?

		shownav('topic', 'nav_posting_attachtypes');
		showsubmenu('nav_posting_attachtypes');
		showtips('attachtypes_tips');
		showformheader('misc&operation=attachtypes');
		showtableheader();
		showtablerow('class="partition"', array('class="td25"'), array('', lang('attachtypes_ext'), lang('attachtypes_maxsize')));
		echo $attachtypes;
		echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['attachtypes_add'].'</a></div></tr>';
		showsubmit('typesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}attachtypes WHERE id IN ($ids)");
		}

		if(is_array($extension)) {
			foreach($extension as $id => $val) {
				$db->query("UPDATE {$tablepre}attachtypes SET extension='$extension[$id]', maxsize='$maxsize[$id]' WHERE id='$id'");
			}
		}

		if(is_array($newextension)) {
			foreach($newextension as $key => $value) {
				if($newextension1 = trim($value)) {
					if($db->result_first("SELECT id FROM {$tablepre}attachtypes WHERE extension='$newextension1'")) {
						cpmsg('attachtypes_duplicate', '', 'error');
					}
					$db->query("INSERT INTO	{$tablepre}attachtypes (extension, maxsize) VALUES
							('$newextension1', '$newmaxsize[$key]')");
				}
			}
		}

		cpmsg('attachtypes_succeed', 'admincp.php?action=misc&operation=attachtypes', 'succeed');

	}

} elseif($operation == 'crons') {

	if(empty($edit) && empty($run)) {

		if(!submitcheck('cronssubmit')) {

			shownav('misc', 'crons');
			showsubmenu('nav_misc_crons');
			showtips('crons_tips');
			showformheader('misc&operation=crons');
			showtableheader('', 'fixpadding');
			showsubtitle(array('', 'name', 'available', 'type', 'time', 'crons_last_run', 'crons_next_run', ''));

			$query = $db->query("SELECT * FROM {$tablepre}crons ORDER BY type DESC");
			while($cron = $db->fetch_array($query)) {
				$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';

				if($cron['day'] > 0 && $cron['day'] < 32) {
					$cron['time'] = lang('crons_permonth').$cron['day'].lang('crons_day');
				} elseif($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
					$cron['time'] = lang('crons_perweek').lang('crons_week_day_'.$cron['weekday']);
				} elseif($cron['hour'] >= 0 && $cron['hour'] < 24) {
					$cron['time'] = lang('crons_perday');
				} else {
					$cron['time'] = lang('crons_perhour');
				}

				$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron[hour]).lang('crons_hour') : lang('crons_perhour');

				if(!in_array($cron['minute'], array(-1, ''))) {
					foreach($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
						$cron['minute'][$k] = sprintf('%02d', $v);
					}
					$cron['minute'] = implode(',', $cron['minute']);
					$cron['time'] .= $cron['minute'].lang('crons_minute');
				} else {
					$cron['time'] .= '00'.lang('crons_minute');
				}

				$cron['lastrun'] = $cron['lastrun'] ? gmdate("$dateformat<\b\\r />$timeformat", $cron['lastrun'] + $_DCACHE['settings']['timeoffset'] * 3600) : '<b>N/A</b>';
				$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_DCACHE['settings']['timeoffset'] * 3600 < $timestamp ? 'style="color: #ff0000"' : '';
				$cron['nextrun'] = $cron['nextrun'] ? gmdate("$dateformat<\b\\r />$timeformat", $cron['nextrun'] + $_DCACHE['settings']['timeoffset'] * 3600) : '<b>N/A</b>';

				showtablerow('', array('class="td25"', 'class="crons"', 'class="td25"', 'class="td25"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td25"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$cron[cronid]\" ".($cron['type'] == 'system' ? 'disabled' : '').">",
					"<input type=\"text\" class=\"txt\" name=\"namenew[$cron[cronid]]\" size=\"20\" value=\"$cron[name]\"><br /><b>$cron[filename]</b>",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$cron[cronid]]\" value=\"1\" ".($cron['available'] ? 'checked' : '')." $disabled>",
					$lang['crons_type_'.$cron['type']],
					$cron[time],
					$cron[lastrun],
					$cron[nextrun],
					"<a href=\"admincp.php?action=misc&operation=crons&edit=$cron[cronid]\" class=\"act\">$lang[edit]</a><br />".
					($cron['available'] ? " <a href=\"admincp.php?action=misc&operation=crons&run=$cron[cronid]\" class=\"act\">$lang[crons_run]</a>" : " <a href=\"###\" class=\"act\" disabled>$lang[crons_run]</a>")
				));
			}

			showtablerow('', array('','colspan="10"'), array(
				lang('add_new'),
				'<input type="text" class="txt" name="newname" value="" size="20" />'
			));
			showsubmit('cronssubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM {$tablepre}crons WHERE cronid IN ($ids) AND type='user'");
			}

			if(is_array($namenew)) {
				foreach($namenew as $id => $name) {
					$db->query("UPDATE {$tablepre}crons SET name='".dhtmlspecialchars($namenew[$id])."', available='".$availablenew[$id]."' ".($availablenew[$id] ? '' : ', nextrun=\'0\'')." WHERE cronid='$id'");
				}
			}

			if($newname = trim($newname)) {
				$db->query("INSERT INTO {$tablepre}crons (name, type, available, weekday, day, hour, minute, nextrun)
					VALUES ('".dhtmlspecialchars($newname)."', 'user', '0', '-1', '-1', '-1', '', '$timestamp')");
			}

			$query = $db->query("SELECT cronid, filename FROM {$tablepre}crons");
			while($cron = $db->fetch_array($query)) {
				if(!file_exists(DISCUZ_ROOT.'./include/crons/'.$cron['filename'])) {
					$db->query("UPDATE {$tablepre}crons SET available='0', nextrun='0' WHERE cronid='$cron[cronid]'");
				}
			}

			//updatecache('crons');
			updatecache('settings');
			cpmsg('crons_succeed', 'admincp.php?action=misc&operation=crons', 'succeed');

		}

	} else {

		$cronid = empty($run) ? $edit : $run;
		$cron = $db->fetch_first("SELECT * FROM {$tablepre}crons WHERE cronid='$cronid'");
		if(!$cron) {
			cpmsg('undefined_action', '', 'error');
		}
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cronminute = str_replace("\t", ',', $cron['minute']);
		$cron['minute'] = explode("\t", $cron['minute']);

		if(!empty($edit)) {

			if(!submitcheck('editsubmit')) {

				shownav('misc', 'cron');
				showsubmenu($lang['crons_edit'].' - '.$cron['name']);
				showtips('crons_edit_tips');

				$weekdayselect = $dayselect = $hourselect = '';

				for($i = 0; $i <= 6; $i++) {
					$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').">".$lang['crons_week_day_'.$i]."</option>";
				}

				for($i = 1; $i <= 31; $i++) {
					$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i $lang[crons_day]</option>";
				}

				for($i = 0; $i <= 23; $i++) {
					$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i $lang[crons_hour]</option>";
				}

				shownav('misc', 'crons');
				showformheader("misc&operation=crons&edit=$cronid");
				showtableheader();
				showsetting('crons_edit_weekday', '', '', "<select name=\"weekdaynew\"><option value=\"-1\">*</option>$weekdayselect</select>");
				showsetting('crons_edit_day', '', '', "<select name=\"daynew\"><option value=\"-1\">*</option>$dayselect</select>");
				showsetting('crons_edit_hour', '', '', "<select name=\"hournew\"><option value=\"-1\">*</option>$hourselect</select>");
				showsetting('crons_edit_minute', 'minutenew', $cronminute, 'text');
				showsetting('crons_edit_filename', 'filenamenew', $cron['filename'], 'text');
				showsubmit('editsubmit');
				showtablefooter();
				showformfooter();

			} else {

				$daynew = $weekdaynew != -1 ? -1 : $daynew;
				if($minutenew == '') {
					$minutenew = '';
				} elseif(strpos($minutenew, ',') !== FALSE) {
					$minutenew = explode(',', $minutenew);
					foreach($minutenew as $key => $val) {
						$minutenew[$key] = $val = intval($val);
						if($val < 0 || $var > 59) {
							unset($minutenew[$key]);
						}
					}
					$minutenew = array_slice(array_unique($minutenew), 0, 12);
					$minutenew = implode("\t", $minutenew);
				} else {
					$minutenew = intval($minutenew);
					$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
				}

				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $filenamenew)) {
					cpmsg('crons_filename_illegal', '', 'error');
				} elseif(!is_readable(DISCUZ_ROOT.($cronfile = "./include/crons/$filenamenew"))) {
					cpmsg('crons_filename_invalid', '', 'error');
				} elseif($weekdaynew == -1 && $daynew == -1 && $hournew == -1 && $minutenew == '') {
					cpmsg('crons_time_invalid', '', 'error');
				}

				$db->query("UPDATE {$tablepre}crons SET weekday='$weekdaynew', day='$daynew', hour='$hournew', minute='$minutenew', filename='".trim($filenamenew)."' WHERE cronid='$cronid'");

				updatecache('crons');

				require_once DISCUZ_ROOT.'./include/cron.func.php';
				cronnextrun($cron);

				cpmsg('crons_succeed', 'admincp.php?action=misc&operation=crons', 'succeed');

			}

		} else {

			if(!@include_once DISCUZ_ROOT.($cronfile = "./include/crons/$cron[filename]")) {
				cpmsg('crons_run_invalid', '', 'error');
			} else {
				require_once DISCUZ_ROOT.'./include/cron.func.php';
				cronnextrun($cron);
				cpmsg('crons_run_succeed', 'admincp.php?action=misc&operation=crons', 'succeed');
			}

		}

	}

} elseif($operation == 'tags') {

	if(!$tagstatus) {
		cpmsg('tags_not_open', "admincp.php?action=settings&operation=functions#subtitle_tags");
	}

	if(submitcheck('tagsubmit') && !empty($tag)) {
		$tagdelete = $tagclose = $tagopen = array();
		foreach($tag as $key => $value) {
			if($value == -1) {
				$tagdelete[] = $key;
			} elseif($value == 1) {
				$tagclose[] = $key;
			} elseif($value == 0) {
				$tagopen[] = $key;
			}
		}

		if($tagdelete) {
			$db->query("DELETE FROM {$tablepre}tags WHERE tagname IN (".implodeids($tagdelete).")", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threadtags WHERE tagname IN (".implodeids($tagdelete).")", 'UNBUFFERED');
		}

		if($tagclose) {
			$db->query("UPDATE {$tablepre}tags SET closed=1 WHERE tagname IN (".implodeids($tagclose).")", 'UNBUFFERED');
		}

		if($tagopen) {
			$db->query("UPDATE {$tablepre}tags SET closed=0 WHERE tagname IN (".implodeids($tagopen).")", 'UNBUFFERED');
		}

		if($tagdelete || $tagclose || $tagopen) {
			updatecache(array('tags_index', 'tags_viewthread'));
		}

		cpmsg('tags_updated', 'admincp.php?action=misc&operation=tags&tagsearchsubmit=yes&tagname='.rawurlencode($tagname).'&threadnumlower='.intval($threadnumlower).'&threadnumhigher='.intval($threadnumhigher).'&status='.intval($status), 'succeed');

	}

	shownav('topic', 'nav_posting_tags');
	showsubmenu('nav_posting_tags');

	if(!submitcheck('tagsearchsubmit', 1)) {

		$tagcount[0] = $db->result_first("SELECT count(*) FROM {$tablepre}tags");
		$tagcount[1] = $db->result_first("SELECT count(*) FROM {$tablepre}tags WHERE closed=1");
		$tagcount[2] = $tagcount[0] - $tagcount[1];

		include DISCUZ_ROOT.'./forumdata/cache/cache_index.php';
		showtableheader('tags_hot', 'nobottom fixpadding');
		showtablerow('', '', $_DCACHE['tags']);
		showtablefooter();

		showformheader('misc&operation=tags');
		showtableheader('tags_search', 'notop');
		showsetting('tags_tag', 'tagname', '', 'text'); 
		showsetting('tags_threadnum_between', array('threadnumhigher', 'threadnumlower'), array(), 'range');
		showsetting('tags_status', array( 'status', array(
			array(-1, lang('all')."($tagcount[0])"),
			array(0, lang('tags_status_1')."($tagcount[1])"),
			array(1, lang('tags_status_0')."($tagcount[2])"),
		), TRUE), -1, 'mradio');
		showsubmit('tagsearchsubmit', 'tags_search');
		showtablefooter();
		showformfooter();

	} else {

		$tagpp = 100;
		$page = max(1, intval($page));

		$threadnumlower = !empty($threadnumlower) ? intval($threadnumlower) : '';
		$threadnumhigher = !empty($threadnumhigher) ? intval($threadnumhigher) : '';

		$sqladd = $tagname ? "tagname LIKE '%".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $tagname)."%'" : '1';
		$sqladd .= $threadnumlower ? " AND total<'".intval($threadnumlower)."'" : '';
		$sqladd .= $threadnumhigher ? " AND total>'".intval($threadnumhigher)."'" : '';
		$sqladd .= $status != -1 ? " AND closed='".intval($status)."'" : '';

		$pagetmp = $page;

		$num = $db->result_first("SELECT count(*) FROM {$tablepre}tags WHERE $sqladd");
		$multipage = multi($num, $tagpp, $page, 'admincp.php?action=misc&operation=tags&tagsearchsubmit=yes&tagname='.rawurlencode($tagname).'&threadnumlower='.intval($threadnumlower).'&threadnumhigher='.intval($threadnumhigher).'&status='.intval($status));

		do {
			$query = $db->query("SELECT * FROM {$tablepre}tags WHERE $sqladd ORDER BY total DESC LIMIT ".(($pagetmp - 1) * $tagpp).", $tagpp");
			$pagetmp--;
		} while(!$db->num_rows($query) && $pagetmp);

		showformheader('misc&operation=tags&page='.$page);
		showhiddenfields(array(
			'tagname' => $tagname,
			'threadnumlower' => $threadnumlower,
			'threadnumhigher' => $threadnumhigher,
			'tagname' => $tagname,
			'status' => $status,
		));
		showtableheader('nav_posting_tags', 'fixpadding');
		showtablerow('', array('class="td21"', 'class="td25"'), array(
			lang('tags_tag'),
			lang('tags_threadnum'),
			''
		));

		while($tag = $db->fetch_array($query)) {
			showtablerow('', array('class="td21"', 'class="td25"'), array(
				'<a href="tag.php?name='.rawurlencode($tag['tagname']).'" target="_blank">'.$tag['tagname'].'</a>',
				$tag['total'],
				'<input name="tag['.$tag['tagname'].']" type="radio" class="radio" value="-1"> '.$lang['delete'].'&nbsp;<input name="tag['.$tag['tagname'].']" type="radio" class="radio" value="1"'.($tag['closed'] ? ' checked' : '').'> '.$lang['tags_status_1'].'&nbsp;<input name="tag['.$tag['tagname'].']" type="radio" class="radio" value="0"'.(!$tag['closed'] ? ' checked' : '').'> '.$lang['tags_status_0']
			));
		}

		showsubmit('tagsubmit', 'submit', '', '<a href="#" onclick="checkAll(\'option\', $(\'cpform\'), \'-1\')">'.lang('tags_all_delete').'</a> &nbsp;<a href="#" onclick="checkAll(\'option\', $(\'cpform\'), \'1\')">'.lang('tags_all_close').'</a> &nbsp;<a href="#" onclick="checkAll(\'option\', $(\'cpform\'), \'0\')">'.lang('tags_all_open').'</a>', $multipage);
		showtablefooter();
		showformfooter();

	}

} elseif($operation == 'custommenu') {

	if(!$do) {

		if(!submitcheck('optionsubmit')) {
			$page = max(1, intval($page));
			$mpp = 10;
			$startlimit = ($page - 1) * $mpp;
			$num = $db->result_first("SELECT count(*) FROM {$tablepre}admincustom WHERE uid='$discuz_uid' AND sort='1'");
			$multipage = $inajax ? multi($num, $mpp, $page, 'admincp.php?action=misc&operation=custommenu', 0, 3, TRUE, TRUE) :
				multi($num, $mpp, $page, 'admincp.php?action=misc&operation=custommenu');
			$optionlist = $ajaxoptionlist = '';
			$query = $db->query("SELECT id, title, displayorder, url FROM {$tablepre}admincustom WHERE uid='$discuz_uid' AND sort='1' ORDER BY displayorder, dateline DESC, clicks DESC LIMIT $startlimit, $mpp");
			while($custom = $db->fetch_array($query)) {
				$optionlist .= showtablerow('', array('class="td25"', 'class="td28"', '', 'class="td26"'), array(
					"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$custom[id]\">",
					"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayordernew[$custom[id]]\" value=\"$custom[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"25\" name=\"titlenew[$custom[id]]\" value=\"".lang($custom['title'])."\"><input type=\"hidden\" name=\"langnew[$custom[id]]\" value=\"$custom[title]\">",
					"<input type=\"text\" class=\"txt\" size=\"40\" name=\"urlnew[$custom[id]]\" value=\"$custom[url]\">"
				), TRUE);
				$ajaxoptionlist .= '<li><a href="'.$custom['url'].'" target="'.(substr($custom['url'], 0, 19) == 'admincp.php?action=' ? 'main' : '_blank').'">'.lang($custom['title']).'</a></li>';
			}

			if($inajax) {
				ajaxshowheader();
				echo $ajaxoptionlist.'<li>'.$multipage.'</li><script reload="1">initCpMenus(\'custommenu\');parent.cmcache=true;</script>';
				ajaxshowfooter();
				exit;
			}

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'<input type="text" class="txt" name="newtitle[]" size="25">'],
			[1,'<input type="text" class="txt" name="newurl[]" size="40">', 'td26']
		]
	];
</script>
EOT;
			shownav('misc', 'nav_custommenu');
			showsubmenu('nav_custommenu');
			showformheader('misc&operation=custommenu');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'URL'));
			echo $optionlist;
			echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['custommenu_add'].'</a></div></td></tr>';
			showsubmit('optionsubmit', 'submit', 'del', '', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM {$tablepre}admincustom WHERE id IN ($ids) AND uid='$discuz_uid'");
			}

			if(is_array($titlenew)) {
				foreach($titlenew as $id => $title) {
					$title = dhtmlspecialchars($langnew[$id] && lang($langnew[$id], false) ? $langnew[$id] : $title);
					$db->query("UPDATE {$tablepre}admincustom SET title='$title', displayorder='$displayordernew[$id]', url='".dhtmlspecialchars($urlnew[$id])."' WHERE id='$id'");
				}
			}

			if(is_array($newtitle)) {
				foreach($newtitle as $k => $v) {
					$db->query("INSERT INTO {$tablepre}admincustom (title, displayorder, url, sort, uid) VALUES ('".dhtmlspecialchars($v)."', '".intval($newdisplayorder[$k])."', '".dhtmlspecialchars($newurl[$k])."', '1', '$discuz_uid')");
				}
			}

			cpmsg('custommenu_edit_succeed', 'admincp.php?action=misc&operation=custommenu', 'succeed', '<script type="text/JavaScript">parent.cmcache=false;</script>');

		}

	} elseif($do == 'add') {

		if($title && $url) {
			admincustom($title, dhtmlspecialchars($url), 1);
			cpmsg('custommenu_add_succeed', 'admincp.php?'.$url, 'succeed', '<script type="text/JavaScript">parent.cmcache=false;</script>');
		} else {
			cpmsg('undefined_action', '', 'error');
		}

	} elseif($do == 'clean') {

		if(!$confirmed) {
			cpmsg('custommenu_history_delete_confirm', "admincp.php?action=misc&operation=custommenu&do=clean", 'form');
		} else {
			$db->query("DELETE FROM {$tablepre}admincustom WHERE uid='$discuz_uid' AND sort='0'");
			cpmsg('custommenu_history_delete_succeed', '#', 'succeed', '<script type="text/JavaScript">setTimeout("parent.location.reload();", 2999);</script>');
		}

	} else {
		cpmsg('undefined_action');
	}

}

?>