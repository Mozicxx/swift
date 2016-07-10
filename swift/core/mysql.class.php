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
	protected $error = null;
	protected $datas = array ();
	protected $links = array ('read' => null,'write' => null );
	protected $ds = null;
	protected $options = array (PDO::ATTR_CASE => PDO::CASE_LOWER,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,PDO::ATTR_STRINGIFY_FETCHES => false );
	protected $configs = array ();
	protected $errConfigs = array ();
	
	/**
	 * void public function __construct(array $configs)
	 */
	public function __construct(array $configs) {
		if (! $configs) return;
		elseif (! $this->isIntSeq ( array_keys ( $configs ), true )) return;
		foreach ( $configs as $config ) {
			if ($this->isConfig ( $config )) $this->configs [] = $config;
		}
	}
	
	/**
	 * void public function __destruct(void)
	 */
	public function __destruct() {
		$this->close ();
	}
	
	/**
	 * string public function __get(string $prop)
	 */
	public function __get(string $prop) {
		return $this->datas [$prop] ?? '';
	}
	
	/**
	 * array protected function dsn(string $rw)
	 */
	protected function dsn(string $rw): array 

	{
		$configs = array ();
		foreach ( $this->configs as $key => $config ) {
			$operate = in_array ( $config ['operate'], array ($rw,self::operate_both ), true );
			$err = in_array ( $key, $this->errConfigs, true );
			if ($operate && ! $err) $configs [$key] = $config;
		}
		if ($configs) {
			$key = array_rand ( $configs );
			extract ( $configs [$key] );
			$dsn = array ('host=' . $host,'port=' . ( string ) $port,'dbname=' . $dbname,'charset=' . $charset );
			return array ($key,$type . ':' . implode ( ';', $dsn ),$username,$password );
		}
		return array ();
	}
	
	/**
	 * boolean protected function link(string $rw)
	 */
	protected function link(string $rw): bool {
		if ($this->link [$rw]) return true;
		$params = $this->dsn ( $rw );
		if ($params) list ( $key, $dsn, $username, $password ) = $params;
		else return false;
		try {
			$this->link [$rw] = new \PDO ( $dsn, $username, $password, $this->options );
			$this->errConfigs = array ();
			return true;
		} catch ( \PDOException $e ) {
			E($e->getMessage())
			$this->errConfigs [] = $key;
			return $this->link ( $rw );
		}
	}
	
	/**
	 * void public function close(void)
	 */
	public function close() {
		$this->free ();
		$this->links = array ('read' => null,'write' => null );
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
	public function begin(): bool {
		if (! $this->link ( self::operate_write )) return false;
		elseif ($this->links [self::operate_write]->inTransaction ()) return false;
		return $this->links [self::operate_write]->beginTransaction ();
	}
	
	/**
	 * boolean public function end(void)
	 */
	public function end(): bool {
		if (! $this->links [self::operate_write]) return false;
		elseif (! $this->links [self::operate_write]->inTransaction ()) return false;
		return $this->links [self::operate_write]->commit ();
	}
	
	/**
	 * boolean public function rollback(void);
	 */
	public function rollback(): bool {
		if (! $this->links [self::operate_write]) return false;
		elseif (! $this->links [self::operate_write]->inTransaction ()) return false;
		return $this->links [self::operate_write]->rollback ();
	}
	
	/**
	 * string public function error(void)
	 */
	public function error(): string {
		$this->error = $this->ds ? implode ( ':', $this->ds->errorInfo () ) : null;
		return $this->error;
	}
	
	/**
	 * boolean public function data(string $key, string $value)
	 */
	public function data(string $key, string $value): bool {
		$keysRegular = array ('distinct','field','table','join','where','group','having','order','limit' );
		if (in_array ( $key, $keysRegular, true )) {
			$this->datas [$key] = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * boolean public function clearData(string $key)
	 */
	public function clearData(string $key): bool {
		$keysRegular = array ('distinct','field','table','join','where','group','having','order','limit' );
		if (in_array ( $key, $keysRegular, true )) {
			unset ( $this->datas [$key] );
			return true;
		}
		return false;
	}
	
	/**
	 * void public function clear(void)
	 */
	public function clear() {
		$this->datas = array ();
	}
	
	/**
	 * integer public function cmd(string $sql)
	 */
	public function cmd(string $sql): int {
		$this->sql = $sql;
		if (! $this->link ( self::operate_write )) return - 1;
		$this->ds = $this->link->prepare ( $this->sql );
		return $this->ds && $this->ds->execute () ? $this->ds->rowCount () : - 1;
	}
	
	/**
	 * array public function query(string $sql)
	 */
	public function query(string $sql): array {
		$this->sql = $sql;
		if (! $this->link ( self::operate_read )) return array ();
		$this->ds = $this->link->prepare ( $this->sql );
		return $this->ds && $this->ds->execute () ? $this->ds->fetchAll ( PDO::FETCH_ASSOC ) : array ();
	}
	
	/**
	 * array public function select(void)
	 */
	public function select() {
		$sqlSections = array ('select',$this->distinct,$this->column,'from',$this->table,$this->join,$this->where,$this->group,$this->having,$this->order,$this->limit );
		$sql = implode ( ' ', array_filter ( $sqlSections, 'strlen' ) );
		return $this->query ( $sql );
	}
	
	/**
	 * integer public function insert(array $datas=array(string $field=>scalar $data,...))
	 */
	public function insert(array $datas): int {
		$datas = $this->shell ( $datas );
		if (! $datas) return 0;
		$keyStr = implode ( ',', array_keys ( $datas ) );
		$valueStr = implode ( ',', array_values ( $datas ) );
		$sqlSections = array ('insert','into',$this->table . '(' . $keyStr . ')','values(' . $valueStr . ')' );
		$sql = implode ( ' ', $sqlSections );
		return $this->cmd ( $sql );
	}
	
	/**
	 * integer public function update(array $datas=array(string $field=>scalar $data,...))
	 */
	public function update(array $datas): int {
		$datas = $this->shell ( $datas );
		if (! $datas) return 0;
		foreach ( $datas as $key => &$value ) {
			$value = $key . '=' . $value;
		}
		$dataStr = implode ( ',', $datas );
		$sqlSections = array ('update',$this->table,'set',$dataStr,$this->where,$this->order,$this->limit );
		$sql = implode ( ' ', array_filter ( $sqlSections, 'strlen' ) );
		return $this->cmd ( $sql );
	}
	
	/**
	 * integer public function delete(void)
	 */
	public function delete(): int {
		$sqlSections = array ('delete','from',$this->table,$this->where,$this->order,$this->limit );
		$sql = implode ( ' ', array_filter ( $sqlSections, 'strlen' ) );
		return $this->cmd ( $sql );
	}
	
	/**
	 * array protected function shell(array $datas=array(string $field=>scalar $data,...))
	 */
	protected function shell(array $datas): array {
		if (! $datas) return array ();
		elseif (! $this->isIntSeq ( array_keys ( $datas ), true )) return array ();
		$regulars = array ();
		foreach ( $datas as $key => $value ) {
			
			$key = $this->backquote ( $key );
			if (is_integer ( $value ) or is_float ( $value )) $value = ( string ) $value;
			elseif (is_string ( $value )) {
				$pattern = '/\{(.*)\}/';
				$value = preg_match ( $pattern, $value, $matchs ) ? $value = $matchs [1] : "'" . htmlspecialchars ( $value ) . "'";
			} elseif (is_bool ( $value )) $value = $value ? '1' : '0';
			elseif (is_null ( $value )) $value = 'null';
			else return array ();
			$regulars [$key] = $value;
		}
		return $regulars;
	}
	
	/**
	 * array public function fields(string $table)
	 */
	public function fields(string $table) {
		if (! $this->isDbRegular ( $stable )) return array ();
		$sql = 'describe ' . $this->backquote ( $table );
		$datas = $this->query ( $sql );
		if ($datas) return array ();
		$fields = array ();
		foreach ( $datas as $data ) {
			$keys = array_map ( 'strtolower', array_keys ( $data ) );
			$values = array_map ( 'strotolower', array_values ( $data ) );
			$data = array_combine ( $keys, $values );
			if ('tiny(1)' == $data ['type']) $data ['type'] = 'boolean';
			else {
				$pattern = '/^([a-z]+).*$/';
				preg_match ( $pattern, $data ['type'], $matchs );
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
		return $maps = array ('string' => array ('char','varchar','binary','varbinary','tinyblob','blob','mediumblob','longblob','tinytext','text','mediumtext','longtext','date','datetime','timestamp','time','year','bit' ),'integer' => array ('tinyint','smallint','int','mediumint','bigint' ),'float' => array ('decimal','float','double' ),'boolean' => array ('boolean' ),'null' => array () );
	}
	
	/**
	 * string protected backquote(string $data)
	 */
	protected function backquote(string $data): string {
		$dataSections = explode ( '.', $data );
		foreach ( $dataSections as &$value ) {
			$value = '`' . $value . '`';
		}
		return implode ( '.', $dataSections );
	}
	
	/**
	 * string public function lastSql(void)
	 */
	public function lastSql(): string {
		return $this->sql;
	}
	
	/**
	 * integer public function lastId(void)
	 */
	public function lastId(): int {
		return $this->id;
	}
	
	/**
	 * boolean protected function isDbRegular(string $data)
	 */
	protected function isDbRegular(string $data): bool {
		$pattern = '/^([a-z]+_)*[a-z]+$/';
		return preg_match ( $pattern, $data ) ? true : false;
	}
	
	/**
	 * boolean protected function isDbRegularPlus(string $data)
	 */
	protected function isDbRegularPlus(string $data): bool {
		$datas = explode ( '.', $data );
		if (count ( $datas ) != 2) return false;
		foreach ( $datas as $value ) {
			if (! $this->isDbRegular ( $value )) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isConfig(array $datas=array(string $param=>string|integer $data,...))
	 */
	protected function isConfig(array $datas) {
		if (count ( $datas ) != 8) return false;
		foreach ( $datas as $key => $value ) {
			if (! in_array ( $key, array ('operate','type','host','port','database','charset','username','password' ), true )) return false;
			elseif ('port' == $key && ! is_integer ( $value )) return false;
			elseif (! is_string ( $value )) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isIntSeq(array $datas ,boolean $mode=false)
	 */
	protected function isIntSeq(array $datas, bool $mode = false): bool {
		$datas = $mode ? array_filter ( array_values ( $datas ), 'is_integer' ) : array_values ( $datas );
		foreach ( $datas as $key => $value ) {
			if ($key !== $value) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isStrSeq(array $datas, boolean $mode = false)
	 */
	protected function isStrSeq(string $datas, bool $mode = false): bool {
		$datas = array_values ( $datas );
		foreach ( $datas as $value ) {
			if (! is_string ( $value )) return false;
			elseif ($mode && '' == $value) return false;
		}
		return true;
	}
	//
}



