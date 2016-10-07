<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/profile/jsauth.ctrl.php : v e030dfc0c020 : 2015/07/18 08:43:21 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('profile_jsauth');
//获取所有的认证服务号
$_W['page']['title'] = '功能选项 - 公众号选项 - 借用js分享权限';

$where = '';
$params = array();
if(empty($_W['isfounder'])) {
	$where = " WHERE `uniacid` IN (SELECT `uniacid` FROM " . tablename('uni_account_users') . " WHERE `uid`=:uid)";
	$params[':uid'] = $_W['uid'];
}
$sql = "SELECT * FROM " . tablename('uni_account') . $where;
$uniaccounts = pdo_fetchall($sql, $params);

$accounts = array();
if(!empty($uniaccounts)) {
	foreach($uniaccounts as $uniaccount) {
		$accountlist = uni_accounts($uniaccount['uniacid']);
		if(!empty($accountlist)) {
			foreach($accountlist as $account) {
				if(!empty($account['key']) 
				&& !empty($account['secret']) 
				&& in_array($account['level'], array(3, 4))) {
					$accounts[$account['acid']] = $account['name'];
				}
			}
		}
	}
}

//获取已保存的 jsauth_acid 信息
if(checksubmit('submit')) {
	$jsauth_acid = intval($_GPC['jsauth_acid']);
	if ($jsauth_acid == 0) {
	} elseif(!array_key_exists($jsauth_acid, $accounts)){
		message('指定的公众号不存在或没有权限借用指定的公众号.');
	}

	pdo_update('uni_settings', array('jsauth_acid' => $jsauth_acid), array('uniacid' => $_W['uniacid']));
	cache_delete("unisetting:{$_W['uniacid']}");
	message('设置借用 js 分享权限成功', referer() ,'success');
}

$jsauth_acid = pdo_fetchcolumn('SELECT `jsauth_acid` FROM '.tablename('uni_settings').' WHERE `uniacid` = :uniacid LIMIT 1',array(':uniacid' => $_W['uniacid']));
template('profile/jsauth');