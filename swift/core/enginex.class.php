<?php
declare(strict_types = 1)
	;

namespace Swift;

class Enginex {
	/**
	 * template_prototype="Prototype"
	 * template_prototype_extension=".prototype.tmpl"
	 * template_cache="Cache"
	 * template_cache_extension=".cache.tmpl";
	 */
	protected $md5 = '';
	protected $module = '';
	protected $controller = '';
	protected $view = '';
	
	/**
	 * void public funtion __construct(string $url)
	 */
	public function __construct(string $url) {
		list ( $this->module, $this->controller, $this->view ) = explode ( '.', $url );
		$this->hash = md5 ( $url );
	}
	
	/**
	 * string public function compiler(void)
	 */
	public function compiler() {
		$cacheOn = file_exists ( $this->parseCachePath () );
		if ($cacheOn && ! $this->appDebug) return $this->getCache ();
		$data = $this->getTemplate ();
	}
	
	/**
	 * string protected function clearNativePhp(string $data)
	 */
	protected function clearNativePhp($data) {
		$pattern = '/<\?php\s.*?>/i';
		return preg_replace($pattern, '', $data)
	}
	
	/**
	 */
	protected function parseLiteral(string $data) {
		$pattern = '/<literal>(.*)</literal>/i';
		return preg_replace_callback($pattern, function($matches){
			$this->literals[]=$matches[1];
			$key=count($this-.literals)-1;
			return '<literal>'.(string)$key.'</literal>';
		}, $data)
	}
	
	/**
	 * boolean protected function setCache(string $data)
	 */
	protected function setCache(string $data) {
		$path = $this->parseCachePath ();
		return is_bool ( file_put_contents ( $path, $data ) ) ? false : true;
	}
	
	/**
	 * string protected function getCache(void)
	 */
	protected function getCache() {
		$path = $this->parseCachePath ();
		return $this->getFile ( $path );
	}
	
	/**
	 * string protected function parseCachePath(void)
	 */
	protected function parseCachePath(string $url) {
		$pathSections = array ($this->appPath,$this->module,$this->viewLayer,$this->cache,$this->md5 . $this->cacheExtension );
		return implode ( '/', $pathSections );
	}
	
	/**
	 * string protected function getTemplate(void)
	 */
	protected function getTemplate() {
		$path = $this->parseTemplatePath ();
		return $this->getFile ( $path );
	}
	
	/**
	 * string protected function parseTemplatePath(void)
	 */
	protected function parseTemplatePath() {
		$pathSections = array ($this->appPath,$this->module,$this->viewLayer,$this->controller,$this->view . $this->templateExtension );
		return implode ( '/', $pathSections );
	}
	
	/**
	 * string protected function parsePrototype(string $data)
	 */
	protected function parsePrototype(string $data) {
		$regular = '[a-z][a-z0-9_]*';
		$patternPrototype = '/<prototype url="(' . $regular . ')"><\/prototype>/';
		$prototypesNum = preg_match_all ( $patternMaster, $data, $matches1, PREG_SET_ORDER );
		if ($masters !== 1) return $data;
		$prototype = $this->readPrototypeContent ( $matches1 [0] [1] );
		
		$patternDesign = '/<design id="(' . $regular . ')">(.*)<\/design>/';
		$designsNum = preg_match_all ( $patternDesign, $data, $matches2, PREG_PATTERN_ORDER );
		$designs = $designsNum ? array_combine ( $matchs2 [1], $matchs2 [2] ) : array ();
		return preg_replace_callback ( $patternDesign, function ($matches) {
			$key = $matches [1];
			return designs[$key]??
			
			
			
			$matches[2]
		}, $prototype );
	}
	
	/**
	 * string protected function getPrototype(string $url)
	 */
	protected function getPrototype($url) {
		$path = $this->parsePrototypePath ( $url );
		return $this->getFile ( $path );
	}
	
	/**
	 * string protected function parsePrototypePath(string $url)
	 */
	protected function parsePrototypePath(string $url) {
		$pathSections = array ($this->appPath,$this->module,$this->viewLayer,$this->prototype,$url . $this->prototypeExtension );
		return implode ( '/', $pathSections );
	}
	
	/**
	 * string protected function parseUserVar(string $output)
	 */
	public function parseUserVar(string $data) {
		// $ldelimiter = C('tmpl_l_delimiter');
		// $rdelimiter = C('tmpl_r_delimiter');
		$begin = "{";
		$end = "}";
		$regular = '[a-z][a-z0-9]*';
		$patterns = array ('/' . $begin . '(\$' . $regular . ')' . $end . '/i','/' . $begin . '(\$' . $regular . ')\.(' . $regular . ')' . $end . '/i','/' . $begin . '(\$' . $regular . '):(' . $regular . ')' . $end . '/i' );
		$replaces = array ('<?php echo $1; ?>','<?php echo $1[\'$2\']; ?>','<?php echo $s1->$2; ?>' );
		return preg_replace ( $patterns, $replaces, $data );
	}
	
	/**
	 */
	public function parseSysVar(string $data) {
		// $ldelimiter = C('tmpl_l_delimiter');
		// $rdelimiter = C('tmpl_r_delimiter');
		$begin = "{";
		$end = "}";
		$regular = '[a-z][a-z0-9_]*';
		$keys = 'server|env|request|get|post|session|cookie';
		$pattern = '/' . $begin . '\$sys\.(' . $keys . ')\.(' . $regular . ')' . $end . '/i';
		return preg_replace_callback ( $pattern, function ($datas) {
			if (in_array ( $datas [1], array ('server','env' ) )) $datas [2] = strtoupper ( $datas [2] );
			return '<?php echo $_' . strtoupper ( $datas [1] ) . '[\'' . $datas [2] . '\']; ?>';
		}, $data );
	}
	
	/**
	 * string protected function getFile(string $path)
	 */
	protected function getFile(string $path) {
		if (file_exists ( $path )) {
			$data = @file_get_contents ( $path );
			return is_string ( $data ) ? $data : '';
		}
		return '';
	}
	//
}