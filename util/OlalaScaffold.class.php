<?php
	
	class OlalaScaffold {
		
		public static $buffer;
				
		public static function generateFormsFromDB($tableName, $columns, $foreignKeys) {
			
			$scaffold = new OlalaScaffold();
			
			$filename = resolveAbsoluteUrl(VIEW_DIR . '/scaffold/'.call_user_func('__tableToClassName', $tableName).'.php');
			@unlink($filename);
			
			$fp = fopen($filename, 'a');
			fwrite($fp, PHP_EOL .'	<?php $this->masterpage = "masterpage.php"; ?>'. PHP_EOL . PHP_EOL);
			fwrite($fp, PHP_EOL .'	<div class="form-horizontal">' . PHP_EOL . PHP_EOL);
			fclose($fp);
			
			$i = 0;
			foreach ($columns as $column) {
				if ($i > 0) {
					$fk = false;
					if (@$foreignKeys[$column]) $fk = $foreignKeys[$column];
					$scaffold->generateField($tableName, $column, $fk);					
				}
				$i++;
			}
			
			$fp = fopen(resolveAbsoluteUrl(VIEW_DIR . '/scaffold/'.call_user_func('__tableToClassName', $tableName).'.php'), 'a');
			
			fwrite($fp, '		<div class="form-actions">'. PHP_EOL);
			fwrite($fp, '			<input class="btn" type="hidden" value="<?php echo $model->item["'.$columns[0]['Field'].'"]?>" name="'.$columns[0]['Field'].'"/>'. PHP_EOL);
			fwrite($fp, '			<button class="btn btn-large btn-primary" type="submit"><i class="icon-ok"></i> salvar</button>'. PHP_EOL);
			fwrite($fp, '			<button class="btn" type="button" value="novo" onclick="location.href=\'/Buffet/Prato\';"><i class="icon-file"></i> novo</button>'. PHP_EOL);
			fwrite($fp, '		</div>'. PHP_EOL);
						
			fwrite($fp, PHP_EOL .'	</div>'. PHP_EOL . PHP_EOL);
			fclose($fp);
			
		}
		
		public function generateField($tableName, $column, $fk = false) {
			
			$columnName = $column['Field'];
			$required = ($column['Null']=='NO')?'required':'';
			
			$fp = fopen(resolveAbsoluteUrl(VIEW_DIR . '/scaffold/'.call_user_func('__tableToClassName', $tableName).'.php'), 'a');
			fwrite($fp, '		<div class="control-group">'. PHP_EOL);
			fwrite($fp, '			<label class="control-label" for="'.$columnName.'">'.$columnName.':</label>'. PHP_EOL);
			fwrite($fp, '			<div class="controls">'. PHP_EOL);
			fwrite($fp, '				<input type="text" placeholder="'.$columnName.'" value="<?php echo $model->item["'.$columnName.'"]?>" name="'.$columnName.'" id="'.$columnName.'" '.$required.'/>'. PHP_EOL);
			fwrite($fp, '			</div>'. PHP_EOL);
			fwrite($fp, '		</div>'. PHP_EOL . PHP_EOL);
			fclose($fp);
			
		}
		
		public static function startBuildDBClass() {
			$filename = resolveAbsoluteUrl(MODEL_DIR . '/data/_db.class.php');
			@unlink($filename);
			$fp = fopen($filename, 'a');
			fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
			fclose($fp);
		}
		
		public static function buildDBClass($tableName, $columns, $foreignKeys) {
				
			$fp = fopen(resolveAbsoluteUrl(MODEL_DIR . '/data/_db.class.php'), 'a');
				
			if (function_exists("__tableToClassName")) {
				fwrite($fp, '	class ' . call_user_func('__tableToClassName', $tableName) . ' extends OlalaMySQL {' . PHP_EOL);
			} else {
				fwrite($fp, '	class ' . $tableName . ' extends OlalaMySQL {' . PHP_EOL);
			}
				
			fwrite($fp, '		function __construct() {' . PHP_EOL);
			fwrite($fp, '			$this->tableName = "'.$tableName.'";' . PHP_EOL);
		
			foreach ($columns as $column) {
				
				if (isset($foreignKeys[$column['Field']])) {

					$isNotNull = false;
					
					if ($column['Null'] == 'NO') {
						$isNotNull = true;
					}
					
					fwrite($fp, '			$this->addForeignKey("'.$column['Field']
					.'", "'.$foreignKeys[$column['Field']]['table']
					.'", "'.$foreignKeys[$column['Field']]['foreignColumnName']
					.'", '. (($isNotNull)?'true':'false')
					.', 0, \''. $column['Comment']
					.'\');' . PHP_EOL);
					
				} else if ($column['Key'] == 'PRI') {
					fwrite($fp, '			$this->primaryKey = "'.$column['Field'].'";' . PHP_EOL);
					fwrite($fp, '			$this->primaryKeyType = "'.OlalaScaffold::getType($column['Type']).'";' . PHP_EOL);
				} else {
					fwrite($fp, '			$this->addColumn("'	. $column['Field'].'", "'
																. OlalaScaffold::getType($column['Type']).'",'
																. (($column['Null']=='NO')?'true':'false') . ','
																. OlalaScaffold::getSize($column['Type']) . ','
																. '\'' . $column['Comment'] .'\');' . PHP_EOL);
				}
			}
				
			fwrite($fp, '		}' . PHP_EOL . '	}' . PHP_EOL . PHP_EOL);
			fclose($fp);
				
		}
		
		public static function getType($DBType) {
			$pos = strpos($DBType, '(');
			if ($pos > 0) {
				$DBType = substr($DBType, 0, $pos);
			}
			return $DBType;
		}
		
		public static function getSize($DBType) {
			
			$pos1 = strrpos($DBType, '(') + 1;
			$pos2 = strrpos($DBType, ')');
			
			if ($pos1 > 1) {
			
				$DBType = substr($DBType, $pos1, ($pos2 - $pos1));
				
				if (!is_numeric($DBType)) {
					$DBType = "array($DBType)";
				
				}
				
			} else {
				$DBType = 0;
			}
			
			return $DBType;
			
		}
				
	}
	
	
	
	
	
	