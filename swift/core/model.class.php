<?php

namespace Swift;

class Model {
	protected $name = '';
	protected $datas = array();
	protected $database = null;
	
	/**
	 * void public function __construct(string $name=null, string $server=null)
	 */
	public function __construct(string $name = null, $dsn = null) {
		if ($this->isCamelCaseRegular( $name )) $this->name = $name;
		$this->database( $dsn );
	}
	
	/**
	 * boolean public function database(string $configKey=null)
	 */
	public function database(string $key=null) {
		$dsns = is_null( $key ) ? C( 'databases_dsn' ) : C( $key );
		if (! is_array( $datas ) or empty( $datas )) return false;
		elseif (! $this->isIntSeq( array_keys( $datas ), true )) return false;
		foreach ( $datas as $data ) {
			if (! $this->isDsn( $data )) return false;
		}
		if ($this->database) {
			$this->database->close();
			$this->database = null;
		}
		$this->database = new \Swift\Mysql( $datas );
		return $this->database ? true : false;
	}
	
	/**
	 * Model public function distinct(boolean $data=null)
	 */
	public function distinct(bool $data = null) {
		if ($this->database) {
			if (is_bool( $data )) $data = $data ? 'distinct' : 'all';
			$this->database->data( 'distinct', $data );
		}
		return $this;
	}
	
	/**
	 * Model public function field(string $datas=null)
	 */
	public function field(string $datas = null) {
		if ($this->database) $this->database->data( 'field', $datas );
		return $this;
	}
	
	/**
	 * Model public function table(string $datas=null)
	 */
	public function table(string $datas = null) {
		if ($this->database) $this->database->data( 'table', $datas );
		return $this;
	}
	
	/**
	 * Model public function join(string $data=null)
	 */
	public function join(string $data) {
		if ($this->database) $this->database->data( 'join', $data );
		return $this;
	}
	
	/**
	 * Model public function where(string $data=null)
	 */
	public function where(string $data = null) {
		if ($this->database) $this->database->data('where',$data)
		return $this;
	}
	
	/**
	 * Model public function group(string $datas=null)
	 */
	public function group(string $data) {
		if ($this->database) $this->database->data( 'group', $data );
		return $this;
	}
	
	/**
	 * Model public function having(string $data=null)
	 */
	public function having(string $data) {
		if ($this->database) $this->database->data( 'having', $data );
		return $this;
	}
	
	/**
	 * Model public function order(string $datas=null)
	 */
	public function order($datas) {
		if ($this->database) {
		}
		return $this;
	}
	
	/**
	 * Model public function limit(string $data=null)
	 */
	public function limit(string $data = null) {
		if ($this->database) {
		}
		return $this;
	}
	
	/**
	 * array public function create([array $fields=array(string $field,...)])
	 */
	public function create($fields = array()) {
		$this->clear();
		if (! is_array( $fields )) return $this->datas;
		elseif (! empty( $fields )) {
			if (! $this->isIntSeq( array_keys( $fields ), true )) return $this->datas;
			foreach ( $fields as &$value ) {
				if (! $this->isCamelCaseRegular( $value )) return $this->datas;
				$value = $this->camelCaseToDbRegular( $value );
			}
		}
		if (! empty( $_POST )) {
			$datas = $_POST;
			foreach ( $datas as $key => &$value ) {
				if (! empty( $fields ) && ! in_array( $key, $fields, true )) unset( $value );
				elseif (! $this->isDbRegular( $key )) unset( $value );
				elseif (is_array( $value )) $value = implode( '{}', $value );
			}
			if (! empty( $datas ) && $this->name && $this->database) {
				$fields = $this->database->fields( $this->name );
				if ($fields) {
					$names = array_keys( $fields );
					foreach ( $datas as $key => &$value ) {
						if (! in_array( $key, $names, true )) unset( $value );
						else $value = $this->changeDataType( $value, $this->mapDataType( $fields [$key] ['type'] ) );
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
		$this->clear();
		if (is_array( $datas ) && ! empty( $datas )) {
			foreach ( $datas as $key => &$value ) {
				if (! $this->isCamelCaseRegular( $key )) unset( $value );
				elseif (! is_scalar( $value ) && ! is_null( $value )) unset( $value );
			}
			if (! empty( $datas ) && $this->name && $this->database) {
				$fields = $this->database->fields( $this->name );
				if ($fields) {
					$names = array_keys( $fields );
					foreach ( $datas as $key => &$value ) {
						if (! in_array( $key, $names, true )) unset( $value );
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
	 * void public function clear()
	 */
	public function clear() {
		$this->datas = array();
	}
	
	/**
	 * array public function select(void)
	 */
	public function select() {
		return $this->database ? $this->database->select() : array();
	}
	
	/**
	 * integer public function add(void)
	 */
	public function add() {
		return $this->database ? $this->database->insert( $this->datas ) : - 1;
	}
	
	/**
	 * boolean|integer public function save(void)
	 */
	public function save() {
		return $this->database ? $this->database->update( $this->datas ) : false;
	}
	
	/**
	 * boolean|integer public function delete(void)
	 */
	public function delete() {
		return $this->database ? $this->database->delete() : false;
	}
	
	/**
	 * boolean protected function isDbRegular(string $data)
	 */
	protected function isDbRegular($data) {
		if (is_string( $data ) && $data != '') {
			$pattern = '/^([a-z]+_)*[a-z]+$/';
			return preg_match( $pattern, $data ) ? true : false;
		}
		return false;
	}
	
	/**
	 * boolean protected function isDbRegularPlus(string $data)
	 */
	protected function isDbRegularPlus($data) {
		if (is_string( $data ) && $data != '') {
			$arr = explode( '.', $data );
			if (count( $arr ) != 2) return false;
			foreach ( $arr as $value ) {
				if (! $this->isDbRegular( $value )) return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * boolean protected function isCamelCaseRegular(string $data)
	 */
	protected function isCamelCaseRegular($data) {
		if (is_string( $data )) {
			$pattern = '/^[a-z]+([A-Z][a-z]*)*$/';
			return preg_match( $pattern, $data ) ? true : false;
		}
		return false;
	}
	
	/**
	 * boolean protected function isSingleDsn(array $datas=array(string $key=>string|integer $value,...))
	 */
	protected function isSingleDsn($datas) {
		return $this->isDsn( $datas );
	}
	
	/**
	 * boolean protected function isDistributedDsn(array $datas=array('reads'=>array(...),'writes'=>array(...)))
	 */
	protected function isDistributedDsn($datas) {
		if (! is_array( $datas ) or empty( $datas )) return false;
		foreach ( $datas as $index => $data ) {
			if (! in_array( $index, array( 'reads', 'writes' ), true )) return false;
			elseif (! is_array( $data ) or empty( $data )) return false;
			elseif (! $this->isIntSeq( array_keys( $data ), true )) return false;
			foreach ( $data as $value ) {
				if (! $this->isDsn( $value )) return false;
			}
		}
		return true;
	}
	
	/**
	 * boolean protected function isDsn(array $datas=array(string $key=>string|integer $value,...))
	 */
	protected function isDsn($datas) {
		
		if (is_array( $datas ) && ! empty( $datas )) {
			foreach ( $datas as $key => $value ) {
				if (! in_array( $key, array( 'type', 'host', 'port', 'user', 'pwd', 'database', 'charset' ), true )) return false;
				elseif ('port' == $value && ! is_integer( $value )) return false;
				elseif (! is_string( $value ) or '' == $value) return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * boolean|string protected function camelCaseToDbRegular(string $data)
	 */
	protected function camelCaseToDbRegular($data) {
		if (is_string( $prop ) && $this->isCamelCaseRegular( $data )) {
			$pattern = '/([A-Z])/';
			$replace = '_$1';
			return strtolower( preg_replace( $pattern, $replace, $data ) );
		}
		return false;
	}
	
	/**
	 * boolean protected function rule(array $datas=array(array(str $field, str $rule[=>mixed $value], str $errMessage),...)
	 */
	protected function rule($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			if (! $this->isSequence( $datas )) return false;
			foreach ( $datas as $index => $data ) {
				if (! is_integer( $index )) return false;
				elseif (! $this->isRule( $data )) return false;
			}
			$this->rules = $datas;
			return true;
		}
		return false;
	}
	
	/**
	 * boolean protected function isRule(array $datas=array(str $field, str $rule[=>mixed $value], str $errMessage))
	 */
	protected function isRule($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			if (! $this->isSequence( $datas )) return false;
			switch (count( $datas )) {
				case 3 :
					list ( $key1, $key2, $key3 ) = array_keys( $datas );
					list ( $field, $value2, $errMessage ) = array_values( $datas );
					break;
				default :
					return false;
					break;
			}
			if (! is_integer( $key1 )) return false;
			elseif (! $this->isCamelCaseRegular( $field )) return false;
			elseif (! $this->integer( $key3 )) return false;
			elseif (! is_string( $errMessage )) return false;
			return true;
		}
		return false;
	}
	
	/**
	 * boolean|string protected function mapDataType(string $type)
	 */
	protected function mapDataType($type) {
		if ($this->database && is_string( $type ) && $type != '') {
			$maps = $this->database->map();
			foreach ( $maps as $index => $map ) {
				if (in_array( $type, $map, true )) return $index;
			}
		}
		return false;
	}
	
	/**
	 * mixed protected function changeDataType(scalar $data, string $type)
	 */
	protected function changeDataType($data, $type) {
		if (! is_scalar( $data ) && ! is_null( $data )) return $data;
		elseif (! is_string( $type ) or '' == $type) return $data;
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
		if (! is_array( $datas ) or empty( $data )) return false;
		elseif (! is_bool( $mode )) return false;
		$values = $mode ? array_filter( array_values( $datas ), 'is_integer' ) : array_values( $datas );
		foreach ( $values as $key => $value ) {
			if ($key != $value) return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function isStrSeq(array $datas [,boolean $mode])
	 */
	protected function isStrSeq($datas, $mode = false) {
		if (! is_array( $datas ) or empty( $data )) return false;
		elseif (! is_bool( $mode )) return false;
		$values = array_values( $datas );
		foreach ( $values as $value ) {
			if (! is_string( $value )) return false;
			elseif ($mode && '' == $value) return false;
		}
		return true;
	}
	//
}

