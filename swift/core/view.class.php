<?php

namespace Swift;

class View {
	protected $vars = array ();
	
	/**
	 * public function __construct()
	 */
	public function __construct() {
		//
	}
	
	/**
	 * public function __destruct()
	 */
	public function __destruct() {
		//
	}
	
	/**
	 * public functioin display()
	 */
	public function display($template = null, $type = null, $charset = null) {
		$data = $this->fetch ( $template );
		$this->render ( $data, $type, $charset );
	}
	
	/**
	 * public function show()
	 */
	public function show($data, $type = null, $charset = null) {
		$this->render ( $data, $type, $charset );
	}
	
	/**
	 * public function fetch($template)
	 */
	protected function fetch($template) {
		$template = $this->parseTemplate ( $template );
		if (! is_file ( $template )) {
			//
		}
		ob_start ();
		ob_implicit_flush ( false );
		$engine = C ( 'template_engine' );
		switch ($engine) {
			case 'swift' :
				//
				break;
			case 'php' :
				extract ( $this->vars, EXTR_OVERWRITE );
				include $template;
				break;
			default :
				//
				break;
		}
		return ob_get_clean ();
	}
	
	/**
	 * protected function render($data, $type, $charset)
	 */
	protected function render($data, $type, $charset) {
		! is_null ( $type ) ?  : $type = C ( 'template_default_type' );
		! is_null ( $charset ) ?  : $charset = C ( 'template_default_charset' );
		header ( 'Content-Type:' . $type . '; charset=' . $charset );
		header ( 'Cache-Control: ' . C ( 'http_cache_control' ) );
		echo $data;
	}
	
	/**
	 * protected function parseTemplate($template)
	 */
	protected function parseTemplate($template) {
		$app = app_path;
		$module = module_name;
		$controller = controller_name;
		$action = action_name;
		$view = C ( 'view_layer' );
		$suffix = C ( 'template_suffix' );
		
		if (null != $template) {
			$arr = explode ( '.', $template );
			switch (count ( $arr )) {
				case 3 :
					list ( $module, $controller, $action ) = $arr;
					break;
				case 2 :
					list ( $controller, $action ) = $arr;
					break;
				case 1 :
					list ( $action ) = $arr;
					break;
				default :
					//
					break;
			}
		}
		$depr = '/';
		$template = $app . $depr . $module . $depr . $view . $depr . $controller . $depr . $action . $suffix;
		return $template;
	}
	/**
	 * public function assign($name, $value)
	 * public function assign($vars)
	 */
	public function assign($name, $value = null) {
		is_array ( $name ) ? $this->vars = array_merge ( $this->vars, $name ) : $this->vars [$name] = $value;
	}
	
	/**
	 * public function get()
	 * public function get($name)
	 */
	public function get($name = null) {
		if (is_null ( $name )) {
			return $this->vars;
		}
		return isset ( $this->vars [$name] ) ? $this->vars [$name] : null;
	}
	
	//
}