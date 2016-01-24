<?php
declare(strict_types = 1);

namespace Swift;

class Router {
	protected $module;
	protected $controller;
	protected $action;
	protected $params;
	
	/**
	 */
	public function __construct() {
		$this->module = C( 'default_access_module' );
		$this->controller = C( 'default_access_controller' );
		$this->action = C( 'default_access_action' );
		$this->params = array();
	}
	
	/**
	 */
	protected function parse() {
		$url = isset( $_SERVER ['PATH_INFO'] ) ? trim( $_SERVER ['PATH_INFO'], ' /' ) : '';
		if ($url != '') {
			$frags = explode( '/', $url );
			$length = count( $frags );
			switch ($length) {
				case 3 :
					list ( $this->module, $this->controller, $this->action ) = $frags;
					break;
				case 2 :
					list ( $this->module, $this->controller ) = $frags;
					break;
				case 1 :
					list ( $this->module ) = $frags;
					break;
				default :
					list ( $this->module, $this->controller, $this->action ) = array_splice( $frags, 0, 3 );
					$this->actionParams = $frags;
					break;
			}
		}
	}
	
	/**
	 */
	protected function controller() {
		$depr = '/';
		$file = app_path . $depr . $this->module . $depr . 'controller' . $depr . $this->controller . 'Controller.class.php';
		if (! is_file( $file )) {return false;}
		require_once $file;
		
		$nsdepr = '\\';
		$class = $nsdepr . app_namespace . $nsdepr . $this->module . $nsdepr . 'Controller' . $nsdepr . $this->controller . 'Controller';
		$controller = new $class();
		call_user_func_array( array( $controller, $this->action ), $this->params );
	}
	
	/**
	 */
	public function navigate() {
		$this->parse();
		$this->controller();
	}
	//
}




