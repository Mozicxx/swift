<?php
declare(strict_types = 1)
	;

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
	protected $links = array( 'read' => null, 'write' => null );
	protected $ds = null;
	protected $options = array( PDO::ATTR_CASE => PDO::CASE_LOWER, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, PDO::ATTR_STRINGIFY_FETCHES => false );
	protected $configs = array();
	
	/**
	 * void public function __construct(array $configs)
	 */
	public function __construct($configs) {
		if (empty( $configs )) return;
		elseif (! $this->isIntSeq( array_keys( $configs ), true )) return;
		foreach ( $configs as $config ) {
			if (! $this->isDsn( $config )) return;
		}
		$this->configs = $configs;
	}
	
	/**
	 * void public function __destruct(void)
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * string public function __get(string $prop)
	 */
	public function __get(string $prop) {
		return $this->datas[$prop] ?? '';
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
		$this->free();
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
	 * boolean public function data(string $key [,string $value])
	 */
	public function data(string $key, string $value = null) {
		$keysRegular = array( 'distinct', 'field', 'table', 'join', 'where', 'group', 'having', 'order', 'limit' );
		if (in_array( $key, $keysRegular, true )) {
			if (is_null( $value )) unset( $this->datas [$key] );
			else $this->datas [$key] = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * void public function clear(void)
	 */
	public function clear() {
		$this->datas = array();
	}
	
	/**
	 * integer public function cmd(string $sql)
	 */
	public function cmd(string $sql) {
		$this->sql = $sql;
		if ($this->link( self::operate_write )) {
			if ($this->ds) $this->free();
			$this->ds = $this->link->prepare( $this->sql );
			if ($this->ds && $this->ds->execute()) return $this->ds->rowCount;
		}
		return - 1;
	}
	
	/**
	 * array public function query(string $sql)
	 */
	public function query(string $sql) {
		$this->sql = $sql;
		if ($this->link( self::operate_read )) {
			if ($this->ds) $this->free();
			$this->ds = $this->link->prepare( $this->sql );
			if ($this->ds && $this->ds->execute()) return $this->ds->fetchAll( PDO::FETCH_ASSOC );
		}
		return array();
	}
	
	/**
	 * array public function select(void)
	 */
	public function select() {
		$sqls = array( 'select', $this->distinct, $this->column, 'from', $this->table, $this->join, $this->where, $this->group, $this->having, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->query( implode( ' ', $sqls ) );
	}
	
	/**
	 * integer public function insert(array $datas=array(string $field=>scalar|array $value,...))
	 */
	public function insert(array $datas) {
		$regulars = $this->shell( $datas );
		$keyStr = implode( ',', array_keys( $regulars ) );
		$valueStr = implode( ',', array_values( $regulars ) );
		$sqls = array( 'insert', 'into', $this->table, '(' . $keyStr . ')', 'values(' . $valueStr . ')' );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * integer public function update(array $datas=array(string $field=>scalar|array $value,...))
	 */
	public function update(array $datas) {
		$regulars = $this->shell( $datas );
		foreach ( $regulars as $key => &$value )
			$value = $key . '=' . $value;
		$dataStr = implode( ',', $regulars );
		$sqls = array( 'update', $this->table, 'set', $dataStr, $this->where, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * integer public function delete(void)
	 */
	public function delete() {
		$sqls = array( 'delete', 'from', $this->table, $this->where, $this->order, $this->limit );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * array protected function shell(array $datas=array(string $field=>scalar|array $value,...))
	 */
	protected function shell(array $datas) {
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
	public function fields(string $table) {
		if (! $this->isDbRegular( $stable )) return array();
		$sql = 'show columns from ' . $this->backquote( $table );
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
	protected function backquote(string $data) {
		$datas = explode( '.', $data );
		foreach ( $datas as &$value )
			$value = '`' . $value . '`';
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
	
	/**
	 * boolean protected function isDbRegular(string $data)
	 */
	protected function isDbRegular(string $data) {
		if (strlen( $data )) {
			$pattern = '/^([a-z]+_)*[a-z]+$/';
			return preg_match( $pattern, $data ) ? true : false;
		}
		return false;
	}
	
	/**
	 * boolean protected function isDsn(array $datas=array(string $key=>string|integer $value,...))
	 */
	protected function isDsn(array $datas) {
		if (count( $datas ) != 8) return false;
		foreach ( $datas as $key => $value ) {
			if (! in_array( $key, array( 'operate', 'type', 'host', 'port', 'user', 'pwd', 'database', 'charset' ), true )) return false;
			elseif ('port' == $value && ! is_integer( $value )) return false;
			elseif (! is_string( $value ) or ! strlen( $datas )) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isIntSeq(array $datas [,boolean $mode])
	 */
	protected function isIntSeq($datas, $mode = false) {
		if (! is_array( $datas ) or empty( $data )) return false;
		elseif (! is_bool( $mode )) return false;
		$values = $mode ? array_filter( array_values( $datas ), 'is_integer' ) : array_values( $datas );
		foreach ( $values as $key => $value ) {
			if ($key != $value) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isStrSeq(array $datas [,boolean $mode])
	 */
	protected function isStrSeq($datas, $mode = false) {
		if (! is_array( $datas ) or empty( $data )) return false;
		elseif (! is_bool( $mode )) return false;
		$values = array_values( $datas );
		foreach ( $values as $value ) {
			if (! is_string( $value )) return false;
			elseif ($mode && '' == $value) return false;
		}
		return true;
	}
	//
}



