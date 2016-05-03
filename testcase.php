<?php
include ('src\pdolite.php');

use PdoLite\PdoLite;

const DB_SQLITE = 'sqlite:db/mydb.sqlite';
const DB_MYSQL = 'mysql:host=localhost;port=3306;;dbname=bookshelf';
const DB_SQLSRV = 'sqlsrv:server=uwmca-db1;Database=bookshelf';

$dsn = DB_SQLSRV;

$user = 'user';
$passwd = 'user';

defined('PDOLITE_DB_DSN') or define('PDOLITE_DB_DSN', $dsn);
defined('PDOLITE_DB_USER') or define('PDOLITE_DB_USER', $user);
defined('PDOLITE_DB_PASS') or define('PDOLITE_DB_PASS', $passwd);

$db = new PdoLite();
$db->dbConnect($dsn, $user, $passwd);

//echo "<pre>";
// base test case
echo "<p />rows2array-bef";
$sql = 'insert into authors ' . PdoLite::a2sInsert(['name'=>'test','biography'=>'test insert']);
$results = PdoLite::exec($sql);
$lastid=$db->getLastId("authors","id");
PdoLite::pln($results,"last id: $lastid status");
PdoLite::pln(PdoLite::row2Array("select * from authors where id=$lastid", "assoc"),"added to assoc"); 
$results = PdoLite::exec("delete authors where id=".$lastid);
PdoLite::pln($results,"delete lastid: $lastid status ");

$fArray = PdoLite::schema("authors", "_none_");
PdoLite::pln($fArray,"authors");
$sql = "select ".PdoLite::a2sSelect($fArray)." from authors";
PdoLite::pln($sql,"sql");
PdoLite::pln(PdoLite::rows2array($sql, "assoc"),"assoc-aft");

PdoLite::pln($db->getNextId("books","id"),"next book id");
$sql = "update books set title='Gregor the Overlander' where id =1"; 
PdoLite::pln($sql,"sql");
$results = PdoLite::exec($sql);
PdoLite::pln($results,"update");

// current test case
$iArray =[ 'x'=>null, 'name' => 'some name', 'biography' => 'some bio'
 ,  'csrf_name' => 'csrf1280394586',  'csrf_value' => 'ccf28ee0ddd73'];
$fields = PdoLite::fieldsKey("authors","_none");
PdoLite::pln($fields,"schema");
$one = PdoLite::schemaBasedArray("authors", $iArray);
PdoLite::pln($one,"schema based");
PdoLite::pln(PdoLite::a2sSelect($one),"select");
PdoLite::pln(PdoLite::a2sInsert($one),"insert");
PdoLite::pln(PdoLite::a2sUpdate($one),"update");
PdoLite::pln(PdoLite::a2sSelect($iArray),"select");

// old test case
$sql = "SELECT * FROM books where id <3"; 
echo "<p />_call dbFetch";
PdoLite::pln(PdoLite::row2Array($sql),"row2array"); 
PdoLite::pln($db->dbFetchAssoc($db->query($sql)),"dbFetchAssoc"); 
echo "<br />__callStatic dbFetch: ";
PdoLite::pln(PdoLite::dbFetchRow(PdoLite::query($sql)),"row"); 
$arr = PdoLite::dbFetchArray(PdoLite::query($sql)); 
PdoLite::pln($arr,"both"); 
PdoLite::pln(array_values($arr),"value"); 

$sql = "SELECT * FROM books where id < 3"; 
echo "<p />rows2arrayAll: ";
PdoLite::pln($db->rows2arrayAll($sql, "assoc"),"assoc"); 
echo "<br />rows2array: ";
PdoLite::pln($db->rows2Array($sql, "assoc"),"assoc"); 
PdoLite::pln($db->rows2Array($sql, "num"),"num"); 
PdoLite::pln($db->rows2Array($sql, "both"),"both"); 

echo "<p />dbFetch obj: ";
$res = PdoLite::query($sql);
while ($row = PdoLite::dbFetch($res, "obj")) { 
    PdoLite::prt($row->id,"id"); 
}   
echo "<br />dbFetch lazy: ";
$res = PdoLite::query($sql);
while ($row = PdoLite::dbFetch($res, "lazy")) { 
    PdoLite::prt($row->id,"id"); 
}   

foreach (PdoLite::query($sql) as $row) {
    PdoLite::pln($row,"foreach obj"); 
    PdoLite::prt($row['id'],"id"); 
}

$sql = "SELECT * FROM books where id =1"; 
PdoLite::pln(PdoLite::findRow($sql, "lazy"),"findRow lazy"); 
PdoLite::pln(PdoLite::findRow($sql, "obj"),"findRow obj"); 



//echo "</pre>";
