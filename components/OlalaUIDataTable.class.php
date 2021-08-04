<?php

	require_olala("util/OlalaUtil.class.php");

	class OlalaUIDataTable extends OlalaUI {
		
		public $DB;
		public $rowCount = 0;
		public $useDefaultCommands = true;
		private $pageSize = 10;
		private $currentPage = 1;
		private $usePagination = false;
		private $filter = array();
		private $columns = array();
		private $columnsString = false;
		
												
		function OlalaUIDataTable($db = false, $filter = false, $ID = false) {			
			if ($db && !$ID) {
				$ID = $db->primaryKey . '_DataTable'; 
			}
			if ($ID) $this->ID = $ID;
			if ($db) $this->DB = $db;
			if ($filter) $this->filter = $filter;			
		}
		
		public function addColumn($column, $label = false, $type = false) {
			array_push($this->columns, array("column" => $column, "label" => $label, "type" => $type));			
		}
						
		public function show($trace = false) {
			
			$this->chooseColumns();
			$this->attr('class', 'table table-condensed table-hover footable');
			
			$html = "<table data-filter=\"#filter-datatable\" id=\"$this->ID\" ".$this->getAttributeString().">";
			$html .= "<thead><tr>";
			
			$i = 0;
			foreach ($this->columns as $column) {	
				if ($i <= 9) {			
					if (!$column['label'])$column['label'] = $column['column'];
					$expand = '';
					if ($i == 0) {
						$expand = 'data-class="expand"';
					} else if ($i < 3) {
						$expand = 'data-hide="phone"';
					} else if ($i < 5) {	
						$expand = 'data-hide="phone,tablet"';
					} else {
						$expand = 'data-hide="phone,tablet,pc"';
					}				
					$html .= "<th $expand>{$column['label']}</th>";
				}
				$i++;
			}
			if ($this->useDefaultCommands) {
				$html .= "<th></th>";
			}
			$html .= "</tr></thead><tbody>";
			
			echo $html;
			
			$this->showRows($trace);
			
			echo "</tbody></table>";
			
			if ($this->usePagination) {
				$this->showPagination();
			}
			
		}
		
		public function pagination($size, $page) {
			$this->resultSize = $size;
			$this->currentPage = $page;
			$this->usePagination = true;
			$this->DB->limit($size, ($page-1));
		}
		
		public function showPagination() {
			
			$pages = round($this->rowCount/$this->pageSize);
			$pages = $pages + ((($pages * $this->pageSize - $this->rowCount) < 0)?1:0);
			
			$html = '<div class="pagination pagination-center"><ul class="pagination pagination-sm">';			
			$html .= '<li class="disabled"><a>' . $this->rowCount . ' registros</a></li>';
			if ($pages > 1) {
    			$html .= '<li class="disabled"><a>' . $this->currentPage . '/' . $pages . '</a></li>';
    			$html .= '<li'.(($this->currentPage==1)?' class="disabled"':'').'><a href="javascript:void(0)" class="button-page" page="1">&laquo;</a></li>';
    			
    			$offset = 1;
    			$pagerSize = 10;
    			
    			if ($this->currentPage > 7) {
    				$offset = $this->currentPage - ($pagerSize / 2);
    				if ($pages - $offset < $pagerSize) $offset = $pages - ($pages - $offset);
    			}
    			$u = 1;
    			for($p = $offset; $p <= $pages; $p++) {
    				if ($u <= $pagerSize) {
    					$class = ($p == $this->currentPage)?' class="active"':'';
    					$html .= '<li '.$class.'><a href="javascript:void(0)" class="button-page" page="' . $p . '">' . $p . '</a></li>';
    				}
    				$u++;
    			}
    			
    			$html .= '<li'.(($this->currentPage==$pages)?' class="disabled"':'').'><a href="javascript:void(0)" class="button-page" page="' . $pages . '">&raquo;</a></li>';
    			
			}
			$html .= '</ul></div>';
			
			echo $html;
			
		}

		private function chooseColumns() {
			
			$this->columnsString = array();
			
			if (count($this->columns) == 0) {
				foreach ($this->DB->columns as $column) {
					$this->addColumn($column, (($this->DB->comments[$column])?$this->DB->comments[$column]:$column), $this->DB->types[$column]);
				}
			}
			
			foreach ($this->columns as $column) {
				if (@DB_SGBD == 'PostgreSQL') {
					array_push($this->columnsString, $this->DB->tableName . '"."' . $column['column']);
				} else {
					array_push($this->columnsString, $column['column']);
				}
			}
			
			$numColumns = count($this->columnsString);
			if (@DB_SGBD == 'PostgreSQL') {
				$this->columnsString = join('","', $this->columnsString);
			} else {
				$this->columnsString = join(',', $this->columnsString);
			}
		}
		
		public function showRows($trace = false) {
			
			$html = "";
			
			if (!$this->columnsString) {
				$this->chooseColumns();
			}
			
			if ($this->DB->resultSize == 0) $this->DB->limit(300);
						
			$this->DB->orderBy($this->DB->primaryKey, true);
			
			if (@DB_SGBD == 'PostgreSQL') {
				$this->DB->setResultColumns('"' . $this->DB->tableName . '"."' . $this->DB->primaryKey . '","' . $this->columnsString . '"');
			} else {
				$this->DB->setResultColumns($this->DB->primaryKey.','.$this->columnsString);
			}
						
			$rows = $this->DB->get($this->filter, $trace);
			$this->rowCount = $this->DB->count($this->filter);
			
			$rows = OlalaUtil::encodeResult($rows);
		
			foreach ($rows as $row) {
				$html .= "<tr class='tr-row' row-id='{$row[$this->DB->primaryKey]}'>";
				$j=0;
				foreach ($this->columns as $column) {
					if ($j <= 9) {	
						if ($column['type'] == 'date') {
							$html .= "<td>" . OlalaUtil::timestampParaData($row[$column['column']]) . "</td>";
						} else if ($column['type'] == 'timestamp') {
							$html .= "<td>" . OlalaUtil::timestampParaDataHora($row[$column['column']]) . "</td>";
						} else if ($column['type'] == 'float') {
							$html .= "<td style='text-align:right;'>" . number_format($row[$column['column']], 2) . "</td>";
						} else {
							$html .= "<td>{$row[$column['column']]}</td>";
						}
					}
					$j++;
				}
				
				if ($this->useDefaultCommands) {
					$html .= "<td>";
					$html .= "<div class='pull-right' style='width:85px;height:30px;'><button type='button' class='row-button btn btn-primary' button-action='edit' row-id='{$row[$this->DB->primaryKey]}'>";
					$html .= "<i class='fa fa-pencil'></i>";
					//$html .= "<span>Editar</span>";
					$html .= "</button>";
					$html .= "<button type='button' class='row-button btn btn-danger' button-action='delete' row-id='{$row[$this->DB->primaryKey]}'>";
					$html .= "<i class='fa fa-remove'></i>";
					//$html .= "<span>Excluir</span>";
					$html .= "</button></div>";
					$html .= "</td>";
				}
				
				$html .= "</tr>";
			}
			
			echo $html;
			
		}
		
	}
	