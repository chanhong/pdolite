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

$db = new PdoLite();
$db->dbConnect(PdoLite::$cfg['dsn'], PdoLite::$cfg['dbuser'], PdoLite::$cfg['dbpass']);

$iArray =[ 'idx'=>0, 'name' => 'some name', 'biography' => 'some bio'
 ,  'csrf_name' => 'csrf1280394586',  'csrf_value' => 'ccf28ee0ddd73'];

// SUDI: select, update, insert, delete test case
echo "<p />rows2array-bef";
PdoLite::pln(PdoLite::select("authors", ['type'=>'assoc']),"assoc-all");
$sqlUpdList = ['biography'=>'Suzanne Marie Collins is an American television writer and novelist, best known as the author of The Underland Chronicles and The Hunger Games trilogy'];
PdoLite::pln(PdoLite::update("authors", ['fl'=>$sqlUpdList, 'where'=>'id=1']),"update");
$fldList = ['name'=>"t'est",'biography'=>"t'est insert"];
PdoLite::pln(PdoLite::insert("authors", ['fl'=>$fldList]),"status");
$lastid=$db->getLastId("authors","id");
PdoLite::pln(PdoLite::select("authors", ['where'=>"id=$lastid"]),"RS of last insert: $lastid");
PdoLite::pln(PdoLite::delete("authors", ['where'=>"id=$lastid"]),"deleted $lastid");
PdoLite::pln(PdoLite::select("authors", ['type'=>'assoc']),"assoc");
echo "<br />rows2array-after";

// various select test case
PdoLite::pln(PdoLite::select("authors", ['fl'=>'name','type'=>'both','where'=>'id=1']),"both-name",'p');
PdoLite::pln(PdoLite::select("authors", ['fl'=>'id, name,xid','type'=>'assoc','where'=>'id=1']),"assoc-name,xid");
PdoLite::pln(PdoLite::select("authors", ['fl'=>$iArray,'type'=>'num','where'=>'id=1']),"num");
PdoLite::pln(PdoLite::select("books", ['type'=>'assoc','where'=>'id<3']),"assoc");
PdoLite::pln(PdoLite::select("books", ['type'=>'assoc','where'=>'id<3','all'=>'all']),"all");

// schema test case
$fields = PdoLite::fieldsKey("authors","_none");
PdoLite::pln($fields,"schema");
$one = PdoLite::filterBySchema("authors", $iArray);
PdoLite::pln($one,"schema filter");
PdoLite::pln($db->getNextId("books","id"),"next books id");

// array to string
PdoLite::pln(PdoLite::a2sSelect($one),"select");
PdoLite::pln(PdoLite::a2sInsert($one),"insert");
PdoLite::pln(PdoLite::a2sUpdate($one),"update");
PdoLite::pln(PdoLite::a2sSelect($iArray),"select");

// old test case
$sql = PdoLite::qbSelect("books", ['where'=>"id <3"]);
PdoLite::pln($sql,"sql");
echo "<p />_call dbFetch";
PdoLite::pln(PdoLite::row2Array($sql),"row2array"); 
PdoLite::pln($db->dbFetchAssoc($db->query($sql)),"dbFetchAssoc"); 
echo "<br />__callStatic dbFetch: ";
PdoLite::pln(PdoLite::dbFetchRow(PdoLite::query($sql)),"row"); 
$arr = PdoLite::dbFetchArray(PdoLite::query($sql)); 
PdoLite::pln($arr,"both"); 
PdoLite::pln(array_values($arr),"value"); 

$sql = PdoLite::qbSelect("books", ['where'=>"id <3"]);

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
