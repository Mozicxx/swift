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
		
	public function connect() {
		if (! isset( $this->link )) {
			try {
				$this->link = new \PDO( $this->dsn(), $this->configs['user'], $this->configs['password'], $this->options);
			} catch ( \PDOException $e ) {
				echo $e->getMessage();		// E() ?
			}
		}
		return $this->link;
	}
	
	protected function dsn() {
		$dsn = array();
		if (! empty( $this->configs['host'] )) {
			$dsn []= 'host=' . $this->configs['host'];
			! empty( $this->configs['port'] ) ? $dsn []='port=' . $this->configs['port']: null;
		} elseif (! empty( $this->configs['socket'] )) {
			$dsn []= 'unix_socket=' . $this->configs['socket'];
		}
		! empty( $this->configs['dbname'] ) ? $dsn []= 'dbname='.$this->configs['dbname']:null;
		! empty( $this->configs['charset'] ) ? $dsn []= 'charset='.$this->configs['charset']:null;
		return 'mysql:'.implode(';',$dsn);
	}
	
	public function aa(){
		$this->options=array_merge($this->options, array(PDO::ATTR_STRINGIFY_FETCHES => true ));
		print_r($this->options);
	}
	//
}
	/*********************/
	$db=new mydb();
	$db->connect();
	
	
	
	
	
	
	