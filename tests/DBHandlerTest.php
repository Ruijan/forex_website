<?php

use src\DBHandler;

require_once(str_replace("tests", "src", __DIR__."/").'DBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

class DBHandlerTest extends PHPUnit_Framework_TestCase
{

    private $dBHandler;


    protected function setUp()
    {
        parent::setUp();
        
        $this->mysqli = connect_database();
        $this->dBHandler = new DBHandler($this->mysqli, "");
    }


    protected function tearDown()
    {
        $this->dBHandler = null;
        
        parent::tearDown();
    }


    public function testCreateTable()
    {
        $this->dBHandler->createTable();
        assert($this->dBHandler->doesTableExists());
    }

    public function testDeleteTable()
    {
        $this->dBHandler->createTable();
        $this->dBHandler->deleteTable();
        assert(!$this->dBHandler->doesTableExists());
    }
    
    public function testTryEmptyNonExistingTableShouldThrowError(){
        $this->expectExceptionMessage('Table does not exists.');
        $this->dBHandler->emptyTable();
    }
}

