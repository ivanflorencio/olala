<?php

	/**
	 *  INTERFACE PARA CLASSES DE ACESSO A DADOS DO OBJETO PARA O SGBD
	 *  Criado por: Ivan Florencio
	 *  Dia: 13/03/2013
	 *
	 */
	 
	interface OlalaDBSystem {
		
		/**
		 *  M�todo DE GRAVA��O HIBRIDO: INSERT E UPDATE
		 *  $dto, $trace = false, $isNew
		 */
		public function save($dto, $trace = false, $isNew = false, $updateFilter = false);
		
		/**
		 *  M�todo DE GRAVA��O DE NOVO REGISTRO: INSERT
		 *  $dto, $trace = false, $isNew
		 */
		public function insert($dto, $trace = false);
		
		/**
		 *  M�todo DE ALTERA��O DE REGISTRO: UPDATE
		 *  $dto, $trace = false, $isNew
		 */
		public function update($dto, $filter, $trace = false);
		
		/**
		 *  M�todo DE CONSULTA
		 *  $filter, $where = false, $trace = false
		 */
		public function get($filter = array(), $trace = false, $where = false, $isCount = false, $distinct = "");
		
		/**
		 *	M�todo DE CONSULTA DA QUANTIDADE DE REGISTROS
		 *   $filter, $trace = false
		 */
		public function count($filter = array(), $trace = false, $where = false);
		
		/**
		 *	M�todo DE EXCLUS�O DE REGISTROS
		 *   $id, $trace = false
		 */
		public function delete($filter, $trace = false);
		
		/**
		 * M�todo DE EXECUCAO DE QUERY GENERICA
		 * $id, $trace = false
		 */
		public function execute($queryBuffer, $trace = false, $queryType = 'S');
	
	}
	
	
	