<?php

$pattern = '/^([a-z]+).*$/';
preg_match( $pattern, 'text', $matchs );
echo $matchs[1];