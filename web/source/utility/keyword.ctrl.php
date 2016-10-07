<?php
/**
 * 素材管理
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
$dos = array('list');
if (!in_array($do, array('keyword'))) {
	exit('Access Denied');
}

if($do == 'keyword') {
	$type = trim($_GPC['type']) == 'all' ? '' : trim($_GPC['type']);
	
	if(!empty($type)) {
		$condition = " WHERE uniacid = :uniacid AND status = 1 AND module = :module";
		$params = array(':uniacid' => $_W['uniacid'], ':module' => $type);
	}else {
		$condition = " WHERE uniacid = :uniacid AND status = 1";
		$params = array(':uniacid' => $_W['uniacid']);
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$limit = " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ", {$psize}";

	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule') . $condition, $params);
	$lists = pdo_fetchall('SELECT * FROM ' . tablename('rule') . $condition . $limit, $params, 'id');
	if(!empty($lists)) {
		foreach($lists as &$row) {
			if(!empty($type)) {
				$row['child_items'] = pdo_getall('rule_keyword', array('uniacid' => $_W['uniacid'], 'rid' => $row['id'], 'status' => 1, 'module' => $type));
			}else {
				$row['child_items'] = pdo_getall('rule_keyword', array('uniacid' => $_W['uniacid'], 'rid' => $row['id'], 'status' => 1));
			}
		}
	}
	$result = array(
		'items' => $lists,
		'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	);
	message($result, '', 'ajax');
}
