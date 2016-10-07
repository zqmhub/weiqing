<?php
/**
 * 系统中一些必须以模块形式表现的功能
 * @author 微擎团队
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class CoreModuleSite extends WeModuleSite {
	public function doMobilePay() {
		global $_W, $_GPC;
		$fee = floatval($_GPC['fee']);
		
		$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
		$pay = $setting['payment'];
		if (empty($_W['member']['uid'])) {
			$pay['credit']['switch'] = false;
		}
		if (!empty($pay['credit']['switch'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
		}
		include $this->template('pay');
	}
}