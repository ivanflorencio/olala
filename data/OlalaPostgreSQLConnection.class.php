<?php

	/**
	 * 	Descrição: 	Classe que implementa conexão com SGBD PostgreSQL 				
	 * 	
	 * 	Autor: 		Ivan Florencio
	 *  Data: 		24/09/2014 
	 * 
	 */

	class OlalaPostgreSQLConnection {
		
		public static $instancy;
		
		public $hostName = DB_HOST;
		public $userName = DB_USER;
        public $password = DB_PASSWORD;
        public $dbName = DB_NAME;
                
       	public $conn;
       
        public function __construct($dbNameSuffix = false) {
        	if ($dbNameSuffix) {
				$constants = get_defined_constants();
				$this->dbName = $constants['DB_HOST'.$dbNameSuffix];
				$this->hostName = $constants['DB_USER'.$dbNameSuffix];
				$this->password = $constants['DB_PASSWORD'.$dbNameSuffix];
				$this->userName = $constants['DB_NAME'.$dbNameSuffix];
			}
			$this->connect();
		}
		
		public static function singleton($dbNameSuffix = '')
	    {
	        if (1 || !isset(self::$instancy)) {
	            $c = __CLASS__;
	            self::$instancy = new $c($dbNameSuffix);
	        }	
	        return self::$instancy;
	    }
		
		function connect() {

			if ($this->password) {
				$this->conn = pg_connect("host=$this->hostName port=5432 dbname=$this->dbName user=$this->userName password=$this->password");
			} else {
				$this->conn = pg_connect("host=$this->hostName port=5432 dbname=$this->dbName user=$this->userName");
			}
			                 
            return $this->conn;            
        }
		
		function close(){
        	return pg_close($this->conn);        	
        }
        
	}
	
	