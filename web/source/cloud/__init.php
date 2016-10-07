<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 * 
 * account 所有操作在GW界面进行
 */

define('IN_GW', true);

if(in_array($action, array('profile', 'device', 'callback', 'appstore', 'sms'))) {
	$do = $action;
	$action = 'redirect';
}
if($action == 'touch') {
	exit('success');
}
