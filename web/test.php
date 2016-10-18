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

echo tomedia('//taobao.com');
exit;
print_r(parse_url('http://wx.panomall.com/web/index.php?c=material&a=mass&do=cron&id=47'));