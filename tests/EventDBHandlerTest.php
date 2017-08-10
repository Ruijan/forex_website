<?php

require_once '../src/EventDBHandler.php';
require_once '../src/connect.php';

/**
 * EventDBHandler test case.
 */
class EventDBHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $eventDBHandler;

    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated EventDBHandlerTest::setUp()
        $this->mysqli = connect_database();
        $this->eventDBHandler = new EventDBHandler($this->mysqli);
        $this->deleteTableIfExists();
    }

    protected function tearDown()
    {
        $this->deleteTableIfExists();
        $this->eventDBHandler = null;
        parent::tearDown();
    }
    
    private function deleteTableIfExists()
    {
        if($this->eventDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE events");
        }
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
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

