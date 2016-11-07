<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/index.php : v 14b9a4299104 : 2015/09/11 10:44:21 : yanghf $
 */
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
load()->web('common');
load()->web('template');
load()->func('communication');
load()->model('cache');
load()->model('frame');
load()->model('cloud');
load()->classs('coupon');


$content = '您的短信验证码为: 238924 您正在使用瓷都圈相关功能, 需要你进行身份确认';
cloud_sms_send('13734004544', $content);
echo '发送成功';
exit;
print_r(parse_url('http://wx.panomall.com/web/index.php?c=material&a=mass&do=cron&id=47'));