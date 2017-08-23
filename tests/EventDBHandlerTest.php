<?php

require_once(str_replace("tests", "src", __DIR__."/").'EventDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

/**
 * EventDBHandler test case.
 */
class EventDBHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $eventDBHandler;

    protected function setUp()
    {
        parent::setUp();
        
        $this->mysqli = connect_database();
        $this->eventDBHandler = new EventDBHandler($this->mysqli);
        $this->deleteTableIfExists();
    }

    protected function tearDown()
    {
        $this->deleteTableIfExists();
        $this->eventDBHandler = null;
        $this->mysqli->close();
        parent::tearDown();
    }
    
    private function deleteTableIfExists()
    {
        $this->eventDBHandler->deleteTable();
    }

    public function __construct()
    {
    }
    
    public function test__constructTableShouldNotExists(){
        assert($this->eventDBHandler->doesTableExists() == False);
    }

    public function test__createTable(){
        $this->eventDBHandler->createTable();
        assert($this->eventDBHandler->doesTableExists());
    }
    
    public function test__deleteTable(){
        $this->eventDBHandler->createTable();
        $this->eventDBHandler->deleteTable();
        assert(!$this->eventDBHandler->doesTableExists());
    }
    
    public function test__checkSizeInEmptyDB_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->eventDBHandler->getTableSize();
    }
    
    public function test__tryAddEvent_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->eventDBHandler->addEvent(null);
    }
}

