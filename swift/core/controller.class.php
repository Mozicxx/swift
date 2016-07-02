<?php

namespace Swift;

abstract class controller {
	
	/**
	 * protected $view
	 */
	protected $view = null;
	
	/**
	 * public function __construct()
	 */
	public function __construct() {
		$this->view = new \Swift\View ();
	}
	
	/**
	 * public function __destruct()
	 */
	public function __destruct() {
		//
	}
	
	/**
	 * public function __get($name)
	 */
	public function __get($name) {
		return $this->get ( $name );
	}
	
	/**
	 * public function __set($name, $value)
	 */
	public function __set($name, $value) {
		$this->view->assing ( $name, $value );
	}
	
	/**
	 * public function __isset($name)
	 */
	public function __isset($name) {
		return $this->view->get ( $name );
	}
	
	/**
	 * public function display($data, $type, $charset)
	 */
	public function display($data, $type = null, $charset = null) {
		$this->view->display ( $data, $type, $charset );
	}
	
	/**
	 * public function show($data, $type, $charset)
	 */
	public function show($data, $type, $charset) {
		$this->view->show ( $data, $type, $charset );
	}
	
	/**
	 * public functioin ajax($data, $type)
	 */
	public function ajax($data, $type = null) {
		$type = is_null ( $type ) ? C ( 'ajax_default_type' ) : strtolower ( $type );
		switch ($type) {
			case 'json' :
				header ( 'Content-Type:application/json; charset=utf-8' );
				die ( json_encode ( $data ) );
				break;
			default :
				//
				break;
		}
	}
	
	/**
	 * public function assign($name, $value)
	 */
	public function assign($name, $value = null) {
		$this->view->assign ( $name, $value );
		return $this;
	}
	
	/**
	 * public function get($name)
	 */
	public function get($name = null) {
		return $this->view->get ( $name );
	}
	

	
	//
}
