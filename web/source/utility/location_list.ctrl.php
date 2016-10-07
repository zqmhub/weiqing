<?php
/**
 * 门店列表
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('activity');
activity_coupon_type_init();
$source = COUPON_TYPE == WECHAT_COUPON ? 2 : 1;
$locations = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid'], 'source' => $source));
template('utility/location_list');