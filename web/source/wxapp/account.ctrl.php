<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
* $sn: pro/web/source/account/display.ctrl.php : v 3ab687fd5968 : 2015/09/17 03:04:00 : yanghf $
*/
defined('IN_IA') or exit('Access Denied');
$_W['page']['title'] = '小程序列表 - 帐号';

$dos = array('display');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'display') {
	$uniacid = 99998;
	$acid = 99999;
	$list = array();
	$list[$uniacid] = array(
		'uniacid' => $uniacid,
		'groupid' => 0,
		'name' => '小程序演示号',
		'description' => '',
		'type' => ACCOUNT_TYPE_APP_NORMAL,
		'isdeleted' => 0,
		'details' => array(
			$acid => array(
				'acid' => $acid,
				'uniacid' => $uniacid,
				'name' => '小程序演示号',
				'isconnect' => 1,
			),
		),
		'role' => 'founder',
		'setmeal' => array (
			'uid' => '-1',
			'username' => '创始人',
			'timelimit'=> '未设置',
			'groupid' => '-1',
			'groupname' => '所有服务',
		)
	);
	template('wxapp/account-display');
}
