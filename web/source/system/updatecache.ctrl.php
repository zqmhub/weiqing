<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/system/updatecache.ctrl.php : v 25c4f271f9c1 : 2015/09/16 10:49:43 : RenChao $
 */
$_W['page']['title'] = '更新缓存 - 系统管理';
load()->model('cache');
load()->model('setting');
if (checksubmit('submit')) {
	cache_build_template();
	cache_build_users_struct();
	cache_build_setting();
	cache_build_account_modules();
	cache_build_account();
	cache_build_accesstoken();
	cache_build_frame_menu();
	cache_build_module_subscribe_type();
	cache_build_platform();
	cache_build_stat_fans();
	cache_clean('stat');
	cache_build_cloud_ad();
	message('缓存更新成功！', url('system/updatecache'));
} else {
	template('system/updatecache');
}



















