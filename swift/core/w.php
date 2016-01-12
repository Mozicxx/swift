<?php

	declare(strict_types=1);

	/**
	 * void public function data(string $key [,string $value])
	 */
	 function data(string $key, string $value=null): bool{
		 $keysRegular=array('distinct', 'field', 'table', 'join', 'where', 'group', 'having', 'order', 'limit' );
		 if(!in_array($key, $keysRegular)) return false;
	 }
	 
	var_dump( data('field',null));