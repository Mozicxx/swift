<?php

namespace Swift;

use PDO;

class Mysql {
	const operate_read = 'read';
	const operate_write = 'write';
	protected $id = 0;
	protected $sql = '';
	protected $error = '';
	protected $datas = array();
	protected $frags = array();
	protected $link = null;
	protected $ds = null;
	protected $options = array( PDO::ATTR_CASE => PDO::CASE_LOWER, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, PDO::ATTR_STRINGIFY_FETCHES => false );
	protected $errConfigs = array();
	protected $configs = array();
	
	/**
	 * void public function __construct(array $configs)
	 */
	public function __construct($configs) {
		$this->configs=$configs
	}
	
	/**
	 * void public function __destruct(void)
	 */
	public function __destruct() {
		$this->free();
		$this->close();
	}
	
	/**
	 * array protected function dsn()
	 */
	protected function dsn($rw) {
		foreach ( $this->configs as $key=>$config ) {
			if (in_array( $config ['operate'], array( $rw, self::operate_both ) )) $configs [] = $config;
		}
		if (isset( $configs ) && $configs) $num = mt_rand( 0, count( $configs ) - 1 );
		else return array();
		extract( $configs [$num] );
		$dsnConfigs = array( 'host=' . $host, 'port=' . ( string ) $port, 'dbname=' . $dbname, 'charset=' . $charset );
		return array( $type . ':' . implode( ';', $dsnConfigs ), $username, $password );
	}
	
	/**
	 * boolean protected function link(string $rw);
	 */
	protected function link($rw) {
		if (! $this->link) {
			$configs = $this->dsn( $rw );
			if ($configs) list ( $dsn, $username, $password ) = $configs;
			else return false;
			try {
				$this->link = new \PDO( $dsn, $username, $password, $this->options );
			} catch ( \PDOException $e ) {
				// E($e->getMessage())
				return false;
			}
		}
		return true;
	}
	
	/**
	 * void public function close(void)
	 */
	public function close() {
		$this->link = null;
	}
	
	/**
	 * void public function free(void)
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
	 * void protected function sql(void);
	 */
	protected function sql() {
		$frags = array( 'distinct', 'field', 'table', 'join', 'where', 'group', 'having', 'order', 'limit' );
		$this->frags = array();
		foreach ( $frags as $frag ) {
			if (isset( $this->datas [$frag] )) {
				$this->$frags [$frag] = $this->$frag( $this->datas [$frag] );
			}
		}
	}
	
	/**
	 * string protected function distinct(boolean $datas)
	 */
	protected function distinct($datas) {
		if (is_bool( $datas )) return empty( $datas ) ? 'all' : 'distinct';
		return '';
	}
	
	/**
	 * string protected function filed(array $datas=array([string $alias=>]string $field,...))
	 * string protected function field(string $datas)
	 */
	protected function field($datas) {
		if (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $key => $value ) {
				$sqls [] = is_integer( $key ) ? $value : $key . ' as ' . $value;
			}
			return implode( ',', $sqls );
		}
		return '';
	}
	
	/**
	 * string protected function table(array $datas=array([string $alias=>]string $table,...))
	 * string protected function table(string $datas)
	 */
	protected function table($datas) {
		if (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $key => $value ) {
				$sqls [] = is_integer( $key ) ? $value : $value . ' ' . $key;
			}
			return implode( ',', $sqls );
		}
		return '';
	}
	
	/**
	 * string protected function join(array $datas=array(array([string $alias=>]string $r.field ,[string $operator=>]string $l.field [,string $type]),...)
	 * string protected function join(string $datas)
	 */
	protected function join($datas) {
		if (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $data ) {
				list ( $alias, $operator ) = array_keys( $data );
				if (count( $data ) == 3) list ( $rfield, $lfield, $type ) = array_values( $data );
				elseif (count( $data ) == 2) list ( $rfield, $lfield, $type ) = array_merge( array_values( $data ), 'inner' );
				$types = array( 'inner' => 'inner join', 'left' => 'left outer join', 'right' => 'right outer join' );
				$type = $types [$type];
				$operators = array( 'eq' => '=', 'neq' => '!=' );
				$operator = $operators [$operator];
				list ( $r, $field ) = explode( '.', $rfield );
				if (! is_string( $alias )) $rfield = $alias . '.' . $filed;
				$sqls [] = $type . ' ' . $r . ' ' . $alias . ' on ' . $lfield . $operator . $rfield;
			}
			return implode( ' ', $sqls );
		}
		return '';
	}
	
	/**
	 * string protected function where(array $datas=array(array([string $logic=>]string $field, [string $operator=>] scalar|array $require),...))
	 * stirng protected function where(string $datas)
	 */
	protected function where($datas) {
		if (is_string( $datas )) return 'where ' . $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $data ) {
				list ( $logic, $operator ) = array_keys( $data );
				list ( $field, $require ) = array_values( $data );
				is_integer( $logic ) ? $logic = 'and' : null;
				is_integer( $operator ) ? $operator = 'eq' : null;
				$field = $this->backquote( $field );
				switch ($operator) {
					case 'eq' :
						if (is_integer( $require ) or is_float( $require )) $require = ( string ) $require;
						elseif (is_string( $require )) $require = "'$require'";
						elseif (is_bool( $require )) $require = empty( $require ) ? '0' : '1';
						elseif (is_null( $require )) $require = 'null';
						$sqls [] = $field . '=' . $require . ' ' . $logic;
						break;
					case 'neq' :
						if (is_integer( $require ) or is_float( $require )) $require = ( string ) $require;
						elseif (is_string( $require )) $require = "'$require'";
						elseif (is_bool( $require )) $require = empty( $require ) ? '0' : '1';
						elseif (is_null( $require )) $require = 'null';
						$sqls [] = $field . '!=' . $require . ' ' . $logic;
						break;
				}
			}
			$sql = implode( ' ', $sqls );
			return 'where ' . substr( $sql, 0, strrpos( $sql, ' ' ) );
		}
		return '';
	}
	
	/**
	 * string protected function group(array $datas=array(string $field[=>string $type],...)
	 * string protected function group(string $datas)
	 */
	protected function group($datas) {
		if (is_string( $datas )) return 'group by ' . $datas;
		elseif (is_array( $datas )) {
			foreach ( $data as $key => $value ) {
				$sqls [] = is_integer( $key ) ? $value : $key . ' ' . $value;
			}
			return 'group by ' . implode( ',', $sqls );
		}
		return '';
	}
	
	/**
	 * string protected function having(array $datas=array(array([string $logic=>]string $field, [string $operator=>] scalar|array $require),...))
	 * stirng protected function having(string $datas)
	 */
	protected function having($datas) {
		if (is_string( $datas )) return 'having ' . $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $data ) {
				list ( $logic, $operator ) = array_keys( $data );
				list ( $field, $require ) = array_values( $data );
				is_integer( $logic ) ? $logic = 'and' : null;
				is_integer( $operator ) ? $operator = 'eq' : null;
				$field = $this->backquote( $field );
				switch ($operator) {
					case 'eq' :
						if (is_integer( $require ) or is_float( $require )) $require = ( string ) $require;
						elseif (is_string( $require )) $require = "'$require'";
						elseif (is_bool( $require )) $require = empty( $require ) ? '0' : '1';
						elseif (is_null( $require )) $require = 'null';
						$sqls [] = $field . '=' . $require . ' ' . $logic;
						break;
					case 'neq' :
						if (is_integer( $require ) or is_float( $require )) $require = ( string ) $require;
						elseif (is_string( $require )) $require = "'$require'";
						elseif (is_bool( $require )) $require = empty( $require ) ? '0' : '1';
						elseif (is_null( $require )) $require = 'null';
						$sqls [] = $field . '!=' . $require . ' ' . $logic;
						break;
				}
			}
			$sql = implode( ' ', $sqls );
			return 'having ' . substr( $sql, 0, strrpos( $sql, ' ' ) );
		}
		return '';
	}
	
	/**
	 * string protected function order(array $datas=array(string $field[=>string $type],...))
	 * string protected function order(string $datas)
	 */
	protected function order($datas) {
		if (is_string( $datas )) return 'order by ' . $datas;
		elseif (is_array( $datas )) {
			foreach ( $data as $key => $value ) {
				$sqls [] = is_integer( $key ) ? $value : $key . ' ' . $value;
			}
			return 'order by ' . implode( ',', $sqls );
		}
		return '';
	}
	
	/**
	 * string protected function limit(array $datas=array(integer $offset, integer $row))
	 * string protected function limit(integer $datas)
	 * string protected function limit(string $datas)
	 */
	protected function limit($datas) {
		if (is_string( $datas )) return 'limit ' . $datas;
		elseif (is_integer( $datas )) return 'limit ' . ( string ) $datas;
		elseif (is_array( $datas )) return 'limit ' . implode( ',', $datas );
		return '';
	}
	
	/**
	 * bool|int public function cmd(str $sql)
	 */
	public function cmd($sql) {
		if (is_string( $sql )) {
			$this->sql = $sql;
			if (! $this->link( self::operate_write )) return false;
			if ($this->ds) $this->free();
			$this->ds = $this->link->prepare( $this->sql );
			if ($this->ds && $this->ds->execute()) return $this->ds->rowCount;
		}
		return false;
	}
	
	/**
	 * bool|array public function query(str $sql)
	 */
	public function query($sql) {
		if (is_string( $sql )) {
			$this->sql = $sql;
			if (! $this->link( self::operate_read )) return false;
			if (! $this->ds) $this->free();
			$this->ds = $this->link->prepare( $this->sql );
			if ($this->ds && $this->ds->execute()) return $this->ds->fetchAll( PDO::FETCH_ASSOC );
		}
		return false;
	}
	
	/**
	 * bool|array public function select(void)
	 */
	public function select() {
		$this->sql();
		$sqls = array( 'select', $this->distinct, $this->column, 'from', $this->table, $this->join, $this->where, $this->group, $this->having, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->query( implode( ' ', $sqls ) );
	}
	
	/**
	 * bool|int public function insert(array $datas=array(str $field=>int|float|bool|null $value,...))
	 */
	public function insert($datas) {
		if (empty( $datas )) return false;
		elseif (is_array( $datas )) {
			foreach ( $datas as $key => &$value ) {
				if (! is_string( $key )) return false;
				elseif (is_integer( $value ) || is_float( $value )) $value = $value; // expr=default ?
				elseif (is_string( $value )) $value = "'" . $value . "'";
				elseif (is_bool( $value )) $value = $value ? '1' : '0';
				elseif (is_null( $value )) $value = 'null';
				elseif (is_array( $value )) { // expr=function(...) or expr=default ?
					if (1 == count( $value ) && is_string( $value [0] ) && ! empty( $value [0] )) {
						$copy = $value [0];
						$value = array(); // unset($value) ?
						$value = $copy;
					} else
						return false;
				} else
					return false;
			}
			$keyStr = implode( ',', array_keys( $datas ) );
			$valueStr = implode( ',', array_values( $datas ) );
			
			$this->sql();
			$this->sql = 'insert into ' . $this->table . '(' . $keyStr . ') values(' . $valueStr . ')';
			return $this->cmd( $this->sql );
		}
		return false;
	}
	
	/**
	 * bool|int public function update(array $datas=array(str $filed=>mixed $value,...))
	 */
	public function update($datas) {
		if (is_array( $datas )) {
			foreach ( $datas as $key => &$value ) {
				if (! is_string( $key )) return false;
				elseif (is_integer( $value ) || is_float( $value )) $value = $key . '=' . $value;
				elseif (is_string( $value )) $value = $key . "='" . $value . "'";
				elseif (is_bool( $value )) $value = $key . '=' . $value ? '1' : '0';
				elseif (is_null( $value )) $value = $key . '=null';
				elseif (is_array( $value )) { // expr=function(...) or expr=default ?
					if (1 == count( $value ) && is_string( $value [0] ) && ! empty( $value [0] )) {
						$copy = $value [0];
						$value = array(); // unset($value) ?
						$value = $key . '=' . $copy;
					} else
						return false;
				} else
					return false;
			}
			$dataStr = implode( ',', $datas );
			$this->sql();
			$sqls = array( 'update', $this->table, 'set', $dataStr, $this->where, $this->order, $this->limit );
			$sqls = array_filter( $sqls, 'strlen' );
			return $this->cmd( implode( ' ', $sqls ) );
		}
		return false;
	}
	
	/**
	 * bool|int public function delete(void)
	 */
	public function delete() {
		$this->sql();
		$sqls = array( 'delete from', $this->table, $this->where, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * bool|array public function tables(void)
	 */
	public function tables() {
		$sql = 'show tables';
		$arr = $this->query( $sql );
		if (false === $arr) return false;
		$datas = array();
		foreach ( $arr as $row ) {
			$datas [] = current( $row );
		}
		return $datas;
	}
	
	/**
	 * boolean|array public function fields(string $table)
	 */
	public function fields($table) {
		if (empty( $table )) return false;
		elseif (is_string( $table )) {
			$sql = 'show columns from `' . $table . '`';
			$arr = $this->query( $sql );
			if (false === $arr) return false;
			$datas = array();
			foreach ( $arr as $row ) {
				$key = current( $row );
				$datas [$key] = array_map( 'strtolower', $row );
			}
			return $datas;
		}
		return false;
	}
	
	/**
	 * str public function lastSql(void)
	 */
	public function lastSql() {
		return $this->sql;
	}
	
	/**
	 * int public function lastId(void)
	 */
	public function lastId() {
		return $this->id;
	}
	
	/**
	 * array public function map(void)
	 */
	public function map() {
		return $maps = array( 'string' => array( 'char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'tinytext', 'text', 'mediumtext', 'longtext', 'date', 'datetime', 'timestamp', 'time', 'year', 'bit' ), 'integer' => array( 'tinyint', 'smallint', 'int', 'mediumint', 'bigint' ), 'float' => array( 'decimal', 'float', 'double' ), 'boolean' => array( 'bool' ), 'null' => array() );
	}
	
	/**
	 * string backquote(string $data)
	 */
	protected function backquote($data) {
		if (is_string( $data ) && $data != '') {
			$group = explode( '.', $data );
			foreach ( $group as &$value ) {
				$value = '`' . $value . '`';
			}
			return implode( '.', $group );
		}
		return '';
	}
	//
}



