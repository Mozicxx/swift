<?php
declare(strict_types = 1);

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
	protected $errorConfigs = array();
	
	/**
	 * void public function __construct(array $configs)
	 */
	public function __construct(array $configs) {
		if (! $configs) return;
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
	 * boolean public function begin(void);
	 */
	public function begin() {
		if (! $this->link( self::operate_write )) return false;
		elseif ($this->links [self::operate_write]->inTransaction()) return false;
		return $this->links [self::operate_write]->beginTransaction();
	}
	
	/**
	 * boolean public function end(void)
	 */
	public function end() {
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
		if (! $this->link( self::operate_write )) return - 1;
		$this->ds = $this->link->prepare( $this->sql );
		if ($this->ds && $this->ds->execute()) {
			$this->id = ( int ) $this->link->lastInsertId();
			return $this->ds->rowCount();
		} else
			return - 1;
	}
	
	/**
	 * array public function query(string $sql)
	 */
	public function query(string $sql) {
		$this->sql = $sql;
		if (! $this->link( self::operate_read )) return array();
		$this->ds = $this->link->prepare( $this->sql );
		return $this->ds && $this->ds->execute() ? $this->ds->fetchAll( PDO::FETCH_ASSOC ) : array();
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
	 * integer public function insert(array $datas=array(string $field=>scalar $value,...))
	 */
	public function insert(array $datas) {
		$datas = $this->shell( $datas );
		if (! $datas) return 0;
		$keyStr = implode( ',', array_keys( $datas ) );
		$valueStr = implode( ',', array_values( $datas ) );
		$sqls = array( 'insert', 'into', $this->table . '(' . $keyStr . ')', 'values(' . $valueStr . ')' );
		$sqls = array_filter( $sqls, 'strlen' );
		return $this->cmd( implode( ' ', $sqls ) );
	}
	
	/**
	 * integer public function update(array $datas=array(string $field=>scalar $value,...))
	 */
	public function update(array $datas) {
		$datas = $this->shell( $datas );
		if (! $datas) return 0;
		foreach ( $regulars as $key => &$value ) {
			$value = $key . '=' . $value;
		}
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
	 * array protected function shell(array $datas=array(string $field=>scalar $value,...))
	 */
	protected function shell(array $datas) {
		if (! $datas) return array();
		elseif (! $this->isIntSeq( array_keys( $datas ), true )) return array();
		$regulars = array();
		foreach ( $datas as $key => $value ) {
			$key = backquote( $key );
			if (is_integer( $value ) or is_float( $value )) $value = ( string ) $value;
			elseif (is_string( $value )) {
				$pattern = '/\{(.*)\}/';
				$value = preg_match( $pattern, $value, $matchs ) ? $value = $matchs [1] : "'" . htmlspecialchars( $value ) . "'";
			} elseif (is_bool( $value )) $value = $value ? '1' : '0';
			elseif (is_null( $value )) $value = 'null';
			else return array();
			$regulars [$key] = $value;
		}
		return $regulars;
	}
	
	/**
	 * array public function fields(string $table)
	 */
	public function fields(string $table) {
		if (! $this->isDbRegular( $stable )) return array();
		$sql = 'describe ' . $this->backquote( $table );
		$datas = $this->query( $sql );
		if ($datas) return array();
		$fields = array();
		foreach ( $datas as $data ) {
			$keys = array_map( 'strtolower', array_keys( $data ) );
			$values = array_map( 'strotolower', array_values( $data ) );
			$data = array_combine( $keys, $values );
			if ('tiny(1)' == $data ['type']) $data ['type'] = 'boolean';
			else {
				$pattern = '/^([a-z]+).*$/';
				preg_match( $pattern, $data ['type'], $matchs );
				$data ['type'] = $matchs [1];
			}
			$fields [$data ['field']] = $data;
		}
		return $fields;
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
	
	/**
	 * boolean protected function isDbRegular(string $data)
	 */
	protected function isDbRegular(string $data) {
		$pattern = '/^([a-z]+_)*[a-z]+$/';
		return preg_match( $pattern, $data ) ? true : false;
	}
	
	/**
	 * boolean protected function isDbRegularPlus(string $data)
	 */
	protected function isDbRegularPlus(string $data) {
		$datas = explode( '.', $data );
		if (count( $datas ) != 2) return false;
		foreach ( $datas as $value ) {
			if (! $this->isDbRegular( $value )) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isCamelCaseRegular(string $data)
	 */
	protected function isCamelCaseRegular(string $data) {
		$pattern = '/^[a-z]+([A-Z][a-z]*)*$/';
		return preg_match( $pattern, $data ) ? true : false;
	}
	
	/**
	 * boolean protected function isDsn(array $datas=array(string $key=>string|integer $value,...))
	 */
	protected function isDsn(array $datas) {
		if (count( $datas ) != 8) return false;
		foreach ( $datas as $key => $value ) {
			if (! in_array( $key, array( 'operate', 'type', 'host', 'port', 'user', 'pwd', 'database', 'charset' ), true )) return false;
			elseif ('port' == $value && ! is_integer( $value )) return false;
			elseif (! is_string( $value ) or '' == $value) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isIntSeq(array $datas [,boolean $mode=false])
	 */
	protected function isIntSeq(array $datas, bool $mode = false) {
		$values = $mode ? array_filter( array_values( $datas ), 'is_integer' ) : array_values( $datas );
		foreach ( $values as $key => $value ) {
			if ($key !== $value) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isStrSeq(array $datas [,boolean $mode=false])
	 */
	protected function isStrSeq(string $datas, bool $mode = false) {
		$datas = array_values( $datas );
		foreach ( $datas as $value ) {
			if (! is_string( $value )) return false;
			elseif ($mode && '' == $value) return false;
		}
		return true;
	}
	//
}



