<?php

namespace Swift;

class Core {
	
	/**
	 * Core protected function getSysFunc(void)
	 */
	protected function getSysFunc() {
		$function = 'func';
		$fileName = 'swift.func.php';
		$path = implode( '/', array( swift_path, $function, $fileName ) );
		if (is_file( $path )) include $path;
		return $this;
	}
	
	/**
	 * Core protected function getSysConfig(void)
	 */
	protected function getSysConfig() {
		$config = 'conf';
		$fileName = 'swift.conf.php';
		$path = implode( '/', array( swift_path, $config, $fileName ) );
		if (is_file( $path )) {
			$configs = include $path
			if (is_array( $configs )) {
				foreach ( $configs as $key => $value ) {
					C( $key, $value );
				}
			}
		}
		return $this;
	}
	
	/**
	 * Core protected function getSysCore(void)
	 */
	protected function getSysCore() {
		$sysCore = 'core';
		$path = implode( '/', array( swift_path, $sysCore ) );
		if (is_dir( $path )) {
			$files = scandir( $path );
			foreach ( $files as $file ) {
				$file = $dir . $depr . $value;
				if (is_file( $file ) && ('.class.php' == substr( $value, - 10 )) && ('core.class.php' != $value)) {
					require $file;
				}
			}
		}
		return $this;
	}
	
	/**
	 * Core protected function getSysLibrary(void)
	 */
	protected function getSysLibrary() {
		$sysLibrary = 'library';
		$path = implode( '/', array( swift_path, $sysLibrary ) );
		if (is_dir( $path )) {
			$files = scandir( $path );
			foreach ( $files as $file ) {
				$filePath=implode('/',array($path, $file))
				if (is_file( $filePath ) && strotolower( substr( $file, - 4 ) ) == '.php') include $filePath;
			}
		}
		return $this;
	}
	
	/**
	 * void public function fire(void)
	 */
	public function fire() {
		$this->getSysFunc()->getSysConfig()->getSysCore()->getSysLibrary();
	}
	//
}
