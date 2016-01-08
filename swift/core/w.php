<?php

function yes(){
try {
				$dsn='mysql:host=localhost;port=3306;dbname=html;charset=utf8';
				$username='root';
				$password='goodwin@00000';
				$link = new \PDO( $dsn, $username, $password);
				echo 'ok';
			} catch ( \PDOException $e ) {
				// E($e->getMessage())
				print $e->getMessage()."<br />";
				yes();
			}
}


yes();
	