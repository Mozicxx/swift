<?php

namespace Swift;

class App {
	
	/**
	 */
	protected function loadFuncs() {
		$depr = '/';
		$file = app_path . $depr . 'func' . $depr . app_name . 'func.php';
		loadFuncs ( $file );
	}
	
	/**
	 */
	protected function loadConfig() {
		$depr = '/';
		$file = app_path . $depr . 'conf' . $depr . app_name . 'conf.php';
		loadConfig ( $file );
	}
	
	/**
	 */
	public function work() {
		$this->loadFuncs ();
		$this->loadConfig ();
		$router = new Router ();
		$router->navigate ();
	}
	//
}