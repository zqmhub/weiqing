<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');
$do = 'edit';
$dos = array('edit');
$do = in_array($do, $dos) ? $do : 'display';
if ($do == 'edit') {
	$multiid = intval($_GPC['multiid']);
	$_W['uniacid'] = 281;
	$multiid = 13;
	$operate = $_GPC['operate'];
	if ($operate == 'delete') {
		$type = $_GPC['type'];
		$id = intval($_GPC['id']);
		pdo_delete('site_'.$type, array('id' => $id));
		message('删除成功', url('wxapp/manage/edit'), 'success');
	}
	if ($operate == 'change_category') {
		$parentid = intval($_GPC['categoryid']);
		$categorys = pdo_getall('site_category', array('parentid' => $parentid, 'uniacid' => $_W['uniacid']));
		return message(error('1', $categorys), '', 'ajax');
	}
	if (checksubmit('submit')) {
		$slide = $_GPC['slide'];
		$nav = $_GPC['nav'];
		$recommend = $_GPC['recommend'];
		$category = $_GPC['category'];
		$id = intval($_GPC['id']);
		//更新幻灯片
		if (!empty($slide)) {
			if (empty($id)) {
				$slide['uniacid'] = $_W['uniacid'];
				$slide['multiid'] = $multiid;
				pdo_insert('site_slide', $slide);
				message('添加幻灯片成功', url('wxapp/manage/edit'), 'success');
			} else {
				$result = pdo_update('site_slide', $slide, array('uniacid' => $_W['uniacid'], 'multiid' => $multiid, 'id' => $id));
				message('更新幻灯片成功', url('wxapp/manage/edit'), 'success');
			}
		}
		if (!empty($nav)) {
			if (empty($id)) {
				$nav['uniacid'] = $_W['uniacid'];
				$nav['multiid'] = $multiid;
				pdo_insert('site_nav', $nav);
				message('添加导航图标成功', url('wxapp/manage/edit', array('wxapp' => 'nav')), 'success');
			} else {
				pdo_update('site_nav', $nav, array('uniacid' => $_W['uniacid'], 'multiid' => $multiid, 'id' => $id));
				message('更新导航图标成功', url('wxapp/manage/edit', array('wxapp' => 'nav')), 'success');
			}
		}
		if (!empty($recommend)) {
			if (empty($id)) {
				$recommend['uniacid'] = $_W['uniacid'];
				pdo_insert('site_article', $recommend);
				message('添加推荐图片成功', url('wxapp/manage/edit', array('wxapp' => 'recommend')), 'success');
			} else {
				pdo_update('site_article', $recommend, array('uniacid' => $_W['uniacid'], 'id' => $id));
				message('更新推荐图片成功', url('wxapp/manage/edit', array('wxapp' => 'recommend')), 'success');
			}
		}
		if (!empty($category)) {
			if (empty($id)) {
				$category['uniacid'] = $_W['uniacid'];
				$category['multiid'] = $multiid;
				if (!empty($_GPC['parentid'])) {
					$category['parentid'] = intval($_GPC['parentid']);
				}
				pdo_insert('site_category', $category);
				message('添加分类成功', url('wxapp/manage/edit', array('wxapp' => 'category')), 'success');
			} else {
				pdo_update('site_category', $category, array('uniacid' => $_W['uniacid'], 'id' => $id));
				message('更新分类成功', url('wxapp/manage/edit', array('wxapp' => 'category')), 'success');
			}
		}
		//导航图标
	}
	$slides = pdo_getall('site_slide', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	$navs = pdo_getall('site_nav', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid));
	if (!empty($navs)) {
		foreach($navs as &$nav) {
			$nav['css'] = iunserializer($nav['css']);
		}
	}
//	$category_navs = pdo_fetchall("SELECT * FROM ".tablename('site_nav')." as a RIGHT JOIN ". tablename('site_category')." as b ON a.categoryid = b.id WHERE a.multiid = :multiid AND a.uniacid = :uniacid", array(':multiid' => $multiid, ':uniacid' => $_W['uniacid']), 'categoryid');
//	$pcates = empty($category_navs) ? '' : array_keys($category_navs);
	$categorys = pdo_getall('site_category', array('uniacid' => $_W['uniacid'], 'multiid' => $multiid, 'parentid' => 0), array(), 'id');
	$recommends = pdo_getall('site_article', array('uniacid' => $_W['uniacid'], 'pcate' => array_keys($categorys)));
	if (!empty($categorys)) {
		foreach ($categorys as &$category) {
			$category['categorys'] = pdo_getall('site_category', array('parentid' => $category['id'], 'uniacid' => $_W['uniacid'], 'multiid' => $multiid));
		}
	}
	$modules = pdo_getcolumn('wxapp_versions', array('multiid' => $multiid), 'modules');
	if (!empty($modules)) {
		$modules = explode(',', $modules);
		foreach ($modules as &$module) {
			$module = pdo_get('modules', array('name' => $module));
		}
	}
	template('wxapp/wxapp-edit');
}