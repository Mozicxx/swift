<?php

namespace Swift;

class Mysql {
	protected $cmd = null;
	protected $options = array ();
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
		$dsn = 'mysql:dbname=' . $this->configs['schema'] . ';host=' . $this->configs['host'];
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
	public function execute($sql){
		if(!$this->link){return false;}
		
	}
	//
}



















