<?php
class mydb{
		public $connection=null;
	
	public function __construct(){
			$dsn = 'mysql:dbname=mysql;host=127.0.0.1';
			$user = 'root';
			$password = 'goodwin@000';

			try {
				$this->connection = new PDO($dsn, $user, $password);
			} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
			}
	}
	
		public function getColumns($table) {
		if (empty ( $table )) return false;
		elseif (is_string ( $table )) {
			$arr = explode ( '.', $table );
			if (2 == count ( $arr )) {
				list ( $db, $table ) = $arr;
				$sql = 'show columns from `' . $db . '`.`' . $table . '`';
			} elseif (1 == count ( $arr )) {
				list ( $table ) = $arr;
				$sql = 'show columns from `' . $table . '`';
			} else
				return false;
			$result = $this->connection->query ( $sql );
			$datas = array ();
			if ($result) {
				foreach ( $result as $row ) {
					$rowData=array();
					$row = array_change_key_case ( $row );
					foreach($row as $key=>$value){
						if(is_string($key))
						$rowData[$key]=strtolower($value);
					}
					$datas [$row ['field']] = $rowData;
				}
			}
			return $datas;
		}
		return false;
	}
	//
}
	/*********************/
	$db=new mydb();
	print_r($db->getColumns('user'));
	
	
	
	
	
	
	