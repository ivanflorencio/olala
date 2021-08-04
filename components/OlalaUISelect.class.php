<?php

	require_olala("util/OlalaUtil.class.php");

	class OlalaUISelect extends OlalaUI {
		
		public $noValueLabel = "Selecione ...";
		public $optionLabelColumns = array();
		public $selectedItem;
		public $items = array();
		public $value = "";
		
		private $DB;
		private $filter = array();
										
		function OlalaUISelect($db = false, $filter = false, $ID = false) {			
			if ($db && !$ID) $ID = $db->primaryKey; 
			if ($ID) $this->ID = $ID;
			if ($db) $this->DB = $db;
			if ($filter) $this->filter = $filter;			
		}
		
		public function addLabelColumn($column, $type = false) {
			array_push($this->optionLabelColumns, array("column" => $column, "type" => $type));			
		}
				
		public function addItem($label, $value) {
			array_push($this->items, array("label" => $label, "value" => $value));			
		}
		
		public function addItemGroup($label) {
			array_push($this->items, array("label" => $label, "value" => 'OlalaItemGroup'));
		}
		
		public function show($trace = false) {
			
			$this->selectedItem = $this->value;
			
			$html = "\n<select name=\"$this->ID\" id=\"$this->ID\" ".$this->getAttributeString().">\n";
			if (!empty($this->noValueLabel)) {
			     $html .= "    <option value=\"\">$this->noValueLabel</option>\n";
			}
			echo $html;
			
			$this->showOptions($trace);
			
			echo "</select>\n";
			
		}
		
		public function showOptions($trace = false) {
			$html = "";
			if (count($this->items) > 0) {
				$i = 0;
				foreach ($this->items as $item) {
					if ($item['value'] == 'OlalaItemGroup') {
						if ($i > 0) $html .= "\t</optgroup>\n";						
						$html .= "  <optgroup label=\"{$item["label"]}\">\n\t\t";												
						$i++;
					} else {
						$html .= "    <option value=\"".$item["value"]."\" ".(($item["value"]==$this->selectedItem)?"selected":"").">";
						$html .= $item["label"];
						$html .= "</option>\n";
					}
				}
				if ($i > 0) $html .= "\t</optgroup>\n";
			} else if ($this->DB != null) {
				//Caso os dados venham do banco
				if ($this->DB->types[$this->DB->columns[0]] == 'date') {
					$this->DB->orderBy($this->DB->columns[0], true);
				} else {
					$this->DB->orderBy($this->DB->columns[0]);
				}
				$this->DB->setResultColumns($this->DB->primaryKey .", ". $this->DB->columns[0]);
				$this->DB->limit(1000);
				
				$rows = $this->DB->get($this->filter, $trace);
			
				foreach ($rows as $row) {
						
					$itemValue = $row[$this->DB->primaryKey];
					$itemLabel = "";
						
					if (count($this->optionLabelColumns) > 0) {
						foreach ($this->optionLabelColumns as $c) {
							$label = $row[$c["column"]];
							$label = OlalaUtil::format($label, $c["type"]);
							$itemLabel .= $label . " | ";
						}
					} else {
						if ($this->DB->types[$this->DB->columns[0]] == 'date') {
							$itemLabel .= OlalaUtil::timestampParaData($row[$this->DB->columns[0]]);
						} else {
							$itemLabel .= $row[$this->DB->columns[0]];
						}
					}
					
					$pos = strpos($itemLabel, '<!--[[IDIOMA]]-->');
				    if ($pos > 0) {
					   $itemLabel = substr($itemLabel, 0, $pos);
				    }
					
					$html .= "    <option value=\"".$itemValue."\" ".(($itemValue==$this->selectedItem)?"selected":"").">";
					$html .= $itemLabel;
					$html .= "</option>\n";
				}
			}
			echo $html;
		}
		
	}
	
	