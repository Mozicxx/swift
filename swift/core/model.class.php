<?php

namespace Swift;

class Model {
	protected $database = null;
	
	/**
	 */
	public function __construct($name = '', $dsn = array()) {
		if (! empty ( $name ) && is_string ( $name )) {
			$this->name = $name;
		}
		$this->database ( $dsn );
	}
	
	/**
	 */
	public function database($dsn = array(), $options = array()) {
		if (empty ( $dsn )) $dsn = C ( 'schema_dsn' );
		elseif (is_string ( $dsn )) $dsn = C ( $dsn );
		elseif (is_array ( $dsn )) )
		else return false;
		
		foreach ( $dsn as $value ) {
			if (! is_string ( $value )) return false;
		}
		
		if ($this->database) {
			$this->database->close ();
			unset ( $this->database );
		}
		
		$this->database = new \Swift\Mysql ( $dsn, $options );
		return $this->database ? true : false;
	}
	
	/**
	 */
	public function table($datas) {
		if (is_string ( $datas )) {
			unset ( $this->database->datas ['table'] );
			$this->database->datas ['table'] = $datas;
		} elseif (is_array ( $datas )) {
			if (! is_array ( $this->database->datas ['table'] )) {
				unset ( $this->databaes->datas ['table'] );
			}
			$this->database->datas ['table'] [] = $datas;
		}
	}
	
	/**
	 */
	public function order($datas) {
		if (is_string ( $datas )) {
			$this->database->datas ['order'] = $datas;
		} elseif (is_array ( $datas )) {
			$this->database->datas ['order'] [] = $datas;
		}
		return $this;
	}
	
	/**
	 */
	public function limit($datas) {
		unset ( $this->database->datas ['limit'] );
		$this->database->datas ['limit'] = $datas;
		return $this;
	}
	
	/**
	 */
	public function where($datas) {
		if (is_string ( $datas )) {
			unset ( $this->database->datas ['where'] );
			$this->database->datas ['where'] = $datas;
		} elseif (is_array ( $datas )) {
			if (! is_array ( $this->database->datas ['where'] )) {
				unset ( $this->databaes->datas ['where'] );
			}
			$this->database->datas ['where'] [] = $datas;
		}
	}
	
	/**
	 */
	public function select() {
		return $this->database->select ();
	}
	
	/**
	 */
	public function add($datas) {
		return $this->database->insert ( $datas );
	}
	
	/**
	 */
	public function save($datas) {
		return $this->database->update ( $datas );
	}
	
	/**
	 */
	public function delete() {
		return $this->database->delete ();
	}
	//
}






















