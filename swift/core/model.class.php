<?php
declare(strict_types = 1);

namespace Swift;

class Model {
	protected $name = null;
	protected $datas = array ();
	protected $database = null;
	
	/**
	 * void public function __construct(string $name=null, string $server=null)
	 */
	public function __construct(string $name = null, $dsn = null) {
		if ($this->isCamelCaseRegular ( $name )) $this->name = $name;
		$this->database ( $dsn );
	}
	
	/**
	 * boolean public function database(string $key=null)
	 */
	public function database(string $key = null): bool {
		$dsns = is_null ( $key ) ? C ( 'database_dsn' ) : C ( $key );
		if (! is_array ( $dsns ) or empty ( $dsns )) return false;
		elseif (! $this->isIntSeq ( array_keys ( $dsns ), true )) return false;
		foreach ( $dsns as $dsn ) {
			if (! $this->isDsn ( $dsn )) return false;
		}
		if ($this->database) {
			$this->database->close ();
			$this->database = null;
		}
		$this->database = new \Swift\Mysql ( $dsns );
		return $this->database ? true : false;
	}
	
	/**
	 * Model public function distinct(boolean $data=null)
	 */
	public function distinct(bool $data = null): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'distinct' );
			else {
				$data = $data ? 'distinct' : 'all';
				$this->database->data ( 'distinct', $data );
			}
		}
		return $this;
	}
	
	/**
	 * Model public function field(string $data=null)
	 */
	public function field(string $data = null): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'field' );
			else $this->database->data ( 'field', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function table(string $data=null)
	 */
	public function table(string $data = null): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'table' );
			else $this->database->data ( 'table', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function join(string $data=null)
	 */
	public function join(string $data): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'join' );
			else $this->database->data ( 'join', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function where(string $data=null)
	 */
	public function where(string $data = null): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'where' );
			else $this->database->data ( 'where', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function group(string $data=null)
	 */
	public function group(string $data=null): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'group' );
			else $this->database->data ( 'group', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function having(string $data=null)
	 */
	public function having(string $data=null): Model {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'having' );
			else $this->database->data ( 'having', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function order(string $data=null)
	 */
	public function order($datas) {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'order' );
			else $this->database->data ( 'order', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function limit(string $data=null)
	 */
	public function limit(string $data = null) {
		if ($this->database) {
			if (is_null ( $data )) $this->database->clearData ( 'limit' );
			else $this->database->data ( 'limit', $data );
		}
		return $this;
	}
	
	/**
	 * array public function create([array $fields=array(string $field,...)])
	 */
	public function create($fields = array()) {
		$this->clear ();
		if (! is_array ( $fields )) return $this->datas;
		elseif (! empty ( $fields )) {
			if (! $this->isIntSeq ( array_keys ( $fields ), true )) return $this->datas;
			foreach ( $fields as &$value ) {
				if (! $this->isCamelCaseRegular ( $value )) return $this->datas;
				$value = $this->camelCaseToDbRegular ( $value );
			}
		}
		if (! empty ( $_POST )) {
			$datas = $_POST;
			foreach ( $datas as $key => &$value ) {
				if (! empty ( $fields ) && ! in_array ( $key, $fields, true )) unset ( $value );
				elseif (! $this->isDbRegular ( $key )) unset ( $value );
				elseif (is_array ( $value )) $value = implode ( '{}', $value );
			}
			if (! empty ( $datas ) && $this->name && $this->database) {
				$fields = $this->database->fields ( $this->name );
				if ($fields) {
					$names = array_keys ( $fields );
					foreach ( $datas as $key => &$value ) {
						if (! in_array ( $key, $names, true )) unset ( $value );
						else $value = $this->changeDataType ( $value, $this->mapDataType ( $fields [$key] ['type'] ) );
					}
				}
			}
			$this->datas = $datas;
		}
		return $this->datas;
	}
	
	/**
	 * array public function data(array $datas=array(string $field=>scalar $value,...))
	 */
	public function data($datas) {
		$this->clear ();
		if (is_array ( $datas ) && ! empty ( $datas )) {
			foreach ( $datas as $key => &$value ) {
				if (! $this->isCamelCaseRegular ( $key )) unset ( $value );
				elseif (! is_scalar ( $value ) && ! is_null ( $value )) unset ( $value );
			}
			if (! empty ( $datas ) && $this->name && $this->database) {
				$fields = $this->database->fields ( $this->name );
				if ($fields) {
					$names = array_keys ( $fields );
					foreach ( $datas as $key => &$value ) {
						if (! in_array ( $key, $names, true )) unset ( $value );
					}
				}
			}
			$this->datas = $datas;
		}
		return $this->datas;
	}
	
	/**
	 */
	public function validate() {
	}
	
	/**
	 * void public function clear(void)
	 */
	public function clear() {
		$this->datas = array ();
	}
	
	/**
	 * array public function select(void)
	 */
	public function select(): array {
		return $this->database ? $this->database->select () : array ();
	}
	
	/**
	 * integer public function add(void)
	 */
	public function add(): int {
		return $this->database ? $this->database->insert ( $this->datas ) : - 1;
	}
	
	/**
	 * integer public function save(void)
	 */
	public function save(): int {
		return $this->database ? $this->database->update ( $this->datas ) : 0;
	}
	
	/**
	 * integer public function delete(void)
	 */
	public function delete() {
		return $this->database ? $this->database->delete () : 0;
	}
	
	/**
	 * boolean protected function isDbRegular(string $data)
	 */
	protected function isDbRegular(string $data): bool {
		$pattern = '/^([a-z]+_)*[a-z]+$/';
		return preg_match ( $pattern, $data ) ? true : false;
	}
	
	/**
	 * boolean protected function isDbRegularPlus(string $data)
	 */
	protected function isDbRegularPlus(string $data): bool {
		$dataSections = explode ( '.', $data );
		if (count ( $dataSections ) != 2) return false;
		foreach ( $dataSections as $value ) {
			if (! $this->isDbRegular ( $value )) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isCamelCaseRegular(string $data)
	 */
	protected function isCamelCaseRegular($data) {
		if (is_string ( $data )) {
			$pattern = '/^[a-z]+([A-Z][a-z]*)*$/';
			return preg_match ( $pattern, $data ) ? true : false;
		}
		return false;
	}
	
	/**
	 * boolean protected function isDsn(array $datas=array(string $key=>string|integer $value,...))
	 */
	protected function isDsn($datas) {
		if (is_array ( $datas ) && ! empty ( $datas )) {
			foreach ( $datas as $key => $value ) {
				if (! in_array ( $key, array ('type','host','port','user','pwd','database','charset' ), true )) return false;
				elseif ('port' == $value && ! is_integer ( $value )) return false;
				elseif (! is_string ( $value ) or '' == $value) return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * boolean|string protected function camelCaseToDbRegular(string $data)
	 */
	protected function camelCaseToDbRegular($data) {
		if (is_string ( $prop ) && $this->isCamelCaseRegular ( $data )) {
			$pattern = '/([A-Z])/';
			$replace = '_$1';
			return strtolower ( preg_replace ( $pattern, $replace, $data ) );
		}
		return false;
	}
	
	/**
	 * boolean protected function rule(array $datas=array(array(str $field, str $rule[=>mixed $value], str $errMessage),...)
	 */
	protected function rule($datas) {
		
	}
	
	/**
	 * boolean protected function isRuleRegular(array $datas=array(str $field, str $rule[=>mixed $value], str $errMessage))
	 */
	protected function isRuleRegular(array $datas): bool {
		
	}
	
	/**
	 * boolean|string protected function mapDataType(string $type)
	 */
	protected function mapDataType($type) {
		if ($this->database && is_string ( $type ) && $type != '') {
			$maps = $this->database->map ();
			foreach ( $maps as $index => $map ) {
				if (in_array ( $type, $map, true )) return $index;
			}
		}
		return false;
	}
	
	/**
	 * mixed protected function changeDataType(scalar $data, string $type)
	 */
	protected function changeDataType($data, $type) {
		if (! is_scalar ( $data ) && ! is_null ( $data )) return $data;
		elseif (! is_string ( $type ) or '' == $type) return $data;
		switch ($type) {
			case 'string' :
				return ( string ) $data;
				break;
			case 'integer' :
				return ( integer ) $data;
				break;
			case 'float' :
				return ( float ) $data;
				break
			case 'boolean' :
				return ( boolean ) $data;
				break;
			case 'null' :
				return ( unset ) $data;
				break;
			default :
				return $data;
				break;
		}
	}
	
	/**
	 * boolean protected function isIntSeq(array $datas [,boolean $mode])
	 */
	protected function isIntSeq($datas, $mode = false) {
		if (! is_array ( $datas ) or empty ( $data )) return false;
		elseif (! is_bool ( $mode )) return false;
		$values = $mode ? array_filter ( array_values ( $datas ), 'is_integer' ) : array_values ( $datas );
		foreach ( $values as $key => $value ) {
			if ($key != $value) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isStrSeq(array $datas [,boolean $mode])
	 */
	protected function isStrSeq($datas, $mode = false) {
		if (! is_array ( $datas ) or empty ( $data )) return false;
		elseif (! is_bool ( $mode )) return false;
		$values = array_values ( $datas );
		foreach ( $values as $value ) {
			if (! is_string ( $value )) return false;
			elseif ($mode && '' == $value) return false;
		}
		return true;
	}
	//
}

