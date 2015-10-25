<?php

/**
 */
$version = '5.5.0';
if (version_compare ( PHP_VERSION, $version, '<' )) {
	die ( 'Current PHP version is ' . PHP_VERSION . ' ,require PHP version greater than ' . $version );
}

/**
 */
define ( 'swift_path', './swift' );

/**
 */
require './swift/dispatcher.php';