<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: stats.inc.php 10920 2007-10-19 00:53:10Z monkey $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

$discuz_action = 194;

$members = $totalmembers;
@extract($db->fetch_first("SELECT SUM(threads) AS threads, SUM(posts) AS posts FROM {$tablepre}forums WHERE status>0"));

echo "<p>$lang[stats]<br /><br />\n".
	"$lang[stats_members]: $members<br />\n".
	"$lang[stats_threads]: $threads<br />\n".
	"$lang[stats_posts]: $posts</p>\n";

?>