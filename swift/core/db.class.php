<?php

namespace Swift;

class Db {
	private static $_pool = array ();
	private static $_connection = null;
	
	/**
	 */
	static public function connect($configs = array()) {
		$key = md5 ( serialize ( $config ) );
		if(!isset(self::$_pool[$key])){
			$options=self::parseConfig($configs);
			self::$_pool[$key]=new \Swift\
		}
		self::$_connection=$self::$_pool[$key];
		return self::$_connection;
	}
	
	/**
	 */
	static protected function parseConfig($configs) {
		if (is_null ( $configs )) {
			$configs = C ( 'database_connection' );
		} else {
		}
	}
	
	/**
	 */
	static protected function parseDsn($dsn) {
		$pattern = '/^(\w+):\\/\\/(\w+):(\w+)@([0-9.]+):(\d+)\\/(\w+)#(\w+)$/';
		if (preg_match ( $pattern, strtolower ( $dsn ), $values ) > 0) {
			array_shift ( $values );
			$keys = array (
					'type',
					'user',
					'password',
					'host',
					'port',
					'schema',
					'charset' 
			);
			return array_combine ( $keys, $values );
		}
		return false;
	}
	//
}
/***********************************************************/










