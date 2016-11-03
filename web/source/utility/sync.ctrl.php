<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/utility/sync.ctrl.php : v de640d7236ab : 2015/06/13 02:39:02 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');

if(!empty($_W['uniacid'])) {
	load()->model('account');
	load()->model('mc');
	$setting = uni_setting($_W['uniacid'], 'sync');
	$sync = $setting['sync'];
	if($sync != 1) {
		exit();
	}
	// 这里仅取了10条数据，进行初始化
	if($_W['account']['type'] == 1 && $_W['account']['level'] >= 3) {
		$data = pdo_fetchall('SELECT fanid, openid, acid, uid, uniacid FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid AND acid = :acid AND follow = '1' ORDER BY fanid DESC LIMIT 10", array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
		if(!empty($data)) {
			foreach($data as $row) {
				mc_init_fans_info($row);
			}
		}
	}
}