<?php

class DBHelper{

	static protected $_class_code = '0001';
	
	static public function prepare_insert($data){
		$method_code = 'A001';
		
		if(!is_array($data)){
            $error_code = static::$_class_code . $method_code . '001';
			throw new Xception('$data is not array!', $error_code);
		}
		
		$fields = array_keys($data);
		foreach($fields as $field){
			$values[] = Util::escapeDBStr($data[$field]);
		}
		
		$sql = ' (`' . implode("`, `", $fields) . '`) VALUES (\'' . implode("', '", $values) . '\') ';
		return $sql;
	}
	
	static public function prepare_update($data){
		$method_code = 'A002';
		
		if(!is_array($data)){
            $error_code = static::$_class_code . $method_code . '001';
			throw new Xception('$data is not array!', $error_code);
		}
		
		$sql = '';
		foreach($data as $field => $value){
			$sql .= ' `' . $field . '` = \'' . Util::escapeDBStr($value) . '\',';
		}
		
		$sql = rtrim($sql, ",") . " ";
		return $sql;
	}
	
	static public function prepare_multi_insert($data){
		$method_code = 'A003';
		
		if(!is_array($data) || !is_array($data[0])){
            $error_code = static::$_class_code . $method_code . '001';
			throw new Xception('$data is not multi-dimensional array!', $error_code);
		}
        
        $fields = array_keys($data[0]);
		foreach($data as $row){
            $values = array();
            foreach($fields as $field){
                $values[] = Util::escapeDBStr($row[$field]);
            }
            $rowsValue[] = '(\'' . implode("', '", $values) . '\')';
		}
        
        $sql = ' (`' . implode("`, `", $fields) . '`) VALUES ' . implode(", ", $rowsValue) . ';';
		return $sql;
	}
	
	static public function prepare_multi_where($data, $logic = 'AND'){
		$method_code = 'A003';
		
        $wheres = array();
		if(!is_array($data)){
            $error_code = static::$_class_code . $method_code . '001';
			throw new Xception('$data is not an array!', $error_code);
		}
        
        if(!in_array($logic, array('AND', 'OR'))){
            $error_code = static::$_class_code . $method_code . '002';
			throw new Xception('$logic is not valid!', $error_code);
        }
        
		foreach($data as $field => $value){
            $prefix = '';
            if (strpos($field, ".") !== false) {
                $fieldElems = explode(".", $field);
                $prefix = $fieldElems[0] . '.';
                $field = $fieldElems[1];
            }
            
            if(is_array($value)){
                $elems = array();
                foreach($value as $elem){
                    $elems[] = Util::escapeDBStr($elem);
                }
                $wheres[] = $prefix . '`' . $field . '` IN (\'' . implode('\',\'', $elems) . '\')';
            }
            else{
                $wheres[] = $prefix . '`' . $field . '` = \'' . Util::escapeDBStr($value) . '\'';
            }
		}
        
        $sql = implode(" " . $logic . " ", $wheres);
		return $sql;
	}

}



?>