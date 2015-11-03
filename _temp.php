<?php

function where($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {				
				foreach($data as $value){
						if(!is_string($value)) continue 2;
				}				
				switch (count($data)) {
					case 3 :
						list ( $column, $condition, $logic ) = $data;
						$sqls[]='('.$column.$condition.') '.$logic;
						break;
					case 2 :
						list ( $column, $condition ) = $data;
						$sqls[]='('.$column.$condition.') and';
						break;
				}
			}
			$sql=implode(' ',$sqls);
			return empty($sql)?'':substr($sql, 0, strrpos($sql, ' '));
		}
		return '';
	}
	
	/*********************/
	$datas='';
	$datas=array();
	$datas=3;
	$datas="id=5 and name='luna'";
	$datas=array();
	$datas[]=array('id','=12', array());
	$datas[]=array('name',"='luna'","or");
	$datas[]=array('sex',"is not null", 'or');
		echo '['.where($datas).']';