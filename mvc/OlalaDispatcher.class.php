<?php
	
	class OlalaDispatcher {
		
		public $URL;
		public $URL_ARQUIVO;
		
		function OlalaDispatcher($url) {
			
			$vars = explode('/', $url);
			
			$this->URL = DIRETORIO_SITE;
			$this->URL_ARQUIVO = "{$_SERVER['DOCUMENT_ROOT']}{$this->URL}{$vars[0]}.php";
			
			if (count($vars) > 0) {
				$this->montarParametrosURL($vars);
				if (file_exists($this->URL_ARQUIVO)) {					
					require "{$_SERVER['DOCUMENT_ROOT']}{$this->URL}{$vars[0]}.php";
				} else {
					require "{$_SERVER['DOCUMENT_ROOT']}{$this->URL}Index.php";
				}
			}
		}

		function montarParametrosURL($vars) {
			$i = 0;
			$url = "";
			foreach ($vars as $var) {
				if ($i > 0) {
					if ($i % 2 == 0) {
						$url .= "=$var";
					} else {
						if ($i != 1) $url .= "&";
						$url .= $var;
					}				
				}
				$i++;
			}
			$vars = explode('&', $url);
			foreach ($vars as $var) {
				$get = explode('=', $var);
				$_GET[$get[0]] = @$get[1];
			}
		}
				
	}