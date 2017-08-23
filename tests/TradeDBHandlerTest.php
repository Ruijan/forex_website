<?php
require_once(str_replace("tests", "src", __DIR__."/").'TradeDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');
/**
 * TradeDBHandler test case.
 */
class TradeDBHandlerTest extends PHPUnit_Framework_TestCase
{

    protected $tradeDBHandler;
    protected $mysqli;

    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated TradeDBHandlerTest::setUp()
        $this->mysqli = connect_database();
        $this->tradeDBHandler = new TradeDBHandler($this->mysqli);
        $this->deleteTableIfExists();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated TradeDBHandlerTest::tearDown()
        
        $this->deleteTableIfExists();
        $this->tradeDBHandler = null;
        $this->mysqli->close();
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function testConstructTableShouldNotExists(){
        assert($this->tradeDBHandler->doesTableExists() == False);
    }

    public function testConstruct()
    {
        assert($this->tradeDBHandler->isInitialized());
        assert($this->tradeDBHandler->getTableName() == "trades");
    }
    
    public function testCreateTable(){
        $this->tradeDBHandler->createTable();
        assert($this->tradeDBHandler->doesTableExists());
    }
    
    public function testDeleteTable(){
        $this->tradeDBHandler->createTable();
        $this->tradeDBHandler->deleteTable();
        assert(!$this->tradeDBHandler->doesTableExists());
    }
    
    public function testCheckSizeInEmptyDB_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->tradeDBHandler->getTableSize();
    }
    
    public function testTryAddEventInNonExistingTable_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->tradeDBHandler->addTrade(null);
    }
    
    private function deleteTableIfExists()
    {
        $this->tradeDBHandler->deleteTable();
    }  
}


