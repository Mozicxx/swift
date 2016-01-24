<?php
declare(strict_types = 1);

/**
 */
$version = '7.0.2';
$message = 'Current PHP version is ' . PHP_VERSION . ' ,require PHP version greater than ' . $version;
if (version_compare( PHP_VERSION, $version, '<' )) die( $message );

/**
 */
require './swift/dispatcher.php';