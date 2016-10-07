<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
if ($action != 'cash') {
	checkauth();
}
load()->model('activity');
$filter = array();
$coupons = activity_coupon_owned();
$setting = uni_setting($_W['uniacid'], array('creditnames', 'creditbehaviors', 'uc', 'payment', 'passport'));
$behavior = $setting['creditbehaviors'];
$creditnames = $setting['creditnames'];
$credits = mc_credit_fetch($_W['member']['uid'], '*');

/*获取当前公众号是否开启会员卡*/
$sql = 'SELECT `status` FROM ' . tablename('mc_card') . " WHERE `uniacid` = :uniacid";
$cardstatus = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));

$ucpage = pdo_fetch("SELECT * FROM ".tablename('site_page')." WHERE uniacid = :uniacid AND type = '3'", array(':uniacid' => $_W['uniacid']));
if (!empty($ucpage['params'])) {
	$ucpage['params'] = json_decode($ucpage['params'], true);
}
$title = $ucpage['title'];