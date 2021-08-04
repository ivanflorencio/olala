<?php

	
	class OlalaCache {
		
		private $cacheDirectory;
		private $page;
		private $cacheFile;
		private $pageTimeout;
		
		function __construct($dir = "cache/", $timeout = 60) {
			$this->cacheDirectory = $dir;
			$this->pageTimeout = $timeout;
			$this->page = $_SERVER['REQUEST_URI'] . "/";
			if (isset($_SERVER['QUERY_STRING'])) {
				$this->page = $this->page . $_SERVER ['QUERY_STRING'];			
			}
			
			$this->page = OlalaUtil::removeSpecialChars($this->page);
			
			$this->init();
		}
		
		public function setCacheDirectory($cacheDirectory) {
			$this->cacheDirectory = $cacheDirectory;
		}
		
		public function setPageTimeout($pageTimeout) {
			$this->pageTimeout = $pageTimeout;
		}

		function init() {
			$this->cacheFile = $this->cacheDirectory . str_replace('/', '_', $this->page) . '_' . @$_SESSION['LANGUAGE'] . '.html';
	        if (file_exists($this->cacheFile)){
	            if((time() - $this->pageTimeout) < filemtime($this->cacheFile)){
	                readfile($this->cacheFile);
	                exit();
	            } else {
	               unlink($this->cacheFile);
	            }
	        } 
		}
		
		function finish($conteudo) {
			$fp = fopen($this->cacheFile, 'w');
			@fwrite($fp, $conteudo);
			@fclose($fp);			
		}
		
		function limpar() {
			$handle = $this->cacheDirectory;
			if ($handle) {
				while ( false !== ($file = readdir ( $handle )) ) {
					if ($file != '.' and $file != '..') {
						unlink($this->cacheDirectory . '/' . $file);
					}
				}
				closedir($handle);			
			}
		}
	}
	
	