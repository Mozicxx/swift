<?php
declare(strict_types = 1);

namespace Swift;

abstract class controller {
	
	/**
	 * protected $view
	 */
	protected $view = null;
	
	/**
	 * void public function __construct()
	 */
	public function __construct() {
		$this->view = new \Swift\View ();
	}
	
	/**
	 * void public function __destruct()
	 */
	public function __destruct() {
		//
	}
	
	/**
	 * mixed public function __get($name)
	 */
	public function __get($name) {
		return $this->view->get($name);
	}
	
	/**
	 * boolean public function __set($name, $value)
	 */
	public function __set($name, $value): bool 

{
	$this->view->assign ( $name, $value );
}
	
	/**
	 * boolean public function __isset(string $name)
	 */
	public function __isset($name): bool {
		return is_null($this->view->get($name)) ? false : true;
	}
	
	/**
	 * void public function display(string $data, string $type = null, string $charset = null)
	 */
	public function display(string $data, string $type = null, string $charset = null) {
		$this->view->display ( $data, $type, $charset );
	}
	
	/**
	 * public function output(string $data, string $type = null, string $charset = null)
	 */
	public function output($data, $type = null, $charset =null) {
		$this->view->output ( $data, $type, $charset );
	}
	
	/**
	 * string public function fetch(string $url)
	 */
	public function fetch(string $url): string{
		return $this->view->fetch($url);
	}
	
	/**
	 * public function ajax($data, $type)
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
	 * boolean public function assign(string $name, mixed $value)
	 */
	public function assign($name, $value): bool 

{
	return $this->view->assign ( $name, $value );
}
	
	/**
	 * integer public function assigns(array $vars)
	 */
	public function assigns(array $vals): int{
		return $this->view->assigns($vars);
	}
	
	/**
	 * mixed public function get(string $name)
	 */
	public function get(string $name) {
		return $this->view->get ( $name );
	}
	
	/**
	 * array public function gets()
	 */
	public function gets(): array {
		return $this->view->gets();
	}
	//
}
