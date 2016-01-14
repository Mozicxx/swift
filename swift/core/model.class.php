<?php

namespace Swift;

class Model {
	protected $name = null;
	protected $datas = array();
	protected $database = null;
	
	/**
	 * void public function __construct(str|null $tabName, str|array|null $dsn)
	 */
	public function __construct($tabName = null, $dsn = null) {
		if ($this->isLowerCamelCase( $tabName )) $this->tabName = $tabName;
		$this->database( $dsn );
	}
	
	/**
	 * boolean public function database([null $dsn])
	 * boolean public function database(array $dsn=array(string $key=>string|integer $value,...)|array('reads'=>array(...), 'writes'=>array(...)))
	 * boolean public function database(string $dsn)
	 */
	public function database($dsn = null) {
		if (is_null( $dsn )) $dsn = C( 'database_dsn' );
		elseif (is_string( $dsn ) && $dsn != '') $dsn = C( $dsn );
		
		if (! is_array( $dsn ) or empty( $dsn )) return false;
		elseif (! $this->isSingleDsn( $dsn ) && ! $this->isDdbDsn( $dsn )) return false;
		elseif ($this->database) {
			$this->database->close();
			$this->database = null;
		}
		$this->database = new \Swift\Mysql( $dsn );
		return $this->database ? true : false;
	}
	
	/**
	 * Model public function distinct(null $datas)
	 * Model public function distinct(boolean $datas)
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
	 * Model public function field(string $datas=null)
	 */
	public function field(string $datas = null) {
		if ($this->database) $this->database->data( 'field', $datas );
		return $this;
	}
	
	/**
	 * Model public function table(null $datas)
	 * Model public function table(array $datas=array([string $alias=>]string $table,...))
	 * Model public function table(string $datas)
	 */
	public function table($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['table'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (! is_integer( $key ) && ! $this->isDbRegular( $key )) return $this;
					elseif (! $this->isDbRegular( $value )) return $this;
				}
				$sqls ['table'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') $sqls ['table'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function join(null $datas)
	 * Model public function join(array $datas=array(array(...),...)
	 * Model public function join(string $datas)
	 */
	public function join($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['join'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				if (! $this->isIntSeq( array_keys( $datas ), true )) return $this;
				foreach ( $datas as $value ) {
					if (! $this->isJoinChild( $value )) return $this;
				}
				$sqls ['join'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') $sqls ['join'] = $datas;
		}
		return $this;
	}
	
	/**
	 * boolean protected function isJoinChild(array $datas=array([string $alias=>]string $r.field ,[string $operator=>]string $l.field [,string $type]))
	 */
	protected function isJoinChild($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			if (! $this->isIntSeq( array_keys( $datas ) )) return false;
			switch (count( $datas )) {
				case 3 :
					list ( $alias, $operator, $nobody ) = array_keys( $datas );
					list ( $rfield, $lfield, $type ) = array_values( $datas );
					if (! is_integer( $nobody )) return false;
					elseif (! in_array( $type, array( 'inner', 'left', 'right' ), true )) return false;
					break;
				case 2 :
					list ( $alias, $operator ) = array_keys( $datas );
					list ( $rfield, $lfield ) = array_values( $datas );
					break;
				default :
					return $this;
					break;
			}
			if (! is_integer( $alias ) && ! $this->isDbRegular( $alias )) return false;
			elseif (! $this->isDbRegularPlus( $rfield )) return false;
			elseif (! in_array( $operator, array( 'eq', 'neq' ), true )) return false;
			elseif (! $this->isDbRegularPlus( $lfield )) return false;
			return true;
		}
		return false;
	}
	
	/**
	 * Model public function where(null $datas)
	 * Model public function where(array $datas=array(array(...),...))
	 * Model public function where(string $datas)
	 */
	public function where($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['where'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				if (! $this->isIntSeq( $datas, true )) return $this;
				foreach ( $datas as $value ) {
					if (! $this->isWhereChild( $value )) return $this;
				}
				$sqls ['where'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') $sqls ['where'] = $datas;
		}
		return $this;
	}
	
	/**
	 * boolean protected function isWhereChild(array $datas=array([string $logic=>]string $field, [string $operator=>] scalar $require))
	 */
	protected function isWhereChild($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			if (count( $datas ) != 2) return false;
			elseif (! $this->isIntSeq( array_keys( $datas ) )) return false;
			list ( $logic, $operator ) = array_keys( $datas );
			list ( $field, $require ) = array_values( $datas );
			if (! is_integer( $logic ) && ! in_array( $logic, array( 'and', 'or' ), true )) return false;
			elseif (! is_integer( $operator ) && ! in_array( $operator, array( 'eq', 'neq' ), true )) return false;
			elseif (! $this->isDbRegular( $field ) && ! $this->isDbRegularPlus( $field )) return false;
			elseif (! is_scalar( $require ) && ! is_null( $require )) return false;
			return true;
		}
		return false;
	}
	
	/**
	 * Model public function group(null $datas)
	 * Model public function group(array $datas=array(string $field[=>string $type],...)
	 * Model public function group(string $datas)
	 */
	public function group($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['group'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (is_integer( $key )) {
						if (! $this->isDbRegular( $value ) && ! $this->isDbRegularPlus( $value )) return $this;
					} else {
						if (! $this->isDbRegular( $key ) && ! $this->isDbRegularPlus( $key )) return $this;
						elseif (! in_array( $value, array( 'asc', 'desc' ), true )) return $this;
					}
				}
				$sqls ['group'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') $sqls ['group'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function having(null $datas)
	 * Model public function having(array $datas=array(array(...),...))
	 * Model public function having(string $datas)
	 */
	public function having($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['having'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				if (! $this->isIntSeq( $datas, true )) return $this;
				foreach ( $datas as $data ) {
					if (! $this->isHavingChild( $data )) return $this;
				}
				$sqls ['having'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') $sqls ['having'] = $datas;
		}
		return $this;
	}
	
	/**
	 * boolean protected function isHavingChild(array $datas=array([string $logic=>]string $field, [string $operator=>] scalar $require))
	 */
	protected function isHavingChild($datas) {
		if (is_array( $datas ) && ! empty( $datas )) {
			if (count( $datas ) != 2) return false;
			elseif (! $this->isIntSeq( array_keys( $datas ) )) return false;
			list ( $logic, $operator ) = array_keys( $datas );
			list ( $field, $require ) = array_values( $datas );
			if (! is_integer( $logic ) && ! in_array( $logic, array( 'and', 'or' ), true )) return false;
			elseif (! is_integer( $operator ) && ! in_array( $operator, array( 'eq', 'neq' ), true )) return false;
			elseif (! $this->isDbRegular( $field ) && ! $this->isDbRegularPlus( $field )) return false;
			elseif (! is_scalar( $require ) && ! is_null( $require )) return false;
			return true;
		}
		return false;
	}
	
	/**
	 * Model public function order(null $datas)
	 * Model public function order(array $datas=array(string $field[=>string $type],...))
	 * Model public function order(string $datas)
	 */
	public function order($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['order'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				foreach ( $datas as $key => $value ) {
					if (is_integer( $key )) {
						if (! $this->isDbRegular( $value ) && ! $this->isDbRegularPlus( $value )) return $this;
					} else {
						if (! $this->isDbRegular( $key ) && ! $this->isDbRegularPlus( $key )) return $this;
						elseif (! in_array( $value, array( 'asc', 'desc' ), true )) return $this;
					}
				}
				$sqls ['order'] = $datas;
			} elseif (is_string( $datas ) && $datas != '') $sqls ['order'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function limit(null $datas)
	 * Model public function limit(array $datas=array(integer $offset, integer $row))
	 * Model public function limit(integer $datas)
	 * Model public function limit(string $datas)
	 */
	public function limit($datas) {
		if ($this->database) {
			$sqls = &$this->database->datas;
			if (is_null( $datas )) unset( $sqls ['limit'] );
			elseif (is_array( $datas ) && ! empty( $datas )) {
				if (count( $datas ) != 2) return $this;
				elseif (! $this->isIntSeq( array_keys( $datas ), true )) return $this;
				list ( $offset, $row ) = array_values( $datas );
				if (! is_integer( $offset ) or $offset <= 0) return $this;
				elseif (! is_integer( $row ) or $row <= 0) return $this;
				$sqls ['order'] = $datas;
			} elseif (is_integer( $datas ) && $datas > 0) $sqls ['limit'] = $datas;
			elseif (is_string( $datas ) && $datas != '') $sqls ['limit'] = $datas;
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
	 * boolean|array public function select(void)
	 */
	public function select() {
		return $this->database ? $this->database->select() : false;
	}
	
	/**
	 * boolean|scalar public function add(void)
	 */
	public function add() {
		return $this->database ? $this->database->insert( $this->datas ) : false;
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

