<?php

	/**
	 *  INTERFACE PARA CLASSES DE ACESSO A DADOS DO OBJETO PARA O SGBD
	 *  Criado por: Ivan Florencio
	 *  Dia: 13/03/2013
	 *
	 */
	 
	interface OlalaDBSystem {
		
		/**
		 *  M้todo DE GRAVAวรO HIBRIDO: INSERT E UPDATE
		 *  $dto, $trace = false, $isNew
		 */
		public function save($dto, $trace = false, $isNew = false, $updateFilter = false);
		
		/**
		 *  M้todo DE GRAVAวรO DE NOVO REGISTRO: INSERT
		 *  $dto, $trace = false, $isNew
		 */
		public function insert($dto, $trace = false);
		
		/**
		 *  M้todo DE ALTERAวรO DE REGISTRO: UPDATE
		 *  $dto, $trace = false, $isNew
		 */
		public function update($dto, $filter, $trace = false);
		
		/**
		 *  M้todo DE CONSULTA
		 *  $filter, $where = false, $trace = false
		 */
		public function get($filter = array(), $trace = false, $where = false, $isCount = false, $distinct = "");
		
		/**
		 *	M้todo DE CONSULTA DA QUANTIDADE DE REGISTROS
		 *   $filter, $trace = false
		 */
		public function count($filter = array(), $trace = false, $where = false);
		
		/**
		 *	M้todo DE EXCLUSรO DE REGISTROS
		 *   $id, $trace = false
		 */
		public function delete($filter, $trace = false);
		
		/**
		 * M้todo DE EXECUCAO DE QUERY GENERICA
		 * $id, $trace = false
		 */
		public function execute($queryBuffer, $trace = false, $queryType = 'S');
	
	}
	
	
	