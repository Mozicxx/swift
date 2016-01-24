<?php
declare(strict_types = 1);

namespace Swift;

class App {
	
	/**
	 * App protected function getFunc(void)
	 */
	protected function getFunc() {
		$appFunction = 'func';
		$path = implode( '/', array( app_path, $appFunction ) );
		if (is_dir( $path )) {
			$files = scandir( $path );
			foreach ( $files as $file ) {
				$filePath = implode( '/', array( $path, $file ) );
				if (! is_file( $filePath )) continue;
				elseif (strtolower( substr( $file, - 4 ) ) != '.php') continue;
				include $filePath;
			}
		}
		return $this;
	}
	
	/**
	 * App protected function getConfig(void)
	 */
	protected function getConfig() {
		$appConfig = 'conf';
		$fileName = 'app.conf.php';
		$path = implode( '/', array( app_path, $appConfig, $fileName ) );
		if (is_file( $path )) {
			$configs = include $path;
			if (is_array( $configs )) {
				foreach ( $configs as $key => $value ) {
					C( $key, $value );
				}
			}
		}
		return $this;
	}
	
	/**
	 * App protected function getLibrary(void)
	 */
	protected function getLibrary() {
		$appLibrary = 'library';
		$path = implode( '/', array( app_path, $appLibrary ) );
		if (is_dir( $path )) {
			$files = scandir( $path );
			foreach ( $files as $file ) {
				$filePath = implode( '/', array( $path, $file ) );
				if (! is_file( $filePath )) continue;
				elseif (strtolower( substr( $file, - 4 ) ) != '.php') continue;
				include $filePath;
			}
		}
		return $this;
	}
	
	/**
	 * void public function fire(void)
	 */
	public function fire() {
		$this->getFunc()->getConfig()->getLibrary();
	}
	//
}