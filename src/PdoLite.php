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

    // various methods ultilized dbFetch
    /* 
     * call query and dbFetch num to get lastID 
     * @param $table, $field 
     * @return $integer 
     */ 
    public static function getLastId($table, $field) {

        try {
            $sql = 'SELECT max(' . $field . ') as lastid FROM ' . $table;
            list($lastid) = self::dbFetch(self::query($sql), "num"); // cause warning when use assoc array
            return (int) $lastid;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
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
     * call query and dbFetch array type to get one row 
     * @param $sql  
     * @return array of one row 
     */ 
    public static function row2Array($sql, $atype = "num") {

        return self::dbFetch(self::query($sql), $atype);
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
    public static function dbFetch($qhandle, $atype = "assoc") {

        try {
            if (is_object($qhandle)) {
                $type = self::getPDOFetchType($atype);
                return $qhandle->fetch($type);
            }
        } catch (PDOException $e) {
            die(self::dbError(__METHOD__));
        }
    }

    // alias method
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
    public static function dbQ2Array($sql, $atype = "assoc", $fetch = "fetch") {

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
    public static function dbQ2ArrayAll($sql, $atype = "assoc") {

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
        
        try {
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
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
    }

    /* 
     * alias to dbQ2Array
     * @param $sql $type 
     * @return nested array of rows 
     */ 
    public static function rows2Array($sql, $atype = "num") {
        
        return self::dbQ2Array($sql, $atype, "fetch");
    }

    /* 
     * alias to dbQ2ArrayAll
     * @param $sql $type 
     * @return nested array of rows 
     */ 
    public static function rows2ArrayAll($sql, $atype = "num") {

        return self::dbQ2Array($sql, $atype, "all");
    }

    // database schema related method
    /* 
     * get one row from table
     * @param $table $filter
     * @return assoc array of fields  
     */ 
    public static function fieldsKey($table, $filter="id") {

        try {
            $row = (array) self::findRow("select * from ".$table, "assoc");
            // remove array element base on filter if _none_ no filter
            if (strtolower($filter)!="_none_") {
                // default is to filter id field
                unset($row[$filter]);
            }
            return array_keys($row);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
    }

    /* 
     * flip fields key into array
     * @param $table $filter
     * @return assoc array of fields Array ( [name] => 0 [biography] => 1 ) 
     */ 
    public static function schema($table, $filter="id") {

        try {    
            return array_flip(self::fieldsKey($table, $filter));
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
    }

    /* 
     * get array elements match from both array
     * @param $iArray, $allowArray 
     * @return assoc array of fields 
     */ 
    public static function aIntersec($iArray, $allowArray = array()) {

        try {        
            $return = $iArray; 
            // if empty do not filter the array
            if (!empty($allowArray)) {
                $return = array_intersect_key($iArray, array_flip($allowArray)); 
            }   
            return $return; 
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
        
    }

     /* filterBySchema might be better, to decide delete this later (TODO)
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
     * filter out fields not match schema fields
     * @param table, $fldArray  
     * @return filtered field Array ( [name] => some name [biography] => some bio ) 
     */ 
    public static function filterBySchema($tname, $iArray) {

        try {    
            $fields = self::schema($tname,"_none_");
            // diff in array with schma fields
            $diffs = array_diff_key($iArray, $fields);
            // remove unwanted fields
            foreach ($diffs as $k=>$v) {
                unset($iArray[$k]);
            }
            return $iArray;    
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
                    
    }

     /* 
     * get value from array
     * @param array  
     * @return string
     */ 
    public static function getKeyVal($iValue, $key, $default="") {

        if (!empty($iValue[$key])) {
            return $iValue[$key];
        } elseif (!empty($default)) {
            return $default;
        }
        else {
            return;
        }
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
     * @param $array $checkNumArray 
     * @return string 
     */ 
    public static function a2sUpdate($iArray, $defaultArray = array()) {

        $str = "";
//        self::pln($iArray,"ns");
        while (list($key, $val) = each($iArray)) {
//            self::pln(gettype($val),"t=$key");
            // override value from $defaultArray value of the same key
            if (empty($val) and isset($defaultArray[$key])) {
                // set to value of $defaultArray when value is empty
               $val = $defaultArray[$key]; 
            // catch the case of integer val=0
            } elseif (isset($val) and empty($val) and gettype($val)<>"string") {
               $val = $val;
            } else {
               $val = self::escapeQuote($val);
            } 
            // concat fields list for sql update
            // use single quote to work around sqlsrv error
            $str .= $key . " ='" . $val . "', ";
//            self::pln($str,"vs");
        }
        // return maker= 'Name', acct= '15',
        return substr($str, 0, strlen($str) - 2); // take out comma and space
    }

     /* 
     * array to sql insert statement
     * @param $array  
     * @return string (title, maker) VALUES ("Title","Maker")
     */ 
    public static function a2sInsert($iArray, $defaultArray = array()) {
        $nameStr = $valStr = "";
//        self::pln($iArray,"ns");
        while (list($key, $val) = each($iArray)) {
//            self::pln(gettype($val),"t=$key");
            // use single quote to work around sqlsrv error
            // override value from $defaultArray value of the same key
            if (empty($val) and isset($defaultArray[$key])) {
               $valStr .= "'" .  $defaultArray[$key] . "', ";
            // catch the case of integer val=0
            } elseif (isset($val) and empty($val) and gettype($val)<>"string") {
               $valStr .= "'$val', ";
            } elseif (!empty($val)) {
               $valStr .= "'" .  self::escapeQuote($val).  "', ";
            // catch the case of null in date field
            } else {
               $valStr .= "null, ";
            }
        }
        $nameStr = implode(", ", array_keys($iArray));       
//        self::pln($nameStr,"ns");
//        self::pln($valStr,"vs");
        // take out comma and space
        $valStr = substr($valStr, 0, strlen($valStr) - 2); 
        return "($nameStr) VALUES ($valStr)";
    }   

    // SIDU Query builder lite
     /* 
     * build select statement (fieldlist must be already single quote safe)
     * @param table, $iFldList, $where  
     * @return string
     */ 
    public static function qbSelect($tname, $options=array()) {

        if (empty($tname)) return;
        
        $one = self::getSUDIOptions("select", $tname, $options);        
        
        (!empty($one['where']))
            ? $where = " WHERE " . $one['where']
            : $where = "";
            
        (empty($one['fl'])) 
            ? $iFldList = "*"
            : $iFldList = $one['fl']
        ;
        
        return "SELECT " . $iFldList
            . " FROM ". $tname 
            . $where . ";"
        ;
    }

     /* 
     * build insert statement (fieldlist must be already single quote safe)
     * @param table, $iFldList 
     * @return string
     */ 
    public static function qbInsert($tname, $options) {
        
        if (empty($tname) or empty($options['fl'])) return;
        $one = self::getSUDIOptions("insert", $tname, $options);        
        return "INSERT INTO $tname " . $one['fl'] . ";"
        ;
    }

     /* 
     * build delete statement
     * @param table, $where  
     * @return string
     */ 
    public static function qbDelete($tname, $options) {
        
        if (empty($tname) or empty($options['where'])) return;
        return "DELETE FROM $tname WHERE " . $options['where'] . ";"
        ;
    }
    
     /* 
     * build update statement (fieldlist must be already single quote safe)
     * @param table, $iFldList, $where  
     * @return string
     */ 
    public static function qbUpdate($tname, $options) {
        
        if (empty($tname) or empty($options['fl'])) return;
        
        $one = self::getSUDIOptions("update", $tname, $options);        

        (!empty($one['where']))
            ? $where = " WHERE " . $one['where']
            : $where = "";
            
        return "UPDATE $tname"
            . " SET " . $one['fl']
            . $where . ";"
        ;
    }
    
    // SIDU lite
     /* 
     * get SUDI options
     * @param table, $iFldList, $where  
     * @return string
     */ 
    public static function getSUDIOptions($opr, $tname, $options=array()) {
    
        try {
            $fldList = "";
            
            $iFldList = self::getKeyVal($options, 'fl');
            $where = self::getKeyVal($options, 'where');
            $otype = self::getKeyVal($options, 'type');
            $defaultArray = self::getKeyVal($options, 'default');
            
            switch (strtolower($opr)) {
                case "update":
                    if (!empty($iFldList) and is_array($iFldList)) {
                        $fldList = self::a2sUpdate(self::filterBySchema($tname, $iFldList), $defaultArray);
                    }
                    break;
                case "insert":
                    if (!empty($iFldList) and is_string($iFldList)) {
                        // fields list then flip the array to key
                        $iFldList = array_flip(explode(",", str_replace(' ','',$iFldList)));
                    } 
                    if (!empty($iFldList) and is_array($iFldList)) {
                        $fldList = self::a2sInsert(self::filterBySchema($tname, $iFldList), $defaultArray);
                    }
                    break;
                case "select":
                defailt :
                    if (!empty($iFldList) and is_string($iFldList)) {
                        // fields list then flip the array to key
                        $iFldList = array_flip(explode(",", str_replace(' ','',$iFldList)));
                    } 
                    
                    if (!empty($iFldList) and is_array($iFldList)) {
                        $fldList = self::a2sSelect(self::filterBySchema($tname, $iFldList));
                    }
            }
            return ['fl'=>$fldList, 'where'=>$where, 'type'=>$otype];
        } catch (PDOException $e) {
            die(self::dbError($tname));
        }
    }

     /* 
     * select record set
     * @param table, $options  
     * @return record set of array ['type']
     */ 
    public static function select($tname, $options=array()) {

        try {      
            $otype = self::getKeyVal($options, 'type', 'assoc');
            $all = self::getKeyVal($options, 'all');
            $sql = self::qbSelect($tname, $options);
            if (strtolower($all)=="all") {
                $return = self::rows2arrayAll($sql, $otype); 
            } else {
                $return = self::rows2Array($sql, $otype);
            }
            return  $return; 
        } catch (PDOException $e) {
            die(self::dbError($tname));
        }
    }

     /* 
     * insert record 
     * @param table, $options  
     * @return status
     */ 
    public static function insert($tname, $options=array()) {

        try {        
            return PdoLite::exec(self::qbInsert($tname, $options));
        } catch (PDOException $e) {
            die(self::dbError($tname));
        }
    }

     /* 
     * delete record 
     * @param table, $options  
     * @return status
     */ 
    public static function delete($tname, $options=array()) {
        
        try {        
            return PdoLite::exec(self::qbDelete($tname, $options));
        } catch (PDOException $e) {
            die(self::dbError($tname));
        }
    }

     /* 
     * update record 
     * @param table, $options  
     * @return status
     */ 
    public static function update($tname, $options=array()) {
        
        try {        
            return PdoLite::exec(self::qbUpdate($tname, $options));
        } catch (PDOException $e) {
            die(self::dbError($tname));
        }
    }

    // misc methods utilize select
    /* 
     * call dbRow to get value of table field 
     * @param $table, $field, $where 
     * @return field value 
     */ 
    public static function dbField($table, $field, $where) {

        $fldValue = "";
        $row = self::dbRow($table, ['type'=>'num', 'fl'=>$field, 'where'=>$where]);
        if (!empty($row)) {
            list($fldValue) = $row;
        }
        return $fldValue;
    }

    /* 
     * call select to get one row 
     * @param $table, $field, $where 
     * @return field value 
     */ 
    public static function dbRow($tname, $options=array()) {

        try {
            $row = array();
            $defArray = ['type'=>'assoc', 'all'=>'all'];
            // options array will override the default array
            $coptions = array_merge($defArray, $options);
            $rows = self::select($tname, $coptions);
            if (!empty($rows)) {
                $row = array_shift($rows); // get top one array
            }
            return $row;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }        
    }

    /* 
     * get query string for string to date based on dbtype such as 'pdo-mysql' or 'pdo-sqlsrv'
     * @param array('dbtype'=>'pdo-mysql','fieldname'=>'first', 'format'=>'%d/%d/%y')
     * @return string 
     */ 
    public function dbtypeSqlStr2Date($one) {
            
        if (empty($one['fieldname'])) return;
        
        if (empty($one['format'])) {
            $one['format'] = "%m/%d/%y";
        } 
        
        switch (strtolower($one['dbtype'])) {
            case "pdo-sqlsrv":
                $ret = "CONVERT(DATETIME, ".$one['fieldname'].")";
                break;
            default:
            case "pdo-mysql":
                $ret = "STR_TO_DATE(".$one['fieldname'].", '".$one['format']."')";
                break;
        }
            return $ret;
    }
            
    /* 
     * get query string for select date range based on dbtype such as 'pdo-mysql' or 'pdo-sqlsrv' MUST have order by in WHERE to work
     * @param array('dbtype'=>'pdo-mysql','tbl'=>'tname', ,'fl'=>'*', 'where'=>'id=1 order by id', 'start'=>1, 'limit'=>10)
     * @return string 
     */ 
    public function dbtypeSqlSelectRange($one) {
            
        if (empty($one['tbl']) or empty($one['where'])) return;

        if (empty($one['fl'])) {
            $one['fl'] = "*";
        } 
        
        if (empty($one['start'])) {
            $one['start'] = 0;
        } 

        if (empty($one['limit'])) {
            $one['limit'] = 1;
        }
        
        switch (strtolower($one['dbtype'])) {
            case "pdo-sqlsrv":
                // require > SQL 2012 
                $ret = "SELECT ".$one['fl']
                    . " FROM ".$one['tbl']
                    . " WHERE ".$one['where']
                    . " OFFSET ".$one['start']." ROWS "
                    . " FETCH NEXT ".$one['limit']." ROWS ONLY"
                    ;
                break;
            default:
            case "pdo-mysql":
                $ret = "SELECT ".$one['fl'].", @rownum:=@rownum+1 RowNumber "
                    . " FROM ".$one['tbl'].", (SELECT @rownum:=0) r "
                    . " WHERE ".$one['where']." LIMIT ".$one['limit']." OFFSET ".$one['start'].";"
                    ;
                break;
        }
        return $ret;
    }
} 
