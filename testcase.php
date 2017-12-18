<?php
include ('src\pdolite.php');

use PdoLite\PdoLite;

if (file_exists("settings.php")) {
    include("settings.php");
} else {
    include("settings-dist.php");
}

PdoLite::$cfg = $cfg;

defined('PDOLITE_DB_DSN') or define('PDOLITE_DB_DSN', PdoLite::$cfg['dsn']);
defined('PDOLITE_DB_USER') or define('PDOLITE_DB_USER', PdoLite::$cfg['dbuser']);
defined('PDOLITE_DB_PASS') or define('PDOLITE_DB_PASS',PdoLite::$cfg['dbpass']);
defined('PDOLITE_DB_TYPE') or define('PDOLITE_DB_TYPE',PdoLite::$cfg['dbtype']);

$db = new PdoLite();
$db->dbConnect(PdoLite::$cfg['dsn'], PdoLite::$cfg['dbuser'], PdoLite::$cfg['dbpass']);

$iArray =[ 'idx'=>0, 'name' => 'some name', 'biography' => 'some bio'
 ,  'csrf_name' => 'csrf1280394586',  'csrf_value' => 'ccf28ee0ddd73'];

// SUDI: select, update, insert, delete test case
echo "<p />rows2array-bef";
$db->pln($db->select("authors"),"select-bef");
$sqlUpdList = ['biography'=>'Suzanne Marie Collins is an American television writer and novelist, best known as the author of The Underland Chronicles and The Hunger Games trilogy'];
$db->pln($db->update("authors", ['fl'=>$sqlUpdList, 'where'=>'id=1']),"update");
$fldList = ['name'=>"t'est",'biography'=>"t'est insert"];
$db->pln($db->insert("authors", ['fl'=>$fldList]),"insert");
$lastid=$db->getLastId("authors","id");
$db->pln($db->select("authors", ['where'=>"id=$lastid"]),"RS of last insert: $lastid");
$db->pln($db->delete("authors", ['where'=>"id=$lastid"]),"deleted $lastid");
$db->pln($db->select("authors"),"select-aft");
echo "<br />rows2array-after";

// various select test case
$db->pln($db->select("authors", ['fl'=>'name','type'=>'both','where'=>'id=1']),"both-name",'p');
$db->pln($db->select("authors", ['fl'=>'id, name,xid','where'=>'id=1']),"name,xid");
$db->pln($db->select("authors", ['fl'=>$iArray,'type'=>'num','where'=>'id=1']),"num");
$db->pln($db->select("books", ['where'=>'id<3']),"assoc");
$db->pln($db->select("books", ['where'=>'id<3','all'=>'all']),"all-assoc");

$db->pln($db->dbRow("authors", ['where'=>'id=1']), 'dbRow');
$db->pln($db->dbField("authors", "name", 'id=1'), 'dbFieldValue');

// schema test case
$fields = $db->fieldsKey("authors","_none");
$db->pln($fields,"schema",'p');
$one = $db->filterBySchema("authors", $iArray);
$db->pln($one,"schema filter");
$db->pln($db->getNextId("books","id"),"next books id");

// array to string
$db->pln($db->a2sSelect($one),"select");
$db->pln($db->a2sInsert($one),"insert");
$db->pln($db->a2sUpdate($one),"update");
$db->pln($db->a2sSelect($iArray),"select");

// old test case
$sql = $db->qbSelect("books", ['where'=>"id <3"]);
$db->pln($sql,"sql");
echo "<p />Samples use object _call dbFetch";
$db->pln($db->row2Array($sql),"row2array-num"); 
$db->pln($db->row2Array($sql,"assoc"),"row2array-assoc"); 
$db->pln($db->dbFetchAssoc($db->query($sql)),"dbFetchAssoc"); 
echo "<br />Samples use static __callStatic dbFetch: ";
$db->pln(PdoLite::dbFetchRow(PdoLite::query($sql)),"row"); 
$arr = PdoLite::dbFetchArray(PdoLite::query($sql)); 
$db->pln($arr,"both"); 
$db->pln(array_values($arr),"value"); 

$sql = $db->qbSelect("books", ['where'=>"id <3"]);
echo "<p />dbFetch obj: ";
$res = $db->query($sql);
while ($row = $db->dbFetch($res, "obj")) { 
    $db->prt($row->id,"id"); 
}   
echo "<br />dbFetch lazy: ";
$res = $db->query($sql);
while ($row = $db->dbFetch($res, "lazy")) { 
    $db->prt($row->id,"id"); 
}   

foreach ($db->query($sql) as $row) {
    $db->pln($row,"foreach obj"); 
    $db->prt($row['id'],"id"); 
}

$sql = "SELECT * FROM books where id =1"; 
$db->pln($db->findRow($sql, "lazy"),"findRow lazy"); 
$db->pln($db->findRow($sql),"findRow obj"); 
$sql = $db->dbtypeSqlSelectRange(['dbtype'=>PdoLite::$cfg['dbtype'],
'tbl'=>'books','where'=>"id >2 order by id", 'limit'=>3]);  
$db->pln(PdoLite::rows2Array($sql),"select range"); 
$db->pln(PdoLite::$cfg['dbtype']);

