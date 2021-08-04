<?php
	
	/**
	 *  CLASSE DE ACESSO AOS DADOS DO OBJETO
	 *  Criado por: Ivan Florencio
	 *  Dia: 24/09/2014
	 *
	 */
	 
	require_olala('data/OlalaPostgreSQLConnection.class.php');
	require_olala('data/OlalaDBSystem.class.php');
	require_olala('data/OlalaDB.class.php');
		
	class OlalaPostgreSQL extends OlalaDB implements OlalaDBSystem {
		
		/**
		 * 	CONSTRUTOR 
		 * 
		 */
		function __construct($dbNameSuffix = '') {
			$this->dbNameSuffix = $dbNameSuffix;
			echo $this->SGBD;
		}
		
		/**
		 *  Método DE GRAVAÇÃO HIBRIDO: INSERT E UPDATE
		 *  $dto, $trace = false, $isNew
		 */		
		public function save($dto, $trace = false, $isNew = false, $updateFilter = false) {
					
			if ((isset($dto[$this->primaryKey]) && $dto[$this->primaryKey] > 0 && !$isNew) || (!$isNew && $updateFilter)) {	
				
				$k = 0;
				
				$buffer = 'UPDATE "' . $this->tableName . '" SET ';
				
				if (isset($dto[$this->primaryKey])) {
					$buffer.= "\"" . $this->primaryKey . '" = \'' . $dto[$this->primaryKey] . '\'';
					$k = 1;
				}
				
				foreach ($this->foreignKeys as $fk) {			
					if (isset($dto[$fk['column']])) {
						if ($dto[$fk['column']]) {
							$buffer .= ',"' . $fk['column'] . '" = \'' . addslashes(stripslashes($dto[$fk['column']])) . '\'';
						} else {
							$buffer .= ',"' . $fk['column'] . '" =  NULL';
						}
						$k = 1;
					}
				}
								
				foreach ($this->columns as $column) {	
					if (isset($dto[$column]) && ($dto[$column] == ' ')) {
						if ($k > 0) $buffer .= ',';  
						$buffer .= '"' . $column . '" = NULL ';
						$k++;
					} else if (isset($dto[$column]) && trim($dto[$column]) != '') {
						if ($k > 0) $buffer .= ',';
						$buffer .= '"' . $column . '" = \'' . addslashes(stripslashes($dto[$column])) . '\'';
						$k++;
					}
				}
				
				if ($updateFilter) {
					$buffer.= ' WHERE 1 = 1 ' . $this->getConditions($updateFilter);
				} else {
					$buffer.= ' WHERE "' . $this->primaryKey . '" = \''. $dto[$this->primaryKey] . '\'';
				}
				
			} else {
				
				$buffer = 'INSERT INTO "' . $this->tableName .'" ("';			
				
				$buffer .= $this->primaryKey;
								
				if (count($this->foreignKeys) > 0) {
					foreach ($this->foreignKeys as $fk) {
						$buffer .= '","' . $fk['column'];
					}
				}
				
				if (count($this->columns)>0) {
					$buffer .= '","';
				}
				
				$buffer .= join('","', $this->columns);
				
				if (!$isNew) {
					$buffer.= '") VALUES (nextval(\'"' . $this->tableName . '_' . $this->primaryKey . '_seq"\'::regclass)';								
				} else {
					$buffer.= '") VALUES (\'' . $dto[$this->primaryKey] . '\'';
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
			
			$tables = "\"$this->tableName\"";
			$noRepeatTables = array();
			$joins = '';
			
			foreach ($this->foreignKeys as $fk) {
				if ($fk['isNotNull'] && $fk['table'] != $this->tableName && array_search($fk['table'], $noRepeatTables) === FALSE) {
					$tables .= ',"' . $fk['table'] . '"';
					$joins .= ' AND "' . $this->tableName . '"."' . $fk['column'] . '"="' . $fk['table'] . '"."' . $fk['foreignColumn'] . '"';
					array_push($noRepeatTables, $fk['table']);
				}
			}
			
			if ($isCount) {
				$this->resultColumns = "COUNT(*) AS resultCount";
			}
			
			$buffer	= 'SELECT ' . $distinct . ' ' . $this->resultColumns . ' FROM ' . $tables . ' WHERE 1 = 1 ' . $joins . ' ' . $this->getConditions($filter);
						
			//Ordenamento e paginacao
			if (!$isCount) {
				$buffer .= isset($filter['olalaOrderBy'])? ' ORDER BY ' . $filter['olalaOrderBy']:'';
				if ($this->resultSize) {
					if ($this->resultOffset < 0) {
						$this->resultOffset = 0;
					}
					$buffer .= ' LIMIT ' . $this->resultSize . ' OFFSET ' . (($this->resultOffset) * $this->resultSize) . ' ';
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
				$buffer	= 'DELETE FROM "' . $this->tableName . '" WHERE 1 = 1 ' . $this->getConditions($filter);
			} else {
				$buffer	= 'DELETE FROM "' . $this->tableName . '" WHERE "' . $this->primaryKey . '" = ' . $filter;
			}	
			return $this->execute($buffer, $trace, 'D');			
		}
	
		/**
		 * Método DE EXECUCAO DE QUERY GENERICA
		 * $id, $trace = false
		 */
		public function execute($queryBuffer, $trace = false, $queryType = 'S') {
			
			$result = false;
						
			$dbConnection = OlalaPostgreSQLConnection::singleton ( $this->dbNameSuffix );
			$dbConnection->connect();
			
			$query = pg_query ($dbConnection->conn, $queryBuffer);
			
			if ($query) {
				if ($queryType == 'S') {
					$result = array ();
					$i = 0;
					do {
						$fetch = pg_fetch_assoc ( $query );
						if ($fetch) {
							$result [$i] = $fetch;
							$i ++;
						}
					} while ( $fetch );
					if ($trace) {
						$this->printQueryResult($queryBuffer, $result);
					}
				} else if ($queryType == 'D') {
					$result = true;
				} else {
					if ($trace) {
						$this->printQuery($queryBuffer, @pg_affected_rows($dbConnection->conn));
					}
					$result = $this->lastInsertID($this->tableName, $this->primaryKey);
				}
				$dbConnection->close ();
			} else {
				if ($queryType == 'D' && pg_errormessage($dbConnection->conn) == 1451) {
					$result = false;
				} else {
					$this->printQuery($queryBuffer, @pg_affected_rows($dbConnection->conn));
					showError ( 'Execução de query no postgreSQL - #' . pg_errormessage($dbConnection->conn) . ': ' . pg_errormessage($dbConnection->conn) );
				}
				$dbConnection->close ();
			}
			return $result;
		}
		
		//Seta campo que ordena a consulta
		public function orderBy($column, $isDescending = false) {
			if ($column == 'RAND' || $column == 'RANDON') {
				$this->queryFilter['olalaOrderBy'] = 'RAND()';
			} else {
				$this->queryFilter['olalaOrderBy'] = '"'.$this->tableName.'"."' . $column . (($isDescending)?'" DESC':'"');
			}
		}
		
		private function lastInsertID($tablename, $fieldname) {
			$result = pg_query("SELECT last_value FROM \"${tablename}_${fieldname}_seq\"");
			$seq_array = pg_fetch_row($result, 0);
			return $seq_array[0];
		}
		
		/**
		 * Método privado para montagens do Bloco WHERE a partir dos filtros
		 * $filter
		 */
		private function getConditions($filter) {			
			
			$buffer = "";
			
			//Filtro de primary key
			
			if (isset($filter[$this->primaryKey]) && $filter[$this->primaryKey]) {
				$buffer .= ' AND "' . $this->primaryKey . '" = \'' . $filter[$this->primaryKey] . '\' ';
			
			//Filtro de foreign keys e outras colunas
			} else {
				foreach ($this->foreignKeys as $fk) {
					if ($fk['table'] == $this->tableName) {
						$buffer .= (isset($filter[$fk['column']]))? ' AND "' . $fk['column'] . '" = \'' . $filter[$fk['column']] . '\'':'';
					} else {
						$buffer .= (isset($filter[$fk['column']]))? ' AND "' . $this->tableName . '"."' . $fk['column'] . '" = \'' . $filter[$fk['column']] . '\'':'';
					}
				}
				foreach ($this->columns as $column) {
					if (isset($filter[$column]) && ($filter[$column] == 'IS NULL' || $filter[$column] == 'IS NOT NULL')) {
						$buffer .= (isset($filter[$column]) && trim($filter[$column]) != '')? ' AND "' . $column . '" '.$filter[$column].' ':'';
					} else {
						$buffer .= (isset($filter[$column]) && trim($filter[$column]) != '')? ' AND "' . $column . '" = \'' . addslashes($filter[$column]) . '\'':'';
					}
				}
			}			
			
			//Filtros com palavra-chave
			$j = 0;
			if (isset($filter['keyWords']) && count($filter['keyWords']) > 0) {
				$buffer .= ' AND (';
				foreach ($filter['keyWords'] as $word) {
					if ($j > 0) {
						$buffer .= ' OR ';
					}
					$buffer .= ' UPPER("'.$word['column'].'") LIKE UPPER(\'%'.$word['filter'].'%\') ';
					$j = 1;
				}
				$buffer .= ')';
			}
				
			//Clausula WHERE adicional
			if ($this->where) {
				$buffer .= ' ' . $this->where;
			}
			return $buffer;
		}
		
		protected function startTransaction(){
			$null = pg_query($this->connection, "START TRANSACTION");
			return pg_query($this->connection, "BEGIN");
		}
		
		protected function commit(){
			return pg_query($this->connection, "COMMIT");
		}
		 
		protected function rollback(){
			return pg_query($this->connection, "ROLLBACK");
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
			return $this->execute("SHOW TABLES FROM \"".DB_NAME."\"");
		}
		
		private function getColumnsTable($tableName) {
			return $this->execute("SHOW FULL COLUMNS FROM \"".$tableName."\"");
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