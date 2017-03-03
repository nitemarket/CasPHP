<?php

class SiteDatabase{
	static protected $_class_code = '0000';
	
    /**
     * @var Database The PDO singleton instance, will be instantiated during first
	 * use. Subsequent calls will return the previous instance instead of
	 * instantiating a new one.
     */
    protected static $_PDOInstance;
	
	/**
	 * @var string $_mode the mode of the database to connect to, defaults to 'live'.
	 */
    protected static $_mode = 'live';
	
	
	/**
	 * switch the database connection to connect live database
	 */
	static public function switch_to_live(){
		static::$_mode = 'live';
	}
	
	static public function instance(){
		$method_code = '0001';
		global $vars;
		
        if(!static::$_PDOInstance){
            if(static::$_mode == 'live'){
                static::$_PDOInstance = new Database(
                    $vars['dbi']['host'],
                    $vars['dbi']['name'],
                    $vars['dbi']['user'],
                    $vars['dbi']['pass'],
                    $vars['dbi']['prefix'],
                    $vars['dbi']['port']
                );
            }
        }
        
        return static::$_PDOInstance;
	}
}

/**
 * database class - PDO
 */ 
/**
 * Database is the PDO wrapper class.
 *
 * This class uses singleton design pattern, as such there will only be one active instance
 * throughout the application.
 *
 */
class Database extends PDO{
	
    protected $_db_name;
    protected $_prefix;
     
  	/**
  	 * Creates a PDO instance representing a connection to a database and makes the instance available as a singleton
  	 * 
  	 * @return Database
  	 */
    public function __construct($host, $dbname, $user, $pass, $prefix = '', $port = '') {
		global $vars;
		if(!$host){
			throw new exception ('Database host not defined!');
		}
		if(!$dbname){
			throw new exception ('Database name not defined!');
		}
		if(!$user){
			throw new exception ('Database username not defined!');
		}
		$this->_db_name = $dbname;
		$this->_prefix = $prefix;
        $dsn = "mysql:host={$host};dbname={$dbname};";
		if($port){
			$dsn .= "port={$port};";
		}
		$driver_options = array(
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		);
		try{
			$dba = @parent::__construct($dsn, $user, $pass, $driver_options);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('dbStatement'));
			$this->query('SET NAMES utf8'); 
		}
		catch(exception $e){
			throw new exception($e->getMessage());
		}
    }
	
	public function getDBName(){
		$method_code = '0002';
		return $this->_db_name;
	}
	
	public function prefix(){
		$method_code = '0003';
		return $this->_prefix;
	}
	
	public function addToDB($data, $table){
		$method_code = '0004';
		$ufq = $uvq = '';
		foreach($data as $f => $v){
			$ufq .= "$f,";
			$uvq .= "'".Util::escapeDBStr($v)."',";
		}
		$ufq = rtrim($ufq, ',');
		$uvq = rtrim($uvq, ',');
		$sql = "INSERT INTO {$table} ($ufq) VALUES ($uvq)";
		
		try{
			$new = $this->query($sql);
			$id = $this->lastInsertId();
		}
		catch(exception $e){
			$log = "SQL: $sql\n\nError: " . $e->getMessage();
			throw new exception($log);
		}
		
		return $id;
	}
	
	public function updateToDB($data, $id, $table){
		$method_code = '0005';
		$ufvq = '';
		foreach($data as $f => $v){
			$ufvq .= "$f='".Util::escapeDBStr($v)."',";
		}
		$ufvq = rtrim($ufvq, ',');
		$sql = "UPDATE {$table} SET $ufvq WHERE id='{$id}' limit 1";
		try{
			return $new = $this->query($sql);
		}
		catch(exception $e){
			$log = "SQL: $sql\n\nError: " . $e->getMessage();
			throw new exception($log);
		}
	}
	
	public function get_columns_name_from_table($table){
		$method_code = '0006';
		
		$sql = "SHOW COLUMNS FROM {$table}";
		try{
			$fields = $this->query($sql)->fetchAllAssoc();
			if($fields){
				$i = 0;
				foreach($fields as $details){
					$all_fields[$i++] = $details['Field'];
				}
				return $all_fields;
			}
		}
		catch(exception $e){
			$log = "SQL: $sql\n\nError: " . $e->getMessage();
			throw new exception($log);
		}
	}
}
  /**
  * dbStatement is the PDOStatement wrapper class to compliment Database.
  *
  * This class extends PDOStatement, and is meant to be a helper class to Database. Further data manipulation
  * can be done before it is passed out. 
  *
  */
class dbStatement extends PDOStatement {
	protected $_class_code = '0002';

    /**
     * Fetches the given field of next row from a result set.
     * @param mixed $fieldname The fieldname or a zero-based index for the field number.
     * @return mixed
     * The field value on success, FALSE on failure
     */
    public function fetchField($fieldname = 0) {
		$method_code = '0001';
        $data = $this->fetch(PDO::FETCH_BOTH);
        if(!isset($data[$fieldname])){
            $data[$fieldname] = FALSE;
        }
        return $data[$fieldname];
    }

    /**
     * Fetches the next row from a result set as an associative array.
     * @return array An associative array with the row data.
     */
    public function fetchAssoc() {
		$method_code = '0002';
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches the next row from a result set as an array with index is the number start with 0.
     */
    public function fetchNum() {
		$method_code = '0002';
        return $this->fetch(PDO::FETCH_NUM);
    }

    public function fetchAllAssoc(){
		$method_code = '0003';
        return $this->fetchAll(PDO::FETCH_ASSOC);
    }

	/**
     * Fetches the next row from a result set as an associative array and outputs it as a table.
     * @return array A table with the row data.
     */
    public function fetchTable($attributes = array()){
		$method_code = '0004';
        $table = "<table";
        $table .= " class='table'";
        foreach($attributes as $attribute => $value){
            if(is_array($value)){
                //support multiple classes (e.g. class = "class1 class2").
                $value = implode(" ", $value);
            }
            $table .= " " . $attribute . "=\"" . $value . "\"";
        }
        $table .= ">\n";
        $tableheaders = "";
        $rows = "";
        $header = "";
		$k = 0;
        while($row = $this->fetchAssoc()){
            if(empty($tableheaders)){
                $header .= "\t<tr class='header'>\n";
            }
            $rows .= "\t<tr class='row$k'>\n";
            foreach ($row as $fieldname => $field){
                if(empty($tableheaders)){
                    $header .= "\t\t<td>" . $fieldname . "</td>\n";
                }
                $rows .= "\t\t<td>" . $field . "</td>\n";
            }
            $rows .= "\t</tr>\n";
            if(empty ($tableheaders)){
                $header .= "\t</tr>\n";
                $tableheaders .= $header;
            }
			$k = 1 - $k;
        }
        $table .= $tableheaders . $rows . "</table>\n";
        return $table;
    }
}	
?>