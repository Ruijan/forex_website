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
    
    public function test_removingTrade_expectDecrementationInSize(){
        $id = $this->tradeDBHandler->addTrade(Trade::createWithIdAndEventID(1, 999));
        $this->tradeDBHandler->removeTradeById($id);
        assert($this->tradeDBHandler->getTableSize() == 0);
    }
    
    public function test_openTrade_shouldUpdateOpenTime(){
        $trade = $this->openTrade();
        $this->tradeDBHandler->openTrade($trade);
        $db_trade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfOpenDBTradeEqualTrade($trade, $db_trade);
    }
    
    private function checkIfOpenDBTradeEqualTrade($trade, $db_trade)
    {
        assert($this->tradeDBHandler->getTradeByID($trade->getId())->getOpenTime()->format('Y-m-d H:i:s') == 
            $trade->getOpenTime()->format('Y-m-d H:i:s'), 
            "Expect equal closing time. Got:".$trade->getOpenTime()->format('Y-m-d H:i:s').
            " and ".$db_trade->getOpenTime()->format('Y-m-d H:i:s'));
        assert($db_trade->getState() == $trade->getState(), "Expect equal state of 2");
    }

    private function openTrade()
    {
        $trade = new Trade(60);
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        $trade->open(new DateTime('NOW'));
        return $trade;
    }

    public function test_closeTrade_shouldUpdateCloseTimeGainCommissionState(){
        $trade = $this->openCloseTrade();
        $this->tradeDBHandler->openTrade($trade);
        $this->tradeDBHandler->closeTrade($trade);
        $db_trade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfClosedDBtradeEqualTrade($trade, $db_trade);
    }
    
    private function checkIfClosedDBtradeEqualTrade($trade, $db_trade)
    {
        assert($db_trade->getCloseTime()->format('Y-m-d H:i:s') == $trade->getCloseTime()->format('Y-m-d H:i:s'), 
            "Expect equal closing time. Got:".$trade->getCloseTime()->format('Y-m-d H:i:s').
            " and ".$db_trade->getCloseTime()->format('Y-m-d H:i:s'));
        assert($db_trade->getGain() == $trade->getGain(), "Expect equal gain in DB");
        assert($db_trade->getCommission() == $trade->getCommission(), "Expect equal commission in DB");
        assert($db_trade->getState() == $trade->getState(), "Expect equal state of 3");
    }

    
    private function openCloseTrade()
    {
        $trade = $this->openTrade();
        $minutes_to_add = 45;
        $close_time = $trade->getOpenTime();
        $trade->close(0.50, 0.12, $close_time->add(new DateInterval('PT' . $minutes_to_add . 'M')));
        return $trade;
    }

    public function test_predictTrade_shouldUpdatePredictPProbaState(){
        $trade = new Trade(60);
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        $trade->predict(1, 0.75);
        $this->tradeDBHandler->predictTrade($trade);
        $db_trade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfPredictedDBTradeEqualTrade($trade, $db_trade);
    }
    private function checkIfPredictedDBTradeEqualTrade($trade, $db_trade)
    {
        assert($db_trade->getP_proba() == $trade->getP_proba(), "Expect equal p_prediction in DB");
        assert($db_trade->getPrediction() == $trade->getPrediction(), "Expect equal prediction in DB");
        assert($db_trade->getState() == $trade->getState(), "Expect equal state of 1");
    }

    
}

?>