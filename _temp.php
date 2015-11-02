<?php
function column($datas) {
		if (empty ( $datas )) return '';
		elseif (is_string ( $datas )) return $datas;
		elseif (is_array ( $datas )) {
			$sqls = array ();
			$datas = array_filter ( $datas, 'is_array' );
			foreach ( $datas as $data ) {
				$data = array_filter ( $data, 'is_string' );
				foreach ( $data as $key=>$value ) {
					$sqls [] = is_integer($key)?$value:$value.' as '.$key;
				}
			}
			return implode ( ',', $sqls );
		}
		return '';
	}

/**
 * *********************
 */

$datas[0]=array("id","name");
$datas[1]=array("sex");
$datas[2]=array("grade"=>"sum(name)");
echo "[" . column ( $datas ) . "]";