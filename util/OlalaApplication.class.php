<?php

	require_olala('mvc/model/OlalaModel.class.php');
	require_olala('mvc/view/OlalaView.class.php');
	require_olala('mvc/controller/OlalaController.class.php');
	
	class OlalaApplication {
		
		public $_controller;
		public $_action;
		public $_params = array();
		
		public function __construct() {
			
			if (isset($_GET['url'])) {
				$word = explode("/", $_GET['url']);
			} else {
				$word = array();
			}
			
			$controller = isset($word[0]) ? $word[0] : "index";
			$controllerFile = resolveAbsoluteUrl(CONTROLLER_DIR . $controller . 'Controller.class.php');
						
			if (file_exists($controllerFile)) {
				$this->getParams(false, $word);
				$this->instanceController($controllerFile);
			} else {
				$controllerFile = resolveAbsoluteUrl(CONTROLLER_DIR . 'IndexController.class.php');
				if (file_exists($controllerFile)) {
					$this->getParams(true, $word);
					$this->instanceController($controllerFile);
				} else {
					showError("Controller $controllerFile não encontrado!");
				}
			}						
		}
		
		private function getParams($isIndex, $word) {
			
			$_GET['url'] = (isset($_GET['url'])) ?  $_GET['url'] : "";
						
			if ($isIndex) {
				$this->_controller = "Index";
				$this->_action = (isset($word[0]) && $word[0]) ? $word[0] : "Index";
			} else {
				$this->_controller = (isset($word[0]) && $word[0]) ? $word[0] : "Index";
				$this->_action = (isset($word[1]) && $word[1]) ? $word[1] : "Index";
			}
			
			$chave = '';
			
			if ($isIndex) {
				for ($i = 1; $i < count($word); $i++) {
					if ($i%2 != 0) {
						$chave = $word[$i];
					} else {
						$this->_params[$chave] = $word[$i];
					}
				}
			} else {
				for ($i = 2; $i < count($word); $i++) {
					if ($i%2 == 0) {
						$chave = $word[$i];
					} else {
						$this->_params[$chave] = $word[$i];
					}
				}
			}
			if (isset($_POST) && is_array($_POST)) {
			
				$this->_params = array_merge($this->_params, $_POST);
			}
		}
		
		private function instanceController($controllerFile) {
			require_once $controllerFile;
			$controllerClassName = $this->_controller . "Controller";
			$classeController = new $controllerClassName;
			$actionName = $this->_action;
			$_GET['OlalaControllerName'] = $this->_controller;
			$_GET['OlalaActionName'] = $actionName;
			if (method_exists($classeController, $actionName)) {
				$classeController->$actionName($this->_params);
			} else {
				showError("Action '$actionName' não encontrada no controller '$controllerClassName'.");	
			}
		}
	
	}
	
	