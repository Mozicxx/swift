<?php
declare(strict_types = 1)
	;

namespace Swift;

class Sharp {
	/**
	 * template_suffix=".html"
	 * template_prototype_layer="Prototype"
	 * template_prototype_suffix=".prototype.html"
	 * template_cache_layer="Cache"
	 * template_cache_suffix=".cache.html";
	 */
	protected $md5 = '';
	protected $module = '';
	protected $controller = '';
	protected $action = '';
	protected $literals = array();
	protected $phps = array();
	
	/**
	 * void public funtion __construct(string $url) "shopSystem.onlineProduct.addType"
	 */
	public function __construct(string $url) {
		list ( $this->module, $this->controller, $this->action ) = explode( '.', $url );
		$this->hash = md5( $url );
	}
	
	/**
	 * string public function compiler(void)
	 */
	public function compiler() {
		$cacheOn = file_exists( $this->parseCachePath() );
		if ($cacheOn && ! $this->appDebug) return $this->getCache();
		$data = $this->getTemplate();
	}
	
	/**
	 * string protected function clearNativePhp(string $data)
	 */
	protected function clearNativePhp(string $data) {
		$pattern = '/<\?php\s.*?\?>/i';
		return preg_replace( $pattern, '', $data );
	}
	
	/**
	 * string protecred function parseLiteral(string $data)
	 * <literal>...something is here...</literal>
	 */
	protected function parseLiteral(string $data) {
		$this->literals = array();
		$pattern = '/<literal>(.*?)<\/literal>/i';
		return preg_replace_callback( $pattern, function ($matches) {
			$key = md5( $matches [1] );
			$this->literals [$key] = $matches [1];
			return '<literal>' . $key . '</literal>';
		}, $data );
	}
	
	/**
	 * string protected function restoreLiteral(string $data)
	 */
	public function restoreLiteral() {
		$pattern = '/<literal>(.*?)<\/literal>/i';
		$this->data = preg_replace_callback( $pattern, function ($matches) {
			$key = $matches [1];
			return isset( $this->literals [$key] ) ? $this->literals [$key] : '';
		}, $data );
		return $this;
	}
	
	/**
	 * string protected function parsePhp(string $data)
	 */
	protected function parsePhp(string $data) {
		$this->phps = array();
		$pattern = '/<php>(.*?)<\/php>/i';
		return preg_replace_callback( $pattern, function ($matches) {
			$key = md5( $matches [1] );
			$this->literals [$key] = $matches [1];
			return '<php>' . $key . '</php>';
		}, $data );
	}
	
	/**
	 * string protected function restorePhp(string $data)
	 */
	protected function restorePhp(string $data) {
		$pattern = '/<php>(.*?)<\/php>/';
		return preg_replace_callback( $pattern, function ($matches) {
			$key = $matches [1];
			return isset( $this->phps [$key] ) ? '<?php ' . $this->phps [$key] . ' ?>' : '';
		}, $data );
	}
	
	/**
	 * string protected function parseUserVar(string $data)
	 */
	public function parseUserVar(string $data) {
		$begin = C( 'template_left_delimiter' );
		$end = C( 'template_right_delimiter' );
		$regular = '[a-z][a-z0-9]*';
		$patterns = array( '/' . $begin . '(\$' . $regular . ')' . $end . '/i', '/' . $begin . '(\$' . $regular . ')\.(' . $regular . ')' . $end . '/i', '/' . $begin . '(\$' . $regular . '):(' . $regular . ')' . $end . '/i' );
		$replaces = array( '<?php echo $1; ?>', '<?php echo $1[\'$2\']; ?>', '<?php echo $s1->$2; ?>' );
		return preg_replace( $patterns, $replaces, $data );
	}
	
	/**
	 */
	public function parseSysVar(string $data) {
		$begin = C( 'template_left_delimiter' );
		$end = C( 'template_right_delimiter' );
		$regular = '[a-z][a-z0-9]*';
		$keys = 'server|env|request|get|post|session|cookie';
		$pattern = '/' . $begin . '\$sys\.(' . $keys . ')\.(' . $regular . ')' . $end . '/i';
		return preg_replace_callback( $pattern, function ($matches) {
			if (in_array( $matches [1], array( 'server', 'env' ) )) $matches [2] = strtoupper( $matches [2] );
			return '<?php echo $_' . strtoupper( $matches [1] ) . '[\'' . $matches [2] . '\']; ?>';
		}, $data );
	}
	
	/**
	 * string protected function getTemplate(void)
	 */
	protected function getTemplate() {
		$path = $this->parseTemplatePath();
		return $this->parseTemplate( $this->getFile( $path ) );
	}
	
	/**
	 * string protected function parseTemplatePath(void)
	 */
	protected function parseTemplatePath() {
		$viewLayer = C( 'view_layer' );
		$templateSuffix = C( 'template_suffix' );
		$module = $this->ctu( $this->module );
		$controller = $this->ctu( $this->controller );
		$action = $this->ctu( $this->action );
		$pathSections = array( app_path, $module, $viewLayer, $controller, $action . $templateSuffix );
		return implode( '/', $pathSections );
	}
	
	/**
	 * string protected function parseTemplate(string $data)
	 */
	protected function parseTemplate(string $data) {
		$data = $this->clearNativePhp( $data );
		$data = $this->parseSysVar( $this->parseUserVar( $this->parsePhp( $this->parseLiteral( $data ) ) ) );
		return $this->restoreLiteral( $this->restorePhp( $data ) );
	}
	
	/**
	 * string protected function parseCachePath(void)
	 */
	protected function parseCachePath() {
		$viewLayer = C( 'view_layer' );
		$templateCacheLayer = C( 'template_cache_layer' );
		$templateCacheSuffix = C( 'template_cache_suffix' );
		$module = $this->ctu( $this->module );
		$pathSections = array( app_path, $module, $viewLayer, $templateCacheLayer, $this->md5 . $templateCacheSuffix );
		return implode( '/', $pathSections );
	}
	
	/**
	 * string protected function getCache(void)
	 */
	protected function getCache() {
		$path = $this->parseCachePath();
		return $this->getFile( $path );
	}
	
	/**
	 * boolean protected function setCache(string $data)
	 */
	protected function setCache(string $data) {
		$path = $this->parseCachePath();
		return is_bool( file_put_contents( $path, $data ) ) ? false : true;
	}
	
	/**
	 * boolean protected function isProtetype(string $data)
	 */
	protected function isPrototype(string $data) {
		$regular = '[a-z][a-z0-9_]*';
		$pattern = '/<prototype url="(' . $regular . ')"><\/prototype>/';
		return preg_match( $pattern, $data ) ? true : false;
	}
	
	/**
	 * string protected function parsePrototypePath(string $url)
	 */
	protected function parsePrototypePath(string $url) {
		$viewLayer = C( 'view_layer' );
		$templatePrototypeLayer = C( 'template_prototype_layer' );
		$templatePrototypeSuffix = C( 'template_prototype_suffix' );
		$module = $this->ctu( $this->module );
		$pathSections = array( app_path, $module, $viewLayer, $templatePrototypeLayer, $url . $templatePrototypeSuffix );
		return implode( '/', $pathSections );
	}
	
	/**
	 * string protected function parsePrototype(string $data)
	 */
	protected function parsePrototype(string $data) {
		$regular = '[a-z][a-z0-9_]*';
		$patternPrototype = '/<prototype url="(' . $regular . ')"><\/prototype>/';
		preg_match( $patternPrototype, $data, $matches1 );
		$prototype = $this->getPrototype( $matches1 [1] );
		
		$patternDesign = '/<design id="(' . $regular . ')">(.*?)<\/design>/';
		$designsNum = preg_match_all( $patternDesign, $data, $matches2, PREG_PATTERN_ORDER );
		$designs = $designsNum ? array_combine( $matches2 [1], $matches2 [2] ) : array();
		return preg_replace_callback( $patternDesign, function ($matches) {
			$key = $matches [1];
			return $designs[$key] ?? $matches [2];
		}, $prototype );
	}
	
	/**
	 * string protected function getPrototype(string $url)
	 */
	protected function getPrototype($url) {
		$path = $this->parsePrototypePath( $url );
		return $this->getFile( $path );
	}
	
	/**
	 * string protected function getFile(string $path)
	 */
	protected function getFile(string $path) {
		$data = @file_get_contents( $path );
		return is_string( $data ) ? $data : '';
	}
	
	/**
	 * string protected function ctu(string $data)
	 */
	protected function ctu(string $data) {
		$pattern = '/[A-Z]/';
		return preg_replace_callback($pattern, function($matches){
			return '_' . $matches[0];
		} $data);
	}
	//
}
