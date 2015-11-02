<?php

namespace Swift;

class Mysql {
	protected $cmd = null;
	protected $sql = null;
	protected $frags = array ();
	protected $datas = array ();
	protected $pool = array ();
	protected $link = null;
	protected $configs = array ( 'type' => 'mysql','user' => '','password' => '','host' => '127.0.0.1','port' => '3306','schema' => '','charset' => 'utf8','params' => array () );
	
	/**
	 */
	public function __construct($configs = null) {
		if (! is_null ( $configs )) {
			$this->configs = array_merge ( $this->configs, $configs );
			if (is_array ( $this->configs ['params'] )) {
				$this->options = $this->configs ['params'] + $this->options;
			}
		}
	}
	
	/**
	 */
	public function __get($prop) {
		return isset ( $this->datas [$prop] ) ? $this->datas [$prop] : null;
	}
	
	/**
	 */
	public function __set($prop, $value) {
		$this->frags [$prop] = $value;
	}
	
	/**
	 */
	public function connect($linkNum = 0) {
		if (! isset ( $this->pool [$linkNum] )) {
			$dsn = $this->parseDsn ();
			$this->pool [$linkNum] = new PDO ( $dsn, $this->configs ['user'], $this->configs ['password'], $this->options );
		}
		return $this->pool [$linkNum];
	}
	
	/**
	 */
	protected function dsn() {
		$dsn = 'mysql:dbname=' . $this->configs ['schema'] . ';host=' . $this->configs ['host'];
		if (! empty ( $config ['host'] )) {
			$dsn .= ';port=' . $config ['hostport'];
		} elseif (! empty ( $config ['socket'] )) {
			$dsn .= ';unix_socket=' . $config ['socket'];
		}
		
		if (! empty ( $config ['charset'] )) {
			$dsn .= ';charset=' . $config ['charset'];
		}
		return $dsn;
	}
	
	/**
	 */
	public function execute($sql) {
		if (! $this->link) {
			return false;
		}
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
	protected function sql() {
		$names = array ( 'distinct','column','table','alias','join','where','group','having','order','limit' );
		foreach ( $names as $name ) {
			$this->$name = $this->$name ( $this->$name );
		}
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
	protected function column($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				$data = array_filter ( $data, 'is_string' );
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
	protected function distinct($datas) {
		if (true === $datas) return 'distinct';
		return '';
	}
	
	/**
	 */
	protected function having($datas) {
	}
	
	/**
	 */
	protected function where($datas) {
		if (is_string ( $datas ) && '' != $datas) {
			return 'where ' . trim ( $datas );
		}
		if (is_array ( $datas ) && count ( $datas ) > 0) {
			$datas = array_change_key_case ( $datas );
			$sqls = array ();
			foreach ( $datas as $key => $value ) {
				if (is_string ( $value ) || is_numeric ( $value )) {
					$sqls [] = '(`' . $key . '`=' . (is_string ( $value ) ? "'$value'" : ( string ) $value) . ')';
				}
			}
			return count ( $sqls ) > 0 ? 'where ' . implode ( ' and ', $sqls ) : '';
		}
		return '';
	}
	
	/**
	 */
	protected function order($datas) {
		if (is_string ( $datas ) && ! empty ( $datas )) {
			return 'order by ' . strtolower ( trim ( $datas ) );
		}
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
		if (is_string ( $datas ) && ! empty ( $datas )) {
			return 'limit '.strtolower(trim($datas))
		}
		if (is_integer ( $datas )) {
			return 'limit '.$datas
		}
		if (is_array ( $datas )) {
			$datas = array_filter ( $datas, 'is_integer' );
			if (2 == count ( $datas )) {
				list ( $offset, $rows ) = $datas;
				return "limit $offset, $rows"
			}
		}
	}
	
	/**
	 */
	protected function insert($datas) {
		if (! is_array ( $datas )) {
			return false;
		}
		$table = "_table"; // ?
		$keys = $this->parseColumn ( array_keys ( $datas ) );
		$values = $this->parseValue ( array_values ( $datas ) );
		$sql = 'insert into ' . $table . '(' . $keys . ') values(' . $values . ')';
		return $this->execute ( $sql );
	}
	
	/**
	 */
	public function update($datas) {
		$this->sql ();
		$sql = "update $this->table $set $this->where $this->order $this->limit";
		return $this->execute ( $sql );
	}
	
	/**
	 */
	public function delete() {
		$table = $this->table ( $this->pieces ['table'] );
		$where = $this->where ( $this->pieces ['where'] );
		$order = $this->order ( $this->pieces ['order'] );
		$limit = $this->limit ( $this->pieces ['limit'] );
		$sql = "delete from $table $where $limit";
		return $this->execute ( $sql );
	}
	
	/**
	 */
	protected function parseColumn($datas) {
		foreach ( $datas as &$value ) {
			$value = '`' . $value . '`';
		}
		unset ( $value );
		return implode ( ',', $datas );
	}
	
	/**
	 */
	protected function parseValue($datas) {
		foreach ( $datas as &$value ) {
			if (is_string ( $value )) {
				$value = "'" . $value . "'";
			}
		}
		unset ( $value );
		return implode ( ',', $datas );
	}
	//
}



















