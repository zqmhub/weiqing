<?php
/**
 * 基本文字回复模块
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class AutoReplyModule extends WeModule {
	public $modules = array('basic', 'news', 'image', 'music', 'voice', 'video', 'wxcard', 'keyword');
	public $tablename = array(
			'basic' => 'basic_reply',
			'news' => 'news_reply',
			'image' => 'images_reply',
			'music' => 'music_reply',
			'voice' => 'voice_reply',
			'video' => 'video_reply',
			'wxcard' => 'wxcard_reply'
		);
	private $replies = array();
	
	public function fieldsFormDisplay($rid = 0) {
		$replies = array();
		if(!empty($rid) && $rid > 0) {
			$isexists = pdo_fetch("SELECT id, module FROM ".tablename('rule')." WHERE id = :id", array(':id' => $rid));
		}
		if(!empty($isexists)) {
			$module = $isexists['module'];
			$module = $module == 'images' ? 'image' : $module;
			if(empty($module) || !in_array($module, array('basic', 'news', 'image', 'music', 'voice', 'video', 'wxcard', 'keyword', 'auto'))) {
				return '模块错误，请联系管理员。';
			}
			foreach ($this->tablename as $key => $tablename) {
				$replies[$key] = pdo_fetchall("SELECT * FROM ".tablename($tablename)." WHERE rid = :rid ORDER BY `id`", array(':rid' => $rid));
			}
		}
		include $this->template('display');
	}
	
	public function fieldsFormValidate($rid = 0) {
		global $_GPC;
		//判断回复内容是否全部为空：1、全空 ； 0、至少一个值不空
		$ifEmpty = 1;
		$reply = '';
		foreach ($this->modules as $key => $value) {
			if(trim($_GPC['reply_'.$value]) != '') {
				$ifEmpty = 0;
			}
			if( ($value == 'video' || $value == 'wxcard') && !empty($_GPC['reply_'.$value])) {
				$reply = ltrim($_GPC['reply_'.$value], '{');
				$reply = rtrim($reply, '}');
				$reply = explode('},{', $reply);
				foreach ($reply as &$val) {
					$val = htmlspecialchars_decode('{'.$val.'}');
				}
				$this->replies[$value] = $reply;
			}else {
				$this->replies[$value] = htmlspecialchars_decode($_GPC['reply_'.$value]);
			}
		}
		echo "<pre>";
		print_r($this->replies);
		echo "</pre>";
		echo "1541345<hr>";

		if($ifEmpty) {
			return '必须填写有效的回复内容.';
		}
		return '';
	}
	
	public function fieldsFormSubmit($rid = 0) {
		global $_GPC;
		$delsql = '';
		foreach ($this->modules as $k => $val) {
			$tablename = $this->tablename[$val];
			$delsql .= 'DELETE FROM '. tablename($tablename) . ' WHERE `rid`='.$rid.';';
		}
		pdo_run($delsql);

		foreach ($this->modules as $val) {
			$replies = array();
			$tablename = $this->tablename[$val];
			if($this->replies[$val]) {
				if(is_array($this->replies[$val])) {
					foreach ($this->replies[$val] as $value) {
						$replies[] = json_decode($value, true);
					}
				}else {
					$replies = explode(',', $this->replies[$val]);
				}
			}
			switch ($val) {
				case 'basic':
					if(!empty($replies)) {
						foreach($replies as $reply) {
							$reply = trim($reply, '"');
							pdo_insert($tablename, array('rid' => $rid, 'content' => $reply));
						}
					}
					break;
				case 'news':
					
					break;
				case 'image':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'mediaid' => $reply, 'createtime' => time()));
						}
					}
					break;
				case 'music':
					
					break;
				case 'voice':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'mediaid' => $reply, 'createtime' => time()));
						}
					}
					break;
				case 'video':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'mediaid' => $reply['mediaid'], 'title' => $reply['title'], 'description' => $reply['description'], 'createtime' => time()));
						}
					}
					break;
				case 'wxcard':
					if(!empty($replies)) {
						foreach ($replies as $reply) {
							pdo_insert($tablename, array('rid' => $rid, 'title' => $reply['title'], 'card_id' => $reply['mediaid'], 'cid' => $reply['cid'], 'brand_name' => $reply['brandname'], 'logo_url' => $reply['logo_url'], 'success' => $reply['success'], 'error' => $reply['error']));
						}
					}
					break;
			}
		}
		return true;
	}
	
	public function ruleDeleted($rid = 0) {
		$reply_modules = array("basic", "news", "music", "images", "voice", "video", "wxcard");
		foreach($this->tablename as $tablename) {
			pdo_delete($tablename, array('rid' => $rid));
		}
	}
}