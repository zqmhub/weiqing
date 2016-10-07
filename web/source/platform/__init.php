<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/platform/__init.php : v 9f5368ee3726 : 2015/09/06 09:02:15 : yanghf $
 */
//控制左侧选中的链接(从多微站进微站访问入口时左侧选中多微站)
if(!empty($_GPC['multiid'])) {
	define('ACTIVE_FRAME_URL', url('site/multi/display'));
}
$sysmods = system_modules();
if($action == 'cover') {
	$dos = array('site', 'mc', 'card', 'module', 'clerk');
	$do = in_array($do, $dos) ? $do : 'module';
	if(in_array($do, array('mc', 'card', 'clerk'))) {
		define('FRAME', 'mc');
	}
	if($do == 'site') {
		define('FRAME', 'site');
	}
} elseif($action == 'reply') {
	$m = $_GPC['m'];
	if(in_array($m, $sysmods)) {
		define('FRAME', 'platform');
	}
} elseif($action == 'stat') {
	$m = $_GPC['m'];
	if(!empty($m) && !in_array($m, $sysmods)) {
		define('FRAME', 'ext');
		define('ACTIVE_FRAME_URL', url('home/welcome/ext/') . 'm=' . $m);
	} elseif(!empty($m)) {
		define('FRAME', 'platform');
		define('ACTIVE_FRAME_URL', url('platform/reply/') . 'm=' . $m);
	} else {
		define('FRAME', 'platform');
	}
} else {
	define('FRAME', 'platform');
}

$frames = buildframes(array(FRAME));
$frames = $frames[FRAME];
