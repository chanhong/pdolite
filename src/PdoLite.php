<?php
/*
// Must set three const or use dbConnect 
const PDOLITE_DB_USER = 'dbuser';
const PDOLITE_DB_PASS = 'dbpass';
const PDOLITE_DB_DSN = 'sqlite:db/mydb.sqlite';

// Sample code using New Ojbect call

$db = new PdoLite();
$conn = $db->dbConnect($dsn,$username,$password);
print_r($conn->dbFetch($sql, "assoc")); 

// Sample code using static call

PdoLite::exec("update test set title='Test' where id =1h"); 
$sql = "SELECT * FROM test";
foreach( PdoLite::query($sql) as $row){ print_r($row); } 
print_r(PdoLite::findRow($sql, "lazy")); 
*/

namespace PdoLite;

use PDO;

class PdoLite {
     
    public static $options;
    
    private static $objInstance; 
    
    /* 
     * Class Constructor - Create a new database connection if one doesn't exist 
     * If Set to private so no-one can create a new instance via ' = new DB();' 
     */ 
    public function __construct() {} 
    
    /* 
     * Like the constructor, we make __clone private so nobody can clone the instance 
     */ 
    private function __clone() {} 
    
    /* 
     * Returns DB instance or create initial connection 
     * @param 
     * @return $objInstance; 
     */ 
    public static function getInstance(  ) { 
            
        if(!self::$objInstance){ 
            self::$options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
            
            self::$objInstance = new PDO(PDOLITE_DB_DSN, PDOLITE_DB_USER, PDOLITE_DB_PASS, self::$options); 
        } 
        
        return self::$objInstance; 
    } # end method 
    
    /* 
     * Passes on any static calls to this class onto the singleton PDO instance 
     * @param $chrMethod, $arrArguments 
     * @return $mix 
     */ 
    final public static function __callStatic( $chrMethod, $arrArguments ) { 

        $objInstance = self::getInstance(); 
        
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments); 
    } # end method 

    /* 
     * Passes on any calls to this class onto the singleton PDO instance 
     * @param $chrMethod, $arrArguments 
     * @return $mix 
     */ 
    final public function __call( $chrMethod, $arrArguments ) {

        $objInstance = self::getInstance(); 
        
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments); 
    } # end method 
    
    /*
     * Backtrace errors 
     * @param 
     * @return $str; 
    */
    public static function backTrace() {

        $str = "<br />[backTrace]";
        foreach (debug_backtrace() as $row) {
            $str .= "<br />FILE: "
                    . $row['file'] . " FUNC: " . $row['function']
                    . " LINE: " . $row['line'] . " ARGS: "
                    . print_r($row['args'], true);
        }
        return $str;
    }
    
    /*
     * database errors 
     * @param $msg
     * @return $str; 
    */    
    public static function dbError($msg = "") {
        
            return '<pre>' . $msg. $e->getMessage() . '</pre><br />' . $this->backTrace();            
    }

    /* 
     * Returns DB instance or create initial connection 
     * @param 
     * @return $objInstance; 
     */ 
	public static function dbConnect($dsn, $user="", $passwd="", $options=array()) {

		try {
            if (!self::$objInstance){
                if (!empty($dsn)) {
                    defined('PDOLITE_DB_DSN') or define('PDOLITE_DB_DSN', $dsn);
                    defined('PDOLITE_DB_USER') or define('PDOLITE_DB_USER', $user);
                    defined('PDOLITE_DB_PASS') or define('PDOLITE_DB_PASS', $passwd);
                } 
                if (empty($options)) {
                    self::$options = array(
                        PDO::ATTR_PERSISTENT => true, 
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    );
                } else {
                        self::$options = $options;
                };
                self::$objInstance = self::getInstance(); 
            }
            return self::$objInstance;
        } catch (PDOException $e) {
            die(self::dbError($dsn));            
        }
    }

    /* 
     * alias to PDO query 
     * @param $sql 
     * @return object of rows 
     */ 
    public static function dbQuery($sql) {
        
        try {
            return self::query($sql);   
		} catch (PDOException $e) {
            die(self::dbError($sql));
		}
    }

    /* 
     * call query and dbFetch num to get lastID 
     * @param $table, $field 
     * @return $integer 
     */ 
    public static function getNextId($table, $field) {

        $sql = 'SELECT max(' . $field . ') as lastid FROM ' . $table;
        list($lastid) = self::dbFetch(self::query($sql), "num"); // cause warning when use assoc array
        return (int) $lastid + 1;
    }

    /* 
     * call query and dbFetch assoc to get one row 
     * @param $sql  
     * @return array of one row 
     */ 
    public static function row2Array($sql) {

        return self::dbFetch(self::query($sql), "assoc");
    }

    /* 
     * call query and dbFetch obj to get one row 
     * @param $sql $atype (obj or lazy) 
     * @return obj of one row 
     */ 
    public static function findRow($sql, $atype = "obj") {

        return self::dbFetch(self::query($sql), $atype);
    }

    /* 
     * Alias to fetch to use with while ($row = Pdolite::dbFetch($res, "both")) {}
     * @param $qhandle, $atype (both, assoc or array, num or blank)
     * @return $array of FETCH_BOTH 
     */ 
    public static function dbFetch($qhandle, $atype = "both") {

        try {
            if (is_object($qhandle)) {
                $type = self::getPDOFetchType($atype);
                return $qhandle->fetch($type);
            }
		} catch (PDOException $e) {
            die(self::dbError(__METHOD__));
		}
    }

    /* 
     * alias to dbFetch obj 
     * @param $qhandle 
     * @return Obj 
     */ 
    public static function dbFetchObj($qhandle) {

        return self::dbFetch($qhandle, "obj");    
    }

    /* 
     * alias to dbFetch lazy 
     * @param $qhandle 
     * @return Obj 
     */ 
    public static function dbFetchLazy($qhandle) {

        return self::dbFetch($qhandle, "lazy");    
    }

    /* 
     * alias to dbFetch both 
     * @param $qhandle 
     * @return array of FETCH_BOTH 
     */ 
    public static function dbFetchArray($qhandle) {

        return self::dbFetch($qhandle, "both");    
    }

    /* 
     * alias to dbFetch assoc 
     * @param $qhandle 
     * @return array of FETCH_ASSOC 
     */ 
    public static function dbFetchAssoc($qhandle) {

        return self::dbFetch($qhandle, "assoc");    
    }
    
    /* 
     * alias to dbFetch num 
     * @param $qhandle 
     * @return array of FETCH_NUM 
     */ 
    public static function dbFetchRow($qhandle) {

        return self::dbFetch($qhandle, "num");    
    }

    /* 
     * call query and dbFetch  (slower but less memory)
     * @param $sql $atype 
     * @return nested array of rows 
     */ 
    public static function dbQ2Array($sql, $atype = "both", $fetch = "fetch") {

        try {
            $myrows = array();
            $res = self::query($sql);
            if (is_object($res)) {
                If (strtolower($fetch) == "all") {
                    $type = self::getPDOFetchType($atype);
                    return $res->fetchAll($type);
                } else {
                    while ($myrow = self::dbFetch($res, $atype)) { 
                        // use [] so the resulting array will be the same as fetchAll
                        $myrows[] = $myrow; 
                    }
                    return $myrows;
                }
            }
		} catch (PDOException $e) {
            die(self::dbError($sql));
		}
    }

    /* 
     * call query and fetchAll  (faster but more memory)
     * @param $sql  
     * @return nested array of rows 
     */ 
    public static function dbQ2ArrayAll($sql, $atype = "both") {

        try {
            $res = self::query($sql);
            if (is_object($res)) {
                $type = self::getPDOFetchType($atype);
                return $res->fetchAll($type);
            }
		} catch (PDOException $e) {
            die(self::dbError($sql));
		}
    }

    public static function getPDOFetchType($atype) {
        
        switch ($atype) {
            case 'obj':
                $type = PDO::FETCH_OBJ;
                break;
            case 'lazy':
                $type = PDO::FETCH_LAZY;
                break;
            case 'both':
                $type = PDO::FETCH_BOTH;
                break;
            case 'array':
            case 'assoc':
                $type = PDO::FETCH_ASSOC;
                break;
            case '':
            case 'num':
            case 'value':
            default:
                $type = PDO::FETCH_NUM;
                break;
        }
        return $type;
    }

    /* 
     * alias to dbQ2Array
     * @param $sql $type 
     * @return nested array of rows 
     */ 
    public static function rows2Array($sql, $atype = "") {
        
        return self::dbQ2Array($sql, $atype, "fetch");
    }

    /* 
     * alias to dbQ2ArrayAll
     * @param $sql $type 
     * @return nested array of rows 
     */ 
    public static function rows2ArrayAll($sql, $atype = "") {

        return self::dbQ2Array($sql, $atype, "all");
    }

    /* 
     * get one row from table
     * @param $table 
     * @return assoc array of fields 
     */ 
    public static function schema($table, $filter="id") {

        $row = (array) self::findRow("select * from ".$table,"assoc");
        // remove array element base on filter if _none_ no filter
        if (strtolower($filter)!="_none_") {
            // default is to filter id field
            unset($row[$filter]);
        }
        return array_keys($row);
    }

    /* 
     * get array elements match from both array
     * @param $iArray, $allowArray 
     * @return assoc array of fields 
     */ 
    public static function aIntersec($iArray, $allowArray = array()) {
        
        $return = $iArray; 
        // do not filter the array
        if (!empty($allowArray)) {
                $return = array_intersect_key($iArray, array_flip($allowArray)); 
        }   
        return $return; 
    }

     /* 
     * merge and intersec array with schema
     * @param table, $array  
     * @return array of assoc
     */ 
    public static function schemaBasedArray($tname, $iArray) {

        if (empty($tname) or empty($iArray)) return array();
        $fields = self::schema($tname);
        return self::aIntersec(array_merge($fields, $iArray), $fields);
    }  

     /* 
     * array to fields list for sql select
     * @param $array  
     * @return string title, maker
     */ 
    public static function a2SelStr($iArray) {

        return implode(", ", array_keys($iArray));
    }
    
     /* 
     * array to fields list for sql update
     * @param $array  
     * @return string 
     */ 
    public static function a2UptStr($iArray) {

        $str = "";
        while (list($key, $val) = each($iArray)) {
            if (empty($val) and (gettype($val)=="integer" or gettype($val)=="double")) {
                $val = "0"; 
            }
            // concat fields list for sql update
            $str .= $key . ' ="' . $val . '", ';
        }
        // return maker= 'Name', acct= '15',
        return substr($str, 0, strlen($str) - 2); // take out comma and space
    }

     /* 
     * array to sql insert statement
     * @param $array  
     * @return string (title, maker) VALUES ("Title","Maker")
     */ 
    public static function a2InsStr($iArray) {

        $value = '"' . implode('", "', array_values($iArray)) . '"'; // must use this in case quote in the name
        $name = implode(", ", array_keys($iArray));
        return "($name) VALUES ($value)";
    }    

} 
