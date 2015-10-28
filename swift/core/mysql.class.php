<?php

namespace Swift;

class Mysql {
	protected $cmd = null;
	protected $sql = null;
	protected $pieces = array ();
	protected $pool = array ();
	protected $link = null;
	protected $configs = array (
			'type' => 'mysql',
			'user' => '',
			'password' => '',
			'host' => '127.0.0.1',
			'port' => '3306',
			'schema' => '',
			'charset' => 'utf8',
			'params' => array () 
	);
	
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
	protected function where($datas) {
		if (is_string ( $datas ) && '' != $datas) {
			return 'where ' . trim ( $datas ) . ' ';
		}
		if (is_array ( $datas ) && count ( $datas ) > 0) {
			$datas = array_change_key_case ( $datas );
			$sqls = array ();
			foreach ( $datas as $key => $value ) {
				if (is_string ( $value ) || is_numeric ( $value )) {
					$sqls [] = '(`' . $key . '`=' . (is_string ( $value ) ? "'$value'" : ( string ) $value) . ')';
				}
			}
			return count ( $sqls ) > 0 ? 'where ' . implode ( ' and ', $sqls ) . ' ' : '';
		}
		return '';
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
	public function delete() {
		$table = $this->table ( $this->pieces ['table'] );
		$where = $this->where ( $this->pieces ['where'] );
		$order = $this->order ( $this->pieces ['order'] );
		$limie = $this->limit ( $this->pieces ['limit'] );
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



















