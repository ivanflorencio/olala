<?php

	/**
	 *  CLASSE DE ACESSO AOS DADOS DO OBJETO
	 *  Criado por: Ivan Florencio
	 *  Dia: 12/03/2013
	 *
	 */
	 
	class OlalaDB {
		
		public $tableName;		
		public $foreignKeys = array();
		public $types = array();
		public $nulls = array();
		public $sizes = array();
		public $comments = array();
		
		public $primaryKey;
		public $primaryKeyType;
		public $columns = array();
		public $resultSize = 0;
		public $queryFilter = array();
		
		protected $connection;
		protected $textFilter = array();
		protected $where;
		protected $dbNameSuffix;
		protected $resultColumns = "*";
		protected $tableAlias = array();
		protected $EOL = '/*[\n]*/';
		protected $TAB = '/*[\t]*/';
		
		protected $resultOffset = 0;
		
		function OlalaDB() {
			
		}
		
		//adiciona alias para tabela
		protected function addAlias($table, $alias, $type = 'fk') {
		    if (!isset($this->tableAlias[$type . '_' . $table]) || empty($this->tableAlias[$type . '_' . $table])) {
		        $this->tableAlias[$type . '_' . $table] = $alias;
		        
		    }
		} 
		
		//recupera alias para tabela
		public function getAlias($table, $type = 'fk') {
		    if (isset($this->tableAlias[$type . '_' . $table])) {
		        return $this->tableAlias[$type . '_' . $table];
		    } else {
		        return $table;
		    }
		}
		
		//adiciona filtro do tipo like '%texto%'
	    public function addTextFilter($column, $filter) {
	    	array_push($this->textFilter, array('column'=>$column, 'filter'=>OlalaUtil::antiSQLInjection($filter)));
		}

		//Seta campo que ordena a consulta
	    public function orderBy($column, $isDescending = false) {
	    	if ($column == 'RAND' || $column == 'RANDON') {
	    		$this->queryFilter['olalaOrderBy'] = 'RAND()';
	    	} else {
	    		$this->queryFilter['olalaOrderBy'] = '`[[TABLE_NAME]]`.' . $column . (($isDescending)?' DESC':'');
	    	}
	    }
	    
	    //adiciona filtro com operador ou sem
	    public function addFilter($column, $operatorOrFilter, $filter = false) {
	    	if (!$filter) {
	    	    if ($operatorOrFilter == 'IS NULL' || $operatorOrFilter == 'IS NOT NULL') {
	    	         $this->where("AND $column $operatorOrFilter");
	    	    } else {
	    		     $this->queryFilter[$column] = OlalaUtil::antiSQLInjection($operatorOrFilter);
	    	    }
	    	} else {
	    		if ($operatorOrFilter != 'IN' && $operatorOrFilter != 'NOT IN') {
	    			$filter = '\''. $filter .'\'';
	    		}
	    		$this->where(" AND `$column` $operatorOrFilter $filter ");
	    	}
	    }
	    	    
	    //adiciona filtro NULL
	    public function isNull($column) {
	    	$this->where("AND $column IS NULL");	    	
	    }
	    
	    //adiciona filtro NULL
	    public function isNotNull($column) {
	    	$this->where("AND $column IS NOT NULL");
	    }
	    
	    //adiciona filtro OR IS NULL
	    public function orIsNull($column) {
	        $this->where("OR ($column IS NULL)");
	    }
	     
	    //adiciona filtro OR IS NOT NULL
	    public function orIsNotNull($column) {
	        $this->where("OR ($column IS NOT NULL)");
	    }
	    
	    //adiciona clausula WHERE
	    public function where($clause) {
	    	$this->where .=  ' ' . $clause . ' ';	    	
	    }
	    
	    //limita o resultado
	    public function limit($size, $offset = 0) {
	    	$this->resultSize = $size;
	    	$this->resultOffset = $offset;
	    }
	    
	    //escolhe as colunas do resultado da busca
	    public function setResultColumns($columns) {
	    	$this->resultColumns =  $columns;
	    }
	    		
	    //limpar filtros
	    public function clear() {
	    	$this->where = "";
	    	$this->resultColumns = "*";
	    	$this->queryFilter = array();	    	
	    }
	    
	    //Verifica a existência de um valor em uma string de valores seraparadas por vírgula ou outro separador
	    public function hasValue($value, $inColumn, $delimiter = ',') {
	        $this->where("AND ($inColumn LIKE '$value$delimiter%' OR $inColumn LIKE '%$delimiter$value$delimiter%' OR $inColumn LIKE '%$delimiter$value' OR $inColumn = '$value')");
	    }
	    
	    //Verifica a existência de um valor em uma string de valores seraparadas por vírgula ou outro separador
	    public function orHasValue($value, $inColumn, $delimiter = ',') {
	        $this->where("OR ($inColumn LIKE '$value$delimiter%' OR $inColumn LIKE '%$delimiter$value$delimiter%' OR $inColumn LIKE '%$delimiter$value' OR $inColumn = '$value')");
	    }
	    
	    public function notHasValue($value, $inColumn, $delimiter = ',') {
	        $this->where("AND ($inColumn IS NULL OR $inColumn = '' OR ($inColumn NOT LIKE '{$value}$delimiter%' AND $inColumn NOT LIKE '%$delimiter{$value}$delimiter%' AND $inColumn NOT LIKE '%$delimiter{$value}' AND $inColumn <> '{$value}'))");
	    }
	    
	    public function orNotHasValue($value, $inColumn, $delimiter = ',') {
	        $this->where("OR ($inColumn IS NULL OR $inColumn = '' OR ($inColumn NOT LIKE '{$value}$delimiter%' AND $inColumn NOT LIKE '%$delimiter{$value}$delimiter%' AND $inColumn NOT LIKE '%$delimiter{$value}' AND $inColumn <> '{$value}'))");
	    }
	    	    
	    protected function printQuery($query, $affectedRows) {
	        
	        //Formatando string da QUERY para visualização no browser
	        $query = str_replace('/*[\n]*/', '<br/>', $query);
		    $query = str_replace('/*[\t]*/', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $query);
		    
		    $idDIVQuery = 'qry'.rand(0, 10000);
		    
		    //Montando DIV do resultado da query
		    echo "<div style=\"border:dashed 1px #CCC;background:#FFFFFF;padding:10px;\">";
		    echo '<a style=\'color:Gray;\' href=\'javascript:void(0);\' onclick=\'javascript:if(document.getElementById("'.$idDIVQuery.'").style.display == "none"){document.getElementById("'.$idDIVQuery.'").style.display = ""}else{document.getElementById("'.$idDIVQuery.'").style.display = "none"}\'><b>QUERY</b></a><br/>';
		    echo "<pre style=\"color:Blue;font-size:13px;font-family:Courier;display:none;text-align:left;\" id='$idDIVQuery'>$query</pre>";
	    	echo "<div style=\'padding:5px;color:Gray;font-size:11px;\'>$affectedRows REGISTROS AFETADOS</div></div>";
	    	
	    }
	     	
		protected function printResult($result) {
			$idDIV = 'res'.rand(0, 1000);
			echo '<div style=\'border:dashed 1px #CCC;border-top:none;background:#FFFFFF;padding:10px;color:Blue;font-size:11px;font-family:Courier;\'>
					<a href=\'javascript:void(0);\' onclick=\'javascript:document.getElementById("'.$idDIV.'").style.display = "";\'><b>RESULTADO</b></a><br/>
					<div id=\'',$idDIV,'\' style=\'display:none;\'><pre>';
						print_r($result);
			echo '</pre></div></div>';		
		}
		
		protected function printQueryResult($query, $result) {
		    
		    //Formatando string da QUERY para visualização no browser
		    $query = str_replace('/*[\n]*/', '<br/>', $query);
		    $query = str_replace('/*[\t]*/', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $query);
		    
		    $idDIVResult = 'res'.rand(0, 1000);
		    $idDIVQuery = 'qry'.rand(0, 1000);
		    
		    //Montando DIV do resultado da query
		    echo "<div style=\"border:dashed 1px #CCC;background:#FFFFFF;padding:10px;text-align:left;font-family:arial;\">";
		    echo '<a style=\'color:Gray;\' href=\'javascript:void(0);\' onclick=\'javascript:if(document.getElementById("'.$idDIVQuery.'").style.display == "none"){document.getElementById("'.$idDIVQuery.'").style.display = ""}else{document.getElementById("'.$idDIVQuery.'").style.display = "none"}\'><b>QUERY</b></a><br/>';
		    echo "<pre style=\"color:Blue;font-size:13px;font-family:Courier;display:none;text-align:left;\" id='$idDIVQuery'>$query</pre>";
			echo '<div style=\'padding:5px;color:Gray;font-size:11px;\'>
					<a style=\'color:Gray;\' href=\'javascript:void(0);\' onclick=\'javascript:if(document.getElementById("'.$idDIVResult.'").style.display == "none"){document.getElementById("'.$idDIVResult.'").style.display = ""}else{document.getElementById("'.$idDIVResult.'").style.display = "none"}\'><b>'.count($result).' REGISTROS</b></a><br/>
					<div id=\'',$idDIVResult,'\' style=\'display:none;max-height:500px;overflow:auto;\'>';
			         OlalaUtil::printResultAsHTMLTable($result);
			         //print_r($result);
			echo '</div></div></div>';
		}
		
		protected function setTableName($tableName) {
			$this->tableName = $tableName;
		}
		
		protected function addForeignKey($column, $table, $foreignColumnName = false, $isNotNull = false, $size = 0, $comment = false) {
			$foreignColumnName = (!$foreignColumnName)? $column : $foreignColumnName;
			array_push($this->foreignKeys, array('column' => $column, 'table' => $table, 'foreignColumn' => $foreignColumnName, 'isNotNull' => $isNotNull, 'comment' => $comment));
		}
		
		protected function setPrimaryKey($column) {
			$this->primaryKey = $column;
		}
		
		protected function addColumn($column, $type = false, $isNotNull = false, $size = 0, $comment = false) {
			$this->types[$column] = $type;
			$this->nulls[$column] = $isNotNull;
			$this->sizes[$column] = $size;
			$this->comments[$column] = $comment;
			array_push($this->columns, $column);
		}
	
	}
	
	
	