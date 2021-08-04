<?php

	// CONFIGURAÇÕES AUTOTOMÁTICAS ##
	
	// URL da raiz do site
	define ( 'ROOT_URL', $_SERVER ['DOCUMENT_ROOT'] . '/' . BASE_DIR );
	
	if ($_SERVER ['SERVER_PORT'] == '80') {
		define ( 'SITE_URL', 'http://' . $_SERVER ['SERVER_NAME'] . '/' . BASE_DIR );
	} else {
		define ( 'SITE_URL', 'http://' . $_SERVER ['SERVER_NAME'] . ':' . $_SERVER ['SERVER_PORT'] . '/' . BASE_DIR );
	}
	
	// DEFINIÇÃO DE FUNCOES BÁSICAS GLOBAIS ##
	
	/**
	 * Função auxiliar para requerir arquivos da Framework
	 * Recebe o nome da classe, e faz o require_once da mesma
	 */
	function require_olala($classe) {
		if (file_exists ( ROOT_URL . FRAMEWORK_DIR . FRAMEWORK_VERSION . '/' . $classe )) {
			require_once (ROOT_URL . FRAMEWORK_DIR . FRAMEWORK_VERSION . '/' . $classe);
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Função auxiliar para requerir arquivos da Framework
	 * Recebe o nome da classe, e faz o require_once da mesma
	 */
	function include_olala($arquivo) {
		include (ROOT_URL . FRAMEWORK_DIR . 'olalaphp-' . FRAMEWORK_VERSION . '/' . $arquivo);
	}
	
	/**
	 * Função auxiliar para requerir arquivos da Framework
	 * Recebe o nome da classe, e faz o include_once da mesma
	 */
	function include_once_olala($arquivo) {
		include_once (ROOT_URL . FRAMEWORK_DIR . 'olalaphp-' . FRAMEWORK_VERSION . '/' . $arquivo);
	}
	
	/**
	 * Função auxiliar para requerir arquivos do site
	 * Recebe o nome da classe, e faz o require_once da mesma
	 */
	function require_model_class($class, $dir = 'business') {
		if (file_exists (ROOT_URL . MODEL_DIR . '/' . $dir . '/' . $class . '.class.php')) {
			require_once (ROOT_URL . MODEL_DIR . '/' . $dir . '/' . $class. '.class.php');
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Função auxiliar para requerir arquivos do site
	 * Recebe o nome da classe, e faz o require_once da mesma
	 */
	function require_controller_class($class) {
		if (file_exists ( ROOT_URL . CONTROLLER_DIR . $class . '.class.php')) {
			require_once (ROOT_URL . CONTROLLER_DIR . $class . '.class.php');
			return 1;
		} else {
			return 0;
		}
	}
	
	
	/**
	 * Função auxiliar para resolver URL de arquivos da Framework
	 */
	function resolveOlalaUrl($url) {
		return SITE_URL . FRAMEWORK_DIR . 'olalaphp-' . FRAMEWORK_VERSION . '/' . $url;
	}
	
	/**
	 * Função auxiliar para resolver URL absoluta (caminho com C:/)
	 */
	function resolveAbsoluteUrl($url) {
		return ROOT_URL . $url;
	}
	
	/**
	 * Função auxiliar para resolver URL de arquivos do site
	 */
	function resolveUrl($url) {
		return SITE_URL . $url;
	}
	
	/**
	 * Função para exibir msg de erro
	 */
	function showError($errorMsg) {
	    $idDIVQuery = 'error_' . rand(100, 999);
		echo '<div style=\'border:dashed 1px Red;background:#FFFFFF;padding:10px;color:Red;font-size:14px;font-family:Arial;\'>';
		echo '<b>Olala Framework - Vers&#227;o ' . FRAMEWORK_VERSION . '</b><br/>ERRO: ' . $errorMsg; 
		echo '<a style=\'color:Gray;\' href=\'javascript:void(0);\' onclick=\'javascript:if(document.getElementById("'.$idDIVQuery.'").style.display == "none"){document.getElementById("'.$idDIVQuery.'").style.display = ""}else{document.getElementById("'.$idDIVQuery.'").style.display = "none"}\'><br/><small>Rastrear [+]</small></a><br/>';
		    echo "<pre style=\"color:Blue;font-size:12px;font-family:Courier;display:none;text-align:left;width:100%;max-height:400px;overflow:auto;\" id='$idDIVQuery'>";
		    debug_print_backtrace ();
		echo "</pre>";
		echo '</div>';
	}
	
  
    
	
	if (isset ( $_GET ['dispatcher'] )) {
		require_olala ( "util/OlalaApplication.class.php" );
		require_olala ( "mvc/OlalaDispatcher.class.php" );
		$dispatcher = new OlalaDispatcher ( $_GET ['url'] );
	}
	function __autoload($class) {
		
		if (substr ( $class, 0, 5 ) == 'Olala' && ! (strrpos ( $class, 'DB' ) > 0)) {
			if (!require_olala("util/$class.class.php")) {
				require_olala("components/OlalaUI.class.php");
				require_olala("components/$class.class.php");
			}
		}
		if (strrpos ( $class, 'Controller' ) > 0) {
			require_controller_class ($class);
		} else if (strrpos ( $class, 'Model' ) > 0) {
			require_model_class ($class);
		} else {
			require_olala ( 'data/OlalaDB.class.php' );
			require_olala ( 'data/OlalaMySQL.class.php' );
			if (!require_model_class("_db", 'data')) {
				$db = new OlalaMySQL();
				$db->build();			
				require_model_class("data/_db");
			}
			$u = require_model_class ($class);
			if (! $u) {
				require_model_class ($class, 'util');
			}
		}
	}
	
	function pt($var) {
	    $preID = 'var_' . rand(100,999);
	    echo "<a style='margin:2px;background:#006699;color:#fff;padding:8px;text-transform:uppercase;font-size:11px;font-family:arial;text-decoration:none;border-radius:6px;' href='javascript:void(0);' onclick='javascript:if(document.getElementById(\"{$preID}\").style.display == \"none\"){document.getElementById(\"{$preID}\").style.display = \"\"}else{document.getElementById(\"{$preID}\").style.display = \"none\"}'>" . gettype($var) . (is_array($var)?' (' . count($var) . ')':'') . "</a>";
	    echo '<pre style=\'border:dotted 1px #006699;border-radius:10px;background:#FFFFFF;padding:10px;color:#006699;font-size:12px;font-family:Courier;margin:2px;display:none;max-height:500px;overflow:auto;\' id="'.$preID.'">';
	    print_r($var);
	    echo '</pre>';
	} 

	@session_start ();
	session_cache_expire ( SESSION_EXPIRE_TIME );

	
	
	require_once (ROOT_URL . FRAMEWORK_DIR . FRAMEWORK_VERSION . '/util/OlalaUtil.class.php');
	