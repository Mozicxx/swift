<?php
declare(strict_types = 1);

/**
 */
defined( 'swift_path' ) ?: define( 'swift_path', './swift' );
defined( 'app_path' ) ?: define( 'app_path', './app' );

/**
 */
$sysCore = 'core';
$path = implode( '/', array( swift_path, $sysCore, 'core.class.php' ) );
require $path;
$system = new \Swift\Core();
$system->fire();
$app = new \Swift\App();
$app->fire();
	