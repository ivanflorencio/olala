<?php
	
	class OlalaView  {
		
		public $masterpage = '';
		public $model = null;
		public $viewName = false;
		
		public function open($modelOrView = false, $model = false) {
			
			if (is_string($modelOrView)) {
				$this->viewName = $modelOrView;
				if ($model) {
					$this->model = $model;
				}				
			} else {
				$this->model = $modelOrView;
			}			
			
			if ($_GET['OlalaControllerName'] == 'Index') {
				if ($this->viewName) {
					$this->requireViewFile("{$this->viewName}");					
				} else {
					$this->requireViewFile("/Home/Index");
				}
			} else {
				if ($this->viewName) {
					$this->requireViewFile("/{$this->viewName}");
				} else {
					$this->requireViewFile("/{$_GET['OlalaControllerName']}/{$_GET['OlalaActionName']}");
				}
			}
			
			if (!empty($this->masterpage)) {
			    $GLOBALS['OlalaBodyContent'] = ob_get_contents();
				if ($GLOBALS['OlalaBodyContent']) {
					ob_end_clean();
					ob_start();
					require_once resolveAbsoluteUrl(VIEW_DIR . "/_Shared/" . $this->masterpage);
				} else {
					showError("Impossível usar a masterpage - Erro no leitura do buffer de saída.");
				}
			}
		}
		
		private function requireViewFile($viewName) {
			$urlFile = resolveAbsoluteUrl(VIEW_DIR . $viewName . ".php");
			if (file_exists($urlFile)) {
				require_once $urlFile;
			} else {
				showError("A View '$viewName' não foi encontrada em: " . $urlFile);
			}
		}
		
		public function exists($viewName) {
			return file_exists(resolveAbsoluteUrl(VIEW_DIR . "/{$viewName}.php"));
		}
		
		public function renderBody() {		
			echo $GLOBALS['OlalaBodyContent'];		
		}
		
		public function render($sectorName, $required = false) {
			if (function_exists($sectorName)) {
				echo $sectorName($this->model);
			} else if ($required) {
				showError("A função '$sectorName' é obrigatória e não foi encontrada na View.");
			}			
		}
	
	}
	
	