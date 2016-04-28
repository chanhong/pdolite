PdoLite
================

Very lite PDO database class

Usage
-----

//Sample dsn

$dsn = 'sqlite:/opt/databases/mydb.sq3';
$dsn = 'mysql:host=localhost;port=3306;;dbname=testdb;user=bruce;password=mypass';
$dsn = 'pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass';
$dsn = 'sqlsrv:server=dbhost;Database=testdb';
$dsn = 'odbc:Driver={SQL Server Native Client 10.0};Server=dbhost;Database=testdb';

// Sample code using New Ojbect call

$db = new PdoLite();
$conn = $db->dbConnect($dsn,$username,$password);
print_r($conn->dbFetchAssoc($sql)); 

// Sample code using static call

PdoLite::exec("update test set title='Test' where id =1h"); 
foreach( PdoLite::query("SELECT * FROM test") as $row){ 
        print_r($row); 
} 