PdoLite
================

Very lite PDO database class with lite query builder

Installation
------------

$ ./composer.phar require chanhong/pdolite ~1.0-dev

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

PdoLite::exec("update test set title='Test' where id =1h"); 

foreach( PdoLite::query("SELECT * FROM test") as $row){ 
        
        print_r($row); 
} 

Please see testcase.php for more detail

// PHPUnit Usage

cd pdolite

phpunit test\PdoLiteTest.php 

