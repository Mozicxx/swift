<?php

namespace Swift;

class Model {
	protected $database = null;
	protected $trueTabName = null;
	protected $name = null;
	protected $datas = array();
	
	/**
	 * void public function __construct(str|null $tabName, str|array|null $dsn)
	 */
	public function __construct($tabName = null, $dsn = null) {
		if ($this->isProp( $tabName )) $this->tabName = $tabName;
		$this->database( $dsn );
	}
	
	/**
	 * boolean public function database(null|str|array $dsn)
	 */
	public function database($dsn = null) {
		if (is_null( $dsn )) $dsn = C( 'schema_dsn' );
		elseif (is_string( $dsn ) && $dsn != '') $dsn = C( '$dsn' );
		
		if (is_array( $dsn )) {
			if (! $this->single( $dsn ) && ! $this->ddb( $dsn )) return false;
			
			if ($this->database) {
				$this->database->close();
				unset( $this->database );
			}
			$this->database = new \Swift\Mysql( $dsn );
			return $this->database ? true : false;
		}
		return false;
	}
	
	/**
	 * Model public function distinct(null $datas)
	 * Model public function distinct(bool $datas)
	 */
	public function distinct($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['distinct'] );
			elseif (is_bool( $datas )) $sqls ['distinct'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function field(null $datas)
	 * Model public function field(array $datas=array(str $field|str $alias=>str $field,...))
	 * Model public function field(str $datas)
	 */
	public function field($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['field'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (! is_integer( $key ) && ! $this->nobody( $key )) return $this;
					elseif (! $this->nobody( $value ) && ! $this->nobodyPlus( $value )) return $this;
				}
				if (isset( $sqls ['field'] ) && ! is_array( $sqls ['field'] )) unset( $sqls ['field'] );
				$sqls ['field'] [] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['field'] );
				$sqls ['field'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * Model public function table(null $datas)
	 * Model public function table(array $datas=array(str $table|str $alias=>str $table,...))
	 * Model public function table(str $datas)
	 */
	public function table($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['table'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (! is_integer( $key ) && ! $this->nobody( $key )) return $this;
					elseif (! $this->nobody( $value )) return $this;
				}
				if (isset( $sqls ['table'] ) && ! is_array( $sqls ['table'] )) unset( $sqls ['table'] );
				$sqls ['table'] [] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['table'] );
				$sqls ['table'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * Model public function join(null $datas)
	 * Model public function join(array $datas=array([str $alias=>]str $r.field, [str $realtion=>]str $l.field),str $type)
	 * Model public function join(array $datas=array([str $alias=>]str $r.field, [str $realtion=>]str $l.field))
	 * Model public function join(str $datas)
	 */
	public function join($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['join'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				switch (count( $datas )) {
					case 3 :
						list ( $r, $l, $type ) = $datas;
						list ( $key1, $key2, $key3 ) = array_keys( $datas );
						if (! is_integer( $key3 )) return $this;
						elseif (! in_array( $type, array( 
							'inner', 
							'left', 
							'right' 
						), true )) return $this;
						break;
					case 2 :
						list ( $r, $l ) = $datas;
						list ( $key1, $key2 ) = array_keys( $datas );
						break;
					default :
						return $this;
						break;
				}
				if (! is_integer( $key1 ) && ! $this->nobody( $key1 )) return $this;
				elseif (! $this->nobodyPlus( $r )) return $this;
				elseif (! in_array( $key2, array( 
					'eq', 
					'neq' 
				), true )) return $this;
				elseif (! $this->nobodyPlus( $l )) return $this;
				elseif (isset( $sqls ['join'] ) && ! is_array( $sqls ['join'] )) unset( $sqls ['join'] );
				$sqls ['join'] [] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['join'] );
				$sqls ['join'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * Model public function where(null $datas)
	 * Model public function where(array $datas=array([str $logic=>]str [$alias.]$field, [str $operator=>]scalar $require))
	 * Model public function where(str $datas)
	 */
	public function where($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['where'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				switch (count( $datas )) {
					case 2 :
						list ( $key1, $key2 ) = array_keys( $datas );
						list ( $field, $require ) = $datas;
						break;
					default :
						return $this;
						break;
				}
				if (! is_integer( $key1 ) && ! in_array( $key1, array( 
					'and', 
					'or' 
				), true )) return $this;
				elseif (! $this->nobody( $field ) && ! $this->nobodyPlus( $field )) return $this;
				elseif (! is_integer( $key2 ) && ! in_array( $key2, array( 
					'eq', 
					'neq' 
				), true )) return $this;
				elseif (! is_scalar( $require ) && ! is_null( $require )) return $this;
				elseif (isset( $sqls ['where'] ) && ! is_array( $sqls ['where'] )) unset( $sqls ['where'] );
				$sqls ['where'] [] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['where'] );
				$sqls ['where'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * Model public function group(null $datas)
	 * Model public function group(array $datas=array(str [$alias.]$field[=>str $type],...)
	 * Model public function group(str $datas)
	 */
	public function group($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['group'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (is_integer( $key )) {
						if (! $this->nobody( $value ) && ! $this->nobodyPlus( $value )) return $this;
					} else {
						if (! $this->nobody( $key ) && ! $this->nobodyPlus( $key )) return $this;
						elseif (! in_array( $value, array( 
							'asc', 
							'desc' 
						), true )) return $this;
					}
				}
				if (isset( $sqls ['group'] ) && ! is_array( $sqls ['group'] )) unset( $sqls ['group'] );
				$sqls ['group'] [] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['group'] );
				$sqls ['group'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * Model public function order(null $datas)
	 * Model public function order(array $datas=array([str $alias.]$field[=>str $type],...))
	 * Model public function order(str $datas)
	 */
	public function order($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['order'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (is_integer( $key )) {
						if (! $this->nobody( $value ) && ! $this->nobodyPlus( $value )) return $this;
					} else {
						if (! $this->nobody( $key ) && ! $this->nobodyPlus( $key )) return $this;
						elseif (! in_array( $value, array( 
							'asc', 
							'desc' 
						), true )) return $this;
					}
				}
				if (isset( $sqls ['order'] ) && ! is_array( $sqls ['order'] )) unset( $sqls ['order'] );
				$sqls ['order'] [] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['order'] );
				$sqls ['order'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * Model public function limit(null $datas)
	 * Model public function limit(array $datas=array(int $offset, int $row))
	 * Model public function limit(int $datas)
	 * Model public function limit(str $datas)
	 */
	public function limit($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['limit'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				switch (count( $datas )) {
					case 2 :
						list ( $key1, $key2 ) = array_keys( $datas );
						list($offset, $row)=$datas
						break;
					default :
						return $this;
						break;
				}
				if ($key1 !== 0) return $this;
				elseif ($key2 !== 1) return $this;
				elseif (! is_integer( $offset )) return $this;
				elseif (! is_integer( $row )) return $this;
				unset( $sqls ['order'] );
				$sqls ['order'] = $datas;
			} elseif (is_integer( $datas )) {
				unset( $sqls ['limit'] );
				$sqls ['limit'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') {
				unset( $sqls ['limit'] );
				$sqls ['limit'] = $datas;
			}
		}
		return $this;
	}
	
	/**
	 * array public function create([array $fields=array(string $field,...)])
	 */
	public function create($fields = null) {
		$this->clear();
	}
	
	/**
	 * array public function data(array $datas=array(str $field=>scalar $value,...))
	 */
	public function data($datas) {
		$this->clear();
		if (is_array( $datas ) && ! empty( $datas )) {
			foreach ( $datas as $key => &$value ) {
				if (! $this->isProp( $key )) unset( $value );
				elseif (! is_scalar( $value ) && ! is_null( $value )) unset( $value );
			}
			if (! empty( $datas ) && $this->name && $this->database) {
				$fields = $this->database->fields( $this->name );
				if ($fields) {
					$keys = array_keys( $fields );
					foreach ( $datas as $key => &$value ) {
						if (! in_array( $key, $keys, true )) unset( $value );
					}
				}
			}
			$this->datas = $datas;
		}
		return $this->datas;
	}
	
	/**
	 * void public function clear()
	 */
	public function clear() {
		$this->datas = array();
	}
	
	/**
	 * string protected function dataType(scalar $data)
	 */
	protected function dataType($data) {
		if (is_string( $data )) return 'string';
		elseif (is_integer( $data )) return 'integer';
		elseif (is_float( $data )) return 'float';
		elseif (is_bool( $data )) return 'boolean';
		elseif (is_null( $data )) return 'null';
		return '';
	}
	
	/**
	 * array protectred function mapDataType(string $type)
	 */
	protected function mapDataType($type) {
		if (is_string( $type )) {
			$maps = array( 
				'string' => array(), 
				'integer' => array( 
					'tinyint', 
					'smallint', 
					'int', 
					'mediumint', 
					'bigint' 
				), 
				'float' => array(), 
				'boolean' => array(), 
				'null' => null 
			);
			return in_array( $type, array_keys( $maps ), true ) ? $maps [$type] : array();
		}
		return array();
	}
	
	/**
	 * boolean|array public function select()
	 */
	public function select() {
		return $this->database->select();
	}
	
	/**
	 * boolean public function add(array $datas=array(str $prop=>scalar $value),...)
	 */
	public function add($datas) {
		if (is_array( $datas )) {
			$inputs = array();
			foreach ( $datas as $key => $value ) {
				if (! is_string( $key )) continue;
				elseif (! $this->isProp( $key )) continue;
				elseif (! is_scalar( $value ) && ! is_null( $value )) continue;
				$inputs [$this->propToField( $key )] = $value;
			}
			return empty( $inputs ) ? false : $this->database->insert( $inputs );
		}
		return false;
	}
	
	/**
	 * boolean public function save(array $datas=array(str $prop=>scalar $value),...)
	 */
	public function save($datas) {
		if (is_array( $datas )) {
			$inputs = array();
			foreach ( $datas as $key => $value ) {
				if (! is_string( $key )) continue;
				elseif (! $this->isProp( $key )) continue;
				elseif (! is_scalar( $value ) && ! is_null( $value )) continue;
				$inputs [$this->propToField( $key )] = $value;
			}
			return empty( $inputs ) ? false : $this->database->update( $inputs );
		}
		return false;
	}
	
	/**
	 * boolean public function delete()
	 */
	public function delete() {
		return $this->database->delete();
	}
	
	/**
	 * bool protected function walk(array $arr, str $type)
	 */
	protected function walk($arr, $type) {
		if (empty( $arr )) return false;
		elseif (! is_array( $arr )) return false;
		elseif (! is_array( $type )) return false;
		switch ($type) {
			case 'integer' :
				foreach ( $arr as $value ) {
					if (! is_integer( $value )) return false;
				}
				break;
			case 'float' :
				foreach ( $arr as $value ) {
					if (! is_float( $value )) return false;
				}
				break;
			case 'string' :
				foreach ( $arr as $value ) {
					if (! is_string( $value )) return false;
				}
				break;
			case 'bool' :
				foreach ( $arr as $value ) {
					if (! is_bool( $value )) return false;
				}
				break;
			case 'null' :
				foreach ( $arr as $value ) {
					if (! is_null( $value )) return false;
				}
				break;
			case 'scalar' :
				foreach ( $arr as $value ) {
					if (! is_scalar( $value ) && ! is_null( $value )) return false;
				}
				break;
			case 'array' :
				foreach ( $arr as $value ) {
					if (! is_array( $value )) return false;
				}
				break;
			default :
				return false;
				break;
		}
		return true;
	}
	
	/**
	 * bool protected function walks($datas, $types)
	 */
	// protected function walks($datas, $types) {
	// if (empty ( $datas ) || empty ( $types )) return false;
	// elseif (! is_array ( $datas ) || ! is_array ( $types )) return false;
	// elseif (count ( $datas ) != $count ( $types )) return false;
	// $datas = array_combine ( $datas, $types );
	// foreach ( $datas as $key => $value ) {
	// if(!this->walk(array($key),$value))
	
	// return false;
	// }
	// return true;
	// }
	
	/**
	 * boolean protected function nobody(str $datas)
	 */
	protected function nobody($datas) {
		if (is_string( $datas )) {
			$pattern = '/([a-z])|([a-z][a-z_]{0,48}[a-z])/';
			return preg_match( $pattern, $value ) ? true : false;
		}
		return false;
	}
	
	/**
	 * boolean protected function nobodyPlus(str $datas)
	 */
	protected function nobodyPlus($datas) {
		if (is_string( $datas )) {
			$arr = explode( '.', $datas );
			if (count( $arr ) != 2) return false;
			foreach ( $arr as $value ) {
				if (! $this->nobody( $value )) return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * boolean protected function isProp(str $prop)
	 */
	protected function isProp($prop) {
		if (! is_string( $prop )) return false;
		$pattern = '/^[a-z]+([A-Z][a-z]*)*$/';
		return preg_match( $pattern, $prop ) ? true : false;
	}
	
	/**
	 */
	protected function isBetter($element) {
		if (! is_string( $element )) return false;
		$pattern = '/!^([a-z]+_)*[a-z]+$/';
		return preg_match( $pattern, $element ) ? true : false;
	}
	
	/**
	 * boolean protected function single(array $datas)
	 */
	protected function single($datas) {
		return $this->dsn( $datas );
	}
	
	/**
	 * boolean protected function ddb(array $datas)
	 */
	protected function ddb($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			foreach ( $datas as $index => $data ) {
				if (! in_array( $index, array( 
					'reads', 
					'writes' 
				), true )) return false;
				elseif (! is_array( $data )) return false;
				elseif (empty( $data )) return false;
				foreach ( $data as $key => $value ) {
					if (! is_integer( $key )) return false;
					elseif (! $this->dsn( $value )) return false
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * boolean protected function dsn(array $datas)
	 */
	protected function dsn($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			foreach ( $datas as $key => $value ) {
				if (! in_array( $key, array( 
					'host', 
					'port', 
					'user', 
					'pwd', 
					'database', 
					'charset' 
				), true )) return false;
				elseif ('port' == $value && ! is_integer( $value )) return false;
				elseif (! is_string( $value )) return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * bool|str protected function propToField(str $prop)
	 */
	protected function propToField($prop) {
		if (is_string( $prop )) {
			$pattern = '/([A-Z])/';
			$replace = '_$1';
			return strtolower( preg_replace( $pattern, $replace, $prop ) );
		}
		return false;
	}
	
	/**
	 * boolean protected function isSequence(array $datas)
	 */
	protected function isSequence($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			$keys = array_filter( array_keys( $datas ), 'is_integer' );
			foreach ( $keys as $key => $value ) {
				if ($key != $value) return false;
			}
			return true;
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
			elseif (! $this->isProp( $field )) return false;
			elseif (! $this->integer( $key3 )) return false;
			elseif (! is_string( $errMessage )) return false;
			return true;
		}
		return false;
	}
	
	/**
	 * boolean protected function isStrong(array $datas)
	 */
	protected function isStrong($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			foreach ( $datas as $key => $values ) {
				if ('' === $key or '' === $value) return false;
				elseif (is_array( $value )) {
					if (! $this->isStrong( $value )) return false;
				}
			}
			return true;
		}
		return false
	}
	//
}

$m = new Model();
$datas = array( 
	12 
);
print_r( $m->add( $datas ) );




















