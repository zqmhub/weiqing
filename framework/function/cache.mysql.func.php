<?php
/**
 * 数据库缓存
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取缓存单条数据
 * @param string $key 缓存键名 多个层级或分组请使用:隔开
 * @return array
 */
function cache_read($key) {
	$sql = 'SELECT `value` FROM ' . tablename('core_cache') . ' WHERE `key`=:key';
	$params = array();
	$params[':key'] = $key;
	$val = pdo_fetchcolumn($sql, $params);
	return iunserializer($val);
}

/**
 * 检索缓存中指定层级或分组的所有缓存
 * @param string 缓存分组
 * @return array
 */
function cache_search($prefix) {
	$sql = 'SELECT * FROM ' . tablename('core_cache') . ' WHERE `key` LIKE :key';
	$params = array();
	$params[':key'] = "{$prefix}%";
	$rs = pdo_fetchall($sql, $params);
	$result = array();
	foreach ((array)$rs as $v) {
		$result[$v['key']] = iunserializer($v['value']);
	}
	return $result;
}

/**
 * 将缓存数据写入数据库
 * @param string $key 缓存键名
 * @param mixed $data 缓存数据
 * @return mixed
 */
function cache_write($key, $data) {
	if (empty($key) || !isset($data)) {
		return false;
	}
	$record = array();
	$record['key'] = $key;
	$record['value'] = iserializer($data);
	return pdo_insert('core_cache', $record, true);
}

/**
 * 删除某个键的缓存数据
 * @param string $key 缓存键名
 * @return boolean
 */
function cache_delete($key) {
	$sql = 'DELETE FROM ' . tablename('core_cache') . ' WHERE `key`=:key';
	$params = array();
	$params[':key'] = $key;
	$result = pdo_query($sql, $params);
	return $result;
}

/**
 * 清空指定前缀缓存或所有数据
 * @param string $prefix 缓存前缀
 * @return mixed
 */
function cache_clean($prefix = '') {
	global $_W;
	if (empty($prefix)) {
		$sql = 'DELETE FROM ' . tablename('core_cache');
		$result = pdo_query($sql);
		if ($result) {
			unset($_W['cache']);
		}
	} else {
		$sql = 'DELETE FROM ' . tablename('core_cache') . ' WHERE `key` LIKE :key';
		$params = array();
		$params[':key'] = "{$prefix}:%";
		$result = pdo_query($sql, $params);
	}
	return $result;
}
