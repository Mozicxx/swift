<?php

namespace Swift;

/**
 */
function loadConfig($file) {
	if (is_file ( $file ) && ('php' == pathinfo ( $file, PATHINFO_EXTENSION ))) {
		$configs = include $file;
		if (is_array ( $configs )) {
			foreach ( $configs as $key => $value ) {
				C ( $key, $value );
			}
			unset ( $vlaue );
		}
	}
}

/**
 */
function loadFuncs($file) {
	if (is_file ( $file )) {
		include_once $file;
	}
}

/**
 */
function C($name = null, $value = null) {
	static $_configs = array ();
	if (0 == func_num_args ()) {
		return $_configs;
	}
	if (1 == func_num_args ()) {
		if (is_array ( $name )) {
			$_configs = array_merge ( $_configs, $name );
		} else {
			return $_configs [$name];
		}
	}
	if (2 == func_num_args ()) {
		$_configs [$name] = $value;
	}
}

/**
 */
function controller($name, $path = '') {
	$layer = C ( 'controller_layer' );
	$appName = C ( 'app_name' );
	$module = module_name;
	$controller = controller_name;
	
	$layer = C ( 'DEFAULT_C_LAYER' );
	if (! C ( 'APP_USE_NAMESPACE' )) {
		$class = parse_name ( $name, 1 ) . $layer;
		import ( MODULE_NAME . '/' . $layer . '/' . $class );
	} else {
		$class = ($path ? basename ( ADDON_PATH ) . '\\' . $path : MODULE_NAME) . '\\' . $layer;
		$array = explode ( '/', $name );
		foreach ( $array as $name ) {
			$class .= '\\' . parse_name ( $name, 1 );
		}
		$class .= $layer;
	}
	if (class_exists ( $class )) {
		return new $class ();
	} else {
		return false;
	}
}




















