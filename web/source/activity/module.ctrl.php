<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/activity/module.ctrl.php : v c3bd51624473 : 2015/05/28 03:06:30 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('module');
$do = in_array($do, $dos) ? $do : 'module';

if($do == 'module') {
	$module = uni_modules();
	$new = array();
	if(!empty($module)) {
		$filter = array('services', 'customer', 'activity');
		foreach($module as $mou) {
			if(!in_array($mou['type'], $filter) && !$mou['issystem']) {
				$new[] = $mou;
			}
		}
	}
	unset($module);
	template('activity/module_model');
	die;
}