<?php
require_once 'TradeDBHandlerTest.php';
require_once '../Trade.php';

class TradeDBHandlerCreationTest extends TradeDBHandlerTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->tradeDBHandler->createTable();
        
    }
    
    protected function tearDown()
    {
        // TODO Auto-generated TradeDBHandlerTest::tearDown()
        
        $this->tradeDBHandler->deleteTable();
        parent::tearDown();
    }
    
    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function test_isTableEmpty()
    {
        assert($this->tradeDBHandler->getTableSize() == 0);
    }
    
    public function test_addingTrade_expectIncrementInSize(){
        $id = $this->tradeDBHandler->addTrade(Trade::createWithIdAndEventID(1, 999));
        assert($this->tradeDBHandler->getTableSize() == 1);
        assert($id != 2);
        assert($id == 1);
    }
}

?>