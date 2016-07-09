<?php
declare(strict_types = 1);

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
	 * void public fnnction __destruct(void)
	 */
	public function __destruct() {
		//
	}
	
	/**
	 * void public function display(string $url=null, string $type=null, string $charset=null)
	 */
	public function display(string $url = null, string $type = null, string $charset = null) {
		$data = $this->fetch( $url );
		$this->render( $data, $type, $charset );
	}
	
	/**
	 * void public function output(string $data, string $type=null, string $charset=null)
	 */
	public function output(string $data, string $type = null, string $charset = null) {
		$this->render( $data, $type, $charset );
	}
	
	/**
	 * string public function fetch(string url)
	 */
	public function fetch(string $url): string {
		$templateEngine = C( 'template_engine' );
		$path = $this->getTemplatePath( $url );
		ob_start();
		ob_implicit_flush( false );
		extract( $this->vars, EXTR_OVERWRITE );
		switch ($engine) {
			case 'sharp' :
				//
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
	 * boolean public function assign(string $name, mixed $value)
	 */
	public function assign(string $name, $value): bool {
		$pattern = '/^[a-z_][a-z0-9_]*$/i';
		if (preg_match( $pattern, $name )) {
			$this->vars [$name] = $value;
			return true;
		}else return false;
	}
	
	/**
	 * integer public function assigns(array $vars)
	 */
	public function assigns(array $vars): int {
		$counter=0;
		$pattern = '/^[a-z_][a-z0-9_]*$/i';
		foreach ( $vars as $name => $value ) {
			if (preg_match( $pattern, $name )) {
				$this->vars [$name] = $value;
				$counter++;
			}
		}
		return $counter;
	}
	
	/**
	 * mixed public function get(string $name)
	 */
	public function get(string $name) {
		return $this->vars[$name] ?? null;
	}
	
	/**
	 * array public function gets()
	 */
	 public function gets(): array {
		 return $this->vars;
	 }
	
	/**
	 * void protected function render(string $data, string $type, string $charset)
	 */
	protected function render(string $data, string $type, string $charset) {
		$templateType = $type ?? C( 'template_default_type' );
		$templateCharset = $charset ?? C( 'template_default_charset' );
		$httpCacheControl = C( 'http_cache_control' );
		header( 'Content-Type:' . $templateType . '; charset=' . $templateCharset );
		header( 'Cache-Control: ' . $httpCacheControl );
		echo $data;
	}
	
	/**
	 * string protected function getTemplatePath(string $url=null)
	 */
	protected function getTemplatePath(string $url=null): string {
		$viewLayer = C( 'view_layer' );
		$templateSuffix = C( 'template_suffix' );
		$errPath = implode( '/', array( swift_path, 'view', '404.html' ) );
		
		if(!is_null($url)){
			$name = '[a-z]+([A-Z][a-z]+)*';
			$pattern = '/' . $name . '(\.' . $name . '){0,2}/';
			if (preg_match( $pattern, $url )) {
				$urlSections = explode( '.', $this->ctu( $url ) );
				$num = count( $urlSections );
				if (3 == $num) list ( $this->module, $this->controller, $this->action ) = $urlSections;
				elseif (2 == $num) list ( $this->controller, $this->action ) = $urlSections;
				elseif (1 == $num) list ( $this->action ) = $urlSections;
			} else return $errPath;
		}
		
		$path = implode( '/', array( app_path, $this->module, $viewLayer, $this->controller, $this->action . $templateSuffix ) );
		return is_file( $path ) ? $path : $errPath;
	}
	
	/**
	 * string protected function ctu(string $data)
	 */
	protected function ctu(string $data): string {
		$pattern = '/[A-Z]/';
		return preg_replace_callback($pattern, function(array $matches){
			return '_' . strtolower($matches[0]);
		}, $data);
	}
	//
}