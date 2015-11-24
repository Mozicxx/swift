<?php

namespace Swift;

use PDO;

class Mysql {
	const operate_read = 0;
	const operate_write = 1;
	protected $id = 0;
	protected $sql = '';
	protected $error = '';
	protected $datas = array ();
	protected $frags = array ();
	protected $link = null;
	protected $ds = null;
	protected $options = array (
		PDO::ATTR_CASE => PDO::CASE_LOWER,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES => false 
	);
	protected $dsn = null;
	
	/**
	 */
	public function __construct( $dsn ) {
		$pattern = '/^(\w+):\/\/(\w+):(.*)@([\w.]+):(\d*)\/(\w*)#(\w*)$/'; // mysql://root:123456@localhost:3306/swift#utf8
		$reads = array ();
		$writes = array ();
		$filters = array (
			'reads',
			'writes' 
		);
		if (is_string( $dsn )) {
			preg_match( $pattern, $dsn ) ? $this->dsn = $dsn : null;
		} elseif (is_array( $dsn )) {
			foreach ( $dsn as $key => $datas ) {
				if (in_array( $key, $filters ) && is_array( $datas )) {
					$datas = array_filter( $datas, 'is_string' );
					foreach ( $datas as $data ) {
						preg_match( $pattern, $data ) ? ${$key}[] = $data : null;
					}
				}
			}
			! empty( $reads ) ? $this->dsn['read'] = $reads : null;
			! empty( $writes ) ? $this->dsn['write'] = $writes : null;
		}
	}
	
	/**
	 */
	public function __destruct() {
		$this->free();
		$this->close();
	}
	
	/**
	 */
	public function __get( $prop ) {
		return isset( $this->frags[$prop] ) ? $this->frags[$prop] : null;
	}
	
	/**
	 */
	public function __set( $prop, $value ) {
		unset( $this->frags[$prop] );
		$this->frags[$prop] = $value;
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
		if (! $this->link()) return false;
		elseif ($this->link->inTransaction()) return false;
		return $this->link->beginTransaction();
	}
	
	/**
	 */
	public function commit() {
		if (! $this->link()) return false;
		elseif ($this->link->inTransaction()) return $this->link->commit();
		return false;
	}
	
	/**
	 */
	public function rollback() {
		if (! $this->link()) return false;
		elseif ($this->link->inTransaction()) return $this->link->rollback();
		return false;
	}
	
	/**
	 */
	public function error() {
		$this->error = $this->ds ? implode( ':', $this->ds->errorInfo() ) : '';
		// E($this->error)
		return $this->error;
	}
	
	/**
	 */
	public function link( $rw ) {
		if (! $this->link) {
			if (empty( $this->dsn )) return false;
			elseif (is_string( $this->dsn )) $dsn = $this->single();
			elseif (is_array( $this->dsn )) $dsn = $this->ddb( $rw );
			else return false;
			
			if (false === $dsn) return false;
			$arr = $this->dsn( $dsn );
			if (false === $arr) return false;
			list ( $dsn, $username, $password ) = $arr;
			
			try {
				$this->link = new \PDO( $dsn, $username, $password, $this->options );
			} catch ( \PDOException $e ) {
				// E($e->getMessage())
				return false;
			}
		}
		return $this->link;
	}
	
	/**
	 */
	protected function single() {
		if (empty( $this->dsn )) return false;
		elseif (is_string( $this->dsn )) return $this->dsn;
		return false;
	}
	
	/**
	 */
	protected function ddb( $rw ) {
		if (empty( $this->dsn )) return false;
		elseif (is_array( $this->dsn )) {
			$dsn = array ();
			if (self::operate_read === $rw) {
				$dsn = isset( $this->dsn['read'] ) ? $this->dsn['read'] : array ();
			} elseif (self::operate_write === $rw) {
				$dsn = isset( $this->dsn['write'] ) ? $this->dsn['write'] : array ();
			} else
				return false;
			if (empty( $dsn )) return false;
			elseif (is_string( $dsn )) return $dsn;
			elseif (is_array( $dsn )) {
				$dsn = array_filter( $dsn, 'is_string' );
				return empty( $dsn ) ? false : $dsn[mt_rand( 0, count( $dsn ) - 1 )];
			}
			return false;
		}
		return false;
	}
	
	/**
	 */
	public function dsn( $dsn ) {
		$pattern = '/^(\w+):\/\/(\w+):(.*)@([\w.]+):(\d*)\/(\w*)#(\w*)$/'; // mysql://root:123456@localhost:3306/thinkphp#utf8
		$params = array ();
		if (! preg_match( $pattern, $dsn, $params )) return false;
		list ( $dsn, $type, $username, $password, $host, $port, $dbname, $charset ) = $params;
		$dsn1[] = 'host=' . $host;
		'' != $port ? $dsn1[] = 'port=' . $port : null;
		'' != $dbname ? $dsn1[] = 'dbname=' . $dbname : null;
		'' != $charset ? $dsn1[] = 'charset=' . $charset : null;
		return array (
			'dsn' => $type . ':' . implode( ';', $dsn1 ),
			'username' => $username,
			'password' => $password 
		);
	}
	
	/**
	 */
	protected function sql() {
		$frags = array (
			'distinct',
			'field',
			'table',
			'join',
			'where',
			'group',
			'having',
			'order',
			'limit' 
		);
		$this->frags = array ();
		foreach ( $frags as $frag ) {
			if (isset( $this->datas[$frag] )) {
				$this->$frag = $this->$frag( $this->datas[$frag] );
			}
		}
	}
	
	/**
	 */
	protected function distinct( $datas ) {
		if (is_bool( $datas )) return empty( $datas ) ? 'all' : 'distinct';
		return '';
	}
	
	/**
	 */
	protected function field( $datas ) {
		if (empty( $datas )) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				foreach ( array_filter( $data, 'is_string' ) as $key => $value ) {
					$sqls[] = is_integer( $key ) ? $value : $value . ' as ' . $key;
				}
			}
			return implode( ',', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function table( $datas ) {
		if (empty( $datas )) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				foreach ( array_filter( $data, 'is_string' ) as $value ) {
					$sqls[] = is_integer( $key ) ? $value : $value . ' ' . $key;
				}
			}
			return implode( ',', $sqls ); // from=?
		}
		return '';
	}
	
	/**
	 */
	protected function join( $datas ) {
		if (empty( $datas )) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				$len = count( $data );
				$len2 = count( array_filter( $data, 'is_string' ) );
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
						$sqls[] = $type . ' `' . $table . '` `' . $alias . '` on ' . $on;
						break;
					case 3 :
						list ( $table, $alias, $on ) = $data;
						$sqls[] = 'inner join `' . $table . '` `' . $alias . '` on ' . $on;
						break;
					case 2 :
						list ( $table, $alias ) = $datas;
						$sqls[] = 'inner join ' . $table . ' ' . $alias;
						break;
					case 1 :
						list ( $table ) = $datas;
						$sqls[] = 'inner join ' . $table;
						break;
					default :
						continue 2;
						break;
				}
			}
			return empty( $sqls ) ? '' : implode( ' ', $sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function where( $datas ) {
		if (empty( $datas ) && '0' !== $datas) return $datas;
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( $datas as $index => $data ) {
				if (! is_integer( $index )) return '';
				elseif (! is_array( $data )) return '';
				
				foreach ( $data as $key => $value ) {
					if (! is_integer( $key )) return '';
					elseif (! is_string( $value )) return '';
				}
				switch (count( $data )) {
					case 3 :
						list ( $column, $condition, $logic ) = $data;
						$sqls[] = '(' . $column . $condition . ') ' . $logic;
						break;
					case 2 :
						list ( $column, $condition ) = $data;
						$sqls[] = '(' . $column . $condition . ') and';
						break;
					default :
						return '';
						break;
				}
			}
			$sql = implode( ' ', $sqls );
			return empty( $sql ) ? '' : substr( $sql, 0, strrpos( $sql, ' ' ) );
		}
		return '';
	}
	
	/**
	 */
	protected function group( $datas ) {
		if (empty( $datas ) && '0' !== $datas) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				foreach ( array_filter( $data, 'is_string' ) as $key => $value ) {
					if (empty( $value )) continue;
					elseif (is_integer( $key )) $sqls[] = $value;
					elseif ('asc' == $value || 'desc' == $value) $sqls[] = $key . ' ' . $value;
				}
			}
			return implode( ',' . sqls );
		}
		return '';
	}
	
	/**
	 */
	protected function having( $datas ) {
		if (empty( $datas ) && '0' !== $datas) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				foreach ( $data as $value ) {
					if (! is_string( $value )) continue 2;
				}
				switch (count( $data )) {
					case 3 :
						list ( $column, $condition, $logic ) = $data;
						$sqls[] = '(' . $column . $condition . ') ' . $logic;
						break;
					case 2 :
						list ( $column, $condition ) = $data;
						$sqls[] = '(' . $column . $condition . ') and';
						break;
				}
			}
			$sql = implode( ' ', $sqls );
			return empty( $sql ) ? '' : substr( $sql, 0, strrpos( $sql, ' ' ) );
		}
		return '';
	}
	
	/**
	 */
	protected function order( $datas ) {
		if (empty( $datas ) && '0' !== $datas) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				foreach ( array_filter( $data, 'is_string' ) as $key => $value ) {
					if (empty( $value )) continue;
					elseif (is_integer( $key )) $sqls[] = $value;
					elseif ('asc' == $value || 'desc' == $value) $sqls[] = $key . ' ' . $value;
				}
			}
			return implode( ',' . sqls );
		}
		return '';
	}
	
	/**
	 */
	public function limit( $datas ) {
		if (empty( $datas )) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_integer( $datas )) return ( string ) $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $key => $value ) {
				if (! is_integer( $key )) return '';
				elseif (! is_integer( $value )) return '';
			}
			return count( $datas ) <= 2 ? implode( ',', $datas ) : '';
		}
		return '';
	}
	
	/**
	 */
	public function cmd( $sql ) {
		if (! $this->link()) return false;
		if ($this->ds) $this->free();
		$this->ds = $this->link->prepare( $this->sql() );
		if ($this->ds && $this->ds->execute()) return $this->ds->rowCount;
		return false;
	}
	
	/**
	 */
	public function query( $sql ) {
		if (! $this->link( 'read' )) return false;
		if (! $this->ds) $this->free();
		$this->ds = $this->link->prepare( $this->sql() );
		if ($this->ds && $this->ds->execute()) return $this->ds->fetchAll( PDO::FETCH_ASSOC );
		return false;
	}
	
	/**
	 */
	public function select() {
		$this->sql();
		$sql = 'select ' . $this->distinct . ' ' . $this->column . ' from ' . $this->table . ' ' . $this->join . ' ' . $this->where . ' ' . $this->group . ' ' . $this->having . ' ' . $this->order . ' ' . $this->limit;
		return $this->query( $sql );
	}
	
	/**
	 */
	protected function insert( $datas ) {
		if (empty( $datas )) return false;
		elseif (is_array( $datas )) {
			$keys = array_keys( $datas );
			foreach ( $keys as $key ) {
				if (! is_string( $key )) return false;
			}
			$values = array_values( $datas );
			foreach ( $values as &$value ) {
				if (is_integer( $value ) || is_float( $value )) $value = $value; // Why expr=default ?
				elseif (is_string( $value )) $value = "'" . $value . "'";
				elseif (is_bool( $value )) $value = $value ? '1' : '0';
				elseif (is_null( $value )) $value = 'null';
				else return false;
			}
			$keyStr = implode( ',', $keys );
			$valueStr = implode( ',', $values );
			
			$this->sql();
			$sql = 'insert into ' . $this->table . '(' . $keyStr . ') values(' . $valueStr . ')';
			return $this->cmd( $sql );
		}
		return false;
	}
	
	/**
	 */
	public function update( $datas ) {
		if (empty( $datas )) return false;
		elseif (is_array( $datas )) {
			foreach ( $datas as $key => $value ) {
				if (! is_string( $key )) return false;
				elseif (is_integer( $value ) || is_float( $value )) $datasMay[] = $key . '=' . $value;
				elseif (is_string( $value )) $datasMay[] = $key . "='" . $value . "'";
				elseif (is_bool( $value )) $datasMay[] = $key . '=' . ($value ? '1' : '0');
				elseif (is_null( $value )) $datasMay[] = $key . '=null';
				elseif (is_array( $value )) { // Why expr=function(...) ?
					if (1 == count( $value ) && is_string( $value[0] ) && ! empty( $value[0] )) $datasMay[] = $key . '=' . $value[0];
					else return false;
				} else
					return false;
			}
			if (empty( $updates )) return false;
			$dataStr = implode( ',', $datasMay );
			$sql = 'update ' . $this->table . ' set ' . $dataStr . ' ' . $this->where . ' ' . $this->order . ' ' . $this->limit;
			return $this->cmd( $sql );
		}
		return false;
	}
	
	/**
	 */
	public function delete() {
		$this->sql();
		$sql = 'delete from ' . $this->table . ' ' . $this->where . ' ' . $this->limit;
		return $this->cmd( $sql );
	}
	
	/**
	 */
	public function getTables( $db = '' ) {
		if ('' === $db) $sql = 'show tables';
		elseif (is_string( $db )) $sql = 'show tables from ' . $db;
		else return false;
		$result = $this->query( $sql );
		$datas = array ();
		if ($result) {
			foreach ( $result as $row ) {
				$datas[] = current( $row );
			}
		}
		return $datas;
	}
	
	/**
	 */
	public function getColumns( $table ) {
		if (empty( $table )) return false;
		elseif (is_string( $table )) {
			$arr = explode( '.', $table );
			if (2 == count( $arr )) {
				list ( $db, $table ) = $arr;
				$sql = 'show columns from `' . $db . '`.`' . $table . '`';
			} elseif (1 == count( $arr )) {
				list ( $table ) = $arr;
				$sql = 'show columns from `' . $table . '`';
			} else
				return false;
			$result = $this->query( $sql );
			$datas = array ();
			if ($result) {
				foreach ( $result as $row ) {
					$rowData = array ();
					foreach ( $row as $key => $value ) {
						if (is_string( $key )) $rowData[$key] = strtolower( $value );
					}
					$datas[$row['field']] = $rowData;
				}
			}
			return $datas;
		}
		return false;
	}
	
	/**
	 */
	public function lastSql() {
		return $this->sql;
	}
	
	/**
	 */
	public function lastId() {
		return $this->id;
	}
	//
}

/* */
$mysql = new Mysql( 'mysql://root:goodwin@000@localhost:3306/html#utf8' );
$datas = array (
	'name' => 333 
);
echo '[' . $mysql->limit( $datas ) . ']';






















