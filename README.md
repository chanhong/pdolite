PdoLite
================

Very lite PDO database class with lite query builder and SIDU (Select, Insert, Delete, Update).  

One class file with the size of 22 KB.

You can also use all the native PDO methods: http://php.net/manual/en/book.pdo.php

Installation
------------

$ ./composer.phar require chanhong/pdolite 1.0.x-dev

Usage
-----

//Sample dsn

Please see settings-dist.php 

// Sample code using New Ojbect call

<?php

include ('src\pdolite.php');

use PdoLite\PdoLite;

$db = new PdoLite();

$conn = $db->dbConnect($dsn,$username,$password);

print_r($conn->dbFetchAssoc($sql)); 

// Sample code using static call

PdoLite::pln(PdoLite::select("authors", ['type'=>'assoc']),"select");

$sqlUpdList = ['biography'=>'test'];
PdoLite::pln(PdoLite::update("authors", ['fl'=>$sqlUpdList, 'where'=>'id=1']),"update");

Please see testcase.php for more detail

// PHPUnit Usage

cd pdolite

phpunit test\PdoLiteTest.php 

