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

Use Case: Use with Slim 3 modify 3 files: settings.php, dependencies.php and index.php
--------

You can download the sample application of Slim 3 using PdoLite here: https://github.com/chanhong/slimbookshelf4iis

In settings.php

        'pdolite' => [
            'dsn' => 'mysql:host=localhost;dbname=bookshelf;charset=utf8',
            'username' => 'user',
            'password' => 'password',
        ], 

In dependencies.php
        
use PdoLite\PdoLite;
// Database
$container['pdolite'] = function ($c) {
    PdoLite::$cfg = $settings = $c->get('settings')['pdolite'];    
    $conn = PdoLite::dbConnect($settings['dsn'],$settings['username'],$settings['password']);
    return $conn;        
};        

In your index.php

// Register the database connection with PdoLite
$pdolite = $app->getContainer()->get('pdolite');


In any of your php file that you want to use PdoLite 

        return PdoLite::select("authors");
        

Use Case: Use with your own code: 
--------
        
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

PHPUnit Usage
-------------

cd pdolite

phpunit test\PdoLiteTest.php 

