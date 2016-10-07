<?php
/**
 * 公众号核心类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
/**
 * 公众号业务操作基类
 */
abstract class WeAccount {
	
	const TYPE_WEIXIN = '1';
	const TYPE_YIXIN = '2';
	const TYPE_WEIXIN_PLATFORM = '3';
	
	/**
	 * 创建平台特定的公众号操作对象
	 * @param int $acid 公众号编号
	 * @return WeAccount|NULL
	 */
	public static function create($acidOrAccount = '') {
		global $_W;
		if(empty($acidOrAccount)) {
			$acidOrAccount = $_W['account'];
		}
		if (is_array($acidOrAccount)) {
			$account = $acidOrAccount;
		} else {
			$account = account_fetch($acidOrAccount);
		}
		if (is_error($account)) {
			$account = $_W['account'];
		}
		if(!empty($account) && isset($account['type'])) {
			if($account['type'] == self::TYPE_WEIXIN) {
				load()->classs('weixin.account');
				return new WeiXinAccount($account);
			}
			if($account['type'] == self::TYPE_WEIXIN_PLATFORM) {
				load()->classs('weixin.platform');
				return new WeiXinPlatform($account);
			}
		}
		return null;
	}
	
	static public function token($type = 1) {
		$classname = self::includes($type);
		$obj = new $classname();
		return $obj->fetch_available_token();
	}
	
	static public function includes($type = 1) {
		if($type == '1') {
			load()->classs('weixin.account');
			return 'WeiXinAccount';
		}
		if($type == '2') {
			load()->classs('yixin.account');
			return 'YiXinAccount';
		}
	}
	
	/**
	 * 平台特定的公众号操作对象构造方法
	 * 
	 * @param array $account 统一公号基础对象 
	 */
	abstract public function __construct($account);

	/**
	 * 微擎系统对来自公众平台的请求进行安全校验
	 * 
	 * @retun boolean
	 */
	public function checkSign() {
		trigger_error('not supported.', E_USER_WARNING);
	}

	/**
	 * 获取公众号信息
	 */
	public function fetchAccountInfo() {
		trigger_error('not supported.', E_USER_WARNING);
	}

	/**
	 * 查询当前公号支持的统一消息类型, 当前支持的类型包括: 
	 * &nbsp;&nbsp;&nbsp;通用类型: text, image, voice, video, location, link,
	 * &nbsp;&nbsp;&nbsp;扩展类型: subscribe, unsubscribe, qr, trace, click, view, enter
	 * 类型说明:
	 * &nbsp;&nbsp;&nbsp;通用类型: 文本消息, 图片消息, 音频消息, 视频消息, 位置消息, 链接消息, 
	 * &nbsp;&nbsp;&nbsp;扩展类型: 开始关注, 取消关注, 扫描二维码, 追踪位置, 点击菜单(链接), 点击菜单(模拟关键字), 进入聊天窗口
	 * 
	 * @return array 当前公号支持的消息类型集合
	 */
	public function queryAvailableMessages() {
		return array();
	}
	
	/**
	 * 查询当前公号支持的统一响应结构
	 * 
	 * 微擎当前支持的类型包括:<br/>
	 * &nbsp;&nbsp;&nbsp; text, image, voice, video, music, news, link, card
	 * 
	 * @return array 当前公号支持的响应结构集合
	 */
	public function queryAvailablePackets() {
		return array();
	}

	/**
	 * 分析消息内容,并返回统一消息结构, 参数为公众平台消息结构
	 * @param array $message 统一消息结构
	 * @return array 统一消息结构
	 */
	public function parse($message) {
		global $_W;
		if (!empty($message)){
			$message = xml2array($message);
			$packet = iarray_change_key_case($message, CASE_LOWER);
			$packet['from'] = $message['FromUserName'];
			$packet['to'] = $message['ToUserName'];
			$packet['time'] = $message['CreateTime'];
			$packet['type'] = $message['MsgType'];
			$packet['event'] = $message['Event'];
			switch ($packet['type']) {
				case 'text':
					$packet['redirection'] = false;
					$packet['source'] = null;
					break;
				case 'image':
					$packet['url'] = $message['PicUrl'];
					break;
				case 'video':
				case 'shortvideo':
					$packet['thumb'] = $message['ThumbMediaId'];
					break;
			}
	
			switch ($packet['event']) {
				case 'subscribe':
					$packet['type'] = 'subscribe';
				case 'SCAN':
					if ($packet['event'] == 'SCAN') {
						$packet['type'] = 'qr';
					}
					if(!empty($packet['eventkey'])) {
						$packet['scene'] = str_replace('qrscene_', '', $packet['eventkey']);
						if(strexists($packet['scene'], '\u')) {
							$packet['scene'] = '"' . str_replace('\\u', '\u', $packet['scene']) . '"';
							$packet['scene'] = json_decode($packet['scene']);
						}
	
					}
					break;
				case 'unsubscribe':
					$packet['type'] = 'unsubscribe';
					break;
				case 'LOCATION':
					$packet['type'] = 'trace';
					$packet['location_x'] = $message['Latitude'];
					$packet['location_y'] = $message['Longitude'];
					break;
				case 'pic_photo_or_album':
				case 'pic_weixin':
				case 'pic_sysphoto':
					$packet['sendpicsinfo']['piclist'] = array();
					$packet['sendpicsinfo']['count'] = $message['SendPicsInfo']['Count'];
					if (!empty($message['SendPicsInfo']['PicList'])) {
						foreach ($message['SendPicsInfo']['PicList']['item'] as $item) {
							if (empty($item)) {
								continue;
							}
							$packet['sendpicsinfo']['piclist'][] = is_array($item) ? $item['PicMd5Sum'] : $item;
						}
					}
					break;
				case 'card_pass_check':
				case 'card_not_pass_check':
				case 'user_get_card':
				case 'user_del_card':
				case 'user_consume_card':
				case 'poi_check_notify':
					$packet['type'] = 'coupon';
					break;
			}
		}
		return $packet;
	}
	
	/**
	 * 响应消息内容, 参数为统一响应结构
	 * @param array $packet 统一响应结构, 见文档 todo
	 * @return string 平台特定的消息响应内容
	 */
	public function response($packet) {
		if (is_error($packet)) {
			return '';
		}
		if (!is_array($packet)) {
			return $packet;
		}
		if(empty($packet['CreateTime'])) {
			$packet['CreateTime'] = TIMESTAMP;
		}
		if(empty($packet['MsgType'])) {
			$packet['MsgType'] = 'text';
		}
		if(empty($packet['FuncFlag'])) {
			$packet['FuncFlag'] = 0;
		} else {
			$packet['FuncFlag'] = 1;
		}
		return array2xml($packet);
	}

	/**
	 * 获取当前公号是否支持消息推送
	 * @return bool 是否支持
	 */
	public function isPushSupported() {
		return false;
	}
	
	/*
	 * 向指定的用户推送消息
	 * @param string $uniid 指定用户(统一用户) todo
	 * @param array $packet 统一响应结构
	 * @return bool 是否成功
	 */
	public function push($uniid, $packet) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 获取当前公号是否支持群发消息
	 * @return boolean 是否支持
	 */
	public function isBroadcastSupported() {
		return false;
	}
	
	/**
	 * 向一组用户发送群发消息, 可选的可以指定是否要指定特定组
	 * @param array $packet 统一消息结构
	 * @param array $targets 单独向一组用户群发, 或指定fans列表发送
	 */
	public function broadcast($packet, $targets = array()) {
		trigger_error('not supported.', E_USER_WARNING);
	}

	/**
	 * 查询当前公号是否支持菜单操作
	 * @return bool 是否支持
	 */
	public function isMenuSupported() {
		return false;
	}
	
	/**
	 * 为当前公众号创建菜单
	 * @param array $menu 统一菜单结构 todo
	 * @return bool 是否创建成功
	 */
	public function menuCreate($menu) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 删除当前公众号的菜单
	 * @return bool 是否删除成功
	 */
	public function menuDelete() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 修改当前公众号的菜单
	 * @param array $menu 统一菜单结构
	 * @return bool 是否修改成功
	 */
	public function menuModify($menu) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询菜单
	 * @return array 统一菜单结构
	 */
	public function menuQuery() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号粉丝管理的支持程度
	 * @return array 返回结果为支持的方法列表(fansGroupAll, fansGroupCreate, ...)
	 */
	public function queryFansActions() {
		return array();
	}
	
	/**
	 * 查询当前公号记录的分组信息
	 * @return array 统一分组结构集合
	 */
	public function fansGroupAll() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 在当前公号记录中创建一条分组信息
	 * @param array $group 统一分组结构 todo
	 * @return bool 是否执行成功
	 */
	public function fansGroupCreate($group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 在当前公号记录中修改一条分组信息
	 * @param array $group 统一分组结构
	 * @return bool 是否执行成功
	 */
	public function fansGroupModify($group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 将指定用户移至另一分组中
	 * @param string $uniid 指定用户(统一用户)
	 * @param array $group 统一分组结构
	 * @return bool 是否执行成功
	 */
	public function fansMoveGroup($uniid, $group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询指定的用户所在的分组
	 * @param string $uniid 指定用户(统一用户)
	 * @return array $group 统一分组结构
	 */
	public function fansQueryGroup($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询指定的用户的基本信息
	 * @param string $uniid 指定用户(统一用户)
	 * @param boolean $isPlatform 指定的参数是否为平台编号
	 * @return array 统一粉丝信息结构 todo
	 */
	public function fansQueryInfo($uniid, $isPlatform) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号的所有粉丝
	 * @return array 统一粉丝信息结构集合
	 */
	public function fansAll() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号地理位置追踪的支持情况
	 * @return array 返回结果为支持的方法列表(traceCurrent, traceHistory)
	 */
	public function queryTraceActions() {
		return array();
	}
	
	/**
	 * 追踪指定的用户的当前位置
	 * @param string $uniid 指定用户(统一用户)
	 * @return array 地理位置信息
	 */
	public function traceCurrent($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 追踪指定的用户的地理位置
	 * @param string $uniid 指定用户(统一用户)
	 * @param int $time 追踪的时间范围
	 * @return array 地理位置信息追踪集合
	 */
	public function traceHistory($uniid, $time) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 查询当前公号二维码支持情况
	 * @return array 返回结果为支持的方法列表(barCodeCreateDisposable, barCodeCreateFixed)
	 */
	public function queryBarCodeActions() {
		return array();
	}
	
	/**
	 * 生成临时的二维码
	 * 
	 */
	public function barCodeCreateDisposable($barcode) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	/**
	 * 生成永久的二维码
	 */
	public function barCodeCreateFixed($barcode) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	public function downloadMedia($media){
		trigger_error('not supported.', E_USER_WARNING);
	}
}

/**
 * 模块组件工厂
 */
class WeUtility {
	
	private static function defineConst($obj){
		global $_W;
		
		if ($obj instanceof WeBase) {
			if (!defined('MODULE_ROOT')) {
				define('MODULE_ROOT', dirname($obj->__define));
			}
			if (!defined('MODULE_URL')) {
				define('MODULE_URL', $_W['siteroot'].'addons/'.$obj->modulename.'/');
			}
		}
	}
	
	/**
	 * 创建模块(Module)
	 * @param string $name
	 * @return NULL|WeModule
	 */
	public static function createModule($name) {
		global $_W;
		static $file;
		$classname = ucfirst($name) . 'Module';
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/module.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/module.php";
			}
			if(!is_file($file)) {
				trigger_error('Module Definition File Not Found', E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('Module Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModule) {
			return $o;
		} else {
			trigger_error('Module Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块消息处理器
	 * @param string $name
	 * @return null | ModuleProcessor
	 */
	public static function createModuleProcessor($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleProcessor";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/processor.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/processor.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleProcessor Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleProcessor Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleProcessor) {
			return $o;
		} else {
			trigger_error('ModuleProcessor Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块订阅器
	 * @param $name
	 * @return null | WeModuleReceiver
	 */
	public static function createModuleReceiver($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleReceiver";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/receiver.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/receiver.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleReceiver Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleReceiver Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleReceiver) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块站点类
	 * @param unknown $name
	 * @return NULL|WeModuleSite
	 */
	public static function createModuleSite($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleSite";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/site.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/site.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleSite Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleSite Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		$o->inMobile = defined('IN_MOBILE');
		if($o instanceof WeModuleSite) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块计划任务类
	 * @param unknown $name
	 * @return NULL|WeModuleSite
	 */
	public static function createModuleCron($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleCron";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/cron.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/cron.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleCron Definition File Not Found '.$file, E_USER_WARNING);
				return error(-1006, 'ModuleCron Definition File Not Found');
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleCron Definition Class Not Found', E_USER_WARNING);
			return error(-1007, 'ModuleCron Definition Class Not Found');
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleCron) {
			return $o;
		} else {
			trigger_error('ModuleCron Class Definition Error', E_USER_WARNING);
			return error(-1008, 'ModuleCron Class Definition Error');
		}
	}

	/**
	 * 记录日志
	 * @param string $level
	 * @param string $message
	 */
	public static function logging($level = 'info', $message = '') {
		$filename = IA_ROOT . '/data/logs/' . date('Ymd') . '.log';
		load()->func('file');
		mkdirs(dirname($filename));
		$content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
		if(is_string($message) && !in_array($message, array('post', 'get'))) {
			$content .= "String:\n{$message}\n";
		}
		if(is_array($message)) {
			$content .= "Array:\n";
			foreach($message as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'get') {
			$content .= "GET:\n";
			foreach($_GET as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'post') {
			$content .= "POST:\n";
			foreach($_POST as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		$content .= "\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $content);
		fclose($fp);
	}
}
/**
 * 模块组件基类
 * 
 * $modulename 模块名称
 * $module 模块信息
 * $weid 公众号编号
 * $uniacid 公众号编号
 * $__define 文件地址
 */
abstract class WeBase {
	/**
	 * @var string 当前模块名称 {identifie}
	 */
	public $modulename;
	/**
	 * @var array 当前模块参数及配置信息
	 */
	public $module;
	/**
	 * @var int 当前统一公众号编号
	 */
	public $weid;
	/**
	 * @var int 当前统一公众号编号
	 */
	public $uniacid;
	/**
	 * @var string 定义了当前模块组件的文件绝对路径.
	 */
	public $__define;

	/**
	 * 保存当前统一公号下的模块配置参数
	 * 
	 * @param $settings array 配置参数
	 * @return bool 是否成功保存
	 */
	public function saveSettings($settings) {
		global $_W;
		$pars = array('module' => $this->modulename, 'uniacid' => $_W['uniacid']);
		$row = array();
		$row['settings'] = iserializer($settings);
		cache_build_account_modules();
		if (pdo_fetchcolumn("SELECT module FROM ".tablename('uni_account_modules')." WHERE module = :module AND uniacid = :uniacid", array(':module' => $this->modulename, ':uniacid' => $_W['uniacid']))) {
			return pdo_update('uni_account_modules', $row, $pars) !== false;
		} else {
			return pdo_insert('uni_account_modules', array('settings' => iserializer($settings), 'module' => $this->modulename ,'uniacid' => $_W['uniacid'], 'enabled' => 1)) !== false;
		}
	}

	/**
	 * 构造手机页面URL
	 * @param $do string 要进入的操作名称对应当前模块的 doMobileXXX 中的 Xxx
	 * @param array $query 附加的查询参数
	 * @param boolean $noredirect mobile 端url是否要附加 &wxref=mp.weixin.qq.com#wechat_redirect
	 * @return string 返回的 URL
	 */
	protected function createMobileUrl($do, $query = array(), $noredirect = true) {
		global $_W;
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		return murl('entry', $query, $noredirect);
	}

	/**
	 * 构造Web页面URL
	 * @param $do string 要进入的操作名称对应当前模块的 doWebXXX 中的 XXX
	 * @param array $query 附加的查询参数
	 * @return string 返回的 URL
	 */
	protected function createWebUrl($do, $query = array()) {
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		return wurl('site/entry', $query);
	}

	/**
	 * <b>返回模板编译后的文件路径，需要 include 调用</b>
	 * 
	 * 使用说明: 
	 * 依次在以下位置查找模板定义文件
	 * App:
	 * 微站风格中 app/themes/{当前模板}/{模块标识}/{模板名称}.html
	 * 微站风格中 app/themes/default/{模块标识}/{模板名称}.html
	 * 模块定义中 addons/{模块标识}/template/mobile/{模板名称}.html
	 * 微站风格中 app/themes/{当前模板}/{模板名称}.html
	 * 微站风格中 app/theme/default/{模板名称}.html
	 *
	 * Web:
	 * 后台风格中 web/themes/{当前模板}/modules/{模板标识}/{模板名称}.html
	 * 后台风格中 web/themes/default/modules/{模板标识}/{模板名称}.html
	 * 模块定义中 addons/{模块标识}/template/{模板名称}.html
	 * 后台风格中 web/themes/{当前模板}/{模板标识}/{模板名称}.html
	 * 后台风格中 web/theme/default/{模板标识}/{模板名称}.html
	 * 
	 * @param string $filename 模板文件路径
	 * @return string 编译后的模板文件路径
	 */
	protected function template($filename) {
		global $_W;
		$name = strtolower($this->modulename);
		$defineDir = dirname($this->__define);
		if(defined('IN_SYS')) {
			$source = IA_ROOT . "/web/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$name}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = $defineDir . "/template/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/{$_W['template']}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$filename}.html";
			}
		} else {
			$source = IA_ROOT . "/app/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/app/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = IA_ROOT . "/app/themes/default/{$name}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = $defineDir . "/template/mobile/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/app/themes/{$_W['template']}/{$filename}.html";
			}
			if(!is_file($source)) {
				if (in_array($filename, array('header', 'footer', 'slide', 'toolbar', 'message'))) {
					$source = IA_ROOT . "/app/themes/default/common/{$filename}.html";
				} else {
					$source = IA_ROOT . "/app/themes/default/{$filename}.html";
				}
			}
		}
		if(!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		}
		$paths = pathinfo($compile);
		$compile = str_replace($paths['filename'], $_W['uniacid'] . '_' . $paths['filename'], $compile);
		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		}
		return $compile;
	}
}

/**
 * 模块规则及自定义配置.
 */
abstract class WeModule extends WeBase {
	/**
	 * 可能需要实现的操作,附加其他字段内容至规则表单. 
	 * 编辑当前模块规则时,调用此方法将返回 HTML 内容附加至规则表单之后
	 * 
	 * @param int $rid 规则编号. $rid 大于 0 为更新规则, $rid 等于 0 为新增规则.
	 * @return string 要附加的字段内容(HTML内容)
	 */
	public function fieldsFormDisplay($rid = 0) {
		return '';
	}
	/**
	 * 可能需要实现的操作, 验证附加到规则表单的字段内容.
	 * 编辑当前模块规则时, 在保存规则之前调用此方法验证附加字段的有效性.
	 * 
	 * @param int $rid 规则编号. $rid 大于 0 为更新规则, $rid 等于 0 为新增规则.
	 * @return string 返回验证的结果, 如果为空字符串则表示验证成功, 否则返回验证失败的提示信息 
	 */
	public function fieldsFormValidate($rid = 0) {
		return '';
	}
	/**
	 * 可能需要实现的操作, 编辑当前模块规则时,在规则保存成功后后调用此方法
	 * @param int $rid 规则编号. $rid 大于 0 为更新规则, $rid 等于 0 为新增规则.
	 * @return void
	 */
	public function fieldsFormSubmit($rid) {
		//
	}
	/**
	 * 可能需要实现的操作, 在删除模块规则成功后调用此方法，做一些删除清理工作。
	 * @param int $rid 规则编号
	 * @return boolean 删除成功返回true, 否则返回false
	 */
	public function ruleDeleted($rid) {
		return true;
	}
	/**
	 * 可能需要实现的操作, 如果模块需要配置参数, 请在此方法内部处理展示和保存配置项
	 * @param array $settings 已保存的配置项数据
	 * @return void
	 */
	public function settingsDisplay($settings) {
		//
	}
}

/**
 * 模块消息处理器
 */
abstract class WeModuleProcessor extends WeBase {
	/**
	 * @var int 规则优先级(0~255)
	 */
	public $priority;
	/**
	 * @var array 预定义的消息数据结构,本次请求消息,来自粉丝用户, 此属性由系统初始化, 消息格式请参阅 "开发术语 - 消息类型"
	 */
	public $message;
	/**
	 * @var boolean 本次对话是否为上下文响应对话, 如果当前对话是由上下文锁定而路由到的. 此值为 true, 否则为 false
	 */
	public $inContext;
	/**
	 * @var int 本次请求匹配到的规则编号
	 */
	public $rule;

	public function __construct(){
		global $_W;
		
		$_W['member'] = array();
		if(!empty($_W['openid'])){
			load()->model('mc');
			$_W['member'] = mc_fetch($_W['openid']);
		}
	}
	
	/**
	 * 预定义的操作, 开始上下文会话, 可附加参数设置超时时间.
	 * @param int $expire 当前上下文的超时时间, 单位秒.
	 * @return boolean 成功启动上下文返回true, 如果当前已经在上下文环境中也会返回false
	 */
	protected function beginContext($expire = 1800) {
		if($this->inContext) {
			return true;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextmodule'] = $this->module['name'];
		$_SESSION['__contextrule'] = $this->rule;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		$_SESSION['__contextpriority'] = $this->priority;
		$this->inContext = true;
		
		return true;
	}
	/**
	 * 预定义的操作, 重置上下文过期时间
	 * @param int $expire 新的会话过期时间
	 * @return bool 成功刷新上下文返回true, 如果当前不在上下文环境中也会返回false
	 */
	protected function refreshContext($expire = 1800) {
		if(!$this->inContext) {
			return false;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		
		return true;
	}
	/**
	 * 预定义的操作, 结束上下文会话. <b>注意: 这个操作会销毁$_SESSION中的数据</b>
	 * @return void
	 */
	protected function endContext() {
		unset($_SESSION['__contextmodule']);
		unset($_SESSION['__contextrule']);
		unset($_SESSION['__contextexpire']);
		unset($_SESSION['__contextpriority']);
		unset($_SESSION);
		session_destroy();
	}
	/**
	 * 需要实现的操作, 应答此条请求. 如果响应内容为空. 将会调用优先级更低的模块, 直到默认回复为止
	 * @return array|string 返回值为消息数据结构, 或者消息xml定义
	 */
	abstract function respond();
	/**
	 * 预定义的操作, 构造返回文本消息结构
	 * @param string $content 回复的消息内容
	 * @return array 返回统一响应消息结构
	 */
	protected function respText($content) {
		if (empty($content)) {
			return error(-1, 'Invaild value');
		}
		if(stripos($content,'./') !== false) {
			preg_match_all('/<a .*?href="(.*?)".*?>/is',$content,$urls);
			if (!empty($urls[1])) {
				foreach ($urls[1] as $url) {
					$content = str_replace($url, $this->buildSiteUrl($url), $content);
				}
			}
		}
		$content = str_replace("\r\n", "\n", $content);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'text';
		$response['Content'] = htmlspecialchars_decode($content);
		preg_match_all('/\[U\+(\\w{4,})\]/i', $response['Content'], $matchArray);
		if(!empty($matchArray[1])) {
			foreach ($matchArray[1] as $emojiUSB) {
				$response['Content'] = str_ireplace("[U+{$emojiUSB}]", utf8_bytes(hexdec($emojiUSB)), $response['Content']);
			}
		}
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回图像消息结构
	 * @param string $mid 回复的图像资源ID
	 * @return array 返回的消息数组结构
	 */
	protected function respImage($mid) {
		if (empty($mid)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'image';
		$response['Image']['MediaId'] = $mid;
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回声音消息结构
	 * @param string $mid 回复的音频资源ID
	 * @return array 返回的消息数组结构
	 */
	protected function respVoice($mid) {
		if (empty($mid)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'voice';
		$response['Voice']['MediaId'] = $mid;
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回视频消息结构
	 * @param array $video 回复的视频定义(包含两个元素 video - string: 视频资源ID, thumb - string: 视频缩略图资源ID)
	 * @return array 返回的消息数组结构
	 */
	protected function respVideo(array $video) {
		if (empty($video)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'video';
		$response['Video']['MediaId'] = $video['MediaId'];
		$response['Video']['Title'] = $video['Title'];
		$response['Video']['Description'] = $video['Description'];
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回音乐消息结构
	 * @param string $music 回复的音乐定义(包含元素 title - string: 音乐标题, description - string: 音乐描述, musicurl - string: 音乐地址, hqhqmusicurl - string: 高品质音乐地址, thumb - string: 音乐封面资源ID)
	 * @return array 返回的消息数组结构
	 */
	protected function respMusic(array $music) {
		if (empty($music)) {
			return error(-1, 'Invaild value');
		}
		global $_W;
		$music = array_change_key_case($music);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'music';
		$response['Music'] = array(
			'Title' => $music['title'],
			'Description' => $music['description'],
			'MusicUrl' => tomedia($music['musicurl'])
		);
		if (empty($music['hqmusicurl'])) {
			$response['Music']['HQMusicUrl'] = $response['Music']['MusicUrl'];
		} else {
			$response['Music']['HQMusicUrl'] = tomedia($music['hqmusicurl']);
		}
		if($music['thumb']) {
			$response['Music']['ThumbMediaId'] = $music['thumb'];
		}
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回图文消息结构, 一条图文消息不能超过 10 条内容
	 * @param array $news 回复的图文定义,定义为元素集合
	 * <pre>
	 * 	array(
				'title' => '', 		// string: 新闻标题
				'picurl' => '',		// string: 新闻描述
				'url' => '',		// string: 图片链接
				'description' => ''	/string: 原文链接
			);
		</pre>
	 * @return array 返回的消息数组结构
	 */
	protected function respNews(array $news) {
		if (empty($news) || count($news) > 10) {
			return error(-1, 'Invaild value');
		}
		$news = array_change_key_case($news);
		if (!empty($news['title'])) {
			$news = array($news);
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($news);
		$response['Articles'] = array();
		foreach ($news as $row) {
			$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => ($response['ArticleCount'] > 1) ? '' : $row['description'],
				'PicUrl' => tomedia($row['picurl']),
				'Url' => $this->buildSiteUrl($row['url']),
				'TagName' => 'item'
			);
		}
		return $response;
	}

	/**
	 * 预定义的操作, 构造返回转接多客服结构
	 * @return array 返回的消息数组结构
	 */
	protected function respCustom(array $message = array()) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'transfer_customer_service';
		if (!empty($message['TransInfo']['KfAccount'])) {
			$response['TransInfo']['KfAccount'] = $message['TransInfo']['KfAccount'];
		}
		return $response;
	}

	/**
	 * 对要返回到微信端的微擎微站链接中注入身份验证信息
	 * 
	 * @param string $url 要返回的微擎链接
	 * @return string 返回注入了身份验证信息的链接
	 */
	protected function buildSiteUrl($url) {
		global $_W;
		$mapping = array(
			'[from]' => $this->message['from'],
			'[to]' => $this->message['to'],
			'[rule]' => $this->rule,
			'[uniacid]' => $_W['uniacid'],
		);
		$url = str_replace(array_keys($mapping), array_values($mapping), $url);
		if(strexists($url, 'http://') || strexists($url, 'https://')) {
			return $url;
		}
		if (uni_is_multi_acid() && strexists($url, './index.php?i=') && !strexists($url, '&j=') && !empty($_W['acid'])) {
			$url = str_replace("?i={$_W['uniacid']}&", "?i={$_W['uniacid']}&j={$_W['acid']}&", $url);
		}
		if ($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
			return $_W['siteroot'] . 'app/' . $url;
		}
		static $auth;
		if(empty($auth)){
			$pass = array();
			$pass['openid'] = $this->message['from'];
			$pass['acid'] = $_W['acid'];
			
			$sql = 'SELECT `fanid`,`salt`,`uid` FROM ' . tablename('mc_mapping_fans') . ' WHERE `acid`=:acid AND `openid`=:openid';
			$pars = array();
			$pars[':acid'] = $_W['acid'];
			$pars[':openid'] = $pass['openid'];
			$fan = pdo_fetch($sql, $pars);
			if(empty($fan) || !is_array($fan) || empty($fan['salt'])) {
				$fan = array('salt' => ''); 
			}
			$pass['time'] = TIMESTAMP;
			$pass['hash'] = md5("{$pass['openid']}{$pass['time']}{$fan['salt']}{$_W['config']['setting']['authkey']}");
			$auth = base64_encode(json_encode($pass));
		}
		
		$vars = array();
		$vars['uniacid'] = $_W['uniacid'];
		$vars['__auth'] = $auth;
		$vars['forward'] = base64_encode($url);

		return $_W['siteroot'] . 'app/' . str_replace('./', '', url('auth/forward', $vars));
	}

	/**
	 * 在 processor 中扩展 $_W 供开发者使用.
	 * 使用方式: $this->extend_W();
	 */
	protected function extend_W(){
		global $_W;
		
		if(!empty($_W['openid'])){
			load()->model('mc');
			$_W['member'] = mc_fetch($_W['openid']);
		}
		if(empty($_W['member'])){
			$_W['member'] = array();
		}
		
		if(!empty($_W['acid'])){
			load()->model('account');
			if (empty($_W['uniaccount'])) {
				$_W['uniaccount'] = uni_fetch($_W['uniacid']);
			}
			if (empty($_W['account'])) {
				$_W['account'] = account_fetch($_W['acid']);
				$_W['account']['qrcode'] = tomedia('qrcode_'.$_W['acid'].'.jpg').'?time='.$_W['timestamp'];
				$_W['account']['avatar'] = tomedia('headimg_'.$_W['acid'].'.jpg').'?time='.$_W['timestamp'];
				$_W['account']['groupid'] = $_W['uniaccount']['groupid'];
			}
		}
	}
}

/**
 * 模块订阅器
 */
abstract class WeModuleReceiver extends WeBase {
	/**
	 * @var array 预定义的数据, 本次请求的参数情况. 
	 * <pre>
	 * array(
	 * 		module - string: 模块名称, 
	 * 		rule - int: 规则编号, 
	 * 		context - bool: 是否在上下文中
	 * )
	 * </pre>
	 */
	public $params;
	/**
	 * @var array 预定义的数据, 本次请求的响应情况, 响应格式请参阅 "开发术语 - 响应类型"
	 */
	public $response;
	/**
	 * @var array 预定义的数据, 本次请求所匹配的关键字
	 */
	public $keyword;
	/**
	 * @var array 粉丝发送的数据消息
	 */
	public $message;
	/**
	 * 需要实现的操作. 处理此条请求订阅, 此方法内部的输出无效.
	 * <b>请不要调用 exit 或 die 来结束程序执行</b>.
	 * @return void
	 */
	abstract function receive();
}

/**
 * 模块微站
 */
abstract class WeModuleSite extends WeBase {
	/**
	 * @var bool 预定义的数据, 是否在移动终端
	 */
	public $inMobile;

	public function __call($name, $arguments) {
		$isWeb = stripos($name, 'doWeb') === 0;
		$isMobile = stripos($name, 'doMobile') === 0;
		if($isWeb || $isMobile) {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/';
			if($isWeb) {
				$dir .= 'web/';
				$fun = strtolower(substr($name, 5));
			}
			if($isMobile) {
				$dir .= 'mobile/';
				$fun = strtolower(substr($name, 8));
			}
			$file = $dir . $fun . '.inc.php';
			if(file_exists($file)) {
				require $file;
				exit;
			} else {
				$dir = str_replace("addons", "framework/builtin", $dir);
				$file = $dir . $fun . '.inc.php';
				if(file_exists($file)) {
					require $file;
					exit;
				}
			}
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return null;
	}

	/**
	 * 调用系统的支付功能, 只能在 Mobile 端调用
	 * @param array $params
	 * $params['tid'] 支付订单编号, 应保证在同一模块内部唯一
	 * $params['title'] 商家名称
	 * $params['fee'] 总费用, 只能大于 0
	 * $params['user'] 付款用户, 付款的用户名(选填项)
	 * @param array $mine 开发者自定义的信息（二维数组）
	 * 格式：array(array('name' => '自定义信息', 'value' => '自定义值'))；
	 * @return void
	 */
	protected function pay($params = array(), $mine = array()) {
		global $_W;
		load()->model('activity');
		activity_coupon_type_init();
		if(!$this->inMobile) {
			message('支付功能只能在手机上使用');
		}
		$params['module'] = $this->module['name'];
//		如果价格为0 直接执行模块支付回调方法
		if($params['fee'] <= 0) {
			$pars = array();
			$pars['from'] = 'return';
			$pars['result'] = 'success';
			$pars['type'] = '';
			$pars['tid'] = $params['tid'];
			$site = WeUtility::createModuleSite($pars[':module']);
			$method = 'payResult';
			if (method_exists($site, $method)) {
				exit($site->$method($pars));
			}
		}
		$log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $params['module'], 'tid' => $params['tid']));
		if (empty($log)) {
			$log = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'openid' => $_W['member']['uid'],
				'module' => $this->module['name'],
				'tid' => $params['tid'],
				'fee' => $params['fee'],
				'card_fee' => $params['fee'],
				'status' => '0',
				'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $log);
		}
		if($log['status'] == '1') {
			message('这个订单已经支付成功, 不需要重复支付.');
		}
		$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
		if(!is_array($setting['payment'])) {
			message('没有有效的支付方式, 请联系网站管理员.');
		}
		$pay = $setting['payment'];
		$cards = activity_paycenter_coupon_available();
		if (!empty($cards)) {
			foreach ($cards as $key => &$val) {
				if ($val['type'] == '1') {
					$val['discount_cn'] = sprintf("%.2f", $params['fee'] * (1 - $val['extra']['discount'] * 0.01));
					$coupon[$key] = $val;
				} else {
					$val['discount_cn'] = sprintf("%.2f", $val['extra']['reduce_cost'] * 0.01);
					$token[$key] = $val;
					if ($log['fee'] < $val['extra']['least_cost'] * 0.01) {
						unset($token[$key]);
					}
				}
				unset($val['icon']);
			}
		}
		$cards_str = json_encode($cards);
		if (empty($_W['member']['uid'])) {
			$pay['credit']['switch'] = false;
		}
		if ($params['module'] == 'paycenter') {
			$pay['delivery']['switch'] = false;
			$pay['line']['switch'] = false;
		}
		if (!empty($pay['credit']['switch'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
		}
		$you = 0;
		include $this->template('common/paycenter');
	}

	/**
	 * 这是一个回调方法, 当系统在支付完成时调用这个方法通知模块支付结果
	 * @param array $ret
	 * $ret['uniacid'] 当前公众号编号
	 * $ret['result'] 支付结果 success - 成功, 其它值失败
	 * $ret['type'] 支付方式 alipay - 支付宝, wechat - 微信支付, credit - 余额支付
	 * $ret['from'] 通知来源 notify - 后台通知(没有页面访问, 不能进行页面跳转), return - 页面通知(有用户访问, 可以进行跳转和引导)
	 * $ret['tid'] 支付订单编号
	 * $ret['user'] 支付此订单的用户
	 * $ret['fee'] 订单支付金额
	 * $ret['tag'] 订单附加信息, 根据支付类型不同, 所包含数据不同
	 * @return void
	 */
	public function payResult($ret) {
		global $_W;
		if($ret['from'] == 'return') {
			if ($ret['type'] == 'credit2') {
				message('已经成功支付', url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])));
			} else {
				message('已经成功支付', '../../' . url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])));
			}
		}
	}

	/**
	 * 查询当前模块的特定订单支付结果
	 * @param int $tid 支付订单编号
	 * @return array $ret 支付结果
	 * $ret['uniacid'] 当前公众号编号
	 * $ret['result'] 支付结果 success - 成功, 其它值失败
	 * $ret['type'] 支付方式 alipay - 支付宝, wechat - 微信支付, credit - 余额支付
	 * $ret['from'] 通知来源 notify - 后台通知(没有页面访问, 不能进行页面跳转), return - 页面通知(有用户访问, 可以进行跳转和引导)
	 * $ret['tid'] 支付订单编号
	 * $ret['user'] 支付此订单的用户
	 * $ret['fee'] 订单支付金额
	 * $ret['tag'] 订单附加信息, 根据支付类型不同, 所包含数据不同
	 */
	protected function payResultQuery($tid) {
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid';
		$params = array();
		$params[':module'] = $this->module['name'];
		$params[':tid'] = $tid;
		$log = pdo_fetch($sql, $params);
		$ret = array();
		if(!empty($log)) {
			$ret['uniacid'] = $log['uniacid'];
			$ret['result'] = $log['status'] == '1' ? 'success' : 'failed';
			$ret['type'] = $log['type'];
			$ret['from'] = 'query';
			$ret['tid'] = $log['tid'];
			$ret['user'] = $log['openid'];
			$ret['fee'] = $log['fee'];
		}
		return $ret;
	}

	/**
	 * 统一分享操作
	 * @param array $params
	 * $params['action'] 分享的操作类型或是原因
	 * $params['module'] 模块名称
	 * $params['uid'] 当前用户
	 * $params['sign'] 积分操作标志,每个模块里面唯一
	 */
	protected function share($params = array()) {
		global $_W;
		$url = murl('utility/share', array('module' => $params['module'], 'action' => $params['action'], 'sign' => $params['sign'], 'uid' => $params['uid']));
		echo <<<EOF
		<script>
			//转发成功后事件
			window.onshared = function(){
				var url = "{$url}";
				$.post(url);
			}
		</script>
EOF;
	}

	/**
	 * 统一点击操作
	 * @param array $params
	 * $params['action'] 分享的操作类型或是原因
	 * $params['module'] 模块名称
	 * $params['tuid'] 当前用户
	 * $params['fuid'] 当前用户
	 * $params['sign'] 积分操作标志,每个模块里面唯一
	 */
	protected function click($params = array()) {
		global $_W;
		$url = murl('utility/click', array('module' => $params['module'], 'action' => $params['action'], 'sign' => $params['sign'], 'tuid' => $params['tuid'], 'fuid' => $params['fuid']));
		echo <<<EOF
		<script>
			var url = "{$url}";
			$.post(url);
		</script>
EOF;
	}

}

/**
 * 模块计划任务
 */
abstract class WeModuleCron extends WeBase {
	public function __call($name, $arguments) {
		if($this->modulename == 'task') {
			$dir = IA_ROOT . '/framework/builtin/task/cron/';
		} else {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/cron/';
		}
		$fun = strtolower(substr($name, 6));
		$file = $dir . $fun . '.inc.php';
		if(file_exists($file)) {
			require $file;
			exit;
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return error(-1009, "访问的方法 {$name} 不存在.");
	}

	//记录触发记录
	public function addCronLog($tid, $errno, $note, $tag = array()) {
		global $_W;
		if(!$tid) {
			message(error(-1, 'tid参数错误'), '', 'ajax');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $this->modulename,
			'type' => $_W['cron']['filename'],
			'tid' => $tid,
			'note' => $note,
			'tag' => iserializer($tag),
			'createtime' => TIMESTAMP
		);
		pdo_insert('core_cron_record', $data);
		message(error($errno, $note), '', 'ajax');
	}
}
