<?php
	
	class OlalaModel  {
		
		public $db;
		public $record;
		public $list;
		
		public function value($columnName, $format = 'TEXT') {
			$value = "";
			if (isset($this->record[$columnName])) {
				if ($format != 'TEXT') {
					$value = $this->record[$columnName];
				} else {
					$value = $this->formatValue($this->record[$columnName]);
				}				
			}
			return $value;
		}
		
		private function formatValue($value, $format) {
			$valueFormated = $format;
			return $valueFormated;
		}
		
	}