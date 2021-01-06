<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: recyclebin.inc.php 13440 2008-04-16 01:10:37Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./include/post.func.php';
require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

cpheader();

if(!$operation) {

	if(!submitcheck('rbsubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect().'</select>';

		if($inforum) {
			$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}

		shownav('topic', 'nav_recyclebin');
		showsubmenu('nav_recyclebin', array(
			array('search', 'recyclebin', 1),
			array('clean', 'recyclebin&operation=clean', 0)
		));
		echo '<script type="text/javascript" src="include/javascript/calendar.js"></script>';
		showtagheader('div', 'threadsearch', !$searchsubmit);
		showformheader('recyclebin');
		showtableheader('recyclebin_search');
		showsetting('recyclebin_search_forum', '', '', $forumselect);
		showsetting('recyclebin_search_author', 'authors', $authors, 'text');
		showsetting('recyclebin_search_keyword', 'keywords', $keywords, 'text');
		showsetting('recyclebin_search_admin', 'admins', $admins, 'text');
		showsetting('recyclebin_search_post_time', array('pstarttime', 'pendtime'), array($pstarttime, $pendtime), 'daterange');
		showsetting('recyclebin_search_mod_time', array('mstarttime', 'mendtime'), array($mstarttime, $mendtime), 'daterange');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		if(submitcheck('searchsubmit')) {

			$sql = '';
			$sql .= $inforum		? " AND t.fid='$inforum'" : '';
			$sql .= $authors != ''		? " AND t.author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authors))."')" : '';
			$sql .= $admins != ''		? " AND tm.username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $admins))."')" : '';
			$sql .= $pstarttime != ''	? " AND t.dateline>='".(strtotime($pstarttime) - $timeoffset * 3600)."'" : '';
			$sql .= $pendtime != ''		? " AND t.dateline<'".(strtotime($pendtime) - $timeoffset * 3600)."'" : '';
			$sql .= $mstarttime != ''	? " AND tm.dateline>='".(strtotime($mstarttime) - $timeoffset * 3600)."'" : '';
			$sql .= $mendtime != ''		? " AND tm.dateline<'".(strtotime($mendtime) - $timeoffset * 3600)."'" : '';

			if(trim($keywords)) {
				$sqlkeywords = $or = '';
				foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
					$sqlkeywords .= " $or t.subject LIKE '%$keyword%'";
					$or = 'OR';
				}
				$sql .= " AND ($sqlkeywords)";
			}

			$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
				t.tid, t.fid, t.authorid, t.author, t.subject, t.views, t.replies, t.dateline,
				p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff,
				tm.uid AS moduid, tm.username AS modusername, tm.dateline AS moddateline, tm.action AS modaction
				FROM {$tablepre}threads t
				LEFT JOIN {$tablepre}posts p ON p.tid=t.tid AND p.first='1'
				LEFT JOIN {$tablepre}threadsmod tm ON tm.tid=t.tid
				LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
				WHERE t.displayorder='-1' $sql
				GROUP BY t.tid ORDER BY t.dateline DESC");

			$threadcount = $db->num_rows($query);

			echo '<script type="text/JavaScript">function attachimg() {}</script>';
			showtagheader('div', 'threadlist', $searchsubmit);
			showformheader('recyclebin', '', 'rbform');
			showtableheader(lang('recyclebin_result').' '.$threadcount.' <a href="#" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';" class="act lightlink normal">'.lang('research').'</a>', 'fixpadding');

			while($thread = $db->fetch_array($query)) {
				$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], sprintf('%00b', $thread['htmlon']), $thread['allowsmilies'], $thread['allowbbcode'], $thread['allowimgcode'], $thread['allowhtml']);
				$thread['moddateline'] = gmdate("$dateformat $timeformat", $thread['moddateline'] + $timeoffset * 3600);
				$thread['dateline'] = gmdate("$dateformat $timeformat", $thread['dateline'] + $timeoffset * 3600);

				if($thread['attachment']) {
					require_once DISCUZ_ROOT.'./include/attachment.func.php';
					$queryattach = $db->query("SELECT aid, filename, filetype, filesize FROM {$tablepre}attachments WHERE tid='$thread[tid]'");
					while($attach = $db->fetch_array($queryattach)) {
						$thread['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype'])." $attach[filename] (".sizecount($attach['filesize']).")";
					}
				}

				showtablerow("id=\"mod_$thread[tid]_row1\"", array('rowspan="3" class="rowform threadopt" style="width:80px;"', 'class="threadtitle"'), array(
					"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"delete\" checked=\"checked\" /><label for=\"mod_$thread[tid]_1\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"undelete\" /><label for=\"mod_$thread[tid]_2\">$lang[undelete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" /><label for=\"mod_$thread[tid]_3\">$lang[ignore]</label></li></ul>",
					"<h3><a href=\"forumdisplay.php?fid=$thread[fid]\" target=\"_blank\">$thread[forumname]</a> &raquo; $thread[subject]</h3><p><span class=\"bold\">$lang[author]:</span> <a href=\"space.php?uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a> &nbsp;&nbsp; <span class=\"bold\">$lang[time]:</span> $thread[dateline] &nbsp;&nbsp; $lang[threads_replies]: $thread[replies] $lang[threads_views]: $thread[views]</p>"
				));
				showtablerow("id=\"mod_$thread[tid]_row2\"", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$thread['message'].'</div>');
				showtablerow("id=\"mod_$thread[tid]_row3\"", 'class="threadopt threadtitle" colspan="2"', "$lang[operator]: <a href=\"space.php?uid=$thread[moduid]\" target=\"_blank\">$thread[modusername]</a> &nbsp;&nbsp; $lang[recyclebin_delete_time]: $thread[moddateline]");
			}

			showsubmit('rbsubmit', 'submit', '', '<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'delete\')">'.lang('recyclebin_all_delete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'undelete\')">'.lang('recyclebin_all_undelete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'ignore\')">'.lang('recyclebin_all_ignore').'</a> &nbsp;');
			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {

		$moderation = array('delete' => array(), 'undelete' => array(), 'ignore' => array());

		if(is_array($mod)) {
			foreach($mod as $tid => $action) {
				$moderation[$action][] = intval($tid);
			}
		}

		$threadsdel = deletethreads($moderation['delete']);
		$threadsundel = undeletethreads($moderation['undelete']);

		cpmsg('recyclebin_succeed', 'admincp.php?action=recyclebin&operation=', 'succeed');

	}

} elseif($operation == 'clean') {

	if(!submitcheck('rbsubmit')) {

		shownav('topic', 'nav_recyclebin');
		showsubmenu('nav_recyclebin', array(
			array('search', 'recyclebin', 0),
			array('clean', 'recyclebin&operation=clean', 1)
		));
		showformheader('recyclebin&operation=clean');
		showtableheader('recyclebin_prune');
		showsetting('recyclebin_prune_days', 'days', '30', 'text');
		showsubmit('rbsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$deletetids = array();
		$query = $db->query("SELECT tm.tid FROM {$tablepre}threadsmod tm, {$tablepre}threads t
			WHERE tm.dateline<$timestamp-'$days'*86400 AND tm.action='DEL' AND t.tid=tm.tid AND t.displayorder='-1'");
		while($thread = $db->fetch_array($query)) {
			$deletetids[] = $thread['tid'];
		}
		$threadsdel = deletethreads($deletetids);
		$threadsundel = 0;
		cpmsg('recyclebin_succeed', 'admincp.php?action=recyclebin&operation=clean', 'succeed');

	}
}

function deletethreads($tids = array()) {
	global $db, $tablepre, $losslessdel, $creditspolicy;
	$threadsdel = 0;
	if($tids && is_array($tids)) {
		$tids = '\''.implode('\',\'', $tids).'\'';
		$auidarray = array();
		$query = $db->query("SELECT uid, attachment, dateline, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($tids)");
		while($attach = $db->fetch_array($query)) {
			dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			if($attach['dateline'] > $losslessdel) {
				$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
			}
		}
		if($auidarray) {
			updateattachcredits('-', $auidarray, $creditspolicy['postattach']);
		}

		$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}polloptions WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}trades WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($tids)");
		$threadsdel = $db->affected_rows();
	}
	return $threadsdel;
}

function undeletethreads($tids) {
	global $db, $tablepre, $creditspolicy;
	$threadsundel = 0;
	if($tids && is_array($tids)) {
		$tids = '\''.implode('\',\'', $tids).'\'';

		$tuidarray = $ruidarray = $fidarray = array();
		$query = $db->query("SELECT fid, first, authorid FROM {$tablepre}posts WHERE tid IN ($tids)");
		while($post = $db->fetch_array($query)) {
			if($post['first']) {
				$tuidarray[] = $post['authorid'];
			} else {
				$ruidarray[] = $post['authorid'];
			}
			if(!in_array($post['fid'], $fidarray)) {
				$fidarray[] = $post['fid'];
			}
		}
		if($tuidarray) {
			updatepostcredits('+', $tuidarray, $creditspolicy['post']);
		}
		if($ruidarray) {
			updatepostcredits('+', $ruidarray, $creditspolicy['reply']);
		}

		$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE tid IN ($tids)", 'UNBUFFERED');
		$db->query("UPDATE {$tablepre}threads SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
		$threadsundel = $db->affected_rows();

		updatemodlog($tids, 'UDL');
		updatemodworks('UDL', $threadsundel);

		foreach($fidarray as $fid) {
			updateforumcount($fid);
		}
	}
	return $threadsundel;
}

?>