<?php
function where($datas) {
		if(is_string($datas)&&''!=$datas){
			return 'where '.trim($datas).' ';
		}
		if(is_array($datas)&&count($datas)>0){
			$datas=array_change_key_case($datas);
			$sqls=array();
			foreach($datas as $key=>$value){
				if(is_string($value)||is_numeric($value)){
					$sqls[]='(`'.$key.'`='.(is_string($value)?"'$value'":(string)$value).')';
				}
			}
			return count($sqls)>0?'where '.implode(' and ',$sqls).' ':'';
		}
		return '';
	}
	
	//$datas="`id`>10 and `id`<20";
	//$datas=array('id'=>12,'name'=>'goodwin','sex'=>'male');
	$datas=array(12,array(10,12));
	echo "{".where($datas)."}";