<?php

namespace Swift;

use PDO;

class Mysql {
	const operate_read = 'read';
	const operate_write = 'write';
	const operate_both = 'both';
	protected $id = 0;
	protected $sql = '';
	protected $error = '';
	protected $datas = array();
	protected $frags = array();
	protected $links = array( 'read' => null, 'write' => null );
	protected $ds = null;
	protected $options = array( PDO::ATTR_CASE => PDO::CASE_LOWER, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, PDO::ATTR_STRINGIFY_FETCHES => false );
	protected $configs = array();
	
	/**
	 * void public function __construct(array $configs)
	 */
	public function __construct($configs) {
		$this->configs = $configs;
	}
	
	/**
	 * string public function __get(string $prop)
	 */
	public function __get($prop) {
		return isset( $this->frags [$prop] ) ? $this->frags [$prop] : '';
	}
	
	/**
	 * void public function __destruct(void)
	 */
	public function __destruct() {
		$this->free();
		$this->close();
	}
	
	/**
	 * array protected function dsn(string $rw)
	 */
	protected function dsn($rw) {
		$configs = array();
		foreach ( $this->configs as $index => $config ) {
			if (in_array( $config ['operate'], array( $rw, self::operate_both ) ) && ! in_array( $key, $this->errConfigs )) $configs [$index] = $config;
		}
		if ($configs) {
			$key = array_rand( $configs );
			extract( $configs [$key] );
			$dsn = array( 'host=' . $host, 'port=' . ( string ) $port, 'dbname=' . $dbname, 'charset=' . $charset );
			return array( $key, $type . ':' . implode( ';', $dsn ), $username, $password );
		}
		return array();
	}
	
	/**
	 * boolean protected function link(string $rw);
	 */
	protected function link($rw) {
		if ($this->link [$rw]) return true;
		$config = $this->dsn( $rw );
		if ($config) list ( $key, $dsn, $username, $password ) = $config;
		else return false;
		try {
			$this->link [$rw] = new \PDO( $dsn, $username, $password, $this->options );
			$this->errConfigs = array();
			return true;
		} catch ( \PDOException $e ) {
			// E($e->getMessage())
			$this->errConfigs [] = $key;
			return $this->link( $rw );
		}
	}
	
	/**
	 * void public function close(void)
	 */
	public function close() {
		$this->links = array( 'read' => null, 'write' => null );
	}
	
	/**
	 * void public function free(void)
	 */
	public function free() {
		$this->ds = null;
	}
	
	/**
	 * boolean public function work(void);
	 */
	public function work() {
		if (! $this->link( self::operate_write )) return false;
		elseif ($this->links [self::operate_write]->inTransaction()) return false;
		return $this->links [self::operate_write]->beginTransaction();
	}
	
	/**
	 * boolean public function commit(void)
	 */
	public function commit() {
		if (! $this->links [self::operate_write]) return false;
		elseif (! $this->links [self::operate_write]->inTransaction()) return false;
		return $this->links [self::operate_write]->commit();
	}
	
	/**
	 * boolean public function rollback();
	 */
	public function rollback() {
		if (! $this->links [self::operate_write]) return false;
		elseif (! $this->links [self::operate_write]->inTransaction()) return false;
		return $this->links [self::operate_write]->rollback();
	}
	
	/**
	 * string public function error(void)
	 */
	public function error() {
		$this->error = $this->ds ? implode( ':', $this->ds->errorInfo() ) : '';
		return $this->error;
	}
	
	/**
	 * void protected function sql(void);
	 */
	protected function sql() {
		$keys = array( 'distinct', 'field', 'table', 'join', 'where', 'group', 'having', 'order', 'limit' );
		$this->frags = array();
		foreach ( $keys as $key ) {
			if (isset( $this->datas [$key] )) {
				$this->frags [$key] = $this->$key( $this->datas [$key] );
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
				if (! is_string( $alias )) $rfield = $alias . '.' . $field;
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
	 * boolean|integer public function cmd(string $sql)
	 */
	public function cmd($sql) {
		$this->sql = $sql;
		if ($this->link( self::operate_write )) {
			if ($this->ds) $this->free();
			$this->ds = $this->link->prepare( $this->sql );
			if ($this->ds && $this->ds->execute()) return $this->ds->rowCount;
		}
		return false;
	}
	
	/**
	 * boolean|array public function query(string $sql)
	 */
	public function query($sql) {
		$this->sql = $sql;
		if ($this->link( self::operate_read )) {
			if ($this->ds) $this->free();
			$this->ds = $this->link->prepare( $this->sql );
			if ($this->ds && $this->ds->execute()) return $this->ds->fetchAll( PDO::FETCH_ASSOC );
		}
		return false;
	}
	
	/**
	 * boolean|array public function select(void)
	 */
	public function select() {
		$this->sql();
		$sqls = array( 'select', $this->distinct, $this->column, 'from', $this->table, $this->join, $this->where, $this->group, $this->having, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->query( implode( ' ', $sqls ) );
	}
	
	/**
	 * boolean|integer public function insert(array $datas=array(string $field=>scalra|array $value,...))
	 */
	public function insert($datas) {
		$regulars = $this->shell( $datas );
		$keyStr = implode( ',', array_keys( $regulars ) );
		$valueStr = implode( ',', array_values( $regulars ) );
		$this->sql();
		$sqls = array( 'insert', 'into', $this->table, '(' . $keyStr . ')', 'values(' . $valueStr . ')' );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * boolean|integer public function update(array $datas=array(string $field=>scalar $value,...))
	 */
	public function update($datas) {
		$regulars = $this->shell( $datas );
		foreach ( $regulars as $key => &$value ) {
			$value = $key . '=' . $value;
		}
		$dataStr = implode( ',', $regulars );
		$this->sql();
		$sqls = array( 'update', $this->table, 'set', $dataStr, $this->where, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * boolean|integer public function delete(void)
	 */
	public function delete() {
		$this->sql();
		$sqlFrags = array_filter( array( 'delete from', $this->table, $this->where, $this->order, $this->limit ), 'strlen' );
		return $this->cmd( implode( ' ', $sqlFrags ) );
	}
	
	/**
	 * boolean|array protected function shell(array $datas=array(string $field=>scalar|array $value,...))
	 */
	protected function shell($datas) {
		foreach ( $datas as $key => $value ) {
			$key = backquote( $key );
			if (is_integer( $value ) or is_float( $value )) $value = ( string ) $value;
			elseif (is_string( $value )) $value = "'" . htmlspecialchars( $value ) . "'";
			elseif (is_bool( $value )) $value = $value ? '1' : '0';
			elseif (is_null( $value )) $value = 'null';
			elseif (is_array( $value )) $value = $value [0];
			$regulars [$key] = $value;
		}
		return $regulars;
	}
	
	/**
	 * array public function fields(string $table)
	 */
	public function fields($table) {
		$sql = 'show columns from `' . $table . '`';
		$fields = $this->query( $sql );
		if (is_bool( $fields )) return array();
		foreach ( $fields as $field ) {
			$keys = array_map( 'strtolower', array_keys( $field ) );
			$values = array_map( 'strotolower', array_values( $field ) );
			$field = array_combine( $keys, $values );
			if ('tiny(1)' == $field ['type']) $field ['type'] = 'boolean';
			else {
				$pattern = '/^([a-z]+).*$/';
				preg_match( $pattern, $field ['type'], $matchs );
				$field ['type'] = $matchs [1];
			}
			$key = current( $field );
			$datas [$key] = $field;
		}
		return $datas;
	}
	
	/**
	 * array public function map(void)
	 */
	public function map() {
		return $maps = array( 'string' => array( 'char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'tinytext', 'text', 'mediumtext', 'longtext', 'date', 'datetime', 'timestamp', 'time', 'year', 'bit' ), 'integer' => array( 'tinyint', 'smallint', 'int', 'mediumint', 'bigint' ), 'float' => array( 'decimal', 'float', 'double' ), 'boolean' => array( 'boolean' ), 'null' => array() );
	}
	
	/**
	 * string protected backquote(string $data)
	 */
	protected function backquote($data) {
		$datas = explode( '.', $data );
		foreach ( $datas as &$value ) {
			$value = '`' . $value . '`';
		}
		return implode( '.', $datas );
	}
	
	/**
	 * string public function lastSql(void)
	 */
	public function lastSql() {
		return $this->sql;
	}
	
	/**
	 * integer public function lastId(void)
	 */
	public function lastId() {
		return $this->id;
	}
	//
}



