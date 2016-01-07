<?php

function where($datas) {
		if (is_string( $datas )) return 'where ' . $datas;
		elseif (is_array( $datas )) {
			foreach ( $datas as $data ) {
				list ( $logic, $operator ) = array_keys( $data );
				list ( $field, $require ) = array_values( $data );
				is_integer( $logic ) ? $logic = 'and' : null;
				is_integer( $operator ) ? $operator = 'eq' : null;
				//$field = $this->backquote( $field );
				
				switch ($operator) {
					case 'eq' :
						if (is_integer( $require ) or is_float( $require )) $require = ( string ) $require;
						elseif (is_string( $require )) $require = "'$require'";
						elseif (is_bool( $require )) $require = empty( $require ) ? '0' : '1';
						elseif (is_null( $require )) $require = 'null';
						else return '';
						$sqls[]=$field.'='.$require.' '.$logic;
						break;
					case 'neq' :
						if (is_integer( $require ) || is_float( $require )) $require = ( string ) $require;
						elseif (is_string( $require )) $require = "'$require'";
						elseif (is_bool( $require )) $require = empty( $require ) ? '0' : '1';
						elseif (is_null( $require )) $require = 'null';
						else return '';
						$sqls[]=$field.'!='.$require.' '.$logic;
						break;
					default:
						return '';
						break;
				}
			}
			$sql = implode( ' ', $sqls );
			return empty( $sql ) ? '' : 'where ' . substr( $sql, 0, strrpos( $sql, ' ' ) );
		}
		return '';
	}
	
	$datas=array(array('userName','luna'),array('sex','neq'=>'male'));
	print where('aaaa');