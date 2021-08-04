<?php
	
	/**
	 *  CLASSE DE ACESSO AOS DADOS DO OBJETO
	 *  Criado por: Ivan Florencio
	 *  Dia: 12/03/2013
	 *
	 */
	 
	require_olala('data/OlalaMySQLConnection.class.php');
	require_olala('data/OlalaDBSystem.class.php');
	require_olala('data/OlalaDB.class.php');
		
	class OlalaMySQL extends OlalaDB implements OlalaDBSystem {
		
		/**
		 * 	CONSTRUTOR 
		 * 
		 */
		function OlalaMySQL($dbNameSuffix = '') {
			$this->dbNameSuffix = $dbNameSuffix;
			
		}
		
		/**
		 *  Método DE GRAVAÇÃO HIBRIDO: INSERT E UPDATE
		 *  $dto, $trace = false, $isNew
		 */		
		public function save($dto, $trace = false, $isNew = false, $updateFilter = false) {
					
			if ((isset($dto[$this->primaryKey]) && $dto[$this->primaryKey] > 0 && !$isNew) || (!$isNew && $updateFilter)) {	
				
				$k = 0;
				
				$buffer = 'UPDATE `' . $this->tableName . '` SET ';
				
				if (isset($dto[$this->primaryKey])) {
					$buffer.= $this->primaryKey . ' = \'' . $dto[$this->primaryKey] . '\'';
					$k = 1;
				}
				
				foreach ($this->foreignKeys as $fk) {			
					if (isset($dto[$fk['column']])) {
						if ($dto[$fk['column']]) {
							$buffer .= ',' . $fk['column'] . ' = \'' . addslashes(stripslashes($dto[$fk['column']])) . '\'';
						} else {
							$buffer .= ',' . $fk['column'] . ' =  NULL';
						}
						$k = 1;
					}
				}
								
				foreach ($this->columns as $column) {	
					if (isset($dto[$column]) && ($dto[$column] == ' ')) {
						if ($k > 0) $buffer .= ',';  
						$buffer .= $column . ' = NULL ';
						$k++;
					} else if (isset($dto[$column]) && trim($dto[$column]) != '') {
						if ($k > 0) $buffer .= ',';
						$buffer .= $column . ' = \'' . addslashes(stripslashes($dto[$column])) . '\'';
						$k++;
					}
				}
				
				if ($updateFilter) {
					$buffer.= ' WHERE 1 ' . $this->getConditions($updateFilter);
				} else {
					$buffer.= ' WHERE ' . $this->primaryKey . ' = \''. $dto[$this->primaryKey] . '\'';
				}
				
			} else {
				
				$buffer = 'INSERT INTO `' . $this->tableName .'` (';			
				$buffer .= $this->primaryKey;
				
				if (count($this->foreignKeys) > 0) {
					foreach ($this->foreignKeys as $fk) {
						$buffer .= ',' . $fk['column'];
					}
				}
				
				if (count($this->columns)>0) {
					$buffer .= ',';
				}
				
				$buffer .= join(',', $this->columns);
				
				if (!$isNew) {
					$buffer.= ') VALUES (NULL';								
				} else {
					$buffer.= ') VALUES (\'' . $dto[$this->primaryKey] . '\'';
				}
				foreach ($this->foreignKeys as $fk) {			
					$buffer .= ',' . ((isset($dto[$fk['column']]) && trim($dto[$fk['column']]) != '')?'\''.addslashes(stripslashes($dto[$fk['column']])).'\'':'NULL');
				}
				foreach ($this->columns as $campo) {
					$buffer .= ',' . ((isset($dto[$campo]) && trim($dto[$campo]) != '')?'\''.addslashes(stripslashes($dto[$campo])).'\'':'NULL');
				}
				$buffer .= ');';
			}
			
			$id = $this->execute($buffer, $trace, 'I');
			
			if (!$id && isset($dto[$this->primaryKey]) && $dto[$this->primaryKey] > 0) {
				$id = $dto[$this->primaryKey];
			}
						
			return $id;
			
		} 
		
		/**
		 *  Método DE GRAVAÇÃO DE NOVO REGISTRO: INSERT
		 *  $dto, $trace = false, $isNew
		 */
		public function insert($dto, $trace = false) {
			return $this->save($dto, $trace, true);
		}
		
		/**
		 *  Método DE ALTERAÇÃO DE REGISTRO: UPDATE
		 *  $dto, $trace = false, $isNew
		 */
		public function update($dto, $filter, $trace = false) {
			return $this->save($dto, $trace, false, $filter);
		}
		
		/**
		 *  Método DE CONSULTA 
		 *  $filter, $where = false, $trace = false
		 */
		public function get($filter = array(), $trace = false, $where = false, $isCount = false, $distinct = "") {
			
			$isByPK = false;
			
			if (is_bool($filter)) {
				$trace = $filter;
				$filter = array();
				
			} else if (is_string($filter) || is_int($filter)) {
				$pkValue = $filter;
				$filter = array();
				$filter[$this->primaryKey] = $pkValue;	
				$isByPK = true;
			}
			
			if (count($this->queryFilter) && is_array($filter)) {
				$filter = array_merge($filter, $this->queryFilter);				
			}
			
			if (count($this->textFilter) && is_array($filter)) {
				$filter['keyWords'] = $this->textFilter;
			}
			
			$tableIdNum = 1; 
			$this->addAlias($this->tableName, "_t$tableIdNum", 'main');
			
			$tables = "`$this->tableName` AS `{$this->getAlias($this->tableName, 'main')}`";
			$noRepeatTables = array();
			$joins = '';
			
			foreach ($this->foreignKeys as $fk) {
				if ($fk['isNotNull'] && $fk['table'] != $this->tableName && array_search($fk['table'], $noRepeatTables) === FALSE) {
				    $tableIdNum++;
				    $fkTableAlias = '_t' . $tableIdNum;
				    $this->addAlias($fk['table'], $fkTableAlias);
					$tables .= ',`' . $fk['table'] . '` AS `' . $fkTableAlias . '`';
					$joins .= ' AND `' . $this->getAlias($this->tableName, 'main') . '`.' . $fk['column'] . '=`' . $fkTableAlias . '`.' . $fk['foreignColumn'];
					array_push($noRepeatTables, $fk['table']);
				}
			}
			
			if ($isCount) {
				$this->resultColumns = "COUNT(*) AS resultCount";
			}
			
			$buffer	= ' SELECT ' . $distinct . ' ' . $this->EOL . $this->TAB . $this->resultColumns . $this->EOL . ' FROM '  . $this->EOL . $this->TAB . $tables . $this->EOL . ' WHERE 1 ' . $this->EOL . $this->TAB . $joins . $this->EOL . $this->getConditions($filter);
						
			//Ordenamento e paginacao
			if (!$isCount) {
				$buffer .= isset($filter['olalaOrderBy'])? ' ORDER BY ' . str_replace('[[TABLE_NAME]]', $this->getAlias($this->tableName, 'main'), $filter['olalaOrderBy']) . $this->EOL:'';
				if ($this->resultSize) {
					if ($this->resultOffset < 0) {
						$this->resultOffset = 0;
					}
					$buffer .= ' LIMIT ' . (($this->resultOffset) * $this->resultSize) . ',' . $this->resultSize . ' ' . $this->EOL;
				}
			}
			if ($isByPK) {
				$dto = $this->execute($buffer, $trace, 'S');
				if (count($dto)) {
					return $dto[0]; 
				} else {
					$dto;
				}
			} else {
				return $this->execute($buffer, $trace, 'S');
			}
			
		}
		
		/**
		*	Método DE CONSULTA DA QUANTIDADE DE REGISTROS
		*   $filter, $trace = false
		*/		

		public function count($filter = array(), $trace = false, $where = false) {
			
			$count = false;
			
			if (!is_array($filter) && is_bool($filter)) {
				$trace = $filter;
				$filter = array();				
			}
			
			$c = $this->get($filter, $trace, $where, true);
			
			if (isset($c[0]['resultCount'])) {
				$count = $c[0]['resultCount'];
			}
			
			$this->resultColumns = "*";
			
			return $count;
			
		}
		
		/**
		*	Método DE EXCLUSÃO DE REGISTRO
		*   $id, $trace = false
		*/
		public function delete($filter, $trace = false) {			
			if (is_array($filter)) {
				$buffer	= 'DELETE FROM `' . $this->tableName . '` WHERE 1 ' . $this->getConditions($filter);
			} else {
				$buffer	= 'DELETE FROM `' . $this->tableName . '` WHERE ' . $this->primaryKey . ' = ' . $filter;
			}	
			
			if ($trace) {
			    $this->printQuery($buffer, 0);
			}
			
			return $this->execute($buffer, $trace, 'D');			
		}
	
		/**
		 * Método DE EXECUCAO DE QUERY GENERICA
		 * $id, $trace = false
		 */
		public function execute($queryBuffer, $trace = false, $queryType = 'S') {
			
			$result = false;
            
			$dbConnection = OlalaMySQLConnection::singleton ( $this->dbNameSuffix );
			$dbConnection->connect();
			
			$query = mysqli_query ($dbConnection->conn, $queryBuffer);
			
			if ($query) {
				if ($queryType == 'S') {
					$result = array ();
					$i = 0;
					do {
					    if ($query) {
    						$fetch = mysqli_fetch_assoc ( $query );
    						if ($fetch) {
    							$result [$i] = $fetch;
    							$i ++;
    						}
					    }
					} while ( $fetch );
					if ($trace) {
						$this->printQueryResult($queryBuffer, $result);
					}
				} else if ($queryType == 'D') {
					$result = true;
				} else {
					if ($trace) {
						$this->printQuery($queryBuffer, mysqli_affected_rows($dbConnection->conn));
					}
					$result = mysqli_insert_id ($dbConnection->conn);
				}
				$dbConnection->close ();
			} else {
				if ($queryType == 'D' && mysqli_errno($dbConnection->conn) == 1451) {
					$result = false;
				} else {
					$this->printQuery($queryBuffer, mysqli_affected_rows($dbConnection->conn));
					showError ( 'Erro de query no MySQL - #' . mysqli_errno ($dbConnection->conn) . ': ' . mysqli_error ($dbConnection->conn) );
				}
				$dbConnection->close ();
			}
			return $result;
		}	
		
		/**
		 * Método privado para montagens do Bloco WHERE a partir dos filtros
		 * $filter
		 */
		private function getConditions($filter) {			
			
			$buffer = "";
			
			//Filtro de primary key
			
			if (isset($filter[$this->primaryKey]) && $filter[$this->primaryKey]) {
				$buffer .= ' AND ' . $this->primaryKey . ' = \'' . $filter[$this->primaryKey] . '\' ' . $this->EOL;
			
			//Filtro de foreign keys e outras colunas
			} else {
			    $mainTableAlias = $this->getAlias($this->tableName, 'main');
				foreach ($this->foreignKeys as $fk) {
					if ($fk['table'] == $this->tableName) {
						$buffer .= (isset($filter[$fk['column']]))? $this->TAB . ' AND ' . $fk['column'] . ' = \'' . $filter[$fk['column']] . '\'' . $this->EOL:'';
					} else {
						$buffer .= (isset($filter[$fk['column']]))? $this->TAB . ' AND `' . $mainTableAlias . '`.' . $fk['column'] . ' = \'' . $filter[$fk['column']] . '\'' . $this->EOL:'';
					}
				}
				foreach ($this->columns as $column) {
					if (isset($filter[$column]) && ($filter[$column] == 'IS NULL' || $filter[$column] == 'IS NOT NULL')) {
						$buffer .= (isset($filter[$column]) && trim($filter[$column]) != '')? $this->TAB . ' AND `' . $mainTableAlias . '`.' . $column . ' '.$filter[$column].' ' . $this->EOL:'';
					} else {
						$buffer .= (isset($filter[$column]) && trim($filter[$column]) != '')? $this->TAB . ' AND `' . $mainTableAlias . '`.' . $column . ' = \'' . addslashes($filter[$column]) . '\'' . $this->EOL:'';
					}
				}
			}			
			
			//Filtros com palavra-chave
			$j = 0;
			if (isset($filter['keyWords']) && count($filter['keyWords']) > 0) {
				$buffer .= $this->TAB . ' AND (' . $this->EOL;
				foreach ($filter['keyWords'] as $word) {
					if ($j > 0) {
						$buffer .= $this->TAB . ' OR ' . $this->EOL;
					}
					$buffer .= $this->TAB . $this->TAB . ' UPPER('.$word['column'].') LIKE UPPER(\'%'.$word['filter'].'%\') ' . $this->EOL;
					$j = 1;
				}
				$buffer .= $this->TAB . ' )' . $this->EOL;
			}
				
			//Clausula WHERE adicional
			if ($this->where) {
				$buffer .= $this->TAB . $this->where . $this->EOL;
			}
			return $buffer;
		}
		
		protected function startTransaction(){
			$null = mysqli_query($this->connection, "START TRANSACTION");
			return mysqli_query($this->connection, "BEGIN");
		}
		
		protected function commit(){
			return mysqli_query($this->connection, "COMMIT");
		}
		 
		protected function rollback(){
			return mysqli_query($this->connection, "ROLLBACK");
		}
	
		public function build() {
			OlalaScaffold::startBuildDBClass();
			$tables = $this->getTables();
			foreach ($tables as $table) {
				$columns = $this->getColumnsTable($table['Tables_in_' . DB_NAME]);
				$foreignKeys = $this->getForeignKeys($table['Tables_in_' . DB_NAME]);
				OlalaScaffold::buildDBClass($table['Tables_in_' . DB_NAME], $columns, $foreignKeys);
				//OlalaScaffold::generateFormsFromDB($table['Tables_in_' . DB_NAME], $columns, $foreignKeys);							
			}
		}
		
		private function getTables() {
			return $this->execute("SHOW TABLES FROM `".DB_NAME."`");
		}
		
		private function getColumnsTable($tableName) {
			return $this->execute("SHOW FULL COLUMNS FROM `".$tableName."`");
		}
		
		private function getForeignKeys($tableName) {
			$foreignKeys = array();
			$result = $this->execute("
					select COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
					from INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					WHERE TABLE_NAME = '$tableName' AND CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME IS NOT NULL;
					");
			foreach ($result as $fk) {
				$foreignKeys[$fk['COLUMN_NAME']] = array();
				$foreignKeys[$fk['COLUMN_NAME']]['table'] = $fk["REFERENCED_TABLE_NAME"];
				$foreignKeys[$fk['COLUMN_NAME']]['foreignColumnName'] = $fk["REFERENCED_COLUMN_NAME"];
				$foreignKeys[$fk['COLUMN_NAME']]['notNull'] = $fk["REFERENCED_COLUMN_NAME"];
			}
			return $foreignKeys;
 		}
		
	}
	
	