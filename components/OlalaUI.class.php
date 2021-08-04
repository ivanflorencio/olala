<?php
	
	class OlalaUI {
		
		public $ID;
		protected $attributes = array();
		
		public function attr($label, $value = false) {
			if ($value) {
				$this->attributes[$label] = $value;
			} else {
				return $this->attributes[$label];
			}
		}
		
		protected function getAttributeString() {
			$attributeString = "";
			$keys = array_keys($this->attributes);
			foreach ($keys as $key) {
				$attributeString .= " $key = \"{$this->attributes[$key]}\" ";
			}
			return $attributeString;
		}
		
	}
