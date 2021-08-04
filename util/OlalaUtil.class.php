<?php

/**********************************************************************
 * 	Descrição: 	Classe que implementa funções diversas 			
 * 	
 * 	Autor: 		Ivan Florencio
 *  Data: 		26/02/2008 
 * 
 **********************************************************************/

	class OlalaUtil {
		
		public $meses;
		
		function OlalaUtil() {
			
		} 
		
		public static function arrayOrderBy()
		{
			$args = func_get_args();
			$data = array_shift($args);
			foreach ($args as $n => $field) {
				if (is_string($field)) {
					$tmp = array();
					foreach ($data as $key => $row)
						$tmp[$key] = $row[$field];
					$args[$n] = $tmp;
				}
			}
			$args[] = &$data;
			call_user_func_array('array_multisort', $args);
			return array_pop($args);
		}
		
		public static function encodeResult($result) {
			$newResult = array();
			if (is_array($result)) {
				foreach ($result as $record) {
					array_push($newResult, OlalaUtil::encodeRecord($record));
				}
			}
			return $newResult;
		}
		
		public static function decodeResult($result) {
			$newResult = array();
			if (is_array($result)) {
				foreach ($result as $record) {
					array_push($newResult, OlalaUtil::decodeRecord($record));
				}
			}
			return $newResult;
		}
		
		public static function encodeRecord($record) {
			
			if (!function_exists("f1")) {
				function f1 ($t) { 
					return is_string($t) ? utf8_encode($t) : $t; 
				} 
			}
			
			return array_map("f1", $record);			
		}
		
		public static function replaceSpecialChars($str)
		{
		    $a = array('’','“','”','–');
		    $b = array("'",'"','"','-');
		    return str_replace($a, $b, $str);
		} 
		
		public static function decodeRecord($record) {
			if (!function_exists("f2")) {
				function f2 ($t) {
				    $c = is_string($t) ? utf8_decode(OlalaUtil::replaceSpecialChars($t)) : $t;
				    return $c;
				}
			}
			return array_map("f2", $record);
		}
		
		public static function fromCamelCase($str) {
			$str[0] = strtolower($str[0]);
			$func = create_function('$c', 'return "_" . strtolower($c[1]);');
			return preg_replace_callback('/([A-Z])/', $func, $str);
		}
		
		public static function toCamelCase($str, $capitalise_first_char = false) {
			if($capitalise_first_char) {
				$str[0] = strtoupper($str[0]);
			}
			$func = create_function('$c', 'return strtoupper($c[1]);');
			return preg_replace_callback('/_([a-z])/', $func, $str);
		}
		
		public static function imprimirTextoHTML($texto) {
			$textoHTML = str_replace('\n', '<br/>', $texto);
			return $textoHTML;
		}
		
		public static function descontarPorcentagem($numero, $porcentagem) {
			$numero = $numero - ($numero*($porcentagem/100));
			return OlalaUtil::floatParaReais($numero);
		}
		
		public static function floatParaReais($numero) {
			return 'R$ ' . number_format($numero, 2, ',', '.');
		}
		
		public static function floatParaFormato($numero) {
			return 'R$ ' . number_format($numero, 2, ',', '.');
		}
		
		public static function floatMoedaUS($numero) {
			$posV = strrpos($numero, ',');
			$posP = strrpos($numero, '.');
			$tam = strlen($numero);
			if ($posV || $posP) {
				if ($posV == $tam-2 || $posP == $tam-2) $numero .= '0';
				$numero = str_replace(',','',$numero);
				$numero = str_replace('.','',$numero);
				$valor = substr($numero, 0, strlen($numero)-2) . "." . substr($numero, -2);
			} else {
				$valor = $numero . '.00';
			}			
			return $valor;
		}
		
		public static function montarNomeArquivo($url, $unicidade = true) {
			if ($unicidade) {
				$nome = date('y.m.d-H.i.s') . '-' . OlalaUtil::pegarNomeArquivoDeURL($url);
			} else {
				$nome = '-' . OlalaUtil::pegarNomeArquivoDeURL($url);
			}			
			$nome = OlalaUtil::removeSpecialChars($nome);
			$nome = str_replace('%', '', $nome);
			$nome = str_replace(' ', '_', $nome);
			$nome = strtolower($nome);
			return $nome;
		}
		
		public static function validarLinkExterno($url) {
			if (substr($url, 0, 4) != 'http') {
				$url = "http://" . strip_tags($url);
			}
			return $url;
		}
		
		public static function printResultAsHTMLTable($result) {
		    if (isset($result[0]) && !empty($result[0])) {
		        echo "<table style='width:100%;' class='table table-striped'>";
		        $i=0;
		        foreach ($result as $row) {
		            $columns = array_keys($row);
		            if ($i == 0) {
		                echo "<tr>";
		                echo "<th>#</th>";
		                foreach ($columns as $col) {
	                       echo "<th style='text-transform:uppercase;font-size:10px;'>{$col}</th>";
	                    }
		                echo "</tr>";
		            }
		            echo "<tr>";
		              echo "<td style='text-align:right;background-color:#aaa;color:#fff;'>" . (++$i) . "</td>";
		            foreach ($columns as $col) {
		                echo "<td style='border: solid 1px #ccc;font-size:11px;padding:5px;" . ((is_numeric($row[$col]))?'text-align:right;':'') . "'>{$row[$col]}</td>";
		            }
		            echo "</tr>";
		            
		        }
		        echo "</table>";
		    }
		}
		
		public static function _make_url_clickable_cb($matches) {
			$url = $matches[2];
			if ( empty($url) )
				return $matches[0];
			return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>";
		}
		
		public static function _make_email_clickable_cb($matches) {
			$email = $matches[2] . '@' . $matches[3];
			return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
		}
		
		public static function _make_web_ftp_clickable_cb($matches) {
			$ret = '';
			$dest = $matches[2];
			$dest = 'http://' . $dest;
			if ( empty($dest) )
				return $matches[0];
			// removed trailing [,;:] from URL
			if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
				$ret = substr($dest, -1);
				$dest = substr($dest, 0, strlen($dest)-1);
			}
			return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
		}
		
		public static function makeClickable($ret) {
			$ret = ' ' . $ret;
			// in testing, using arrays here was found to be faster
			$ret = preg_replace_callback('#(?<=[\s>])(\()?([\w]+?://(?:[\w\\x80-\\xff\#$%&~/\-=?@\[\](+]|[.,;:](?![\s<])|(?(1)\)(?![\s<])|\)))+)#is', 'OlalaUtil::_make_url_clickable_cb', $ret);
			$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]+)#is', 'OlalaUtil::_make_web_ftp_clickable_cb', $ret);
			$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', 'OlalaUtil::_make_email_clickable_cb', $ret);
			// this one is not in an array because we need it to run last, for cleanup of accidental links within links
			$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
			$ret = trim($ret);
			return $ret;
		}
		
		public static function identificarLinksTexto($texto, $target = '_blank') {
			return OlalaUtil::makeClickable($texto);
		}
		
		public static function clearSpecialChars($text, $charset = "") {
			$text = preg_replace('/[`^~\'"]/', null, $text);
			return $text;
		}
		
		public static function removeAccents( $str, $utf8=true ) {
		$str = (string)$str;
		if( is_null($utf8) ) {
			if( !function_exists('mb_detect_encoding') ) {
				$utf8 = (strtolower( mb_detect_encoding($str) )=='utf-8');
			} else {
				$length = strlen($str);
				$utf8 = true;
				for ($i=0; $i < $length; $i++) {
					$c = ord($str[$i]);
					if ($c < 0x80) $n = 0; # 0bbbbbbb
					elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
					elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
					elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
					elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
					elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
					else return false; # Does not match any model
					for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
						if ((++$i == $length)
							|| ((ord($str[$i]) & 0xC0) != 0x80)) {
							$utf8 = false;
							break;
						}
						
					}
				}
			}
			
		}
		
		if(!$utf8)
			$str = utf8_encode($str);

		$transliteration = array(
		'?' => 'I', 'Ö' => 'O','' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
		'?' => 'i','ö' => 'o','' => 'o','ü' => 'u','ß' => 's','?' => 's',
		'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
		'Æ' => 'A','A' => 'A','A' => 'A','A' => 'A','Ç' => 'C','C' => 'C',
		'C' => 'C','C' => 'C','C' => 'C','D' => 'D','Ð' => 'D','È' => 'E',
		'É' => 'E','Ê' => 'E','Ë' => 'E','E' => 'E','E' => 'E','E' => 'E',
		'E' => 'E','E' => 'E','G' => 'G','G' => 'G','G' => 'G','G' => 'G',
		'H' => 'H','H' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
		'I' => 'I','I' => 'I','I' => 'I','I' => 'I','I' => 'I','J' => 'J',
		'K' => 'K','L' => 'K','L' => 'K','L' => 'K','?' => 'K','L' => 'L',
		'Ñ' => 'N','N' => 'N','N' => 'N','N' => 'N','?' => 'N','Ò' => 'O',
		'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','O' => 'O','O' => 'O',
		'O' => 'O','R' => 'R','R' => 'R','R' => 'R','S' => 'S','S' => 'S',
		'S' => 'S','?' => 'S','' => 'S','T' => 'T','T' => 'T','T' => 'T',
		'?' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','U' => 'U','U' => 'U',
		'U' => 'U','U' => 'U','U' => 'U','U' => 'U','W' => 'W','Y' => 'Y',
		'' => 'Y','Ý' => 'Y','Z' => 'Z','Z' => 'Z','' => 'Z','à' => 'a',
		'á' => 'a','â' => 'a','ã' => 'a','a' => 'a','a' => 'a','a' => 'a',
		'å' => 'a','ç' => 'c','c' => 'c','c' => 'c','c' => 'c','c' => 'c',
		'd' => 'd','d' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
		'e' => 'e','e' => 'e','e' => 'e','e' => 'e','e' => 'e','' => 'f',
		'g' => 'g','g' => 'g','g' => 'g','g' => 'g','h' => 'h','h' => 'h',
		'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','i' => 'i','i' => 'i',
		'i' => 'i','i' => 'i','i' => 'i','j' => 'j','k' => 'k','?' => 'k',
		'l' => 'l','l' => 'l','l' => 'l','l' => 'l','?' => 'l','ñ' => 'n',
		'n' => 'n','n' => 'n','n' => 'n','?' => 'n','?' => 'n','ò' => 'o',
		'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','o' => 'o','o' => 'o',
		'o' => 'o','r' => 'r','r' => 'r','r' => 'r','s' => 's','' => 's',
		't' => 't','ù' => 'u','ú' => 'u','û' => 'u','u' => 'u','u' => 'u',
		'u' => 'u','u' => 'u','u' => 'u','u' => 'u','w' => 'w','ÿ' => 'y',
		'ý' => 'y','y' => 'y','z' => 'z','z' => 'z','' => 'z','?' => 'A',
		'?' => 'A','?' => 'A','?' => 'A','?' => 'A','?' => 'A','?' => 'A',
		'?' => 'A','?' => 'A','?' => 'A','?' => 'A','?' => 'A','?' => 'A',
		'?' => 'A','?' => 'A','?' => 'A','?' => 'A','?' => 'A','?' => 'A',
		'?' => 'A','?' => 'A','?' => 'A','?' => 'B','G' => 'G','?' => 'D',
		'?' => 'E','?' => 'E','?' => 'E','?' => 'E','?' => 'E','?' => 'E',
		'?' => 'E','?' => 'E','?' => 'E','?' => 'Z','?' => 'I','?' => 'I',
		'?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I',
		'?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I',
		'?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I',
		'T' => 'T','?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I',
		'?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I','?' => 'I',
		'?' => 'I','?' => 'I','?' => 'I','?' => 'K','?' => 'L','?' => 'M',
		'?' => 'N','?' => 'K','?' => 'O','?' => 'O','?' => 'O','?' => 'O',
		'?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'P',
		'?' => 'R','?' => 'R','S' => 'S','?' => 'T','?' => 'Y','?' => 'Y',
		'?' => 'Y','?' => 'Y','?' => 'Y','?' => 'Y','?' => 'Y','?' => 'Y',
		'?' => 'Y','?' => 'Y','F' => 'F','?' => 'X','?' => 'P','O' => 'O',
		'?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O',
		'?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O',
		'?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O','?' => 'O',
		'?' => 'O','a' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a',
		'?' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a',
		'?' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a',
		'?' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a','?' => 'a',
		'?' => 'a','?' => 'a','?' => 'a','ß' => 'b','?' => 'g','d' => 'd',
		'e' => 'e','?' => 'e','?' => 'e','?' => 'e','?' => 'e','?' => 'e',
		'?' => 'e','?' => 'e','?' => 'e','?' => 'z','?' => 'i','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 't','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i',
		'?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'i','?' => 'k',
		'?' => 'l','µ' => 'm','?' => 'n','?' => 'k','?' => 'o','?' => 'o',
		'?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o',
		'?' => 'o','p' => 'p','?' => 'r','?' => 'r','?' => 'r','s' => 's',
		'?' => 's','t' => 't','?' => 'y','?' => 'y','?' => 'y','?' => 'y',
		'?' => 'y','?' => 'y','?' => 'y','?' => 'y','?' => 'y','?' => 'y',
		'?' => 'y','?' => 'y','?' => 'y','?' => 'y','?' => 'y','?' => 'y',
		'?' => 'y','?' => 'y','f' => 'f','?' => 'x','?' => 'p','?' => 'o',
		'?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o',
		'?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o',
		'?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o',
		'?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'o','?' => 'A',
		'?' => 'B','?' => 'V','?' => 'G','?' => 'D','?' => 'E','?' => 'E',
		'?' => 'Z','?' => 'Z','?' => 'I','?' => 'I','?' => 'K','?' => 'L',
		'?' => 'M','?' => 'N','?' => 'O','?' => 'P','?' => 'R','?' => 'S',
		'?' => 'T','?' => 'U','?' => 'F','?' => 'K','?' => 'T','?' => 'C',
		'?' => 'S','?' => 'S','?' => 'Y','?' => 'E','?' => 'Y','?' => 'Y',
		'?' => 'A','?' => 'B','?' => 'V','?' => 'G','?' => 'D','?' => 'E',
		'?' => 'E','?' => 'Z','?' => 'Z','?' => 'I','?' => 'I','?' => 'K',
		'?' => 'L','?' => 'M','?' => 'N','?' => 'O','?' => 'P','?' => 'R',
		'?' => 'S','?' => 'T','?' => 'U','?' => 'F','?' => 'K','?' => 'T',
		'?' => 'C','?' => 'S','?' => 'S','?' => 'Y','?' => 'E','?' => 'Y',
		'?' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','?' => 'a',
		'?' => 'b','?' => 'g','?' => 'd','?' => 'e','?' => 'v','?' => 'z',
		'?' => 't','?' => 'i','?' => 'k','?' => 'l','?' => 'm','?' => 'n',
		'?' => 'o','?' => 'p','?' => 'z','?' => 'r','?' => 's','?' => 't',
		'?' => 'u','?' => 'p','?' => 'k','?' => 'g','?' => 'q','?' => 's',
		'?' => 'c','?' => 't','?' => 'd','?' => 't','?' => 'c','?' => 'k',
		'?' => 'j','?' => 'h'
		);
		$str = str_replace( array_keys( $transliteration ),
							array_values( $transliteration ),
							$str);
		return $str;
	}
		
		public static function removeSpecialChars($text) {
			$text = preg_replace('/[\(\) _]/', '-', $text);
			return preg_replace('/[^A-Za-z0-9\-]/', '', $text); // Removes special chars.
		}
		
		public static function mimeToArray($mime, $fieldSeparator = '&', $valueSeparator = '=', $loneLine = true) {
			$mime = explode($fieldSeparator, $mime);
			$i = 0;
			foreach ($mime as $field) {
				if ($loneLine) {
					$field = explode($valueSeparator, $field);
					$array[@$field[0]] = @$field[1];
				} else {
					$field = explode($valueSeparator, $field);
					$array[$i]['field'] = @$field[0];
					$array[$i]['value'] = @$field[1];
					$i++;
				}
			}
			return $array;			 
		}
		
		public static function format($string, $formato) {
			$stringFortadada = $string;
			if ($formato == 'date') {
				$stringFortadada = OlalaUtil::timestampParaData($string);
			} else if ($formato == 'timestamp') {
				$stringFortadada = OlalaUtil::timestampParaDataHora($string);
			}
			return $stringFortadada;
		}
						
		/**
		 *  Recebe uma URL 
		 *  e retorna somente o nome do arquivo com extensão
		 * 
		 */	
		public static function pegarNomeArquivoDeURL($url) {
			$nomeArquivo = substr($url, strripos($url, '\\'), strlen($url));			
			return $nomeArquivo;			
        }
        
		/**
		 *  Recebe uma URL 
		 *  e retorna somente a com extensão do arquivo
		 * 
		 */	
		public static function pegarExtensaoArquivoDeURL($url) {
			$extArquivo = substr($url, strripos($url, '.'), strlen($url));		
			return strtolower($extArquivo);
        }

		/**
		 *  Recebe um data no formato Sat, 08 Mar 2008 14:40:45 -030 
		 *  e retorna um TIMESTAMP
		 * 
		 */
		function pubDateParaTimestamp($data) {			
			if (($timestamp = strtotime($data)) === false) {
			    $timestamp = time();
			}
			$timestamp = date('Y-m-d h:i:s', $timestamp);	
			return $timestamp;			
        }
        
        /**
         *  Recebe uma abreviatura de 3 caracteres do mês em Inglês
         *  e retorna o mês em numeral de 1 a 12
         * 
         */
        public static function stringMesParaNumeroMes($mes) {
        	return $this->meses[trim($mes)];
        }
		
		/**
		 *  Recebe uma data e uma hora
		 *  e retorna um TIMESTAMP
		 * 
		 */
		public static function dataHoraParaTimestamp($data, $hora) {
			$timestamp = substr($data, 6, 4) . '-' . substr($data, 3, 2) . '-' . substr($data, 0, 2) . ' ' . $hora;
			return (strlen($timestamp)<10)?'':$timestamp;	  
        }
        
        /**
         *  Recebe um TIMESTAMP
         *  e retorna data e hora no formato DD/MM/YYYY às HH:MI 
         * 
         */
        public static function timestampParaDataHora($timestamp, $idioma = 'portuguese') {
            
			setlocale(LC_TIME, $idioma);
            
            $dataFinal = strftime('%c', strtotime($timestamp));
            
        	return $dataFinal;	  	  
        }
        
        /**
         *  Recebe um TIMESTAMP
         *  e retorna data e hora no formato DD de MM de YYYY às HH:MI
         *
         */
        public static function timestampParaDataHoraLonga($timestamp) {
        	return substr($timestamp, 5, 2);
        }
        
        /**
         *  Recebe um TIMESTAMP
         *  e retorna data e hora no formato desejado
         * 
         */
        public static function timestampParaFormato($timestamp, $formato) {
			
			$formatado = strtotime($timestamp);
			$formatado = date($formato, $formatado);
			
			return $formatado; 		  
        }
        
        /**
         *  Recebe um TIMESTAMP
         *  e retorna data no formato DD/MM/YYYY
         * 
         */
        public static function timestampParaData($timestamp) {
            
            $dataFinal = date('d/m/Y', strtotime($timestamp));
            
            return $dataFinal;	  
        }
        
        /**
         *  Recebe um TIMESTAMP
         *  e retorna hora no formato HH:MI
         * 
         */
        public static function timestampParaHora($timestamp) {
			$dataFinal = '';
        	if ($timestamp) {
				$dataFinal = substr($timestamp, 11, 5);
			} 	
			return $dataFinal;	 	  
        }
        
        /**
         *  Recebe uma String de qualquer tamanho e o tamanho final da String
         *  e retorna um String do tamanho que foi passado
         * 
         */
        public static function limitarString($string, $tamanho = 30, $mostraReticencia = true) {
        	
            $string = strip_tags($string);
            $string = preg_replace('/^\s+|\n|\r|\s+$/m', '', $string);
            $tam = strlen($string);
        	if ($tam > $tamanho) {
        		$string = substr($string, 0, $tamanho) . (($mostraReticencia)?'...':'');
        	}
        	return $string;
        }
        
        /**
         *  Recebe uma String de qualquer tamanho e o tamanho final da String
         *  e retorna um String do tamanho que foi passado
         *
         */
        public static function cropString($string, $size = 40, $suspensionPoints = false) {
        	$tam = strlen($string);
        	if ($tam > $size) {
        		$string = substr($string, 0, $size) . (($suspensionPoints)?'...':'');
        	}
        	return $string;
        }
        
        
		public static function limitarStringPorQuebra($string, $link = '#', $txtLink = 'LEIA MAIS »', $quebra = '<!-- pagebreak -->',  $mostraReticencia = true) {
        	$tamanho = strrpos($string, $quebra);
        	if ($tamanho > 0) {
        		$string = substr($string, 0, $tamanho) . (($mostraReticencia)?'...':'');
        		$string = $string . " <a href='$link'>$txtLink</a>";
        	}
        	return $string;
        }
        
        
        
        
        public static function mostrarImagemHTML($url, $largura = '100', $altura = '70', $style = ''){
            echo '<div style=\'background:url(\'',$url,'\') center no-repeat; $style; display:block; z-index:1; width:',$largura,'px;height:',$altura,'px;',$style,'\'>&nbsp;</div>';
        }
        
        public static function destacarTrechoCom($texto, $destaque, $tamanhoTrecho = 200) {
        	
        	$TAMANHO_TRECHO = ($tamanhoTrecho-strlen($destaque)/2) ;
        	
        	$pos = stripos($texto, $destaque);
        	$tam = $pos + strlen($destaque) + $TAMANHO_TRECHO;
        	
        	if ($pos < $TAMANHO_TRECHO) {
        		$offset = 0;  
        	
        	} else {
        		$offset = $pos - $TAMANHO_TRECHO;
        	
        	}
        	
        	$texto = substr($texto, $offset, $tam-$offset);
        	$html = strip_tags($texto);
        	$html = htmlentities($html);
        	$html = str_replace($destaque, "<strong>$destaque</strong>", $texto); 
        	
        	return '...'.$html.'...';
        	
        }

        public static function antiSQLInjection($sql) {			
			if (!is_numeric($sql)) {
		        $sql = get_magic_quotes_gpc() ? stripslashes($sql) : $sql;		        
		        $php = phpversion();		        
		        if ($php <= "5.0.0") {
		        	$sql = mysql_escape_string($sql);
		        } else {
		        	$sql = OlalaUtil::escape($sql);
		        }	        
		    }	
			return $sql;
		}
		
		public static function escape($value) {
			$return = '';
			for($i = 0; $i < strlen($value); ++$i) {
				$char = $value[$i];
				$ord = ord($char);
				if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
					$return .= $char;
				else
					$return .= '\\x' . dechex($ord);
			}
			return $return;
		}
		
		public static function duplicarAspasSimples($texto) {
			
			$texto = str_replace('"', '""', $texto);
			$texto = str_replace('\'', '\'', $texto);
			
			return $texto;
	
		}
		
		public static function antiHTMLInjection($texto) {
			
			//tira tags html e php
			$texto = strip_tags($texto);
					
			//Converte o que sobrou do strip tags
			$texto = htmlentities($texto);
			
			return $texto;
	
		}
		
		public static function pegarMesLiteral($mesNumero){
           
            $mes['01'] = 'Janeiro';
            $mes['02'] = 'Fevereiro';
            $mes['03'] = 'Mar&#231;o';
            $mes['04'] = 'Abril';
            $mes['05'] = 'Maio';
            $mes['06'] = 'Junho';
            $mes['07'] = 'Julho';
            $mes['08'] = 'Agosto';
            $mes['09'] = 'Setembo';
            $mes['10'] = 'Outubro';
            $mes['11'] = 'Novembro';
            $mes['12'] = 'Dezembro';
           
            if($mesNumero != ''){
                return $mes[$mesNumero];
            } else {
                return $mes[date('m')];
            }
           
        }
        
         /*
	        Função que retorna o dia da semana
	        Formato da data deverá ser Y-m-d
        */
        function diaSemana($data) {
            
            $rs = strftime('%w', strtotime($data));
            
            switch($rs) {
                case '0': $diaSemana = 'Domingo'; break;
                case '1': $diaSemana = 'Segunda-feira'; break;
                case '2': $diaSemana = 'Ter&#231;a-feira'; break;
                case '3': $diaSemana = 'Quarta-feira'; break;
                case '4': $diaSemana = 'Quinta-feira'; break;
                case '5': $diaSemana = 'Sexta-feira'; break;
                case '6': $diaSemana = 'S&#225;bado'; break;
            }
            return $diaSemana;
        }   
        
       
        /*
	        Função que retorna a data ex: Domingo 10 de janeiro de 2008
	        Formato da data deverá ser Y-m-d
        */
        public static function timestampToLiteral($data, $idioma = 'pt-BR') {
            
            if ($idioma == 'pt-BR') setlocale(LC_ALL, 'portuguese', 'pt_BR', 'pt');
            if ($idioma == 'en-US') setlocale(LC_ALL, 'english', 'en_US', 'en');
            if ($idioma == 'fr-FR') setlocale(LC_ALL, 'french', 'fr_FR', 'fr');
            if ($idioma == 'es-ES') setlocale(LC_ALL, 'spanish', 'es_ES', 'es');
            
            $diaSemana = ucwords(strftime('%A', strtotime($data)));
                       
            $mes = ucwords(strftime('%B',strtotime($data)));
            
            $ano = strftime('%Y',strtotime($data));
            $dia = strftime('%d',strtotime($data));
            
            $full = "";
            
            if ($idioma == 'pt-BR') {
                $full = $diaSemana.', '.$dia.' de '.$mes.' de '.$ano;
            } else if ($idioma == 'en-US') {
                $full = $diaSemana.', '.$mes.' '.$dia.', '.$ano;
            } else {
                $full = $diaSemana.', '.$dia.' '.$mes.', '.$ano;
            }
            
            return utf8_decode($full);
        }
        public static function dataLiteral($data) {
        
            $rs = strftime('%w', strtotime($data));
        
            switch($rs) {
                case '0': $diaSemana = 'Domingo'; break;
                case '1': $diaSemana = 'Segunda-feira'; break;
                case '2': $diaSemana = 'Ter&#231;a-feira'; break;
                case '3': $diaSemana = 'Quarta-feira'; break;
                case '4': $diaSemana = 'Quinta-feira'; break;
                case '5': $diaSemana = 'Sexta-feira'; break;
                case '6': $diaSemana = 'S&#225;bado'; break;
            }
             
            $mes = strftime('%B',strtotime($data));
            switch($mes) {
                case 'January': $mes = 'Janeiro'; break;
                case 'February': $mes = 'Fevereiro'; break;
                case 'March': $mes = 'Mar&#231;o'; break;
                case 'April': $mes = 'Abril'; break;
                case 'May': $mes = 'Maio'; break;
                case 'June': $mes = 'Junho'; break;
                case 'July': $mes = 'Julho'; break;
                case 'August': $mes = 'Agosto'; break;
                case 'September': $mes = 'Setembro'; break;
                case 'October': $mes = 'Outubro'; break;
                case 'November': $mes = 'Novembro'; break;
                case 'December': $mes = 'Dezembro'; break;
            }
             
            $ano = substr ( $data, 0, 4 );
            $dia = substr ( $data, 8, 2 );
             
            return $diaSemana.', '.$dia.' de '.$mes.' de '.$ano;
        }
        
        public static function pegarIdYouTube($url) {
        	$idYouTube = substr($url, (strripos($url, '?v=') + 3), 11);
        	return $idYouTube;
		}
		
		public static function configurarTextoWP($post) {
			//$post = nl2br(strip_tags($post,'a,b,u,i,strong,img,table,td,tr'));
			$post = OlalaUtil::identificarYoutubeWP($post);
			$post = OlalaUtil::identificarImagensWP($post);
			return $post;
		}
		
		public static function identificarImagensWP($post) {
			$post = preg_replace('/(\[\/?)(.*?)([^\]]*\])/', '', $post);			
			return $post;
		}
		
		public static function identificarYoutubeWP($post) {
			$postyt = '';
			$videos = explode('[youtube=http://www.youtube.com/', $post);
				foreach ($videos as $video) {
					if (substr($video, 0, 5) == 'watch') {
						$idyt = OlalaUtil::pegarIdYouTube($video);
						$video = '<div class="youtube-wp"><object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/'.$idyt.'&hl=pt-br&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/'.$idyt.'&hl=pt-br&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed></object></div>' . substr($video, stripos($video, ']')+1, strlen($video));
					}
					$postyt .= $video;
				}
			return $postyt;
		}
		
		public static function retirarMarcacao($texto, $marcacao, $cardinalidade = 5) {
			$itens = explode(',', $marcacao);
			foreach ($itens as $item) {
				for ($i=1; $i<=$cardinalidade; $i++) {
					$texto = str_replace(str_replace('#', $i, $item), '', $texto);
				}
			}			
			return $texto;
		}
		
		public static function maiuscula ($str) {
	  		$LATIN_UC_CHARS = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ";
	  		$LATIN_LC_CHARS = "àáâãäåæçèéêëìíîïðñòóôõöøùúûüý";
	  		$str = strtr ($str, $LATIN_LC_CHARS, $LATIN_UC_CHARS);
	  		$str = strtoupper($str);
	  		return $str;
		}
		
	}