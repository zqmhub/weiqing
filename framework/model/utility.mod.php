<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/model/utility.mod.php : v a80418cf2718 : 2014/09/16 01:07:43 : Gorden $
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 检查验证码是否存在且正确
 * @param int $uniacid 统一公号
 * @param string $receiver 粉丝用户
 * @param string $code 验证码
 * @return boolean
 */
function code_verify($uniacid, $receiver, $code) {
	$data = pdo_fetch('SELECT * FROM ' . tablename('uni_verifycode') . ' WHERE uniacid = :uniacid AND receiver = :receiver AND verifycode = :verifycode AND createtime > :createtime', array(':uniacid' => $uniacid, ':receiver' => $receiver, ':verifycode' => $code, ':createtime' => time() - 1800));
	if(empty($data)) {
		return false;
	}
	return true;
}
