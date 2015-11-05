<?php

function insert($datas) {		
		if(empty($datas)) return false;
		elseif(is_array($datas)){
			$keys=array_keys($datas);
			foreach($keys as $key){
				if(!is_string($key)) return false;
			}
			$values=array_values($datas);
			foreach($values as &$value){		// default=?
				if(is_integer($value)||is_float($value)) $value=$value;
				elseif(is_string($value)) $value="'".$value."'";
				elseif(is_bool($value)) $value=$value?'1':'0';
				elseif(is_null($value)) $value='null';
				else return false;		
			}
			$keyStr=implode(',',$keys);
			$valueStr=implode(',',$values);
			
			//$this->sql();
			$sql='insert into '.'blog'. '(' . $keyStr . ') values(' . $valueStr . ')';
			//return $this->execute ( $sql );
			return $sql;
		}
		return false;
	}
	
	/*********************/
		$datas='';
		$datas=array();
		$datas=array("name"=>"luna","sex"=>"female","age"=>5.2323432);
		echo '['.insert($datas).']';