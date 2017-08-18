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
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades");
        }
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

    public function test__constructTableShouldNotExists(){
        assert($this->tradeDBHandler->doesTableExists() == False);
    }

    public function test__construct()
    {
        assert($this->tradeDBHandler->isInitialized());
        assert($this->tradeDBHandler->getTableName() == "trades");
    }
    
    public function test__createTable(){
        $this->tradeDBHandler->createTable();
        assert($this->tradeDBHandler->doesTableExists());
    }
    
    public function test__deleteTable(){
        $this->tradeDBHandler->createTable();
        $this->tradeDBHandler->deleteTable();
        assert(!$this->tradeDBHandler->doesTableExists());
    }
    
    public function test__checkSizeInEmptyDB_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->tradeDBHandler->getTableSize();
    }
    
    public function test__tryAddEventInNonExistingTable_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->tradeDBHandler->addTrade(null);
    }
    
    private function deleteTableIfExists()
    {
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades");
        }
    }  
}


