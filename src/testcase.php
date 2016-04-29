<?php
include ('pdolite.php');

use PdoLite\PdoLite;

const DB_SQLITE = 'sqlite:../db/mydb.sqlite';
const DB_MYSQL = 'mysql:host=localhost;port=3306;;dbname=bookshelf';

$dsn = DB_SQLITE;
$user = 'user';
$passwd = 'password';

defined('PDOLITE_DB_DSN') or define('PDOLITE_DB_DSN', $dsn);
defined('PDOLITE_DB_USER') or define('PDOLITE_DB_USER', $user);
defined('PDOLITE_DB_PASS') or define('PDOLITE_DB_PASS', $passwd);

$db = new PdoLite();
$db->dbConnect($dsn, $user, $passwd);

//echo "<pre>";
// base test case
echo "next id: ";
print_r($db->getNextId("books","id")); 
echo "<br />update: ";
$results = PdoLite::exec("update books set title='Gregor the Overlander' where id =1");
print_r($results); 

// current test case
$iArray =[ 'x'=>null, 'name' => 'some name', 'biography' => 'some bio'
 ,  'csrf_name' => 'csrf1280394586',  'csrf_value' => 'ccf28ee0ddd73'];
$fieldsList = PdoLite::a2InsStr($iArray);
echo "<br />no filter ins: ";
print_r($fieldsList);
$fieldsList = PdoLite::a2UptStr($iArray);
echo "<br />no filter upd: ";
print_r($fieldsList);

$one = PdoLite::schemaBasedArray("authors", $iArray);
$fields = PdoLite::schema("authors","_none");
echo "<br />schema: ";
print_r($fields);
echo "<br />schema based: ";
print_r($one);

$fieldsList = PdoLite::a2SelStr($one);
echo "<br />sel: ";
print_r($fieldsList);
$fieldsList = PdoLite::a2InsStr($one);
echo "<br />ins: ";
print_r($fieldsList);
$fieldsList = PdoLite::a2UptStr($one);
echo "<br />upd: ";
print_r($fieldsList);


// old test case
$sql = "SELECT * FROM books where id <3"; 
echo "<p />_call object results: ";
$results = $db->query($sql); 
print_r($results); 
echo "<p />_call object: ";
foreach ($results as $row) {
    print_r($row); 
}   
echo "<p />__callStatic object: ";
$results = PdoLite::query($sql); 
foreach ($results as $row) {
    print_r($row); 
}   

$sql = "SELECT * FROM books where id < 3"; 
echo "<p />rows2arrayAll to assoc: ";
print_r(PdoLite::rows2arrayAll($sql, "assoc")); 
echo "<p />rows2array to assoc: ";
print_r(PdoLite::rows2array($sql, "assoc")); 
echo "<p />rows2array to num : ";
print_r(PdoLite::rows2array($sql, "num")); 
echo "<p />rows2Array both: ";
$arr = $db->rows2Array($sql, "both");
print_r($arr);   

echo "<p />dbFetch obj: ";
$res = PdoLite::query($sql);
while ($row = PdoLite::dbFetch($res, "obj")) { 
    echo "<br />id:";
    print_r($row->id); 
  
}   
echo "<br />dbFetch lazy: ";
$res = PdoLite::query($sql);
while ($row = PdoLite::dbFetch($res, "lazy")) { 
    echo "<br />id:";
    print_r($row->id); 
}   

echo "<br />foreach lazy: ";
foreach (PdoLite::query($sql) as $row) {
    print_r($row);
    echo "<br />id:";
    print_r($row['id']);   
}
  

$sql = "SELECT * FROM books where id =1"; 
echo "<p />findRow lazy: ";
$row = PdoLite::findRow($sql, "lazy"); 
print_r($row); 
echo "<br />findRow obj: ";
$row = PdoLite::findRow($sql, "obj"); 
print_r($row); 

echo "<br />one row2array assoc: ";
$arr = PdoLite::row2Array($sql); 
print_r($arr);
$arr = $db->dbFetchAssoc(PdoLite::query($sql));
echo "<br />dbFetch assoc: ";
print_r($arr);    
echo "<br />dbFetch Row: ";
print_r(PdoLite::dbFetchRow(PdoLite::query($sql))); 
echo "<br />dbFetch both: ";
$arr = PdoLite::dbFetchArray(PdoLite::query($sql)); 
print_r($arr);
echo "<br />dbFetch Array both value: ";
print_r(array_values($arr));


//echo "</pre>";
