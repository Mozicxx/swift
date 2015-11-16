<?php

namespace Swift;

use PDO;

class Mysql {
	protected $cmd = null;
	protected $sql = null;
	protected $frags = array ();
	protected $datas = array ();
	protected $link = null;
	protected $ds = null;
	protected $options = array ( 
			PDO::ATTR_CASE => PDO::CASE_LOWER,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false );
	protected $dsn = array ( 
			'host' => 'localhost',
			'port' => '3306',
			'socket' => '',
			'dbname' => '',
			'charset' => 'utf8',
			'user' => 'root',
			'password' => '' );
	
	/**
	 */
	public function __construct($dsn = array(), $options = array()) {
		if (is_array ( $configs )) {
			$filters = array ();
			$keys = array_keys ( $this->configs );
			foreach ( $configs as $key => $value ) {
				in_array ( $key, $keys ) && is_string ( $value ) ? $filters [$key] = $value : null;
			}
			$this->configs = array_merge ( $this->configs, $filters );
		}
		if (is_array ( $options )) {
			$this->options = $filters + $this->options;
		}
	}
	
	/**
	 */
	public function __destruct() {
		$this->free ();
		$this->close ();
	}
	
	/**
	 */
	public function __get($prop) {
		return isset ( $this->frags [$prop] ) ? $this->frags [$prop] : null;
	}
	
	/**
	 */
	public function __set($prop, $value) {
		$this->frags [$prop] = $value;
	}
	
	/**
	 */
	public function close() {
		$this->link = null;
	}
	
	/**
	 */
	public function free() {
		$this->ds = null;
	}
	
	/**
	 */
	public function work() {
		if (! $this->link) return false;
		if ($this->link->inTransaction ()) return false;
		return $this->link->beginTransaction ();
	}
	
	/**
	 */
	public function commit() {
		if (! $this->link) return false;
		if ($this->link->inTransaction ()) return $this->link->commit ();
		return false;
	}
	
	/**
	 */
	public function rollback() {
		if (! $this->link) return false;
		if ($this->link->inTransaction ()) return $this->link->rollback ();
		return false;
	}
	
	/**
	 */
	public function connect() {
		if (! isset ( $this->link )) {
			try {
				$this->link = new \PDO ( $this->dsn (), $this->configs ['user'], $this->configs ['password'], $this->configs ['options'] );
			} catch ( \PDOException $e ) {
				echo $e->getMessage (); // E() ?
			}
		}
		return $this->link;
	}
	
	/**
	 */
	protected function dsn() {
		$dsn = array ();
		if (! empty ( $this->configs ['host'] )) {
			$dsn [] = 'host=' . $this->configs ['host'];
			! empty ( $this->configs ['port'] ) ? $dsn [] = 'port=' . $this->configs ['port'] : null;
		} elseif (! empty ( $this->configs ['socket'] )) {
			$dsn [] = 'unix_socket=' . $this->configs ['socket'];
		}
		! empty ( $this->configs ['dbname'] ) ? $dsn [] = 'dbname=' . $this->configs ['dbname'] : null; // dbname='0' ?
		! empty ( $this->configs ['charset'] ) ? $dsn [] = 'charset=' . $this->configs ['charset'] : null;
		return 'mysql:' . implode ( ';', $dsn );
	}
	
	/**
	 */
	protected function sql() {
		$names = array ( 
				'distinct',
				'column',
				'table',
				'alias',
				'join',
				'where',
				'group',
				'having',
				'order',
				'limit' );
		foreach ( $names as $name ) {
			$this->$name = $this->$name ( $this->$name );
		}
	}
	
	/**
	 */
	protected function distinct($datas) {
		if (true === $datas) return 'distinct';
		return '';
	}
	
	/**
	 */
	protected function column($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				foreach ( $data as $value ) {
					if (! is_string ( $value )) continue 2;
				}
				foreach ( $data as $key => $value ) {
					$sqls [] = is_integer ( $key ) ? $value : $value . ' as ' . $key;
				}
			}
			return implode ( ',', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function alias($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
	}
	
	/**
	 */
	protected function group($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				$data = array_filter ( $data, 'is_string' );
				foreach ( $data as $key => $value ) {
					$sqls [] = is_integer ( $key ) ? $value : $key . ' ' . $value;
				}
			}
			return implode ( ',', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function having($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				foreach ( $data as $value ) {
					if (! is_string ( $value )) continue 2;
				}
				switch (count ( $data )) {
					case 3 :
						list ( $column, $condition, $logic ) = $data;
						$sqls [] = '(' . $column . $condition . ') ' . $logic;
						break;
					case 2 :
						list ( $column, $condition ) = $data;
						$sqls [] = '(' . $column . $condition . ') and';
						break;
				}
			}
			$sql = implode ( ' ', $sqls );
			return empty ( $sql ) ? '' : substr ( $sql, 0, strrpos ( $sql, ' ' ) );
		}
		return '';
	}
	
	/**
	 */
	protected function where($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				foreach ( $data as $value ) {
					if (! is_string ( $value )) continue 2;
				}
				switch (count ( $data )) {
					case 3 :
						list ( $column, $condition, $logic ) = $data;
						$sqls [] = '(' . $column . $condition . ') ' . $logic;
						break;
					case 2 :
						list ( $column, $condition ) = $data;
						$sqls [] = '(' . $column . $condition . ') and';
						break;
				}
			}
			$sql = implode ( ' ', $sqls );
			return empty ( $sql ) ? '' : substr ( $sql, 0, strrpos ( $sql, ' ' ) );
		}
		return '';
	}
	
	/**
	 */
	protected function join($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				$len = count ( $data );
				$len2 = count ( array_filter ( $data, 'is_string' ) );
				if ($len > $len2) continue;
				switch ($len) {
					case 4 :
						list ( $table, $alias, $on, $type ) = $data;
						switch ($type) {
							case 'inner' :
								$type = 'inner join';
								break;
							case 'left' :
								$type = 'left outer join';
								break;
							case 'right' :
								$type = 'right outer join';
								break;
							default :
								continue 2;
								break;
						}
						$sqls [] = $type . ' `' . $table . '` `' . $alias . '` on ' . $on;
						break;
					case 3 :
						list ( $table, $alias, $on ) = $data;
						$sqls [] = 'inner join `' . $table . '` `' . $alias . '` on ' . $on;
						break;
					case 2 :
						list ( $table, $alias ) = $datas;
						$sqls [] = 'inner join ' . $table . ' ' . $alias;
						break;
					case 1 :
						list ( $table ) = $datas;
						$sqls [] = 'inner join ' . $table;
						break;
					default :
						continue 2;
						break;
				}
			}
			return empty ( $sqls ) ? '' : implode ( ' ', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function table($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				$data = array_filter ( $data, 'is_string' );
				foreach ( $data as $value ) {
					$sqls [] = $value;
				}
			}
			return implode ( ',', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function order($datas) {
		if (is_string ( $datas ) && ! empty ( $datas )) {return 'order by ' . strtolower ( trim ( $datas ) );}
		if (is_array ( $datas ) && ! empty ( $datas )) {
			$datas = array_change_key_case ( $datas );
			$sqls = array ();
			foreach ( $datas as $key => $value ) {
				if ('asc' == $value || 'desc' == $value) {
					$sqls [] = '`' . trim ( $key ) . '` ' . strtolower ( trim ( $value ) );
				}
			}
			return 'order by ' . implode ( ', ', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function limit($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return 'limit ' . $datas;
		elseif (is_integer ( $datas )) return 'limit ' . ( int ) $datas;
		elseif (is_array ( $datas )) {
			foreach ( $datas as $value ) {
				if (! is_integer ( $value )) return '';
			}
			return 2 == count ( $datas ) ? implode ( ',', $datas ) : '';
		}
		return '';
	}
	
	/**
	 */
	public function query() {
		$this->connect ();
		if (! $this->link) return false;
		$this->sql ();
		$sql = "";
		if (! empty ( $this->ds )) $this->free ();
		$this->ds = $this->link->query ( $sql );
		if ($this->ds) {
		}
	}
	
	/**
	 */
	public function cmd($sql) {
		if (! $this->link) {return false;}
		$result = $this->link->exec ( $sql );
		if (false === $result) {
			return false;
		} else {
			$this->rows = $result;
			if (preg_match ( "/^\s*(insert\s+into|replace\s+into)\s+/i", $sql )) {
				$this->lastInsID = $this->_linkID->lastInsertId ();
			}
		}
	}
	
	/**
	 */
	protected function insert($datas) {
		if (empty ( $datas )) return false;
		elseif (is_array ( $datas )) {
			$keys = array_keys ( $datas );
			foreach ( $keys as $key ) {
				if (! is_string ( $key )) return false;
			}
			$values = array_values ( $datas );
			foreach ( $values as &$value ) {
				if (is_integer ( $value ) || is_float ( $value )) $value = $value; // Why expr=default ?
				elseif (is_string ( $value )) $value = "'" . $value . "'";
				elseif (is_bool ( $value )) $value = $value ? '1' : '0';
				elseif (is_null ( $value )) $value = 'null';
				else return false;
			}
			$keyStr = implode ( ',', $keys );
			$valueStr = implode ( ',', $values );
			
			$this->sql ();
			$sql = 'insert into ' . $this->table . '(' . $keyStr . ') values(' . $valueStr . ')';
			return $this->cmd ( $sql );
		}
		return false;
	}
	
	/**
	 */
	public function update($datas) {
		if (empty ( $datas )) return false;
		elseif (is_array ( $datas )) {
			foreach ( $datas as $key => $value ) {
				if (! is_string ( $key )) return false;
				elseif (is_integer ( $value ) || is_float ( $value )) $datasMay [] = $key . '=' . $value;
				elseif (is_string ( $value )) $datasMay [] = $key . "='" . $value . "'";
				elseif (is_bool ( $value )) $datasMay [] = $key . '=' . ($value ? '1' : '0');
				elseif (is_null ( $value )) $datasMay [] = $key . '=null';
				elseif (is_array ( $value )) { // Why expr=function(...) ?
					if (1 == count ( $value ) && is_string ( $value [0] ) && ! empty ( $value [0] )) $datasMay [] = $key . '=' . $value [0];
					else return false;
				} else
					return false;
			}
			if (empty ( $updates )) return false;
			$dataStr = implode ( ',', $datasMay );
			$sql = 'update ' . $this->table . ' set ' . $dataStr . ' ' . $this->where . ' ' . $this->order . ' ' . $this->limit;
			return $this->cmd ( $sql );
		}
		return false;
	}
	
	/**
	 */
	public function delete() {
		$this->sql ();
		$sql = 'delete from ' . $this->table . ' ' . $this->where . ' ' . $this->limit;
		return $this->cmd ( $sql );
	}
	
	/**
	 */
	public public function getTables($db = '') {
		if ('' === $db) $sql = 'show tables';
		elseif (is_string ( $db )) $sql = 'show tables from ' . $db;
		else return false;
		$result = $this->query ( $sql );
		$datas = array ();
		if ($result) {
			foreach ( $result as $row ) {
				$datas [] = current ( $row );
			}
		}
		return $datas;
	}
	
	/**
	 */
	public function getColumns($table) {
		if (empty ( $table )) return false;
		elseif (is_string ( $table )) {
			$arr = explode ( '.', $table );
			if (2 == count ( $arr )) {
				list ( $db, $table ) = $arr;
				$sql = 'show columns from `' . $db . '`.`' . $table . '`';
			} elseif (1 == count ( $arr )) {
				list ( $table ) = $arr;
				$sql = 'show columns from `' . $table . '`';
			} else
				return false;
			$result = $this->connection->query ( $sql );
			$datas = array ();
			if ($result) {
				foreach ( $result as $row ) {
					$rowData = array ();
					$row = array_change_key_case ( $row );
					foreach ( $row as $key => $value ) {
						if (is_string ( $key )) $rowData [$key] = strtolower ( $value );
					}
					$datas [$row ['field']] = $rowData;
				}
			}
			return $datas;
		}
		return false;
	}
	//
}



















