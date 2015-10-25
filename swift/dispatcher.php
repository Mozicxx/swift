<?php

/**
 */
defined ( 'swift_path' ) ?  : define ( 'swift_path', './swift' );

/**
 */
$depr = '/';
require swift_path . $depr . 'core' . $depr . 'core.class.php';
$system = new \Swift\Core ();
//$system->fire ();
	