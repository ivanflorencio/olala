<?php
	
	class OlalaController  {
		
		public function view($modelOrView = false, $model = false) {
		    
		    ob_start();
			
			$view = new OlalaView();
			$view->open($modelOrView, $model);
						
		}
				
		public function viewExists($viewName) {
			$view = new OlalaView();
			return $view->exists($viewName);
		}
		
		protected function serialize($result, $encode = 'ISO-8859-1', $method = 'JSON') {
			if (isset($result[0])) {	
				return 	json_encode(OlalaUtil::encodeResult($result));
			} else {
				return 	json_encode(OlalaUtil::encodeRecord($result));
			}
		}
		
		public function getLastURLParam() {
			$params = explode('/', $_GET['url']);
			return $params[count($params)-1];
		}
				
	}
	