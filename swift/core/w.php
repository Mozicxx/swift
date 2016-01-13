<?php

	declare(strict_types=1);

	$dsn="mysql:host=localhost;port=3306;dbname=html;charset=utf8";
	$username='root';
	$password='goodwin@000';
$database=new PDO($dsn, $username, $password);
$sql="delete from aa";
$ds=$database->prepare($sql);
$result=$ds->execute();
var_dump($result);
var_dump($ds->fetchAll( PDO::FETCH_ASSOC ));