<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/app/source/activity/__init.php : v fac2b7b1f566 : 2015/09/08 07:11:59 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');
checkauth();
load()->model('activity');
load()->model('mc');
load()->classs('coupon');
$coupon_api = new coupon();
//获取公众号积分策略
$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames', 'coupon_type', 'exchange_enable'));
if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
	foreach ($unisettings['creditnames'] as $key=>$credit) {
		$creditnames[$key] = $credit['title'];
	}
}
/*获取当前公众号是否开启会员卡*/
$cardstatus = pdo_get('mc_card', array('uniacid' => $_W['uniacid']), array('status'));
$type_names = activity_coupon_type_label();
