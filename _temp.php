<?php


/**
 * *********************
 */
$datas = "inner join users u on p.id=u.pid";
$datas = array ();
$datas = '';
$datas = array ('users' );
$datas = array ("users","u" );
$datas [0] = array ("users","u","p.id=u.pid","left" );
$datas [1] = array ("user_details","ud","u.id=ud.id","right" );
echo "[" . sjoin ( $datas ) . "]";