<?php

namespace Swift;

class Core {
	
	/**
	 */
	protected function loadConfig() {
		$depr = '/';
		$file = swift_path . $depr . 'conf' . $depr . 'swift.conf.php';
		if (is_file ( $file )) {
			$configs = include $file;
			if (is_array ( $configs )) {
				foreach ( $configs as $key => $value ) {
					C ( $key, $value );
				}
				unset ( $vlaue );
			}
		}
		return $this;
	}
	
	/**
	 */
	protected function loadFunc() {
		$depr = '/';
		$file = swift_path . $depr . 'func' . $depr . 'swift.func.php';
		if (is_file ( $file )) {
			include $file;
		}
		return $this;
	}
	
	/**
	 */
	protected function loadCore() {
		$depr = '/';
		$dir = swift_path . $depr . 'core';
		if (is_dir ( $dir )) {
			$arr = scandir ( $dir );
			foreach ( $arr as $value ) {
				$file = $dir . $depr . $value;
				if (is_file ( $file ) && ('.class.php' == substr ( $value, - 10 )) && ('core.class.php' != $value)) {
					require $file;
				}
			}
		}
		return $this;
	}
	
	/**
	 */
	protected function loadLibrary() {
		$depr = '/';
		$dir = swift_path . $depr . 'library';
		if (is_dir ( $dir )) {
			$arr = scandir ( $dir );
			foreach ( $arr as $value ) {
				$file = $dir . $depr . $value;
				if (is_file ( $file ) && ('.php' == substr ( $value, - 4 ))) {
					include $file;
				}
			}
		}
		return $this;
	}
	
	/**
	 */
	public function fire() {
		$this->loadConfig ()->loadFunc ()->loadCore ()->loadLibrary ();
	}
	//
}
