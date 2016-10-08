<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('platform_menu');
load()->model('mc');
load()->model('platform');
$dos = array('display', 'save', 'remove', 'refresh', 'search_key', 'add', 'push', 'copy');
$do = in_array($do, $dos) ? $do : 'display';

if($_W['isajax']) {
	if($do == 'search_key') {
		$condition = '';
		$key_word = trim($_GPC['key_word']);
		if(!empty($key_word)) {
			$condition = " AND content LIKE '%{$key_word}%' ";
		}
		$data = pdo_fetchall('SELECT content FROM ' . tablename('rule_keyword') . " WHERE (uniacid = 0 OR uniacid = :uniacid) AND status != 0 " . $condition . ' ORDER BY uniacid DESC,displayorder DESC LIMIT 15', array(':uniacid' => $_W['uniacid']));
		$exit_da = array();
		if(!empty($data)) {
			foreach($data as $da) {
				$exit_da[] = $da['content'];
			}
		}
		exit(json_encode($exit_da));
	}
	$post = $_GPC['__input'];
	if(!empty($post['method'])) {
		$do = $post['method'];
	}
}

if($do == 'display') {
	$_W['page']['title'] = '菜单设计器 - 自定义菜单 - 高级功能';
	set_time_limit(0);
	$account = WeAccount::create($_W['acid']);
	$result = $account->menuQuery();
	if(is_error($result)) {
		message($result['message'], '', 'error');
	}
	pdo_update('uni_account_menus', array('status' => 0), array('uniacid' => $_W['uniacid']));
	$default_menu = $result['menu'];
	if(!empty($default_menu)) {
		$condition_menu = $result['conditionalmenu'];
		$condition_menu[] = array(
			'button' => $default_menu['button'],
			'type' => 1,
			'matchrule' => array(),
		);
		if(!empty($condition_menu)) {
			foreach($condition_menu as $menu) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'type' => empty($menu['matchrule']) ? 1 : 3,
					'group_id' => isset($menu['matchrule']['tag_id']) ? $menu['matchrule']['tag_id'] : (isset($menu['matchrule']['group_id']) ? $menu['matchrule']['group_id'] : '-1'),
					'sex' => $menu['matchrule']['sex'],
					'client_platform_type' => $menu['matchrule']['client_platform_type'],
					'area' => trim($menu['matchrule']['country']) . trim($menu['matchrule']['province']) . trim($menu['matchrule']['city']),
					'data' => base64_encode(iserializer($menu)),
					'menuid' => $menu['menuid'],
					'status' => 1,
				);

				if(empty($menu['matchrule'])) {
					$id = pdo_fetchcolumn('SELECT id FROM ' . tablename('uni_account_menus') . ' WHERE uniacid = :uniacid AND type = 1', array(':uniacid' => $_W['uniacid']));
				} else {
					$id = pdo_fetchcolumn('SELECT id FROM ' . tablename('uni_account_menus') . ' WHERE uniacid = :uniacid AND menuid = :menuid', array(':uniacid' => $_W['uniacid'], ':menuid' => $menu['menuid']));
				}
				if(!empty($id)) {
					pdo_update('uni_account_menus', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
				} else {
					pdo_insert('uni_account_menus', $data);
				}
			}
		}
	}
	$isdeleted = $_GPC['status'] == 'history' ? 1 : 0;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('uni_account_menus') . ' WHERE uniacid = :uniacid AND isdeleted = :isdeleted', array(':uniacid' => $_W['uniacid'], ':isdeleted' => $isdeleted));
	$data = pdo_fetchall('SELECT * FROM ' . tablename('uni_account_menus') . ' WHERE uniacid = :uniacid AND isdeleted = :isdeleted ORDER BY type ASC, id DESC', array(':uniacid' => $_W['uniacid'], ':isdeleted' => $isdeleted));
	$names = array(
		'sex' => array(
			0 => '不限',
			1 => '男',
			2 => '女',
		),
		'client_platform_type' => array(
			0 => '不限',
			1 => '苹果',
			2 => '安卓',
			3 => '其他'
		),
	);
	$groups = mc_fans_groups(true);
	template('platform/menu');
}

if($do == 'push') {
	$post_data = $_GPC['__input'];
	$id = intval($post_data['id']);
	$data = pdo_get('uni_account_menus', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($data)) {
		message(error(-1, '菜单不存在或已删除'), referer(), 'ajax');
	}
	if ($post_data['status'] == 1) {
		$post = iunserializer(base64_decode($data['data']));
		if(empty($post)) {
			message(error(-1, '菜单数据错误'), referer(), 'ajax');
		}
		$menu = array();
		if(!empty($post['button'])) {
			foreach($post['button'] as &$button) {
				$temp = array();
				$temp['name'] = preg_replace_callback('/\:\:([0-9a-zA-Z_-]+)\:\:/', create_function('$matches', 'return utf8_bytes(hexdec($matches[1]));'), $button['name']);
				$temp['name'] = urlencode($temp['name']);
				if (empty($button['sub_button'])) {
					$temp['type'] = $button['type'];
					if($button['type'] == 'view') {
						$temp['url'] = urlencode($button['url']);
					} elseif ($button['type'] == 'media_id' || $button['type'] == 'view_limited') {
						$temp['media_id'] = urlencode($button['media_id']);
					} else {
						$temp['key'] = urlencode($button['key']);
					}
				} else {
					foreach($button['sub_button'] as &$subbutton) {
						$sub_temp = array();
						$sub_temp['name'] = preg_replace_callback('/\:\:([0-9a-zA-Z_-]+)\:\:/', create_function('$matches', 'return utf8_bytes(hexdec($matches[1]));'), $subbutton['name']);
						$sub_temp['name'] = urlencode($sub_temp['name']);
						$sub_temp['type'] = $subbutton['type'];
						if($subbutton['type'] == 'view') {
							$sub_temp['url'] = urlencode($subbutton['url']);
						} elseif ($subbutton['type'] == 'media_id' || $subbutton['type'] == 'view_limited') {
							$sub_temp['media_id'] = urlencode($subbutton['media_id']);
						} else {
							$sub_temp['key'] = urlencode($subbutton['key']);
						}
						$temp['sub_button'][] = $sub_temp;
					}
				}
				$menu['button'][] = $temp;
			}
		}

		if(!empty($post['matchrule'])) {
			if($post['matchrule']['sex'] > 0) {
				$menu['matchrule']['sex'] = $post['matchrule']['sex'];
			}
			if($post['matchrule']['group_id'] != -1) {
				$menu['matchrule']['tag_id'] = $post['matchrule']['group_id']; 			// 微信用户组id，变为用户标签id
			}
			if($post['matchrule']['client_platform_type'] > 0) {
				$menu['matchrule']['client_platform_type'] = $post['matchrule']['client_platform_type'];
			}
			if(!empty($post['matchrule']['province'])) {
				$menu['matchrule']['country'] = urlencode('中国');
				$menu['matchrule']['province'] = urlencode(rtrim($post['matchrule']['province'], '省'));
				if(!empty($post['matchrule']['city'])) {
					$menu['matchrule']['city'] = urlencode(rtrim($post['matchrule']['city'], '市'));
				}
			}
		}
		if ($data['type'] == '2') {
			unset($menu['matchrule']);
		}
		$account = WeAccount::create($_W['acid']);
		$ret = $account->menuCreate($menu);
		if(is_error($ret)) {
			message(error(-1, $ret['message']), '', 'ajax');
		} else {
			if($data['type'] = 2) {
				pdo_delete('uni_account_menus', array('uniacid' => $_W['uniacid'], 'type' => 1));
				pdo_update('uni_account_menus', array('status' => 1, 'type' => 1), array('uniacid' => $_W['uniacid'], 'id' => $data['id']));
			}
			// 将$menu中 tag_id 再转为 group_id
			if($post['matchrule']['group_id'] != -1) {
				$menu['matchrule']['groupid'] = $menu['matchrule']['tag_id'];
				unset($menu['matchrule']['tag_id']);
			}
			pdo_update('uni_account_menus', array('status' => 1, 'menuid' => $ret, 'data' => base64_encode(iserializer($menu))), array('uniacid' => $_W['uniacid'], 'id' => $id));
			message(error(0, '推送成功'), url('platform/menu/display'), 'ajax');
		}
	} elseif ($post_data['status'] == 2) {
		if ($_GPC['op'] == 'recover') {
			if($data['type'] == 1) {
				pdo_update('uni_account_menus', array('isdeleted' => 0), array('uniacid' => $_W['uniacid']));
			} else {
				pdo_update('uni_account_menus', array('isdeleted' => 0), array('uniacid' => $_W['uniacid'], 'id' => $id));
			}
			message('恢复菜单成功，是否推送到微信？<a href="'.url('platform/menu/push', array('id' => $id)).'" class="btn btn-primary">是</a> <a href="'.url('platform/menu/display').'" class="btn btn-default">取消</a>', url('platform/menu/display'), 'ajax');
		}
		$status =  $_GPC['status'];
		if($data['type'] == 1 || ($data['type'] == 3 && $data['menuid'] > 0) && $status != 'history') {
			$account = WeAccount::create($_W['acid']);
			$ret = $account->menuDelete($data['menuid']);
			if(is_error($ret) && empty($_GPC['f'])) {
				$url = url('platform/menu/remove', array('id' => $id, 'f' => 1));
				$url_display = url('platform/menu/display', array('id' => $id, 'f' => 1));
				$message = "调用微信接口删除失败:{$ret['message']}<br>";
				//$message .= "强制删除本地数据? <a href='{$url}' class='btn btn-primary'>是</a> <a href='{$url_display}' class='btn btn-default'>取消</a>";
				message($message, '', 'error');
			} else {
				message(error(0, '关闭成功'), referer(), 'ajax');
			}
		}
	}
}

if($do == 'copy') {
	$id = intval($_GPC['id']);
	$menu = pdo_get('uni_account_menus', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($menu)) {
		message('菜单不存在或已经删除', url('platform/menu/display'), 'error');
	}
	if($menu['type'] != 3) {
		message('该菜单不能复制', url('platform/menu/display'), 'error');
	}
	unset($menu['id'], $menu['menuid']);
	$menu['status'] = 0;
	$menu['title'] = $menu['title'] . '- 复本';
	pdo_insert('uni_account_menus', $menu);
	$id = pdo_insertid();
	header('Location:' . url('platform/menu/add', array('id' => $id)));
	die;
}

if($do == 'add') {
	$_W['page']['title'] = '菜单设计器 - 自定义菜单 - 高级功能';
	$type = intval($_GPC['type']);
	$id = intval($_GPC['id']);
	$params = array();
	if($id > 0) {
		$menu = pdo_get('uni_account_menus', array('uniacid' => $_W['uniacid'], 'id' => $id));
		if(!empty($menu)) {
			$menu['data'] = iunserializer(base64_decode($menu['data']));
			if(!empty($menu['data'])) {
				if(!empty($menu['data']['matchrule']['province'])) {
					$menu['data']['matchrule']['province'] .= '省';
				}
				if(!empty($menu['data']['matchrule']['city'])) {
					$menu['data']['matchrule']['city'] .= '市';
				}
				$params = $menu['data'];
				$params['title'] = $menu['title'];
				$params['type'] = $menu['type'];
				$params['id'] = $menu['id'];
				$params['status'] = $menu['status'];
			}
			$type = $menu['type'];
		}
	}
	$groups = mc_fans_groups();
	$languages = platform_menu_languages();
	template('platform/menu');
}

if($do == 'remove') {
	$id = intval($_GPC['id']);
	$data = pdo_get('uni_account_menus', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($data)) {
		message('菜单不存在或已经删除', referer(), 'error');
	}
	if ($_GPC['op'] == 'recover') {
		if($data['type'] == 1) {
			pdo_update('uni_account_menus', array('isdeleted' => 0), array('uniacid' => $_W['uniacid']));
		} else {
			pdo_update('uni_account_menus', array('isdeleted' => 0), array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
		message('恢复菜单成功，是否推送到微信？<a href="'.url('platform/menu/push', array('id' => $id)).'" class="btn btn-primary">是</a> <a href="'.url('platform/menu/display').'" class="btn btn-default">取消</a>', url('platform/menu/display'), 'success');
	}
	$status =  $_GPC['status'];
	if($data['type'] == 1 || ($data['type'] == 3 && $data['menuid'] > 0) && $status != 'history') {
		$account = WeAccount::create($_W['acid']);
		$ret = $account->menuDelete($data['menuid']);
		if(is_error($ret) && empty($_GPC['f'])) {
			$url = url('platform/menu/remove', array('id' => $id, 'f' => 1));
			$url_display = url('platform/menu/display', array('id' => $id, 'f' => 1));
			$message = "调用微信接口删除失败:{$ret['message']}<br>";
			//$message .= "强制删除本地数据? <a href='{$url}' class='btn btn-primary'>是</a> <a href='{$url_display}' class='btn btn-default'>取消</a>";
			message($message, '', 'error');
		}
	}
	if ($status == 'history') {
		if($data['type'] == 1) {
			pdo_delete('uni_account_menus', array('uniacid' => $_W['uniacid']));
		} else {
			pdo_delete('uni_account_menus', array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
	} else {
		if($data['type'] == 1) {
			pdo_update('uni_account_menus', array('isdeleted' => 1), array('uniacid' => $_W['uniacid']));
		} else {
			pdo_update('uni_account_menus', array('isdeleted' => 1), array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
	}

	message('删除菜单成功', url('platform/menu/display'), 'success');
}

if($do == 'save') {
	set_time_limit(0);
	$post = $post['group'];
	$menu = array();
	if(!empty($post['button'])) {
		foreach($post['button'] as &$button) {
			$temp = array();
			$temp['name'] = preg_replace_callback('/\:\:([0-9a-zA-Z_-]+)\:\:/', create_function('$matches', 'return utf8_bytes(hexdec($matches[1]));'), $button['name']);
			$temp['name'] = urlencode($temp['name']);
			if (empty($button['sub_button'])) {
				$temp['type'] = $button['type'];
				if($button['type'] == 'view') {
					$temp['url'] = urlencode($button['url']);
				} elseif ($button['type'] == 'media_id' || $button['type'] == 'view_limited') {
					if (!empty($button['media_id']) && empty($button['key'])) {
						$temp['media_id'] = urlencode($button['media_id']);
					} elseif (empty($button['media_id']) && !empty($button['key'])) {
						$temp['type'] = 'click';
						$temp['key'] = urlencode($button['key']);
					}
				} else {
					$temp['key'] = urlencode($button['key']);
				}
			} else {
				foreach($button['sub_button'] as &$subbutton) {
					$sub_temp = array();
					$sub_temp['name'] = preg_replace_callback('/\:\:([0-9a-zA-Z_-]+)\:\:/', create_function('$matches', 'return utf8_bytes(hexdec($matches[1]));'), $subbutton['name']);
					$sub_temp['name'] = urlencode($sub_temp['name']);
					$sub_temp['type'] = $subbutton['type'];
					if($subbutton['type'] == 'view') {
						$sub_temp['url'] = urlencode($subbutton['url']);
					} elseif ($subbutton['type'] == 'media_id' || $subbutton['type'] == 'view_limited') {
						if (!empty($subbutton['media_id']) && empty($subbutton['key'])) {
							$sub_temp['media_id'] = urlencode($subbutton['media_id']);
						} elseif (empty($subbutton['media_id']) && !empty($subbutton['key'])) {
							$sub_temp['type'] = 'click';
							$sub_temp['key'] = urlencode($subbutton['key']);
						}
					} else {
						$sub_temp['key'] = urlencode($subbutton['key']);
					}
					$temp['sub_button'][] = $sub_temp;
				}
			}
			$menu['button'][] = $temp;
		}
	}

	if($post['type'] == 3 && !empty($post['matchrule'])) {
		if($post['matchrule']['sex'] > 0) {
			$menu['matchrule']['sex'] = $post['matchrule']['sex'];
		}
		if($post['matchrule']['group_id'] != -1) {
			$menu['matchrule']['tag_id'] = $post['matchrule']['group_id'];		// 微信用户组id，变为用户标签id
		}
		if($post['matchrule']['client_platform_type'] > 0) {
			$menu['matchrule']['client_platform_type'] = $post['matchrule']['client_platform_type'];
		}

		if(!empty($post['matchrule']['province'])) {
			$menu['matchrule']['country'] = urlencode('中国');
			$menu['matchrule']['province'] = urlencode(str_replace('省', '', $post['matchrule']['province']));
			if(!empty($post['matchrule']['city'])) {
				$menu['matchrule']['city'] = urlencode(str_replace('市', '', $post['matchrule']['city']));
			}
		}
		if(!empty($post['matchrule']['language'])) {
			$inarray = 0;
			$languages = platform_menu_languages();
			foreach ($languages as $key => $value) {
				if(in_array($post['matchrule']['language'], $value, true)) $inarray = 1;
			}
			if($inarray === 1) $menu['matchrule']['language'] = $post['matchrule']['language'];
		}
	}
	$account = WeAccount::create($_W['acid']);
	$ret = $account->menuCreate($menu);
	if(is_error($ret)) {
		message($ret, '', 'ajax');
	} else {
		// 将$menu中 tag_id 再转为 group_id
		if($post['matchrule']['group_id'] != -1) {
			$menu['matchrule']['groupid'] = $menu['matchrule']['tag_id'];
			unset($menu['matchrule']['tag_id']);
		}
		$menu = json_decode(urldecode(json_encode($menu)), true);
		if(!isset($menu['matchrule'])) {
			$menu['matchrule'] = array();
		}
		$insert = array(
			'uniacid' => $_W['uniacid'],
			'menuid' => $ret,
			'title' => $post['title'],
			'type' => $post['type'],
			'sex' => intval($menu['matchrule']['sex']),
			'group_id' => isset($menu['matchrule']['group_id']) ? $menu['matchrule']['group_id'] : -1,
			'client_platform_type' => intval($menu['matchrule']['client_platform_type']),
			'area' => trim($menus['matchrule']['country']) . trim($menu['matchrule']['province']) . trim($menu['matchrule']['city']),
			'data' => base64_encode(iserializer($menu)),
			'status' => 1,
			'createtime' => TIMESTAMP,
		);
		if($post['type'] == 1) {
			$history = pdo_get('uni_account_menus', array('uniacid' => $_W['uniacid'], 'type' => 2));
			if(empty($history)) {
				$data = $insert;
				$data['type'] = 2;
				$data['status'] = 0;
				pdo_insert('uni_account_menus', $data);
			} else {
				$data = $insert;
				$data['type'] = 2;
				$data['status'] = 0;
				pdo_update('uni_account_menus', $data, array('uniacid' => $_W['uniacid'], 'type' => 2));
			}
			$default = pdo_get('uni_account_menus', array('uniacid' => $_W['uniacid'], 'type' => 1));
			if(!empty($default)) {
				pdo_update('uni_account_menus', $insert, array('uniacid' => $_W['uniacid'], 'type' => 1));
			} else {
				pdo_insert('uni_account_menus', $insert);
			}
			message(error(0, ''), '', 'ajax');
		} elseif($post['type'] == 3) {
			if($post['status'] == 0 && $post['id'] > 0) {
				pdo_update('uni_account_menus', $insert, array('uniacid' => $_W['uniacid'], 'type' => 3, 'id' => $post['id']));
			} else {
				pdo_insert('uni_account_menus', $insert);
			}
			message(error(0, ''), '', 'ajax');
		}
	}
}

