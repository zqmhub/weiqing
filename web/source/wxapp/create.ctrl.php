<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');
$dos = array('display', 'post', 'getapps');
$do = in_array($do, $dos) ? $do : 'display';

if($do == 'post') {

	if(!empty($_GPC['wxappval'])) {
		$submitval = json_decode(ihtml_entity_decode($_GPC['wxappval']), true);
		$version = ($submitval['version0'] ? $submitval['version0'] : 0) .'.'.($submitval['version1'] ? $submitval['version1'] : 0).'.'.($submitval['version2'] ? $submitval['version2'] : 0);
		//构建底部菜单menus
		$bottommenu = array();
		foreach ($submitval['menus'] as $mvalue) {
			$mvalue['defaultImage'] = empty($mvalue['defaultImage']) ? $_W['siteroot'].'web/resource/images/bottom-default.png' : $mvalue['defaultImage'];
			$mvalue['selectedImage'] = empty($mvalue['selectedImage']) ? $_W['siteroot'].'web/resource/images/bottom-default.png' : $mvalue['selectedImage'];
			$bottommenu[] = array(
					'pagePath' => 'we7/page/index/index',
					'iconPath' => $mvalue['defaultImage'],
					'selectedIconPath' => $mvalue['selectedImage'],
					'text' => $mvalue['name']
				);
		}
		//构建包装应用modules
		// $modules = array();
		// foreach ($submitval['modules'] as $modulekey => $modulevalue) {
		// 	$modules[$modulevalue['module']] = $modulevalue['version'];
		// }
		$modules = array(
			'we7_1' => '7.0',
			'we7_gs' => '31.0'
		);
		//构建请求数据
		$request_cloud_data = array();
		$request_cloud_data = array(
			'name' => $submitval['name'],
			'modules' => $modules,
			'siteInfo' => array(
					'uniacid' => '99998',
					'acid' => '99999',
					'multiid'  => '99990',
					'version'  => $version,
					'siteroot' => $_W['siteroot'].'app/index.php'
				),
			
		);		
		if($submitval['showmenu']) {
			$request_cloud_data['tabBar'] = array(
				'color' => $submitval['buttom']['color'],
				'selectedColor' => $submitval['buttom']['selectedColor'],
				'borderStyle' => 'black',
				'backgroundColor' => $submitval['buttom']['boundary'],
				'list' => $bottommenu
			);
		}
		
		$request_cloud_data = json_encode($request_cloud_data);
		load()->classs('cloudapi');
		$api = new CloudApi();
		$rst = $api->post('wxapp', 'download', $request_cloud_data, 'html');
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$submitval['name'].'.zip"');
		echo $rst;
		exit;
	}
	template('wxapp/create-post');
}

if($do == 'display') {
	
	template('wxapp/wxapp-display');
}

if($do == 'getapps') {
	//获取当前系统下所有安装模块及模块信息
	$modulelist = uni_modules();
	$apps = array();
	foreach ($modulelist as $key => $module) {
		if($module['type'] != 'system' && !empty($module['version'])) {
			//获取图标
			if($module['issystem']) {
				$path = '../framework/builtin/' . $module['name'];
			} else {
				$path = '../addons/' . $module['name'];
			}
			$cion = $path . '/icon-custom.jpg';
			if(!file_exists($cion)) {
				$cion = $path . '/icon.jpg';
				if(!file_exists($cion)) {
					$cion = './resource/images/nopic-small.jpg';
				}
			}
			//获取模块相关信息
			$m = module_entries($module['name'], array('home'));
			if(!empty($m['home'])) {
				foreach($m['home'] as $val) {
					$rst = array();
					if(isset($val['eid']) && !empty($val['eid'])) {
						$rst = module_entry($val['eid']);
						$rst['module_title'] = $module['title'];
						$rst['module_icon'] = $cion;
						$rst['version'] = $module['version'];
						$apps[] = $rst;
					}
				}	
			}
		}
	}
	message($apps, '', 'ajax');
}