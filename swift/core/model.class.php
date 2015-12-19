<?php

namespace Swift;

class Model {
	protected $database = null;
	
	/**
	 */
	public function __construct($name = '', $dsn = array()) {
		if (! empty ( $name ) && is_string ( $name )) {
			$this->name = $name;
		}
		$this->database ( $dsn );
	}
	
	/**
	 */
	public function database($dsn = array(), $options = array()) {
		if (empty ( $dsn )) $dsn = C ( 'schema_dsn' );
		elseif (is_string ( $dsn )) $dsn = C ( $dsn );
		elseif (is_array ( $dsn )) )
		else return false;
		
		foreach ( $dsn as $value ) {
			if (! is_string ( $value )) return false;
		}
		
		if ($this->database) {
			$this->database->close ();
			unset ( $this->database );
		}
		
		$this->database = new \Swift\Mysql ( $dsn, $options );
		return $this->database ? true : false;
	}
	
	/**
	 * Model public function distinct(null $datas)
	 * Model public function distinct(bool $datas)
	 */
	public function distinct($datas) {
		if (is_null ( $datas )) unset ( $this->database->datas ['distinct'] );
		elseif (is_bool ( $datas )) $this->database->datas ['distinct'] = $datas;
		return $this;
	}
	
	/**
	 * Model public function field(null $datas)
	 * Model public function field(array $datas=array(str $field[=>str $alias],...))
	 * Model public function field(str $datas)
	 */
	public function field($datas) {
		if (is_null ( $datas )) unset ( $this->database->datas ['field'] );
		elseif (is_array ( $datas ) && ! empty ( $datas )) {
			if (! $this->walk ( $datas, 'str' )) return $this;
			elseif (isset ( $this->database->datas ['field'] ) && ! is_array ( $this->database->datas ['field'] )) unset ( $this->database->datas ['field'] );
			$this->database->datas ['field'] [] = $datas;
		} elseif (is_string ( $datas ) && $datas != '') {
			unset ( $this->database->datas ['field'] );
			$this->database->datas ['field'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function table(null $datas)
	 * Model public function table(array $datas=array(str $name[=>str $alias],...))
	 * Model public function table(str $datas)
	 */
	public function table($datas) {
		if (is_null ( $datas )) unset ( $this->database->datas ['table'] );
		elseif (is_array ( $datas ) && ! empty ( $datas )) {
			if (! $this->walk ( $datas, 'str' )) return $this;
			elseif (isset ( $this->database->datas ['table'] ) && ! is_array ( $this->database->datas ['table'] )) unset ( $this->database->datas ['table'] );
			$this->database->datas ['field'] [] = $datas;
		} elseif (is_string ( $datas ) && $datas != '') {
			unset ( $this->database->datas ['table'] );
			$this->database->datas ['table'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function join(null $datas)
	 * Model public function join(array $datas=array([str $type,] [str alias=>]str right.field, [str $relation=>]str left.field ))
	 * Model public function join(str $datas)
	 */
	public function join($datas) {
		if (is_null ( $datas )) unset ( $this->database->datas ['join'] );
		elseif (is_array ( $datas ) && ! empty ( $datas )) {
			switch (count ( $datas )) {
				case 3 :
					list ( $type, $r, $l ) = $datas;
					list ( $key1, $key2, $key3 ) = array_keys ( $datas );
					break;
				case 2 :
					list ( $r, $l ) = $datas;
					list ( $key2, $key3 ) = array_keys ( $datas );
					break;
				default :
					return $this;
					break;
			}
			if (isset ( $type ) && ! in_array ( $type, array ('inner','left','right' ) )) return $this;
			elseif (isset ( $key1 ) && ! is_integer ( $key )) return $this;
			elseif (! $this->nobody2 ( $r )) return $this;
			elseif (is_string ( $rkey ) && ! $this->nobody ( $rkey )) return $this;
			elseif (! $this->nobody2 ( $l )) return $this;
			elseif (is_string ( $lkey ) && ! in_array ( $rkey, array ('eq','neq' ) )) return $this;
			elseif (isset ( $this->database->datas ['join'] ) && ! is_array ( $this->database->datas ['join'] )) unset ( $this->database->datas ['join'] );
			$this->database->datas ['join'] [] = $datas;
		} elseif (is_string ( $datas ) && $datas != '') {
			unset ( $this->database->datas ['join'] );
			$this->database->datas ['join'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function where(null $datas)
	 * Model public function where(array $datas=array(str [$alias.]$field|str [$alias.]$field=>str $logic, str $operator=>str $require))
	 * Model public function where(str $datas)
	 */
	public function where($datas) {
		$sqls=&$this->database->datas;
		if(is_null($datas)) unset($sqls['where']);
		elseif(is_array($datas)&&2==count($datas)){
			list($key1, $key2)=array_keys($datas);
			list($value1, $value2)=$datas;
			if(is_integer($key1)&&$this->nobody($value1)&&$this->nobody2($value1)) return $this;
			elseif(is_string($key1)&&$this->nobody($key1)&&$this->nobody2($key1)) return $this;
			elseif(is_string($key1)&&!in_array($value1, array('and','or'), true)) return $this;
			elseif(!in_array($key2, array('eq','neq'), true)) return $this;
			elseif(!is_scalar($value2)&&!is_null($value2)) return $this;
			elseif (isset ($sqls ['where'] ) && ! is_array ( $sqls ['where'] )) unset ( $sqls ['where'] );
			$sqls['where'] [] = $datas;
		}
		elseif(is_string($datas)&&$datas!=''){
			unset ( $sqls ['where'] );
			$sqls ['where'] = $datas;
		}
		return $this;
	}
	
	/**
	 * Model public function group(null $datas)
	 * Model public function group(array $datas=array((str [alias.]field=>str method)|(str [alias.]field),...)
	 * Model public function group(str $datas)
	 */
	public function group($datas){
		$sqls=&$this->database->datas;
		if(is_null($datas)) unset($sqls['group']);
		elseif(is_array($datas)){
			foreach($datas as $key=>$value){
				if(is_integer($key)){
					if($this->nobody($value)&&$this->nobody2($value)) return $this;
				}
				else{
					if($this->nobody($key)&&$this->nobody2($key)) return $this;
					elseif(!in_array($value, array('asc','desc'), true)) return $this;
				}
			}
			if (isset ($sqls ['group'] ) && ! is_array ( $sqls ['group'] )) unset ( $sqls ['group'] );
			$sqls['group'] [] = $datas;
		}
		elseif(is_string($datas)){
			unset ( $sqls ['group'] );
			$sqls ['group'] = $datas;
		}
		return $this;
	}
	
	/**
	 */
	public function order($datas) {
		if (is_string ( $datas )) {
			$this->database->datas ['order'] = $datas;
		} elseif (is_array ( $datas )) {
			$this->database->datas ['order'] [] = $datas;
		}
		return $this;
	}
	
	/**
	 */
	public function limit($datas) {
		unset ( $this->database->datas ['limit'] );
		$this->database->datas ['limit'] = $datas;
		return $this;
	}
	

	
	/**
	 */
	public function select() {
		return $this->database->select ();
	}
	
	/**
	 */
	public function add($datas) {
		return $this->database->insert ( $datas );
	}
	
	/**
	 */
	public function save($datas) {
		return $this->database->update ( $datas );
	}
	
	/**
	 */
	public function delete() {
		return $this->database->delete ();
	}
	
	/**
	 * bool protected function walk(array $arr, str $type)
	 */
	protected function walk($arr, $type) {
		if (empty ( $arr )) return false;
		elseif (! is_array ( $arr )) return false;
		elseif (! is_array ( $type )) return false;
		switch ($type) {
			case 'integer' :
				foreach ( $arr as $value ) {
					if (! is_integer ( $value )) return false;
				}
				break;
			case 'float' :
				foreach ( $arr as $value ) {
					if (! is_float ( $value )) return false;
				}
				break;
			case 'string' :
				foreach ( $arr as $value ) {
					if (! is_string ( $value )) return false;
				}
				break;
			case 'bool' :
				foreach ( $arr as $value ) {
					if (! is_bool ( $value )) return false;
				}
				break;
			case 'null' :
				foreach ( $arr as $value ) {
					if (! is_null ( $value )) return false;
				}
				break;
			case 'scalar' :
				foreach ( $arr as $value ) {
					if (! is_scalar ( $value ) && ! is_null ( $value )) return false;
				}
				break;
			case 'array' :
				foreach ( $arr as $value ) {
					if (! is_array ( $value )) return false;
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
	protected function walks($datas, $types) {
		if (empty ( $datas ) || empty ( $types )) return false;
		elseif (! is_array ( $datas ) || ! is_array ( $types )) return false;
		elseif (count ( $datas ) != $count ( $types )) return false;
		$datas = array_combine ( $datas, $types );
		foreach ( $datas as $key => $value ) {
			if(!this->walk(array($key),$value)) 
			
			
			
			
			
			
			
			
			return false;
		}
		return true;
	}
	
	/**
	 * boolean protected function nobody(str $datas)
	 */
	protected function nobody($datas) {
		if(is_string($datas)){
			$pattern = '/([a-z])|([a-z][a-z_]{0,48}[a-z])/';
			return preg_match($pattern,$value)?true:false;
		}
		return false;
	}
	
	/**
	 * boolean protected function nobody2(str $datas)
	 */
	protected function nobody2($datas){
		if(is_string($datas)){
			$arr=explode('.',$datas`);
			if(count($arr)!=2) return false;
			foreach($arr as $value){
				if(!$this->nobody($value)) return false;
			}
			return true;
		}
		return false;
	}
	//
}






















