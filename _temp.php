<?php
class mydb{
		protected $link;
		public $configs=array(
			'host'=>'localhost',
			'port'=>'3306',
			'socket'=>'/temp/mysql.sock',
			'dbname'=>'swift',
			'charset'=>'utf8',
			'user'=>'root',
			'password'=>'1qaz2wsx',
			'options'=>array(),
		);
		protected $options = array (
		PDO::ATTR_CASE => PDO::CASE_LOWER,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES => false ,
		'dkfjdkjfkdjfk'=>false,
	);
		
	public function column( $datas ) {
		if (empty( $datas )) return '';
		elseif (is_string( $datas )) return $datas;
		elseif (is_array( $datas )) {
			$sqls = array ();
			foreach ( array_filter( $datas, 'is_array' ) as $data ) {
				foreach ( array_filter( $data, 'is_string' ) as $key => $value ) {
					$sqls[] = is_integer( $key ) ? $value : $value . ' as ' . $key;
				}
			}
			return implode( ',', $sqls );
		}
		return '';
	}
	//
}
	/*********************/
	$datas[]=array('id','name'=>'myname',null, 12, 'sex');
	$datas[]=array('grade','max'=>'sum(sex)');
	$db=new mydb();
	echo $db->column($datas);
	
	
	
	
	
	
	