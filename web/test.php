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
echo '3211';
echo md5('测试一下cherry');exit;
echo '测试一下商业版合并免费版';
$qiniu_conf = base64_encode(file_get_contents(IA_ROOT.'/framework/library/qiniu/src/Qiniu/Config.php'));
$qiniu_zone = base64_encode(file_get_contents(IA_ROOT.'/framework/library/qiniu/src/Qiniu/Zone.php'));
$cos_conf = base64_encode(file_get_contents(IA_ROOT.'/framework/library/cos/Qcloud_cos/Conf.php'));
$qiniu_conf = 'PD9waHAKbmFtZXNwYWNlIFFpbml1OwoKZmluYWwgY2xhc3MgQ29uZmlnCnsKICAgIGNvbnN0IFNES19WRVIgPSAnNy4wLjYnOwoKICAgIGNvbnN0IEJMT0NLX1NJWkUgPSA0MTk0MzA0OyAvLzQqMTAyNCoxMDI0IOWIhuWdl+S4iuS8oOWdl+Wkp+Wwj++8jOivpeWPguaVsOS4uuaOpeWPo+inhOagvO+8jOS4jeiDveS/ruaUuQoKICAgIGNvbnN0IElPX0hPU1QgID0gJ2h0dHA6Ly9pb3ZpcC16MS5xYm94Lm1lJzsgICAgICAgICAgICAvLyDkuIPniZvmupDnq5lIb3N0CiAgICBjb25zdCBSU19IT1NUICA9ICdodHRwOi8vcnMucWJveC5tZSc7ICAgICAgICAgICAgICAgLy8g5paH5Lu25YWD5L+h5oGv566h55CG5pON5L2cSG9zdAogICAgY29uc3QgUlNGX0hPU1QgPSAnaHR0cDovL3JzZi5xYm94Lm1lJzsgICAgICAgICAgICAgIC8vIOWIl+S4vuaTjeS9nEhvc3QKICAgIGNvbnN0IEFQSV9IT1NUID0gJ2h0dHA6Ly9hcGkucWluaXUuY29tJzsgICAgICAgICAgICAvLyDmlbDmja7lpITnkIbmk43kvZxIb3N0CgogICAgcHJpdmF0ZSAkdXBIb3N0OyAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIOS4iuS8oEhvc3QKICAgIHByaXZhdGUgJHVwSG9zdEJhY2t1cDsgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyDkuIrkvKDlpIfnlKhIb3N0CgogICAgcHVibGljIGZ1bmN0aW9uIF9fY29uc3RydWN0KFpvbmUgJHogPSBudWxsKSAgICAgICAgIC8vIOaehOmAoOWHveaVsO+8jOm7mOiupOS4unpvbmUwCiAgICB7CiAgICAgICAgaWYgKCR6ID09PSBudWxsKSB7CiAgICAgICAgICAgICR6ID0gWm9uZTo6em9uZTAoKTsKICAgICAgICB9CiAgICAgICAgJHRoaXMtPnVwSG9zdCA9ICR6LT51cEhvc3Q7CiAgICAgICAgJHRoaXMtPnVwSG9zdEJhY2t1cCA9ICR6LT51cEhvc3RCYWNrdXA7CiAgICB9CgogICAgcHVibGljIGZ1bmN0aW9uIGdldFVwSG9zdCgpCiAgICB7CiAgICAgICAgcmV0dXJuICR0aGlzLT51cEhvc3Q7CiAgICB9CgogICAgcHVibGljIGZ1bmN0aW9uIGdldFVwSG9zdEJhY2t1cCgpCiAgICB7CiAgICAgICAgcmV0dXJuICR0aGlzLT51cEhvc3RCYWNrdXA7CiAgICB9Cn0K';
$qiniu_zone = "PD9waHAKbmFtZXNwYWNlIFFpbml1OwoKZmluYWwgY2xhc3MgWm9uZQp7CiAgICBwdWJsaWMgJHVwSG9zdDsKICAgIHB1YmxpYyAkdXBIb3N0QmFja3VwOwoKICAgIHB1YmxpYyBmdW5jdGlvbiBfX2NvbnN0cnVjdCgkdXBIb3N0LCAkdXBIb3N0QmFja3VwKQogICAgewogICAgICAgICR0aGlzLT51cEhvc3QgPSAkdXBIb3N0OwogICAgICAgICR0aGlzLT51cEhvc3RCYWNrdXAgPSAkdXBIb3N0QmFja3VwOwogICAgfQoKICAgIHB1YmxpYyBzdGF0aWMgZnVuY3Rpb24gem9uZTAoKQogICAgewogICAgICAgIHJldHVybiBuZXcgc2VsZignaHR0cDovL3VwLXoxLnFpbml1LmNvbScsICdodHRwOi8vdXBsb2FkLXoxLnFpbml1LmNvbScpOwogICAgfQoKICAgIHB1YmxpYyBzdGF0aWMgZnVuY3Rpb24gem9uZTEoKQogICAgewogICAgICAgIHJldHVybiBuZXcgc2VsZignaHR0cDovL3VwLXoxLnFpbml1LmNvbScsICdodHRwOi8vdXBsb2FkLXoxLnFpbml1LmNvbScpOwogICAgfQp9Cg==";
$cos_conf = 'PD9waHANCm5hbWVzcGFjZSBRY2xvdWRfY29zOw0KDQpjbGFzcyBDb25mDQp7DQogICAgY29uc3QgUEtHX1ZFUlNJT04gPSAndjMuMyc7DQoNCiAgICBjb25zdCBBUElfSU1BR0VfRU5EX1BPSU5UID0gJ2h0dHA6Ly93ZWIuaW1hZ2UubXlxY2xvdWQuY29tL3Bob3Rvcy92MS8nOw0KICAgIGNvbnN0IEFQSV9WSURFT19FTkRfUE9JTlQgPSAnaHR0cDovL3dlYi52aWRlby5teXFjbG91ZC5jb20vdmlkZW9zL3YxLyc7DQogICAgY29uc3QgQVBJX0NPU0FQSV9FTkRfUE9JTlQgPSAnaHR0cDovL3dlYi5maWxlLm15cWNsb3VkLmNvbS9maWxlcy92MS8nOw0KICAgIC8v6K+35YiwaHR0cDovL2NvbnNvbGUucWNsb3VkLmNvbS9jb3Pljrvojrflj5bkvaDnmoRhcHBpZOOAgXNpZOOAgXNrZXkNCiAgICBjb25zdCBBUFBJRCA9ICcnOw0KICAgIGNvbnN0IFNFQ1JFVF9JRCA9ICcnOw0KICAgIGNvbnN0IFNFQ1JFVF9LRVkgPSAnJzsNCg0KDQogICAgcHVibGljIHN0YXRpYyBmdW5jdGlvbiBnZXRVQSgpIHsNCiAgICAgICAgcmV0dXJuICdjb3MtcGhwLXNkay0nLnNlbGY6OlBLR19WRVJTSU9OOw0KICAgIH0NCn0NCg0KLy9lbmQgb2Ygc2NyaXB0DQo=';
die;die;die;die;die;die;
echo 222;die;

function site_cloud_ad($params) {
	global $_W;
	$querystring = array(
		'ad_type' => $params['ad_type'],
		'type' => $params['type'],
		'module' => $params['module'],
		'uniacid' => $_W['uniacid'],
		'site_key' => $_W['setting']['site']['key'],
	);
	$url = 'http://s.we7.cc/index.php?c=store&a=link&do=ad&'.http_build_query($querystring, null, '&');
	$ret = ihttp_request($url, array(), array(), 10);
	if (is_error($ret)) {
		echo '';
	}
	
	echo <<<eof
<script type="text/javascript">{$ret['content']}</script>
eof;
}
	
$params = array(
	'ad_type' => 1,
	'type' => 'view',
	'module' => 'ewei_shopping',
	'uniacid' => 20,
	'site_key' => 40716
);
site_cloud_ad($params);

exit;
$ret = cloud_flow_site_stat_day(array('page' => 2, 'size' => 1));
print_r($ret);
exit;
/* $ret = cloud_flow_ad_type_list();
print_r($ret);
exit;
 */
/* $ret = cloud_flow_app_list_get(281);
print_r($ret);
exit;
 */

$ret = cloud_flow_app_post(281, 'we7_demo', 1);
print_r($ret);
$ret = cloud_flow_app_get(281, 'we7_demo');
print_r($ret);
exit;

$uniaccount = $_W['uniaccount'];
$uniaccount['uniacid']++;
$data = array(
	'title' => $uniaccount['name'],
	'uniacid' => $uniaccount['uniacid'] + 1,
	'original' => $uniaccount['original'],
	'gh_type' => $uniaccount['level'],
);
$ret = cloud_flow_uniaccount_post($data);
print_r($ret);
/* $ret = cloud_flow_uniaccount_get($uniaccount['uniacid']);
print_r($ret); */

exit;
$flow_master = array(
	'site_key' => 30128,
	'linkman' => 'linkman value',
	'mobile' => 'mobile value',
	'address' => 'address value',
	'id_card_photo' => 'id_card_photo value', // 身份证 url
	'business_licence_photo' => 'business_licence_photo value', // 营业执照 url
);
$ret = cloud_flow_master_post($flow_master);
print_r($ret);
$ret = cloud_flow_master_get();
print_r($ret);
'teswt';
'test';
'test';
'test';
'test';
'test';