<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: moderate.inc.php 12968 2008-03-19 08:51:22Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

if(empty($fid) || $forum['type'] == 'group' || !$forum['ismoderator'] ) {
	$fid = 0;
	return false;
}

$updatestat = false;

$op = !in_array($op , array('replies', 'threads')) ? 'replies' : $op;

$filter = !empty($filter) ? -3 : 0;
$filtercheck = array($filter => 'checked');

$pstat = $filter == -3 ? -3 : -2;

$tpp = 10;
$page = max(1, intval($page));
$start_limit = ($page - 1) * $tpp;

$postlist = array();

$modpost = array('validate' => 0, 'delete' => 0, 'ignore' => 0);
$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
if(submitcheck('modsubmit')) {

	require_once DISCUZ_ROOT.'./include/post.func.php';

	$updatestat = $op == 'replies' ? 1 : 2;

	if(is_array($mod)) {
		foreach($mod as $id => $act) {
			is_numeric($id) && isset($moderation[$act]) && $moderation[$act][] = intval($id);
		}
	}
	$modpost = array(
	'ignore' => count($moderation['ignore']),
	'delete' => count($moderation['delete']),
	'validate' => count($moderation['validate'])
	);
}

if($op == 'replies') {

	if(submitcheck('modsubmit')) {

		$pmlist = array();
		if($ignorepids = implodeids($moderation['ignore'])) {

			$db->query("UPDATE {$tablepre}posts SET invisible='-3' WHERE pid IN ($ignorepids) AND invisible='-2' AND first='0' AND fid='$fid'");
		}

		if($deletepids = implodeids($moderation['delete'])) {
			$query = $db->query("SELECT pid, authorid, tid, message FROM {$tablepre}posts WHERE pid IN ($deletepids) AND invisible='$pstat' AND first='0' AND fid='$fid'");
			$pids = '0';
			while($post = $db->fetch_array($query)) {
				$pids .= ','.$post['pid'];
				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
					'act' => 'modreplies_delete_',
					'authorid' => $post['authorid'],
					'tid' => $post['tid'],
					'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
					'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($pids) {
				$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE pid IN ($pids)");
				while($attach = $db->fetch_array($query)) {
					dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
				}
				$db->query("DELETE FROM {$tablepre}attachments WHERE pid IN ($pids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}posts WHERE pid IN ($pids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}trades WHERE pid IN ($pids)", 'UNBUFFERED');
			}
			updatemodworks('DLP', count($moderation['delete']));
		}

		$repliesmod = 0;
		if($validatepids = implodeids($moderation['validate'])) {

			$threads = $lastpost = $attachments = $pidarray = $authoridarray = array();
			$query = $db->query("SELECT t.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, p.message, p.anonymous, ff.replycredits
				FROM {$tablepre}posts p
				LEFT JOIN {$tablepre}forumfields ff ON ff.fid=p.fid
				LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
				WHERE p.pid IN ($validatepids) AND p.invisible='$pstat' AND p.first='0' AND p.fid='$fid'");

			while($post = $db->fetch_array($query)) {
				$repliesmod ++;
				$pidarray[] = $post['pid'];
				if($post['replycredits']) {
					updatepostcredits('+', $post['authorid'], unserialize($post['replycredits']));
				} else {
					$authoridarray[] = $post['authorid'];
				}

				$threads[$post['tid']]['posts']++;
				$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
				", lastpost='$post[dateline]', lastposter='".($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]))."'" : '';
				$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';

				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
					'act' => 'modreplies_validate_',
					'authorid' => $post['authorid'],
					'tid' => $post['tid'],
					'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
					'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($authoridarray) {
				updatepostcredits('+', $authoridarray, $creditspolicy['reply']);
			}

			foreach($threads as $tid => $thread) {
				$db->query("UPDATE {$tablepre}threads SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
			}

			updateforumcount($fid);

			if(!empty($pidarray)) {
				$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE pid IN (0,".implode(',', $pidarray).")");
				$repliesmod = $db->affected_rows();
				updatemodworks('MOD', $repliesmod);
			} else {
				updatemodworks('MOD', 1);
			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$post = $pm['post'];
				$tid = intval($pm['tid']);
				sendpm($pm['authorid'], $pm['act'].'subject', $pm['act'].'message');
			}
		}

	}

	$attachlist = array();

	require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
	require_once DISCUZ_ROOT.'./include/attachment.func.php';

	$ppp = 10;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $ppp;

	$modcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE invisible='$pstat' AND first='0' $fidadd[and]$fidadd[fids]");
	$multipage = multi($modcount, $ppp, $page, "admincp.php?action=modreplies&filter=$filter&fid=$fid");

	if($modcount) {
		$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
			p.pid, p.fid, p.tid, p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment,
			p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject
			FROM {$tablepre}posts p
			LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=p.fid
			WHERE p.invisible='$pstat' AND p.first='0' AND p.fid='$fid'
			ORDER BY p.dateline DESC LIMIT $start_limit, $ppp");

		while($post = $db->fetch_array($query)) {
			$post['id'] = $post['pid'];
			$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$post['message'] = nl2br(dhtmlspecialchars($post['message']));

			if($post['attachment']) {
				$queryattach = $db->query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM {$tablepre}attachments WHERE pid='$post[pid]'");
				while($attach = $db->fetch_array($queryattach)) {
					$attachurl = $attach['remote'] ? $ftp['attachurl'] : $attachurl;
					$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"$attachurl/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					: "<a href=\"$attachurl/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$post['message'] .= "<br /><br />File: ".attachtype(fileext($attach['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}
			$postlist[] = $post;
		}
	}


} else {

	if(submitcheck('modsubmit')) {

		if($ignoretids = implodeids($moderation['ignore'])) {
			$db->query("UPDATE {$tablepre}threads SET displayorder='-3' WHERE tid IN ($ignoretids) AND fid='$fid' AND displayorder='-2'");
		}

		$threadsmod = 0;
		$pmlist = array();

		if($ids = implodeids($moderation['delete'])) {
			$deletetids = '0';
			$recyclebintids = '0';
			$query = $db->query("SELECT tid, fid, authorid, subject FROM {$tablepre}threads WHERE tid IN ($ids) AND displayorder='$pstat' AND fid='$fid'");
			while($thread = $db->fetch_array($query)) {
				if($modforums['recyclebins'][$thread['fid']]) {
					$recyclebintids .= ','.$thread['tid'];
				} else {
					$deletetids .= ','.$thread['tid'];
				}
				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
					'act' => 'modthreads_delete_',
					'authorid' => $thread['authorid'],
					'thread' =>  $thread['subject'],
					'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($recyclebintids) {
				$db->query("UPDATE {$tablepre}threads SET displayorder='-1', moderated='1' WHERE tid IN ($recyclebintids)");
				updatemodworks('MOD', $db->affected_rows());

				$db->query("UPDATE {$tablepre}posts SET invisible='-1' WHERE tid IN ($recyclebintids)");
				updatemodlog($recyclebintids, 'DEL');
			}

			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($deletetids)");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			}

			$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}polloptions WHERE tid IN ($deletetids)");
			$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}trades WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($deletetids)", 'UNBUFFERED');
		}

		if($validatetids = implodeids($moderation['validate'])) {

			$tids = $comma = $comma2 = '';
			$authoridarray = $moderatedthread = array();
			$query = $db->query("SELECT t.fid, t.tid, t.authorid, t.subject, t.author, t.dateline FROM {$tablepre}threads t
				WHERE t.tid IN ($validatetids) AND t.displayorder='$pstat' AND t.fid='$fid'");
			while($thread = $db->fetch_array($query)) {
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				if($thread['postcredits']) {
					updatepostcredits('+', $thread['authorid'], $forum['postcredits']);
				} else {
					$authoridarray[] = $thread['authorid'];
				}

				$validatedthreads[] = $thread;

				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
					'act' => 'modthreads_validate_',
					'authorid' => $thread['authorid'],
					'tid' => $thread['tid'],
					'thread' => $thread['subject'],
					'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($tids) {

				if($authoridarray) {
					updatepostcredits('+', $authoridarray, $creditspolicy['post']);
				}

				$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE tid IN ($tids)");
				$db->query("UPDATE {$tablepre}threads SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
				$threadsmod = $db->affected_rows();

				updateforumcount($fid);
				updatemodworks('MOD', $threadsmod);
				updatemodlog($tids, 'MOD');

			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$threadsubject = $pm['thread'];
				$tid = intval($pm['tid']);
				sendpm($pm['authorid'], $pm['act'].'subject', $pm['act'].'message');
			}
		}

	}

	$modcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE fid='$fid' AND displayorder='$pstat'");
	$multipage = multi($modcount, $tpp, $page, "{$cpscript}?action=$action&filter=$filter&fid=$fid");

	if($modcount) {
		$query = $db->query("SELECT t.tid, t.fid, t.author, t.authorid, t.subject as tsubject, t.dateline, t.attachment,
			p.pid, p.message, p.useip, p.attachment
			FROM {$tablepre}threads t
			LEFT JOIN {$tablepre}posts p ON p.tid=t.tid AND p.first = 1
			WHERE t.fid='$fid' AND t.displayorder='$pstat'
			ORDER BY t.dateline DESC LIMIT $start_limit, $tpp");

		while($thread = $db->fetch_array($query)) {

			$thread['id'] = $thread['tid'];

			if($thread['authorid'] && $thread['author'] != '') {
				$thread['author'] = "<a href=\"space.php?uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>";
			} elseif($thread['authorid']) {
				$thread['author'] = "<a href=\"space.php?uid=$thread[authorid]\" target=\"_blank\">UID $thread[uid]</a>";
			} else {
				$thread['author'] = 'guest';
			}

			$thread['dateline'] = gmdate("$dateformat $timeformat", $thread['dateline'] + $timeoffset * 3600);

			$thread['message'] = nl2br(dhtmlspecialchars($thread['message']));

			if($thread['attachment']) {
				require_once DISCUZ_ROOT.'./include/attachment.func.php';

				$queryattach = $db->query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM {$tablepre}attachments WHERE tid='$thread[tid]'");
				while($attach = $db->fetch_array($queryattach)) {
					$attachurl = $attach['remote'] ? $ftp['attachurl'] : $attachurl;
					$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"$attachurl/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					: "<a href=\"$attachurl/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$thread['attach'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			} else {
				$thread['attach'] = '';
			}

			$postlist[] = $thread;
		}
	}

}