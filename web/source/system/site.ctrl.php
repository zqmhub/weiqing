<?php 
/**
 * 站点相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('copyright');
$do = in_array($do, $dos) ? $do : 'copyright';
$settings = $_W['setting']['copyright'];
if(empty($settings) || !is_array($settings)) {
	$settings = array();
} else {
	$settings['slides'] = iunserializer($settings['slides']);
}

if ($do == 'copyright') {
	$_W['page']['title'] = '站点信息设置 - 系统管理';
	if (checksubmit('submit')) {
		$data = array(
			'status' => $_GPC['status'],
			'verifycode' => $_GPC['verifycode'],
			'reason' => $_GPC['reason'],
		);
		setting_save($data, 'copyright');
		message('更新设置成功！', url('system/site'));
	}
}

template('system/site');