<?php
/*
// Must set three const or use dbConnect 
const DB_SQLITE = 'sqlite:db/mydb.sqlite';
const DB_MYSQL = 'mysql:host=localhost;port=3306;;dbname=bookshelf';
const DB_SQLSRV = 'sqlsrv:server=(local);Database=bookshelf';

defined('PDOLITE_DB_DSN') or define('PDOLITE_DB_DSN', $dsn);
defined('PDOLITE_DB_USER') or define('PDOLITE_DB_USER', $user);
defined('PDOLITE_DB_PASS') or define('PDOLITE_DB_PASS', $passwd);

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

    public static $cfg;
     
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
    public static function getLastId($table, $field) {

        $sql = 'SELECT max(' . $field . ') as lastid FROM ' . $table;
        list($lastid) = self::dbFetch(self::query($sql), "num"); // cause warning when use assoc array
        return (int) $lastid;
    }

    /* 
     * call query and dbFetch num to get lastID 
     * @param $table, $field 
     * @return $integer 
     */ 
    public static function getNextId($table, $field) {

        return self::getLastId($table, $field) +1;
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

    // database schema related method
    /* 
     * get one row from table
     * @param $table $filter
     * @return assoc array of fields  
     */ 
    public static function fieldsKey($table, $filter="id") {

        $row = (array) self::findRow("select * from ".$table,"assoc");
        // remove array element base on filter if _none_ no filter
        if (strtolower($filter)!="_none_") {
            // default is to filter id field
            unset($row[$filter]);
        }
        // [0] => id [1] => name 
        return array_keys($row);
    }

    /* 
     * flip fields key into array
     * @param $table $filter
     * @return assoc array of fields 
     */ 
    public static function schema($table, $filter="id") {
    
        // [0] => id [1] => name to [id] => 0 [name] => 1  
        return array_flip(self::fieldsKey($table, $filter));
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
        $fields = self::fieldsKey($tname);
        $carray = array_merge(array_flip($fields), $iArray);
        return self::aIntersec($carray, $fields);
    }  
    
     /* 
     * escape quote before insert to database
     * @param $array  
     * @return string with escape quote
     */ 
    public static function escapeQuote($iArray) {
        
        // clean \' into single quote before double it
        (!empty($iArray)) 
        ? $ret = str_replace("'", "''", str_replace("\'", "'", $iArray)) 
        : $ret = "";
        return $ret;
    }
    
    // array to query string with escape single quote
     /* 
     * array to fields list for sql select
     * @param $array  
     * @return string title, maker
     */ 
    public static function a2sSelect($iArray) {

        return implode(", ", array_keys($iArray));
    }
    
     /* 
     * array to fields list for sql update
     * @param $array  
     * @return string 
     */ 
    public static function a2sUpdate($iArray) {

        $str = "";
        while (list($key, $val) = each($iArray)) {
            if (empty($val) and (gettype($val)=="integer" or gettype($val)=="double")) {
                $val = "0"; 
            }
            // concat fields list for sql update
            // use single quote to work around sqlsrv error
            $str .= $key . " ='" . self::escapeQuote($val) . "', ";
        }
        // return maker= 'Name', acct= '15',
        return substr($str, 0, strlen($str) - 2); // take out comma and space
    }

     /* 
     * array to sql insert statement
     * @param $array  
     * @return string (title, maker) VALUES ("Title","Maker")
     */ 
    public static function a2sInsert($iArray) {

        // must use this in case quote in the name
        $value = "'" . implode("', '", array_values(self::escapeQuote($iArray))) . "'"; 
        $name = implode(", ", array_keys($iArray));
        return "($name) VALUES ($value)";
    }   

    // debug related code
     /* 
     * utilize debug default to br
     * @param $ivar $istr $iformat  
     * @return string 
     */ 
    public static function pln($iVar, $iStr = "", $iFormat = "br") {
    
        print self::debug($iVar, $iStr, $iFormat);
    }

     /* 
     * alias to debug 
     * @param $ivar $istr $iformat  
     * @return string 
     */ 
    public static function prt($iVar, $iStr = "", $iFormat = "") {

        print self::debug($iVar, $iStr, $iFormat);
    }

     /* 
     * print debug message
     * @param $ivar $istr $iformat  
     * @return string 
     */ 
    public static function debug($iVar, $iStr = "", $iFormat = "") {
        $preText = $dTrace = "";
        if (!empty($iStr) and strtolower($iStr) == "dtrace") {
            $dTrace = "dtrace";
        }
        if (!empty($iStr) and strtolower($iStr) <> "dtrace") {
            $preText = "[-" . strtoupper($iStr) . "-] ";
        }
        $fstr = "$preText%s";
        if (!empty($iVar)) {
            if (is_array($iVar) or ( is_object($iVar))) {
                $iVar = print_r($iVar, true);
            }
        } else {
            $iVar = ' Var is empty!';
        }
        switch (strtolower($iFormat)) {
            case "pre":
                $fstr = "<pre>$preText%s</pre>";
                break;
            case "p":
            case "br":
                $fstr = "<$iFormat />$preText%s";
                break;
            default:
                $fstr = " $fstr";
        }
        if (!empty($dTrace)) {
            $dTrace = self::backTrace();
        }
        $ret = sprintf($fstr, $iVar) . $dTrace;
        return $ret;
    }

    // Query builder lite
     /* 
     * build select statement (fieldlist must be already single quote safe)
     * @param table, $iFldList, $where  
     * @return string
     */ 
    public static function select($tname, $iFldList, $iWhere="") {

        if (empty($tname) or empty($iFldList)) return;
        (!empty($iWhere))
        ? $where = " WHERE " . $iWhere
        : $where = "";
        $return = "SELECT " . $iFldList
            . " FROM ". $tname 
            . $where . ";"
        ;
        self::pln($return);
        return $return;
    }

     /* 
     * build update statement (fieldlist must be already single quote safe)
     * @param table, $iFldList, $where  
     * @return string
     */ 
    public static function update($tname, $iFldList, $iWhere="") {
        
        if (empty($tname) or empty($iFldList)) return;
        (!empty($iWhere))
        ? $where = " WHERE " . $iWhere
        : $where = "";
        return "UPDATE $tname"
            . " SET " . $iFldList
            . $where . ";"
        ;
    }

     /* 
     * build delete statement
     * @param table, $where  
     * @return string
     */ 
    public static function delete($tname, $iWhere) {
        
        if (empty($tname) or empty($iWhere)) return;
        return "DELETE FROM $tname WHERE " . $iWhere . ";"
        ;
    }
    
     /* 
     * build insert statement (fieldlist must be already single quote safe)
     * @param table, $iFldList 
     * @return string
     */ 
    public static function insert($tname, $iFldList) {
        
        if (empty($tname) or empty($iFldList)) return;
        return "INSERT INTO $tname " . $iFldList . ";"
        ;
    }

} 
