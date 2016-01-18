<?php
declare(strict_types = 1)
	;

namespace Swift;

class Enginex {
	$this->module='';
	$this->controller='';
	$this->action='';
	$this->hash='';
	
	/**
	 * void public funtion __construct(string $url)
	 */
	public function __construct(string $url){
		list($this->module, $this->controller, $this->action)=explode('.',$url);
		$this->hash=md5($url);
		if()
	}
	
	/**
	 */
	public function compiler(){
		
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
		$patterns = array( 
			'/' . $begin . '(\$' . $regular . ')' . $end . '/i', 
			'/' . $begin . '(\$' . $regular . ')\.(' . $regular . ')' . $end . '/i', 
			'/' . $begin . '(\$' . $regular . '):(' . $regular . ')' . $end . '/i' 
		);
		$replaces = array( 
			'<?php echo $1; ?>', 
			'<?php echo $1[\'$2\']; ?>', 
			'<?php echo $s1->$2; ?>' 
		);
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
			if (in_array( $datas [1], array( 
				'server', 
				'env' 
			) )) $datas [2] = strtoupper( $datas [2] );
			return '<?php echo $_' . strtoupper( $datas [1] ) . '[\'' . $datas [2] . '\']; ?>';
		}, $data );
	}
	//
}

$e = new Enginex();
echo $e->parseSysVar( 'hello,{$sys.server.userid}' );