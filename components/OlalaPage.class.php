<?php

	class OlalaPage {
		
		public $content;
		public $masterPage = false;
		public $params = array();
		public $cache = false;
		
		public function __construct($masterPage, $useCache = false) {
		    
			$this->setMasterPage($masterPage);
			
			$_GET["useOlalaPage"] = 1;
			$_GET["scriptsPage"] = "";
			
			ob_start();
			
			if (!isset($_SESSION['usuarioLogado']) && ($useCache && (defined('CACHE_EXPIRE_TIME') && CACHE_EXPIRE_TIME > 0))) {
				$this->cache = new OlalaCache('cache/', CACHE_EXPIRE_TIME);
			}
		}
		
		public function setMasterPage($masterPage) {
			$this->masterPage = $masterPage;			
		}
		
		public function addJavascript($script) {
			$this->script .= $script;			
		}
		
		public function addParam($key, $value) {
			$this->params[$key] = $value;	
		}
		
		public function getParam($key) {
			return $this->params[$key];
		}
		
		public function finish() {
			
			$contentMaster = &$contentMaster;
			$params = &$params;
			
			$this->content = ob_get_contents();
			
			ob_end_clean();
			ob_start();
			
			$contentMaster = $this->content;			
			$params = $this->params;
			
			if ($this->masterPage) {				
				include_once $this->masterPage;
			}
			
			echo $_GET["scriptsPage"];
			
			
			/*$nomeArquivo = basename($_SERVER['SCRIPT_FILENAME'],'.php');
			$fileHTML = ob_get_contents();
			
			$myfile = fopen($nomeArquivo, "w") or die("Unable to open file!");
            fwrite($myfile, $fileHTML);
            fclose($myfile);*/
					
			
			if ($this->cache) {
				$this->cache->finish(ob_get_contents());
			}
			
		}
		
	}

	
	