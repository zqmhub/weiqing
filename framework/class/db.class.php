<?php
/**
 * 数据库操作类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('PDO_DEBUG', true);

class DB {
	protected $pdo;
	protected $cfg;
	protected $tablepre;
	protected $result;
	protected $statement;
	protected $errors = array();
	protected $link = array();

	public function getPDO() {
		return $this->pdo;
	}

	public function __construct($name = 'master') {
		global $_W;
		$this->cfg = $_W['config']['db'];
		$this->connect($name);
	}

	public function connect($name = 'master') {
		if(is_array($name)) {
			$cfg = $name;
		} else {
			$cfg = $this->cfg[$name];
		}
		$this->tablepre = $cfg['tablepre'];
		if(empty($cfg)) {
			exit("The master database is not found, Please checking 'data/config.php'");
		}
		$dsn = "mysql:dbname={$cfg['database']};host={$cfg['host']};port={$cfg['port']}";
		$dbclass = '';
		$options = array();
		if (class_exists('PDO')) {
			if (extension_loaded("pdo_mysql") && in_array('mysql', PDO::getAvailableDrivers())) {
				$dbclass = 'PDO';
				$options = array(PDO::ATTR_PERSISTENT => $cfg['pconnect']);
			} else {
				if(!class_exists('_PDO')) {
					include IA_ROOT . '/framework/library/pdo/PDO.class.php';
				}
				$dbclass = '_PDO';
			}
		} else {
			include IA_ROOT . '/framework/library/pdo/PDO.class.php';
			$dbclass = 'PDO';
		}
		$this->pdo = new $dbclass($dsn, $cfg['username'], $cfg['password'], $options);
		$sql = "SET NAMES '{$cfg['charset']}';";
		$this->pdo->exec($sql);
		$this->pdo->exec("SET sql_mode='';");
		if(is_string($name)) {
			$this->link[$name] = $this->pdo;
		}
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['error'] = $this->pdo->errorInfo();
			$this->debug(false, $info);
		}
	}

	public function prepare($sql) {
		$statement = $this->pdo->prepare($sql);
		return $statement;
	}
	
	/**
	 * 执行一条非查询语句
	 *
	 * @param string $sql
	 * @param array or string $params
	 * @return mixed
	 *		  成功返回受影响的行数
	 *		  失败返回FALSE
	 */
	public function query($sql, $params = array()) {
		$starttime = microtime();
		if (empty($params)) {
			$result = $this->pdo->exec($sql);
			if(PDO_DEBUG) {
				$info = array();
				$info['sql'] = $sql;
				$info['error'] = $this->pdo->errorInfo();
				$this->debug(false, $info);
			}
			return $result;
		}
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			//更新成功后，清空缓存
			if (in_array(strtolower(substr($sql, 0, 6)), array('update', 'delete', 'insert', 'replac'))) {
				$this->cacheNameSpace($sql, true);
			}
			return $statement->rowCount();
		}
	}

	/**
	 * 执行SQL返回第一个字段
	 *
	 * @param string $sql
	 * @param array $params
	 * @param int $column 返回查询结果的某列，默认为第一列
	 * @return mixed
	 */
	public function fetchcolumn($sql, $params = array(), $column = 0) {
		$cachekey = $this->cacheKey($sql, $params);
		if (($cache = $this->cacheRead($cachekey)) !== false) {
			return $cache['data'];
		}
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			$data = $statement->fetchColumn($column);
			$this->cacheWrite($cachekey, $data);
			return $data;
		}
	}
	
	/**
	 * 执行SQL返回第一行
	 *
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function fetch($sql, $params = array()) {
		$cachekey = $this->cacheKey($sql, $params);
		if (($cache = $this->cacheRead($cachekey)) !== false) {
			return $cache['data'];
		}
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			$data = $statement->fetch(pdo::FETCH_ASSOC);
			$this->cacheWrite($cachekey, $data);
			return $data;
		}
	}

	/**
	 * 执行SQL返回全部记录
	 *
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function fetchall($sql, $params = array(), $keyfield = '') {
		$cachekey = $this->cacheKey($sql, $params);
		if (($cache = $this->cacheRead($cachekey)) !== false) {
			return $cache['data'];
		}
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			if (empty($keyfield)) {
				$result = $statement->fetchAll(pdo::FETCH_ASSOC);
			} else {
				$temp = $statement->fetchAll(pdo::FETCH_ASSOC);
				$result = array();
				if (!empty($temp)) {
					foreach ($temp as $key => &$row) {
						if (isset($row[$keyfield])) {
							$result[$row[$keyfield]] = $row;
						} else {
							$result[] = $row;
						}
					}
				}
			}
			$this->cacheWrite($cachekey, $result);
			return $result;
		}
	}
	
	public function get($tablename, $params = array(), $fields = array()) {
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		$condition = $this->implode($params, 'AND');
		$sql = "SELECT {$select} FROM " . $this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " LIMIT 1";
		return $this->fetch($sql, $condition['params']);
	}
	
	public function getall($tablename, $params = array(), $fields = array(), $keyfield = '', $orderby = array(), $limit = array()) {
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		$condition = $this->implode($params, 'AND');
		
		if (!empty($limit)) {
			if (is_array($limit)) {
				if (count($limit) == 1) {
					$limitsql = " LIMIT " . $limit[0];
				} else {
					$limitsql = " LIMIT " . ($limit[0] - 1) * $limit[1] . ', ' . $limit[1];
				}
			} else {
				$limitsql = strexists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";
			}
		}
		
		if (!empty($orderby)) {
			if (is_array($orderby)) {
				$orderbysql = implode(',', $orderbysql);
			} else {
				$orderbysql = $orderby;
			}
		}
		
		$sql = "SELECT {$select} FROM " .$this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . (!empty($orderbysql) ? " ORDER BY $orderbysql " : '') . $limitsql;
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}
	
	public function getslice($tablename, $params = array(), $limit = array(), &$total = null, $fields = array(), $keyfield = '', $orderby = array()) {
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		$condition = $this->implode($params, 'AND');
		if (!empty($limit)) {
			if (is_array($limit)) {
				$limitsql = " LIMIT " . ($limit[0] - 1) * $limit[1] . ', ' . $limit[1];
			} else {
				$limitsql = strexists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";
			}
		}
		
		if (!empty($orderby)) {
			if (is_array($orderby)) {
				$orderbysql = implode(',', $orderbysql);
			} else {
				$orderbysql = $orderby;
			}
		}
		
		$sql = "SELECT {$select} FROM " . $this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . (!empty($orderbysql) ? " ORDER BY $orderbysql " : '') . $limitsql;
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''), $condition['params']);
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}
	
	public function getcolumn($tablename, $params = array(), $field) {
		$result = $this->get($tablename, $params, array($field));
		if (!empty($result)) {
			return $result[$field];
		} else {
			return false;
		}
	}

	/**
	 * 更新记录
	 *
	 * @param string $table
	 * @param array $data
	 *		要更新的数据数组
	 *			array(
	 *				'字段名' => '值'
	 *			)
	 * @param array $params
	 *			更新条件
	 *			array(
	 *				'字段名' => '值'
	 *			)
	 * @param string $glue
	 *			可以为AND OR
	 * @return mixed
	 */
	public function update($table, $data = array(), $params = array(), $glue = 'AND') {
		$fields = $this->implode($data, ',');
		$condition = $this->implode($params, $glue);
		$params = array_merge($fields['params'], $condition['params']);
		$sql = "UPDATE " . $this->tablename($table) . " SET {$fields['fields']}";
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $params);
	}

	/**
	 * 更新记录
	 *
	 * @param string $table
	 * @param array $data
	 *		要更新的数据数组
	 *		array(
	 *			'字段名' => '值'
	 *		)
	 * @param boolean $replace
	 *		是否执行REPLACE INTO
	 *		默认为FALSE
	 * @return mixed
	 */
	public function insert($table, $data = array(), $replace = FALSE) {
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$condition = $this->implode($data, ',');
		return $this->query("$cmd " . $this->tablename($table) . " SET {$condition['fields']}", $condition['params']);
	}
	
	/**
	 * 返回lastInsertId
	 *
	 */
	public function insertid() {
		return $this->pdo->lastInsertId();
	}

	/**
	 * 删除记录
	 *
	 * @param string $table
	 * @param array $params
	 *		更新条件
	 *		array(
	 *			'字段名' => '值'
	 *		)
	 * @param string $glue
	 *		可以为AND OR
	 * @return mixed
	 */
	public function delete($table, $params = array(), $glue = 'AND') {
		$condition = $this->implode($params, $glue);
		$sql = "DELETE FROM " . $this->tablename($table);
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $condition['params']);
	}

	/**
	 * 启动一个事务，关闭自动提交
	 *
	 */
	public function begin() {
		$this->pdo->beginTransaction();
	}

	/**
	 * 提交一个事务，恢复自动提交
	 * @return boolean
	 */
	public function commit() {
		$this->pdo->commit();
	}

	/**
	 * 回滚一个事务，恢复自动提交
	 * @return boolean
	 */
	public function rollback() {
		$this->pdo->rollBack();
	}

	/**
	 * 将数组格式化为具体的字符串
	 * 增加支持 大于 小于, 不等于, not in, +=, -=等操作符
	 * 
	 * @param array $params
	 * 		要格式化的数组
	 * @param string $glue
	 * 		字符串分隔符
	 * @return array
	 * 		array['fields']是格式化后的字符串
	 */
	private function implode($params, $glue = ',') {
		$result = array('fields' => ' 1 ', 'params' => array());
		$split = '';
		$suffix = '';
		$allow_operator = array('>', '<', '<>', '!=', '>=', '<=', '+=', '-=', 'LIKE', 'like');
		if (in_array(strtolower($glue), array('and', 'or'))) {
			$suffix = '__';
		}
		if (!is_array($params)) {
			$result['fields'] = $params;
			return $result;
		}
		if (is_array($params)) {
			$result['fields'] = '';
			foreach ($params as $fields => $value) {
				$operator = '';
				if (strpos($fields, ' ') !== FALSE) {
					list($fields, $operator) = explode(' ', $fields, 2);
					if (!in_array($operator, $allow_operator)) {
						$operator = '';
					}
				}
				if (empty($operator)) {
					$fields = trim($fields);
					if (is_array($value)) {
						$operator = 'IN';
					} else {
						$operator = '=';
					}
				} elseif ($operator == '+=') {
					$operator = " = `$fields` + ";
				} elseif ($operator == '-=') {
					$operator = " = `$fields` - ";
				}
				if (is_array($value)) {
					$insql = array();
					foreach ($value as $k => $v) {
						$insql[] = ":{$suffix}{$fields}_{$k}";
						$result['params'][":{$suffix}{$fields}_{$k}"] = is_null($v) ? '' : $v;
					}
					$result['fields'] .= $split . "`$fields` {$operator} (".implode(",", $insql).")";
					$split = ' ' . $glue . ' ';
				} else {
					$result['fields'] .= $split . "`$fields` {$operator}  :{$suffix}$fields";
					$split = ' ' . $glue . ' ';
					$result['params'][":{$suffix}$fields"] = is_null($value) ? '' : $value;
				}
			}
		}
		return $result;
	}
	
	/**
	 * 执行SQL文件
	 */
	public function run($sql, $stuff = 'ims_') {
		if(!isset($sql) || empty($sql)) return;

		$sql = str_replace("\r", "\n", str_replace(' ' . $stuff, ' ' . $this->tablepre, $sql));
		$sql = str_replace("\r", "\n", str_replace(' `' . $stuff, ' `' . $this->tablepre, $sql));
		$ret = array();
		$num = 0;
		$sql = preg_replace("/\;[ \f\t\v]+/", ';', $sql);
		foreach(explode(";\n", trim($sql)) as $query) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			foreach($queries as $query) {
				$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
			}
			$num++;
		}
		unset($sql);
		foreach($ret as $query) {
			$query = trim($query);
			if($query) {
				$this->query($query, array());
			}
		}
	}
	
	/**
	 * 查询字段是否存在
	 * 成功返回TRUE，失败返回FALSE
	 * 
	 * @param string $tablename
	 * 		查询表名
	 * @param string $fieldname
	 * 		查询字段名
	 * @return boolean
	 */
	public function fieldexists($tablename, $fieldname) {
		$isexists = $this->fetch("DESCRIBE " . $this->tablename($tablename) . " `{$fieldname}`", array());
		return !empty($isexists) ? true : false;
	}
	
	/**
	 * 查询索引是否存在
	 * 成功返回TRUE，失败返回FALSE
	 * @param string $tablename
	 * 		查询表名
	 * @param array $indexname
	 * 		查询索引名
	 * @return boolean
	 */
	public function indexexists($tablename, $indexname) {
		if (!empty($indexname)) {
			$indexs = $this->fetchall("SHOW INDEX FROM " . $this->tablename($tablename), array(), '');
			if (!empty($indexs) && is_array($indexs)) {
				foreach ($indexs as $row) {
					if ($row['Key_name'] == $indexname) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * 返回完整数据表名(加前缀)(返回是主库的数据表前缀+表明)
	 * @param string $table 表名
	 * @return string
	 */
	public function tablename($table) {
		return "`{$this->tablepre}{$table}`";
	}

	/**
	 * 获取pdo操作错误信息列表
	 * @param bool $output 是否要输出执行记录和执行错误信息
	 * @param array $append 加入执行信息，如果此参数不为空则 $output 参数为 false
	 * @return array
	 */
	public function debug($output = true, $append = array()) {
		if(!empty($append)) {
			$output = false;
			array_push($this->errors, $append);
		}
		if($output) {
			print_r($this->errors);
		} else {
			if (!empty($append['error'][1])) {
				$traces = debug_backtrace();
				$ts = '';
				foreach($traces as $trace) {
					$trace['file'] = str_replace('\\', '/', $trace['file']);
					$trace['file'] = str_replace(IA_ROOT, '', $trace['file']);
					$ts .= "file: {$trace['file']}; line: {$trace['line']}; <br />";
				}
				$params = var_export($append['params'], true);
				if (!function_exists('message')) {
					load()->web('common');
					load()->web('template');
				}
				message("SQL: <br/>{$append['sql']}<hr/>Params: <br/>{$params}<hr/>SQL Error: <br/>{$append['error'][2]}<hr/>Traces: <br/>{$ts}");
			}
		}
		return $this->errors;
	}

	/**
	 * 判断某个数据表是否存在
	 * @param string $table 表名（不加表前缀）
	 * @return bool
	 */
	public function tableexists($table) {
		if(!empty($table)) {
			$data = $this->fetch("SHOW TABLES LIKE '{$this->tablepre}{$table}'", array());
			if(!empty($data)) {
				$data = array_values($data);
				$tablename = $this->tablepre . $table;
				if(in_array($tablename, $data)) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function performance($sql, $runtime = 0) {
		global $_W;
		if ($runtime == 0) {
			return false;
		}
		if (strexists($sql, 'core_performance')) {
			return false;
		}
		//将超时SQL语句存入数据库
		if (empty($_W['config']['setting']['maxtimesql'])) {
			$_W['config']['setting']['maxtimesql'] = 5;
		}
		if ($runtime > $_W['config']['setting']['maxtimesql']) {
			$sqldata = array(
				'type' => '2',
				'runtime' => $runtime,
				'runurl' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'runsql' => $sql,
				'createtime' => time()
			);
			$this->insert('core_performance', $sqldata);
		}
		return true;
	}
	
	private function cacheRead($cachekey) {
		global $_W;
		if (empty($cachekey) || $_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$data = cache_read($cachekey, true);
		if (empty($data) || empty($data['data'])) {
			return false;
		}
		return $data;
	}
	
	private function cacheWrite($cachekey, $data) {
		global $_W;
		if (empty($data) || empty($cachekey) || $_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$cachedata = array(
			'data' => $data,
			'expire' => TIMESTAMP + 2592000,
		);
		cache_write($cachekey, $cachedata, 0, true);
		return true;
	}
	
	private function cacheKey($sql, $params) {
		global $_W;
		if ($_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$namespace = $this->cacheNameSpace($sql);
		if (empty($namespace)) {
			return false;
		}
		return $namespace . ':' . md5($sql . serialize($params));
	}
	
	/**
	 * SQL缓存以表为为单位增加缓存命名空间，当更新、删除或是插入语句时批量删除此表的缓存
	 * @param string $sql
	 * @param boolean $forcenew 是否强制更新命名空间
	 */
	private function cacheNameSpace($sql, $forcenew = false) {
		global $_W;
		if ($_W['config']['setting']['cache'] != 'memcache') {
			return false;
		}
		//获取SQL中的表名
		$table_prefix = str_replace('`', '', tablename(''));
		preg_match('/(?!from|insert into|replace into|update) `?('.$table_prefix.'[a-zA-Z0-9_-]+)/i', $sql, $match);
		$tablename = $match[1];
		
		if (empty($tablename)) {
			return false;
		}
		
		//获取命名空间
		$namespace = cache_read('dbcache:namespace:'.$tablename, true);
		if (empty($namespace) || $forcenew) {
			$namespace = TIMESTAMP;
			cache_write('dbcache:namespace:'.$tablename, $namespace, 0, true);
		}
		return $tablename . ':' . $namespace;
	}
}
