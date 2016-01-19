<?php
declare(strict_types = 1)
	;

namespace Swift;

class Enginex {
	
	/**
	 * void public funtion __construct(string $url)
	 */
	public function __construct(string $url) {
		list ( $this->module, $this->controller, $this->action ) = explode( '.', $url );
		$this->hash = md5( $url );
	}
	
	/**
	 */
	protected function getTemplateUrl(string $name) {
		$datas = explode( '.', $name );
		if(count($datas)==2) list($controller, $action)=$datas;
		elseif(count($datas)==1) list($action)=$datas;
		else return '';
		$keys=array('app_path', 'view_layer', 'template_extension');
		list($app, $view, $extension)=C($keys);
		$controller=$controller??$this->controller;
		return implode('/', array($app, $this->module, $view, $controller, $action.$extension));
	}
	
	/**
	 */
	protected function getMasterTemplateUrl(string $name) {
		$keys = array( 'app_path', 'view_layer', 'template_master_layer', 'template_master_extension' );
		list ( $appPath, $viewLayer, $masterLayer, $masterExtension ) = C( $keys );
		return implode( '/', array( $app, $this->module, $view, $master, $name . $extension ) );
	}
	
	/**
	 * string protected function readTemplateData(string $url)
	 */
	protected function readTemplate(string $path) {
	}
	
	/**
	 * string public function compiler(void)
	 */
	public function compiler() {
	}
	
	/**
	 * string protected function parsePrototype(string $data)
	 */
	protected function parsePrototype(string $data) {
		$regular = '[a-z][a-z0-9_]*';
		$patternMaster = '/<master url="(' . $regular . ')"><\/master>/i';
		$masters = preg_match_all( $patternMaster, $data, $matchs, PREG_SET_ORDER );
		if ($masters !== 1) return $data;
		$url = $matchs [0] [1];
		$path = implode( '/', array( C( 'app_path' ), $this->module, 'view', $url . 'tmpl' ) );
		
		$patternDesign='/<design id="()">(.*)</design>/i';
		
		
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
		$patterns = array( '/' . $begin . '(\$' . $regular . ')' . $end . '/i', '/' . $begin . '(\$' . $regular . ')\.(' . $regular . ')' . $end . '/i', '/' . $begin . '(\$' . $regular . '):(' . $regular . ')' . $end . '/i' );
		$replaces = array( '<?php echo $1; ?>', '<?php echo $1[\'$2\']; ?>', '<?php echo $s1->$2; ?>' );
		return preg_replace( $patterns, $replaces, $data );
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
		return preg_replace_callback( $pattern, function ($datas) {
			if (in_array( $datas [1], array( 'server', 'env' ) )) $datas [2] = strtoupper( $datas [2] );
			return '<?php echo $_' . strtoupper( $datas [1] ) . '[\'' . $datas [2] . '\']; ?>';
		}, $data );
	}
	
	//
}

$e = new Enginex();
echo $e->parseSysVar( 'hello,{$sys.server.userid}' );