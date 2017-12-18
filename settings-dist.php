<?php

$param = array(
);

$mysql = array(
    'dbtype' => "pdo-mysql",
    'dsn' => 'mysql:host=localhost;port=3306;dbname=bookshelf',
    'dbuser' => 'user',
    'dbpass' => 'password',
);

$sqlsrv = array(
    'dbtype' => "pdo-sqlsrv",
    'dsn' => 'sqlsrv:server=(local);Database=bookshelf',
    'dbuser' => 'user',
    'dbpass' => 'password',
);

$pgsql = array(
    'dbtype' => "pgsql",
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=bookshelf',
    'dbuser' => "user",
    'dbpass' => "password",
);

$sqlite = array(
    'dbtype' => "sqlite",
    'dsn' => 'sqlite:db/bookshelf.sqlite',
    'dbuser' => "",
    'dbpass' => "",
);

$db = $sqlsrv;
$cfg = array_merge($db, $param);