<?php
/**
 * 检测远程附件
 * $sn: pro/web/source/utility/checkupgrade.ctrl.php : v 1b4cf3499c79 : 2015/03/13 09:56:35 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');
$pindex = $_GPC['page'];
$psize = 20;
$condition = '';
if (!empty($_GPC['nickname'])) {
	$condition .= " AND nickname LIKE '%".trim($_GPC['nickname'])."%'" ;
}
$check_fans = json_decode($_COOKIE['fans_openids'.$_W['uniacid']]);
$check_fans = empty($check_fans) ? array() : $check_fans;
$fans = pdo_fetchall("SELECT * FROM ". tablename('mc_mapping_fans')." WHERE uniacid = :uniacid AND acid = :acid AND nickname <> '' ". $condition." LIMIT ". ($pindex-1)*$psize.",".$psize, array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
$total =  pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('mc_mapping_fans')." WHERE uniacid = :uniacid AND acid = :acid AND nickname <> '' ". $condition, array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
$pager = pagination($total, $pindex, $psize, '', array('before' => '3', 'after' => '2', 'ajaxcallback' => 'true'));
template('utility/fans');