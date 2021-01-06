<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: ecommerce.inc.php 13390 2008-04-11 15:25:52Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($operation == 'alipay') {

	$settings = array();
	$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable='ec_account'");
	while($setting = $db->fetch_array($query)) {
		$settings[$setting['variable']] = $setting['value'];
	}

	if(!submitcheck('alipaysubmit')) {


		if($from == 'creditwizard') {
			shownav('tools', 'nav_creditwizard');
			showsubmenu('nav_creditwizard', array(
				array('creditwizard_step_menu_1', 'creditwizard&step=1', 0),
				array('creditwizard_step_menu_2', 'creditwizard&step=2', 0),
				array('creditwizard_step_menu_3', 'creditwizard&step=3', 0),
				array('creditwizard_step_menu_4', 'settings&operation=ecommerce&from=creditwizard', 0),
				array('alipay', 'ecommerce&operation=alipay&from=creditwizard', 1),
			));
		} else {
			shownav('extended', 'nav_ec');
			showsubmenu('nav_ec', array(
				array('nav_ec_config', 'settings&operation=ecommerce', 0),
				array('nav_ec_alipay', 'ecommerce&operation=alipay', 1),
				array('nav_ec_credit', 'ecommerce&operation=ec_credit', 0),
				array('nav_ec_orders', 'ecommerce&operation=orders', 0),
				array('nav_ec_tradelog', 'tradelog', 0)
			));
		}

		showtips('alipay_tips');
		showformheader('ecommerce&operation=alipay');

		showtableheader('','nobottom');
		showtitle('alipay');
		showsetting('alipay_account', 'settingsnew[ec_account]', $settings['ec_account'], 'text');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('alipaysubmit');
		showtablefooter();
		showformfooter();

	} else {

		$db->query("UPDATE {$tablepre}settings SET value='$settingsnew[ec_account]' WHERE variable='ec_account'");
		updatecache('settings');

		cpmsg('alipay_succeed', 'admincp.php?action=ecommerce&operation=alipay', 'succeed');

	}

} elseif($operation == 'orders') {

	if(!$creditstrans || !$ec_ratio) {
		cpmsg('orders_disabled', '', 'error');
	}

	if(!submitcheck('ordersubmit')) {

		echo '<script type="text/javascript" src="include/javascript/calendar.js"></script>';
		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'settings&operation=ecommerce', 0),
			array('nav_ec_alipay', 'ecommerce&operation=alipay', 0),
			array('nav_ec_credit', 'ecommerce&operation=ec_credit', 0),
			array('nav_ec_orders', 'ecommerce&operation=orders', 1),
			array('nav_ec_tradelog', 'tradelog', 0)
		));
		showtips('orders_tips');
		showtagheader('div', 'ordersearch', !submitcheck('searchsubmit', 1));
		showformheader('ecommerce&operation=orders');
		showtableheader('orders_search');
		showsetting('orders_search_status', array('orderstatus', array(
			array('', $lang['orders_search_status_all']),
			array(1, $lang['orders_search_status_pending']),
			array(2, $lang['orders_search_status_auto_finished']),
			array(3, $lang['orders_search_status_manual_finished'])
		)), intval($orderstatus), 'select');
		showsetting('orders_search_id', 'orderid', $orderid, 'text');
		showsetting('orders_search_users', 'users', $users, 'text');
		showsetting('orders_search_buyer', 'buyer', $buyer, 'text');
		showsetting('orders_search_admin', 'admin', $admin, 'text');
		showsetting('orders_search_submit_date', array('sstarttime', 'sendtime'), array($sstarttime, $sendtime), 'daterange');
		showsetting('orders_search_confirm_date', array('cstarttime', 'cendtime'), array($cstarttime, $cendtime), 'daterange');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		if(submitcheck('searchsubmit', 1)) {

			$tpp = 1;
			$page = max(1, intval($page));
			$start_limit = ($page - 1) * $tpp;

			$sql = '';
			$sql .= $orderstatus != ''	? " AND o.status='$orderstatus'" : '';
			$sql .= $orderid != ''		? " AND o.orderid='$orderid'" : '';
			$sql .= $users != ''		? " AND m.username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')" : '';
			$sql .= $buyer != ''		? " AND o.buyer='$buyer'" : '';
			$sql .= $admin != ''		? " AND o.admin='$admin'" : '';
			$sql .= $sstarttime != ''	? " AND o.submitdate>='".(strtotime($sstarttime) - $timeoffset * 3600)."'" : '';
			$sql .= $sendtime != ''		? " AND o.submitdate<'".(strtotime($sendtime) - $timeoffset * 3600)."'" : '';
			$sql .= $cstarttime != ''	? " AND o.confirmdate>='".(strtotime($cstarttime) - $timeoffset * 3600)."'" : '';
			$sql .= $cendtime != ''		? " AND o.confirmdate<'".(strtotime($cendtime) - $timeoffset * 3600)."'" : '';

			$ordercount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}orders o, {$tablepre}members m WHERE m.uid=o.uid $sql");
			$multipage = multi($ordercount, $tpp, $page, "admincp.php?action=ecommerce&operation=orders&searchsubmit=yes&orderstatus=$orderstatus&orderid=$orderid&users=$users&buyer=$buyer&admin=$admin&sstarttime=$sstarttime&sendtime=$sendtime&cstarttime=$cstarttime&cendtime=$cendtime");

			showtagheader('div', 'orderlist', TRUE);
			showformheader('ecommerce&operation=orders');
			showtableheader('result');
			showsubtitle(array('', 'orders_id', 'orders_status', 'orders_username', 'orders_buyer', 'orders_amount', 'orders_price', 'orders_submitdate', 'orders_confirmdate'));

			$query = $db->query("SELECT o.*, m.username
				FROM {$tablepre}orders o, {$tablepre}members m
				WHERE m.uid=o.uid $sql ORDER BY o.submitdate DESC
				LIMIT $start_limit, $tpp");

			while($order = $db->fetch_array($query)) {
				switch($order['status']) {
					case 1: $order['orderstatus'] = $lang['orders_search_status_pending']; break;
					case 2: $order['orderstatus'] = '<b>'.$lang['orders_search_status_auto_finished'].'</b>'; break;
					case 3: $order['orderstatus'] = '<b>'.$lang['orders_search_status_manual_finished'].'</b><br />(<a href="space.php?username='.rawurlencode($order['admin']).'" target="_blank">'.$order['admin'].'</a>)'; break;
				}
				$order['submitdate'] = gmdate("$dateformat $timeformat", $order['submitdate'] + $timeoffset * 3600);
				$order['confirmdate'] = $order['confirmdate'] ? gmdate("$dateformat $timeformat", $order['confirmdate'] + $timeoffset * 3600) : 'N/A';

				showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"validate[]\" value=\"$order[orderid]\" ".($order['status'] != 1 ? 'disabled' : '').">",
					$order[orderid],
					$order[orderstatus],
					"<a href=\"space.php?uid=$order[uid]\" target=\"_blank\">$order[username]</a>",
					"<a href=\"mailto:$order[buyer]\">$order[buyer]</a>",
					"{$extcredits[$creditstrans]['title']} $order[amount] {$extcredits[$creditstrans]['unit']}",
					"$lang[rmb] $order[price] $lang[rmb_yuan]",
					$order[submitdate],
					$order[confirmdate]
				));
			}

			showsubmit('ordersubmit', 'submit', 'del', '<a href="#" onclick="$(\'orderlist\').style.display=\'none\';$(\'ordersearch\').style.display=\'\';">'.lang('research').'</a>', $multipage);
			showtablefooter();
			showformfooter();
			showtagfooter('div');
		}

	} else {

		$numvalidate = 0;
		if($validate) {
			$orderids = $comma = '';
			$confirmdate = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);

			$query = $db->query("SELECT * FROM {$tablepre}orders WHERE orderid IN ('".implode('\',\'', $validate)."') AND status='1'");
			while($order = $db->fetch_array($query)) {
				$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+'$order[amount]' WHERE uid='$order[uid]'");
				$orderids .= "$comma'$order[orderid]'";
				$comma = ',';

				$submitdate = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $order['submitdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
				sendpm($order['uid'], 'addfunds_subject', 'addfunds_message', $fromid = '0', $from = 'System Message');
			}
			if($numvalidate = $db->num_rows($query)) {
				$db->query("UPDATE {$tablepre}orders SET status='3', admin='$discuz_user', confirmdate='$timestamp' WHERE orderid IN ($orderids)");
			}
		}

		cpmsg('orders_validate_succeed', "admincp.php?action=ecommerce&operation=orders&searchsubmit=yes&orderstatus=$orderstatus&orderid=$orderid&users=$users&buyer=$buyer&admin=$admin&sstarttime=$sstarttime&sendtime=$sendtime&cstarttime=$cstarttime&cendtime=$cendtime", 'succeed');

	}

} elseif($operation == 'ec_credit') {

	$defaultrank = array(
		1 => 4,
		2 => 11,
		3 => 41,
		4 => 91,
		5 => 151,
		6 => 251,
		7 => 501,
		8 => 1001,
		9 => 2001,
		10 => 5001,
		11 => 10001,
		12 => 20001,
		13 => 50001,
		14 => 100001,
		15 => 200001
	);

	if(!submitcheck('creditsubmit')) {

		$ec_credit = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='ec_credit'");
		$ec_credit = $ec_credit ? unserialize($ec_credit) : array(
			'maxcreditspermonth' => '6',
			'rank' => $defaultrank
		);

		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'settings&operation=ecommerce', 0),
			array('nav_ec_alipay', 'ecommerce&operation=alipay', 0),
			array('nav_ec_credit', 'ecommerce&operation=ec_credit', 1),
			array('nav_ec_orders', 'ecommerce&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog', 0)
		));

		showtips('ec_credit_tips');
		showformheader('ecommerce&operation=ec_credit');
		showtableheader('ec_credit', 'nobottom');
		showsetting('ec_credit_maxcreditspermonth', 'ec_creditnew[maxcreditspermonth]', $ec_credit['maxcreditspermonth'], 'text');
		showtablefooter('</tbody>');

		showtableheader('ec_credit_rank', 'notop fixpadding');
		showsubtitle(array('ec_credit_rank', 'ec_credit_mincredits', 'ec_credit_maxcredits', 'ec_credit_sellericon', 'ec_credit_buyericon'));

		foreach($ec_credit['rank'] as $rank => $mincredits) {
			showtablerow('', '', array(
				$rank,
				'<input type="text" class="txt" name="ec_creditnew[rank]['.$rank.']" value="'.$mincredits.'" />',
				$ec_credit[rank][$rank + 1],
				"<img src=\"images/rank/seller/$rank.gif\" border=\"0\">",
				"<img src=\"images/rank/buyer/$rank.gif\" border=\"0\">"
			));
		}
		showsubmit('creditsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$ec_creditnew['maxcreditspermonth'] = intval($ec_creditnew['maxcreditspermonth']);

		if(is_array($ec_creditnew['rank'])) {
			foreach($ec_creditnew['rank'] as $rank => $mincredits) {
				$mincredits = intval($mincredits);
				if($rank == 1 && $mincredits <= 0) {
					cpmsg('ecommerce_invalidcredit', '', 'error');
				} elseif($rank > 1 && $mincredits <= $ec_creditnew['rank'][$rank - 1]) {
					cpmsg('ecommerce_must_larger', '', 'error');
				}
				$ec_creditnew['rank'][$rank] = $mincredits;
			}
		} else {
			$ec_creditnew['rank'] = $defaultrank;
		}

		$db->query("UPDATE {$tablepre}settings SET value='".serialize($ec_creditnew)."' WHERE variable='ec_credit'");
		updatecache('settings');

		cpmsg('ec_credit_succeed', 'admincp.php?action=ecommerce&operation=ec_credit', 'succeed');

	}
}

?>