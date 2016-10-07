<?php
/**
 * WeSession类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 微擎系统可以将不同公众平台的公众号或同一平台的不同公众号集成为一个 <b>【统一公众号】</b> 来进行管理.
 * 微擎系统将粉丝用户与统一公众号之间的上下文对话定义为一个会话(WeSession).
 */
class WeSession {
	/**
	 * 微擎系统的统一公众号编号
	 * @var int
	 */
	public static $uniacid;
	/**
	 * 用户对应公众平台的唯一标识
	 * @var string
	 */
	public static $openid;
	/**
	 * 会话过期时间
	 * @var int
	 */
	public static $expire;

	/**
	 * 开启粉丝用户与统一公众号之间会话
	 * @param int $uniacid 统一公众号ID
	 * @param string $openid 用户对应平台的唯一标识
	 * @param int $expire 过期时间,单位秒.
	 */
	public static function start($uniacid, $openid, $expire = 3600) {
		if (empty($GLOBALS['_W']['config']['setting']['memcache']['session']) || empty($GLOBALS['_W']['config']['setting']['memcache']['server'])) {
			WeSession::$uniacid = $uniacid;
			WeSession::$openid = $openid;
			WeSession::$expire = $expire;
			$sess = new WeSession();
			session_set_save_handler(
				array(&$sess, 'open'),
				array(&$sess, 'close'),
				array(&$sess, 'read'),
				array(&$sess, 'write'),
				array(&$sess, 'destroy'),
				array(&$sess, 'gc')
			);
			register_shutdown_function('session_write_close');
		}
		session_start();
	}

	public function open() {
		return true;
	}

	public function close() {
		return true;
	}

	/**
	 * 读取指定微擎会话中保存的信息
	 * @param string $sessionid 微擎会话标识
	 * @return array|boolean 会话存在、未失效且在 data 字段存在附加数据则返回该附加数据，否则返回false
	 */
	public function read($sessionid) {
		$sql = 'SELECT * FROM ' . tablename('core_sessions') . ' WHERE `sid`=:sessid AND `expiretime`>:time';
		$params = array();
		$params[':sessid'] = $sessionid;
		$params[':time'] = TIMESTAMP;
		$row = pdo_fetch($sql, $params);
		if(is_array($row) && !empty($row['data'])) {
			return $row['data'];
		}
		return false;
	}

	/**
	 * 将信息写入到指定的微擎会话中.
	 * @param string $sessionid 微擎会话标识
	 * @param string|number $data 附加数据
	 * @return boolean 返回写入操作是否成功.
	 */
	public function write($sessionid, $data) {
		$row = array();
		$row['sid'] = $sessionid;
		$row['uniacid'] = WeSession::$uniacid;
		$row['openid'] = WeSession::$openid;
		$row['data'] = $data;
		$row['expiretime'] = TIMESTAMP + WeSession::$expire;
		return pdo_insert('core_sessions', $row, true) >= 1;
	}

	/**
	 * 销毁指定微擎会话
	 * @param string $sessionid 微擎会话标识
	 * @return boolean 返回销毁会话是否成功
	 */
	public function destroy($sessionid) {
		$row = array();
		$row['sid'] = $sessionid;

		return pdo_delete('core_sessions', $row) == 1;
	}

	/**
	 * 清理微擎系统中所有过期会话
	 * @param int $expire 指定要清理的过期日期时间戳
	 * @return boolean 清理会话是否成功
	 */
	public function gc($expire) {
		$sql = 'DELETE FROM ' . tablename('core_sessions') . ' WHERE `expiretime`<:expire';

		return pdo_query($sql, array(':expire' => TIMESTAMP)) == 1;
	}
}