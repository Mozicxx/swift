<?php
declare(strict_types = 1)
	;

namespace Swift;

class View {
	protected $module;
	protected $controller;
	protected $action;
	protected $vars = array();
	
	/**
	 * void public function __construct(void)
	 */
	public function __construct() {
		$this->module = module_name;
		$this->controller = controller_name;
		$this->action = action_name;
	}
	
	/**
	 * void public functioin display(string $template='', string $type='', string $charset='')
	 */
	public function display(string $template = '', string $type = '', string $charset = '') {
		$data = $this->fetch( $template );
		$this->render( $data, $type, $charset );
	}
	
	/**
	 * void public function show(string $data, string $data='', string $charset='')
	 */
	public function show(string $data, string $type = '', string $charset = '') {
		$this->render( $data, $type, $charset );
	}
	
	/**
	 * protected function fetch(string $template)
	 */
	protected function fetch(string $template) {
		$tmplEngine = C( 'template_engine' );
		$path = $this->parseTemplatePath( $template );
		ob_start();
		ob_implicit_flush( false );
		extract( $this->vars, EXTR_OVERWRITE );
		switch ($tmplEngine) {
			case 'swift' :
				
				break;
			case 'php' :
				include $path;
				break;
			default :
				include $path;
				break;
		}
		return ob_get_clean();
	}
	
	/**
	 * void protected function render(string $data, string $type, string $charset)
	 */
	protected function render(string $data, string $type, string $charset) {
		$templateDefaultType != '' ?: C( 'template_default_type' );
		$tmplDefaultCharset != '' ?: C( 'template_default_charset' );
		$httpCacheControl = C( 'http_cache_control' );
		header( 'Content-Type:' . $tmplDefaultType . '; charset=' . $tmplDefaultCharset );
		header( 'Cache-Control: ' . $httpCacheControl );
		echo $data;
	}
	
	/**
	 * string protected function parseTemplate(string $url)
	 */
	protected function parseTemplatePath(string $url) {
		$viewLayer = C( 'view_layer' );
		$templateSuffix = C( 'template_suffix' );
		$path404 = implode( '/', array( swift_path, 'view', '404.html' ) );
		
		if ($url != '') {
			$name = '[a-z]+([A-Z][a-z]+)*';
			$pattern = '/' . $name . '(\.' . $name . '){0,2}/';
			if (preg_match( $pattern, $url )) {
				$urlSections = explode( '.', $this->camelToUnderline( $url ) );
				$num = count( $urlSections );
				if (3 == $num) list ( $this->module, $this->controller, $this->action ) = $urlSections;
				elseif (2 == $num) list ( $this->controller, $this->action ) = $urlSections;
				elseif (1 == $num) list ( $this->action ) = $urlSections;
			} else
				return $path404;
		}
		
		$path = implode( '/', array( app_path, $this->module, $viewLayer, $this->controller, $this->action . $templateSuffix ) );
		return is_file( $path ) ? $path : $path404;
	}
	
	/**
	 * string protected function caseToUnderline(string $data)
	 */
	protected function camelToUnderline(string $data) {
		$pattern = '/[A-Z]/';
		return preg_replace_callback($pattern, function($matches){
			return '_' . $matches[0];
		} $data);
	}
	
	/**
	 * void public function assign(string $name, mixed $value)
	 */
	public function assign(string $name, $value) {
		$pattern = '/^[a-z_][a-z0-9_]*$/i';
		if (preg_match( $pattern, $key )) $this->vars [$key] = $value;
	}
	
	/**
	 * void public function assigns(array $vars)
	 */
	public function assigns(array $vars) {
		$pattern = '/^[a-z_][a-z0-9_]*$/i';
		foreach ( $vars as $key => $value ) {
			if (preg_match( $pattern, $key )) $this->vars [$key] = $value;
		}
	}
	
	/**
	 * mixed public function get(string $name=null)
	 */
	public function get(string $name = null) {
		if (is_null( $name )) return $this->vars;
		return $this->vars[$name] ?? null;
	}
	//
}