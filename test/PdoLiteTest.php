<?php
/*
 * This file is part of the PdoLite package.
 *
 * (c) Chanh Ong <chanh.ong@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * cd pdolite
 * phpunit test\PdoLiteTest.php 
 */

namespace PdoLite;
const DB_SQLITE = 'sqlite:db/mydb.sqlite';

class PdoLiteTest extends \PHPUnit_Framework_TestCase {


    /**
     * @covers            dbConnect
     * @uses              connect
     * @expectedException 
     */
    public function testdbConnect() {
        $dsn = DB_SQLITE;
        $user = 'user';
        $passwd = 'user';        
        PdoLite::prt($dsn, "dsn", "br");
        $db = PdoLite::dbConnect($dsn, $user, $passwd);
        PdoLite::prt($db, "db", "br");
        return $db;
    }

    /**
     * @covers            row2Array
     * @uses              select
     * @expectedException 
     */
    public function testrow2Array() {
        PdoLite::row2Array("select * from authors", "assoc"); 
    }

    /**
     * @covers            Prt
     * @uses              print
     * @expectedException 
     */
    public function testPrt() {
        PdoLite::prt("test", "print", "br");
    }

}