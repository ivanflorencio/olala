<?php

	/**********************************************************************
	 * 	Descrição: 	Classe que implementa conexão com SGBD MySQL 				
	 * 	
	 * 	Autor: 		Ivan Florencio
	 *  Data: 		00/00/0000 
	 * 
	 **********************************************************************/

	class OlalaSQLServerConnection {
		
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
			
			$serverName = $this->hostName; //serverName\instanceName
			$connectionInfo = array( "Database"=>$this->dbName, "UID"=>$this->userName, "PWD"=>$this->password);
			
			$this->conn = mssql_connect($serverName, $connectionInfo);
			
            return $this->conn;            
        }
		
		function close(){
        	return mssql_close($this->conn);        	
        }
        
	}
	