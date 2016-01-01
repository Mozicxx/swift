<?php

namespace Swift;

class Validator {
	
	/**
	 * boolean public functioin required(str $data)
	 */
	public function required($data) {
		return '' === $data ? false : true;
	}
	
	/**
	 * boolean public function email(str $data)
	 */
	public function email($data) {
		if (! is_string( $data )) return false;
		$pattern = '//';
		return preg_match( $pattern, $data ) ? true : false;
	}
}