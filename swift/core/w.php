<?php

// $dsn="mysql:host=localhost;port=3306;dbname=html;charset=utf8";
// $username='root';
// $password='goodwin@000';
// $database=new PDO($dsn, $username, $password);
// var_dump($database->lastInsertId());
// $sql="insert into ko(name) values('luna')";
// $ds=$database->prepare($sql);
// $result=$ds->execute();
// var_dump($database->lastInsertId());
// $sql="delete from ko0";
// $ds=$database->prepare($sql);
// $result=$ds->execute();
// var_dump((int)$database->lastInsertId());

// echo '-----<br />';

// if($var=array('12')) echo 'NOOOOOOOOOOOOOOOOOOO';
// var_dump($var);

function parseLiteral($data) {
		$pattern = '/<literal>(.*)<\/literal>/i';
		return preg_replace_callback($pattern, function($matches){
			$literals[]=$matches[1];
			$key=count($literals)-1;
			return '<literal>aa</literal>';
		}, $data);
	}
	
$data="<literal>luna</literal>rtrtrt<literal>tom</literal>";
var_dump(parseLiteral($data));