<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/extension/theme.ctrl.php : v 58211b71cef4 : 2015/08/21 08:13:16 : duanbiaowu $
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('installed', 'prepared', 'install', 'batch-install', 'uninstall', 'upgrade', 'check', 'manager', 'designer');
$do = in_array($do, $dos) ? $do : 'installed';
load()->model('extension');
load()->model('cloud');

if($do == 'installed') {
	$_W['page']['title'] = '已安装的后台皮肤 - 风格主题 - 扩展';
	$webthemeids = array();
	$where = (empty($_GPC['type']) || $_GPC['type'] == 'all') ? '' : " WHERE `type` = '{$_GPC['type']}'";
	$webthemes = pdo_fetchall("SELECT * FROM ".tablename('webtheme_templates') . $where);
	foreach($webthemes as $webtheme) {
		$webthemeids[] = $webtheme['name'];
	}
	/*获取后台皮肤分类*/
	$webtheme_types = ext_webtheme_type();
	template('extension/webtheme');
}

if($do == 'prepared') {
	$_W['page']['title'] = '安装后台皮肤 - 风格主题 - 扩展';
	// 本地安装的模板
	$webthemeids = array();
	$webthemes = pdo_fetchall("SELECT * FROM ".tablename('webtheme_templates'));
	foreach($webthemes as $webtheme) {
		$webthemeids[] = $webtheme['name'];
	}
	// 本地未安装的模板
	$uninstall_webthemes = $uninstall_webthemes_title = array();
	$path = IA_ROOT . '/web/themes/';
	if (is_dir($path) || mkdir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($webtheme_path = readdir($handle))) {
				if($webtheme_path != '.' && $webtheme_path != '..'){
					$manifest = ext_webtheme_manifest($webtheme_path, false);
					if(!empty($manifest) && !in_array($manifest['name'], $webthemeids)) {
						$uninstall_webthemes[$manifest['name']] = $manifest;
						$uninstall_webthemes_title[$manifest['name']] = $manifest['title'];
					}
				}
			}
		}
	}
	$prepare_webthemes = json_encode(array_keys($uninstall_webthemes));
	$prepare_webthemes_title = json_encode($uninstall_webthemes_title);
	template('extension/webtheme');
}

if($do == 'install') {
	if(empty($_W['isfounder'])) {
		message('您没有安装后台皮肤的权限', '', 'error');
	}
	$id = $_GPC['webthemeid'];
	if (pdo_fetchcolumn("SELECT id FROM ".tablename('webtheme_templates')." WHERE name = :name", array(':name' => $id))) {
		message('后台皮肤已经安装或是唯一标识已存在！', '', 'error');
	}
	$manifest = ext_webtheme_manifest($id, false);
	if (!empty($manifest)) {
		$r = cloud_w_prepare($id);
		if(is_error($r)) {
			message($r['message'], url('extension/webtheme/prepared'), 'error');
		}
	}
	if (empty($manifest)) {
		$r = cloud_prepare();
		if(is_error($r)) {
			message($r['message'], url('cloud/profile'), 'error');
		}
		$info = cloud_w_info($id);
		if (!is_error($info)) {
			if (empty($_GPC['flag'])) {
				header('location: ' . url('cloud/process', array('w' => $id)));
				exit;
			} else {
				$packet = cloud_w_build($id);
				$manifest = ext_webtheme_manifest_parse($packet['manifest']); 		// 暂用模板的manifest解析
				$manifest['version'] = $packet['version'];
			}
		} else {
			message($info['message'], '', 'error');
		}
	}
	unset($manifest['settings']);
	
	if (empty($manifest)) {
		message('后台皮肤安装配置文件不存在或是格式不正确！', '', 'error');
	}
	if ($manifest['name'] != $id) {
		message('主页安装模板与文件标识不符，请重新安装', '', 'error');
	}
	//模板配置项针对具体的公众号插入数据库
	if (pdo_insert('webtheme_templates', $manifest)) {
		message('后台皮肤安装成功', url('extension/webtheme'), 'success');
	} else {
		message('后台皮肤安装失败, 请联系模板开发者！');
	}
}

if($do == 'batch-install') {
	if($_W['ispost']) {
		$id = $_GPC['webthemeid'];
		$m = ext_webtheme_manifest($id);
		if (empty($m)) {
			exit('error');
		}
		if (pdo_fetchcolumn("SELECT id FROM ".tablename('webtheme_templates')." WHERE name = '{$m['name']}'")) {
			exit('error');
		}
		
		if (pdo_insert('webtheme_templates', $m)) {
			exit('success');
		} else {
			exit('error');
		}
	} else {
		exit('error');
	}
}

if($do == 'uninstall') {
	$name = pdo_fetchcolumn('SELECT name FROM ' . tablename('webtheme_templates') . ' WHERE id = :id', array(':id' => intval($_GPC['id'])));
	if($name == 'default') {
		message('默认后台皮肤不能卸载', '', 'error');
	}
	if (pdo_delete('webtheme_templates', array('id' => intval($_GPC['id'])))) {
		message('后台皮肤移除成功, 你可以重新安装, 或者直接移除文件来安全删除！', referer(), 'success');
	} else {
		message('后台皮肤移除失败, 请联系模板开发者！');
	}
}

if($do == 'upgrade') {
	//增加批量更新模板功能
	$check = intval($_GPC['check']);
	$batch = intval($_GPC['batch']);
	if($check == 1) {
		isetcookie('batch', 1);
		//检测需要更新的模板，记录到数据库缓存
		$batch = 1;
		$r = cloud_prepare();
		if(is_error($r)) {
			exit('cloud service is unavailable');
		}
		$webthemes = pdo_fetchall('SELECT id,name,version FROM ' . tablename('webtheme_templates'), array(), 'name');
		$upgrade = array();
		$mods = array();
		$ret = cloud_w_query();
		if(!is_error($ret)) {
			foreach($ret as $k => $v) {
				if(!$webthemes[$k]) continue;
				if(ver_compare($webthemes[$k]['version'], $v['version']) == -1) {
					$upgrade[] = $k;
				}
			}
		} else {
			message('从云平台获取模板信息失败,请稍后重试', referer(), 'error');
		}
		if(empty($upgrade)) {
			message('您的模板已经是最新版本', referer(), 'success');
		}
		$upgrade_str = iserializer($upgrade);
		cache_write('upgrade:webtheme', $upgrade_str);
	}

	if($batch == 1) {
		$wait_upgrade = (array)iunserializer(cache_read('upgrade:webtheme'));
		if(empty($wait_upgrade)) {
			isetcookie('batch', 0, -10000);
			message('您的主页模板已经是最新版本', url('extension/webtheme'), 'success');
		}
		$id = array_shift($wait_upgrade);
	} else {
		$id = $_GPC['webthemeid'];
	}

	$webtheme = pdo_fetch("SELECT id, name, title FROM " . tablename('webtheme_templates') . " WHERE name = :name", array(':name' => $id));
	if (empty($webtheme)) {
		if($batch == 1) {
			cache_write('upgrade:webtheme', iserializer($wait_upgrade));
			message($webtheme['title'] . ' 主页模板已经被卸载或是不存在。系统将进入下一个主页模板的更新。<br>请勿关闭浏览器', url('extension/webtheme/upgrade', array('batch' => 1)), 'success');
		}
		message('主页模板已经被卸载或是不存在！', '', 'error');
	}
	$r = cloud_prepare();
	if(is_error($r)) {
		message($r['message'], url('cloud/profile'), 'error');
	}

	$info = cloud_w_info($id);
	if (is_error($info)) {
		message($info['message'], referer(), 'error');
	}

	$upgrade_info = cloud_w_upgradeinfo($id);

	if (is_error($upgrade_info)) {
		message($upgrade_info['message'], referer(), 'error');
	}

	// 1.ajax时，各分支的 'upgrade_price = 0'。获取更新信息，ajax返回。
	if ($_W['isajax']) {
		if ($upgrade_info['free']) {
			foreach ($upgrade_info['branches'] as &$branch) {
				$branch['upgrade_price'] = 0;
			}
		}
		message($upgrade_info, '', 'ajax');
	}

	if (!is_error($info)) {
		if (empty($_GPC['flag'])) {

			// 2.点击更新按钮，并选取了更新的版本(同样跳转到 cloud/process，参数多了一个 is_buy=1。即使是免费。。)
			if (intval($_GPC['branch']) > $upgrade_info['version']['branch_id']) {
				header('location: ' . url('cloud/redirect/buybranch', array('m' => $id, 'branch' => intval($_GPC['branch']), 'type' => 'webtheme', 'is_upgrade' => 1)));
				exit;
			}
			load()->func('file');
			rmdirs(IA_ROOT . '/web/themes/' . $id, true);
			header('Location: ' . url('cloud/process', array('w' => $id, 'is_upgrade' => 1)));
			exit;
		} else {

			// 3.更新成功后
			$packet = cloud_w_build($id);
			$manifest = ext_webtheme_manifest_parse($packet['manifest']);
		}
	}
	if (empty($manifest)) {
		if($batch == 1) {
			cache_write('upgrade:webtheme', iserializer($wait_upgrade));
			message($webtheme['title'] . ' 主页模板安装配置文件不存在或是格式不正确。系统将进入下一个主页模板的更新。<br>请勿关闭浏览器', url('extension/webtheme/upgrade', array('batch' => 1)), 'success');
		}
		message('主页模板安装配置文件不存在或是格式不正确！', '', 'error');
	}
	if(ver_compare($webtheme['version'], $packet['version']) != -1) {
		if($batch == 1) {
			cache_write('upgrade:webtheme', iserializer($wait_upgrade));
			message($webtheme['title'] . ' 主页模板版本不低于要更新的版本。系统将进入下一个主页模板的更新。<br>请勿关闭浏览器', url('extension/webtheme/upgrade', array('batch' => 1)), 'success');
		}
		message('已安装的主页模板版本不低于要更新的版本, 操作无效.');
	}
	pdo_update('webtheme_templates', array('version' => $packet['version']), array('id' => $webtheme['id']));
	if($batch == 1) {
		cache_write('upgrade:webtheme', iserializer($wait_upgrade));
		message($webtheme['title'] . ' 主页模板更新成功。系统将进入下一个主页模板的更新。<br>请勿关闭浏览器', url('extension/theme/upgrade', array('batch' => 1)), 'success');
	}
	message('主页模板更新成功！', url('extension/webtheme'), 'success');
}

if($do == 'check') {
	if($_W['isajax']) {
		$foo = $_GPC['foo'];
		
		$r = cloud_prepare();
		if(is_error($r)) {
			exit('cloud service is unavailable');
		}

		if ($foo == 'upgrade') {
			$mods = array();

			$ret = cloud_w_query();

			if (!is_error($ret)) {
				foreach($ret as $k => $v) {
					$mods[$k] = array(
						'from' => 'cloud',
						'version' => $v['version'],
						'branches' => $v['branches'],
						'site_branch' => $v['branches'][$v['branch']],
					);
				}

				$mods['pirate_apps'] = array_values($v['pirate_apps']);
			}

			if(!empty($mods)) {
				exit(json_encode($mods));
			}
		} else {
			// 本地已安装模板
			$webthemeids = array();
			$webthemes = pdo_fetchall("SELECT `name` FROM " . tablename('webtheme_templates') . ' ORDER BY `id` ASC');
			if(!empty($webthemes)) {
				foreach($webthemes as $m) {
					$webthemeids[] = $m['name'];
				}
			}
			// 购买的模板中未安装的模板
			$ret = cloud_w_query();
			if(!is_error($ret)) {
				$cloud_uninstall_webthemes = array();
				foreach($ret as $k => $v) {
					if(!in_array(strtolower($k), $webthemeids)) {
						$v['name'] = $k;
						$cloud_uninstall_webthemes[] = $v;
						$webthemeids[] = $k;
					}
				}
				exit(json_encode($cloud_uninstall_webthemes));
			}
		}
	}
	exit();
}
/*主页模板设计器*/
if ($do == 'designer') {
	if (empty($_W['isfounder'])) {
		message('您没有设计新主页模板的权限', '', 'error');
	}
	$_W['page']['title'] = '设计主页模板风格 - 风格主题 - 扩展';
	/*检测ZIP扩展是否可用*/
	$available['download'] = class_exists('ZipArchive');
	/*检测生成目录是否可写*/
	$available['create'] = is_writable(IA_ROOT . '/web/themes/');
	$versions = array('0.52', '0.6');
	/*获取主页模板分类*/
	$webtheme_types = ext_webtheme_type();
	
	if (checksubmit('submit') && $available[$_GPC['method']]) {
		$t['webtheme']['name'] = trim($_GPC['webtheme']['name']);
		if(empty($t['webtheme']['name']) || preg_match('/\*\/|\/\*|eval|\$\_/i', $t['webtheme']['name'])) {
			message('请输入有效的主页模板名称. ');
		}
		$t['webtheme']['identifie'] = trim($_GPC['webtheme']['identifie']);
		if(empty($t['webtheme']['identifie']) || !preg_match('/^[a-z][a-z\d_]+$/i', $t['webtheme']['identifie'])) {
			message('必须输入主页模板标识符(仅支持字母和数字, 且只能以字母开头). ');
		}
		$t['webtheme']['type'] = array_key_exists($_GPC['webtheme']['type'], $webtheme_types) ? $_GPC['webtheme']['type'] : 'other';
		$t['webtheme']['description'] = trim($_GPC['webtheme']['description']);
		if(empty($t['webtheme']['description']) || preg_match('/\*\/|\/\*|eval|\$\_/i', $t['webtheme']['description'])) {
			message('请输入有效的主页模板介绍. ');
		}
		$t['webtheme']['author'] = trim($_GPC['webtheme']['author']);
		if(empty($t['webtheme']['author']) || preg_match('/\*\/|\/\*|eval|\$\_/i', $t['webtheme']['author'])) {
			message('请输入有效的主页模板作者');
		}
		$t['webtheme']['url'] = trim($_GPC['webtheme']['url']);
		if(empty($t['webtheme']['url']) || preg_match('/\*\/|\/\*|eval|\$\_/i', $t['webtheme']['url'])) {
			message('请输入有效的主页模板发布页');
		}
		if (is_array($_GPC['versions'])) {
			foreach ($_GPC['versions'] as $value) {
				if (in_array($value, $versions)) {
					$t['versions'][] = $value;
				}
			}
		} else {
			message('请设置版本的兼容性');
		}
		if($_FILES['preview'] && $_FILES['preview']['error'] == '0' && !empty($_FILES['preview']['tmp_name'])) {
			$t['preview'] = $_FILES['preview']['tmp_name'];
		}
		/*获取生成的XML文本*/
		$manifest = manifest($t);
		load()->func('file');

		/*生成模板文件*/
		if ($_GPC['method'] == 'create') {
			$tpldir = IA_ROOT . '/web/themes/' . strtolower($t['webtheme']['identifie']);
			if (is_dir($tpldir)) {
				message('主页模板目录' . $tpldir . '已存在，请更换主页模板标识或删除已存在模板');
			}
			mkdirs($tpldir);
			file_put_contents("{$tpldir}/manifest.xml", $manifest);
			if (!empty($t['preview'])) {
				file_move($t['preview'], "{$tpldir}/preview.jpg");
			}
			message('主页模板生成成功，请访问' . $tpldir . '目录进行查看', referer(), 'success');
			exit();
		}

		/*下载主页模板文件*/
		if ($_GPC['method'] == 'download') {
			$zipfile = IA_ROOT . '/data/webtheme.zip';
			$zip = new ZipArchive();
			$zip->open($zipfile, ZipArchive::CREATE);
			$zip->addFromString('manifest.xml', $manifest);
			if (!empty($t['preview'])) {
				$zip->addFile($t['preview'], "preview.jpg");
				
			}
			$zip->close();
			header('content-type: application/zip');
			header('content-disposition: attachment; filename="' . $t['webtheme']['identifie'] . '.zip"');
			readfile($zipfile);
			@unlink($t['preview']);
			@unlink($zipfile);
		}
	}

	template('extension/design-webtheme');
}

if($do == 'manager') {
	$_W['page']['title'] = '管理后台风格 - 风格主题 - 扩展';
	load()->model('setting');
	if(checksubmit('submit')) {
		$data = array(
			'template' => $_GPC['template'],
		);
		setting_save($data, 'basic');
		message('更新设置成功！', 'refresh');
	}
	$webthemes = array();
	$webtheme_templates = pdo_fetchall("SELECT * FROM ".tablename('webtheme_templates'));
	foreach($webtheme_templates as $webtheme) {
		$webthemes[] = $webtheme['name'];
	}
	template('extension/webtheme');
}

/*生成配置XML文本*/
function manifest($t) {
	$versions = implode(',', $t['versions']);
	$tpl = <<<TPL
<?xml version="1.0" encoding="utf-8"?>
<manifest versionCode="{$versions}">
	<identifie><![CDATA[{$t['webtheme']['identifie']}]]></identifie>
	<title><![CDATA[{$t['webtheme']['name']}]]></title>
	<type><![CDATA[{$t['webtheme']['type']}]]></type>
	<description><![CDATA[{$t['webtheme']['description']}]]></description>
	<author><![CDATA[{$t['webtheme']['author']}]]></author>
	<url><![CDATA[{$t['webtheme']['url']}]]></url>
</manifest>
TPL;
	return ltrim($tpl);
}