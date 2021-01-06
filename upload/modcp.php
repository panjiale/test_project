<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: modcp.php 13239 2008-04-02 05:18:35Z cnteacher $
*/

define('NOROBOT', TRUE);
define('IN_MODCP', true);
define('CURSCRIPT', 'modcp');

$action = !empty($_REQUEST['action']) ? $_GET['action'] : (!empty($_POST['action']) ? $_POST['action'] : '');

if($action == 'editsubject' || $action == 'editmessage') {
	define('SQL_ADD_THREAD', 't.subject, t.authorid, t.digest, ');
	require_once './include/common.inc.php';
	require_once './modcp/editpost.inc.php';
	exit();
} else {
	require_once './include/common.inc.php';
	require_once './admin/cpanel.share.php';
}

$cpscript = basename($PHP_SELF);
$modsession = new AdminSession($discuz_uid, $groupid, $adminid, $onlineip);

if($modsession->cpaccess == 1) {
	if($action == 'login' && $cppwd && submitcheck('submit')) {
		require_once DISCUZ_ROOT.'./uc_client/client.php';
		$ucresult = uc_user_login($discuz_uid, $cppwd, 1);
		if($ucresult[0] > 0) {
			$modsession->errorcount = '-1';
			$url_forward = $modsession->get('url_forward');
			$modsession->clear(true);
			$url_forward && dheader("Location: $cpscript?$url_forward");
			$action = 'home';
		} else{
			$modsession->errorcount ++;
			$modsession->update();
		}
	} else {
		$action = 'login';
	}
}

if($action == 'logout') {
	$modsession->destroy();
	showmessage('modcp_logout_successed', $indexname);
}

$modforums = $modsession->get('modforums');
if($modforums === null) {
	$modforums = array('fids' => '', 'list' => array(), 'recyclebins' => array());
	$comma = '';
	if($adminid == 3) {
		$query = $db->query("SELECT m.fid, f.name, f.recyclebin
				FROM {$tablepre}moderators m
				LEFT JOIN {$tablepre}forums f ON f.fid=m.fid
				WHERE m.uid='$discuz_uid' AND f.status>0 AND f.type<>'group'");
		while($forum = $db->fetch_array($query)) {
			$modforums['fids'] .= $comma.$forum['fid']; $comma = ',';
			$modforums['recyclebins'][$forum['fid']] = $forum['recyclebin'];
			$modforums['list'][$forum['fid']] = strip_tags($forum['name']);
		}
	} else {
		require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
		foreach($_DCACHE['forums'] as $temp => $forum) {
			if($forum['type'] != 'group' && (!$forum['viewperm'] && $readaccess) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				$modforums['fids'] .= $comma.$forum['fid']; $comma = ',';
				$modforums['recyclebins'][$forum['fid']] = $forum['recyclebin'];
				$modforums['list'][$forum['fid']] = strip_tags($forum['name']);
			}
		}
	}
	$modsession->set('modforums', $modforums, true);
}

if($fid && $forum['ismoderator']) {
	$forcefid = "&amp;fid=$fid";
} elseif(!empty($modforums) && count($modforums['list']) == 1) {
	$forcefid = "&amp;fid=$modforums[fids]";
} else {
	$forcefid = '';
}

$script = $modtpl = '';

switch ($action) {

	case 'announcements':
		$allowpostannounce && $script = 'announcements';
		break;

	case 'members':
		$op == 'edit' && $allowedituser && $script = 'members';
		$op == 'ban' && $allowbanuser && $script = 'members';
		$op == 'ipban' && $allowbanip && $script = 'members';
		break;

	case 'report':
		$script = 'report';
		break;

	case 'moderate':
		$allowmodpost && $script = 'moderate';
		break;

	case 'forums':
		$script = 'forums';
		break;

	case 'forumaccess':
		$script = 'forumaccess';
		break;

	case 'logs':
		$script = 'logs';
		break;

	case 'login':
		$script = $modsession->cpaccess == 1 ? 'login' : 'home';
		break;

	default:
		$action = $script = 'home';
		$modtpl = 'modcp_home';
}

$script = empty($script) ? 'noperm' : $script;
$modtpl = empty($modtpl) ? (!empty($script) ? 'modcp_'.$script : '') : $modtpl;
$op = isset($op) ? trim($op) : '';

if($script != 'logs') {
	$extra = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formhash', 'submit', 'addsubmit'));
	$modcplog = array($timestamp, $discuz_user, $adminid, $onlineip, $action, $op, $fid, $extra);
	writelog('modcp', implode("\t", clearlogstring($modcplog)));
}

require DISCUZ_ROOT.'./modcp/'.$script.'.inc.php';
include template('modcp');

?>