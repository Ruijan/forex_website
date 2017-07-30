<?php
require_once '../TradeDBHandler.php';
require_once '../connect.php';
/**
 * TradeDBHandler test case.
 */
class TradeDBHandlerTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var TradeDBHandler
     */
    protected $tradeDBHandler;
    protected $mysqli;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated TradeDBHandlerTest::setUp()
        $this->mysqli = connect_database();
        $this->currency = "EUR_USD";
        $this->tradeDBHandler = new TradeDBHandler($this->mysqli, $this->currency);
        $this->tradeDBHandler->__construct($this->mysqli, $this->currency);
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades_".$this->currency);
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


    public function test__construct()
    {
        assert($this->tradeDBHandler->isInitialized());
        assert($this->tradeDBHandler->table_name == "trades_".$this->currency);
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
    
    public function test__tryAddEvent_shouldThrowError(){
        $this->deleteTableIfExists();
        $this->expectExceptionMessage('Table does not exists.');
        $this->tradeDBHandler->addTrade(null);
    }
    
    private function deleteTableIfExists()
    {
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades_".$this->currency);
        }
    }

    
    
    
}


