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
	
	//
}