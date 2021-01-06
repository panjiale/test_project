<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: pm.php 12976 2008-03-19 10:38:12Z monkey $
*/

define('CURSCRIPT', 'pm');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';

include_once DISCUZ_ROOT.'./uc_client/client.php';

if(isset($checknewpm)) {
	@dheader("Expires: 0");
	@dheader("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
	@dheader("Pragma: no-cache");
	$ucnewpm = uc_pm_checknew($discuz_uid);
	if($newpm != $ucnewpm) {
		$db->query("UPDATE {$tablepre}members SET newpm='$ucnewpm' WHERE uid='$discuz_uid'", 'UNBUFFERED');
	}
	setcookie('checkpm', 1, $timestamp + 30);
	include_once template('pm_checknew');
	exit;
}

if(!$discuz_uid) {
	showmessage('not_loggedin', NULL, 'HALTED');
}

$db->query("UPDATE {$tablepre}members SET newpm='0' WHERE uid='$discuz_uid'", 'UNBUFFERED');

if(empty($uid)) {
	setcookie('checkpm', '');
	uc_pm_location($discuz_uid, $newpm);
} else {
	$username = $db->result_first("SELECT username FROM {$tablepre}members WHERE uid='$uid'", 0);
	$subject = $message = '';
	$action = !empty($action) ? $action : '';

	if($action == 'trade' && ($tradepid = intval($pid))) {
		include_once language('misc');

		$tradepid = intval($tradepid);
		$trade = $db->fetch_first("SELECT * FROM {$tablepre}trades WHERE pid='$tradepid'");
		if($trade) {
			$subject = $language['post_trade_pm_subject'].$trade['subject'];
			$message = '[url='.$boardurl.'viewthread.php?do=tradeinfo&tid='.$trade['tid'].'&pid='.$tradepid.']'.$trade['subject']."[/url]\n";
			$message .= $trade['costprice'] ? $language['post_trade_costprice'].': '.$trade['costprice']."\n" : '';
			$message .= $language['post_trade_price'].': '.$trade['price']."\n";
			$message .= $language['post_trade_transport_type'].': ';
			if($trade['transport'] == 1) {
				$message .= $language['post_trade_transport_seller'];
			} elseif($trade['transport'] == 2) {
				$message .= $language['post_trade_transport_buyer'];
			} elseif($trade['transport'] == 3) {
				$message .= $language['post_trade_transport_virtual'];
			} elseif($trade['transport'] == 4) {
				$message .= $language['post_trade_transport_physical'];
			}
			if($trade['transport'] == 1 or $trade['transport'] == 2 or $trade['transport'] == 4) {
				if(!empty($trade['ordinaryfee'])) {
					$message .= ', '.$language['post_trade_transport_mail'].' '.$trade['ordinaryfee'].' '.$language['payment_unit'];
				}
				if(!empty($trade['expressfee'])) {
					$message .= ', '.$language['post_trade_transport_express'].' '.$trade['expressfee'].' '.$language['payment_unit'];
				}
				if(!empty($trade['emsfee'])) {
					$message .= ', EMS '.$trade['emsfee'].' '.$language['payment_unit'];
				}
			}
			$message .= "\n".$language['post_trade_locus'].': '.$trade['locus']."\n\n";
			$message .= $language['post_trade_pm_buynum'].": \n";
			$message .= $language['post_trade_pm_wishprice'].": \n";
			$message .= $language['post_trade_pm_reason'].": \n";
		}
	}
	uc_pm_send($discuz_uid, $username, $subject, $message, 0);
}

?>