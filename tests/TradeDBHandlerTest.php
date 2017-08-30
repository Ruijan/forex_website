<?php
require_once(str_replace("tests", "src", __DIR__."/").'TradeDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'Trade.php');
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

class TradeDBHandlerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mysqli = connect_database();
        $this->currency = "EUR_USD";
        $this->tradeDBHandler = new TradeDBHandler($this->mysqli, $this->currency);
        $this->tradeDBHandler->createTable();
    }
    
    protected function tearDown()
    {
        // TODO Auto-generated TradeDBHandlerTest::tearDown()
        $this->tradeDBHandler->emptyTable();
        $this->mysqli->close();
        parent::tearDown();
    }
    
    public function __destruct()
    {
        $this->mysqli = connect_database();
        $this->deleteTableIfExists();
        $this->mysqli->close();
    }
    
    public function testConstruct()
    {
        assert($this->tradeDBHandler->isInitialized());
        assert($this->tradeDBHandler->getTableName() == "trades");
    }
    
    
    public function testIsTableEmpty()
    {
        assert($this->tradeDBHandler->getTableSize() == 0);
    }
    
    public function testAddingTradeExpectIncrementInSize(){
        $this->createRandomDummyTrade();
        $trade2 = $this->createRandomDummyTrade();
        assert($this->tradeDBHandler->getTableSize() == 2);
        assert($trade2->getId() == 2);
    }
    
    public function testGetTradeByEventID(){
        $this->createRandomDummyTrade();
        $trade2 = $this->createRandomDummyTrade();
        assert($this->tradeDBHandler->getTradeByEventId($trade2->getIDDBEvent()) == $trade2);
    }
    
    public function testRemovingTradeExpectDecrementationInSize(){
        $trade = new Trade(999, new DateTime('NOW'), "EUR_USD");
        $identifier = $this->tradeDBHandler->addTrade($trade);
        $this->tradeDBHandler->removeTradeById($identifier);
        assert($this->tradeDBHandler->getTableSize() == 0);
    }
    
    public function testAddingTradeExpectExactParameters(){
        $trade = $this->createRandomDummyTrade();
        $dbTrade = $this->tradeDBHandler->getTradeByID($trade->getId());
        assert($trade->getCreationTime() == $dbTrade->getCreationTime(), "Expect same Creation Time: ".
            $trade->getCreationTime()->format('Y-m-d H:i:s'). " got ".
            $trade->getCreationTime()->format('Y-m-d H:i:s'));
        assert($trade->getId() == $dbTrade->getId(), "Expect same ID");
        assert($trade->getIDDBEvent() == $dbTrade->getIDDBEvent(), "Expect same event Id");
    }
    
    public function testTryAddingTradeSecondTimeSizeShouldBeOne(){
        $this->tryCreatingTwoSameDummyTrades();
        assert($this->tradeDBHandler->getTableSize() == 1);
    }
    
    private function tryCreatingTwoSameDummyTrades()
    {
        $trade1 = new Trade(60, new DateTime('NOW'), "EUR_USD");
        $trade2 = new Trade(60, new DateTime('NOW'), "EUR_USD");
        $trade1->setId($this->tradeDBHandler->addTrade($trade1));
        $trade2->setId($this->tradeDBHandler->tryAddingTrade($trade2));
    }
    
    private function createRandomDummyTrade()
    {
        $trade = new Trade(rand(1,10000), new DateTime('NOW'), "EUR_USD");
        $identifier = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($identifier);
        return $trade;
    }

    public function testOpenTradeShouldUpdateOpenTime(){
        $trade = $this->openTrade();
        $dbTrade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfOpenDBTradeEqualTrade($trade, $dbTrade);
    }
    
    private function checkIfOpenDBTradeEqualTrade($trade, $dbTrade)
    {
        assert($this->tradeDBHandler->getTradeByID($trade->getId())->getOpenTime()->format('Y-m-d H:i:s') == 
            $trade->getOpenTime()->format('Y-m-d H:i:s'), 
            "Expect equal closing time. Got:".$trade->getOpenTime()->format('Y-m-d H:i:s').
            " and ".$dbTrade->getOpenTime()->format('Y-m-d H:i:s'));
        assert($dbTrade->getState() == $trade->getState(), "Expect equal state of 2");
    }

    private function openTrade()
    {
        $trade = new Trade(60, new DateTime('NOW'), "EUR_USD");
        $identifier = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($identifier);
        $trade->fillMarketInfo(0.0005, 0.0001);
        $trade->predict(1, 0.05);
        $trade->open(new DateTime('NOW'));
        $this->tradeDBHandler->openTrade($trade);
        return $trade;
    }

    public function testCloseTradeShouldUpdateCloseTimeGainCommissionState(){
        $trade = $this->openCloseTrade();
        $dbTrade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfClosedDBtradeEqualTrade($trade, $dbTrade);
    }
    
    private function checkIfClosedDBtradeEqualTrade($trade, $dbTrade)
    {
        assert($dbTrade->getCloseTime()->format('Y-m-d H:i:s') == $trade->getCloseTime()->format('Y-m-d H:i:s'), 
            "Expect equal closing time. Got:".$trade->getCloseTime()->format('Y-m-d H:i:s').
            " and ".$dbTrade->getCloseTime()->format('Y-m-d H:i:s'));
        assert($dbTrade->getGain() == $trade->getGain(), "Expect equal gain in DB");
        assert($dbTrade->getCommission() == $trade->getCommission(), "Expect equal commission in DB");
        assert($dbTrade->getState() == $trade->getState(), "Expect equal state of 3");
    }

    
    private function openCloseTrade()
    {
        $trade = $this->openTrade();
        $minutes_to_add = 45;
        $close_time = $trade->getOpenTime();
        $trade->close(0.50, 0.12, $close_time->add(new DateInterval('PT' . $minutes_to_add . 'M')));
        $this->tradeDBHandler->closeTrade($trade);
        return $trade;
    }
    
    public function testFillMarketTradeShouldUpdateMarketState(){
        $trade = new Trade(60, new DateTime('NOW'), "EUR_USD");
        $identifier = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($identifier);
        $trade->fillMarketInfo(0.005, 0.0001);
        $this->tradeDBHandler->fillTradeWithMarketInfo($trade);
        $dbTrade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfFilledDBTradeEqualTrade($trade, $dbTrade);
    }
    
    private function checkIfFilledDBTradeEqualTrade($trade, $dbTrade)
    {
        assert($dbTrade->getDvPTm5() == $trade->getDvPTm5(), "Expect equal p_prediction in DB");
        assert($dbTrade->getDvPT0() == $trade->getDvPT0(), "Expect equal prediction in DB");
        assert($dbTrade->getState() == $trade->getState(), "Expect equal state of 1");
    }

    public function testPredictTradeShouldUpdatePredictPProbaState(){
        $trade = new Trade(60, new DateTime('NOW'), "EUR_USD");
        $identifier = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($identifier);
        $trade->fillMarketInfo(0.0005, 0.0001);
        $trade->predict(1, 0.75);
        $this->tradeDBHandler->predictTrade($trade);
        $dbTrade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfPredictedDBTradeEqualTrade($trade, $dbTrade);
    }
    
    private function checkIfPredictedDBTradeEqualTrade($trade, $dbTrade)
    {
        assert($dbTrade->getPProba() == $trade->getPProba(), "Expect equal p_prediction in DB");
        assert($dbTrade->getPrediction() == $trade->getPrediction(), "Expect equal prediction in DB");
        assert($dbTrade->getState() == $trade->getState(), "Expect equal state of 2");
    }
    
    public function testCancellingTradeShouldSetState(){
        $trade = new Trade(60, new DateTime('NOW'), "EUR_USD");
        $identifier = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($identifier);
        $trade->cancel();
        $this->tradeDBHandler->cancelTrade($trade);
        $dbTrade = $this->tradeDBHandler->getTradeByID($trade->getId());
        assert($dbTrade->getState() == TradeState::CANCELLED);
    }
    
    public function testGetTradesFromToWithBadArgumentsShouldThrow(){
        $fromDate = "coucou";
        $toDate = 32;
        $this->expectExceptionMessage("Wrong type for from or to. Expected DateTime got: ".gettype($fromDate).
            " and ".gettype($toDate));
        $this->tradeDBHandler->getTradesFromTo($fromDate, $toDate);
    }
    
    public function testGetTradesFromToStateWithBadArgumentsShouldThrow(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        $state = "5";
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype($state));
        $this->tradeDBHandler->getTradesFromTo($fromDate, $toDate, $state);
    }
    
    public function testGetTradesFromTo(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        
        $allTrades = $this->generateDummyTrades();
        $tradesToGet = [$allTrades[0], $allTrades[1], $allTrades[2]];
        $this->addListOfTrades($allTrades);
        $trades = $this->tradeDBHandler->getTradesFromTo($fromDate, $toDate);
        
        $allHere = $this->areListOfTradesEquals($tradesToGet, $trades);
        assert(sizeof($trades) == sizeof($tradesToGet),
            "Different number of trades expected. Expected ".sizeof($tradesToGet)." got ".sizeof($trades));
        assert($allHere, "Trades were not equals");
    }
    
    public function testGetTradesFromToState(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-06");
        $state = TradeState::OPEN;
        
        $allTrades = $this->generateDummyTrades();
        $tradesToGet = [$allTrades[2]];
        $this->addListOfTrades($allTrades);
        $allTrades[2]->fillMarketInfo(0.002,0.003);
        $allTrades[2]->predict(1, 0.75);
        $allTrades[2]->open(new DateTime("2017-08-05 17:30:00"));
        $this->tradeDBHandler->fillTradeWithMarketInfo($allTrades[2]);
        $this->tradeDBHandler->predictTrade($allTrades[2]);
        $this->tradeDBHandler->openTrade($allTrades[2]);
        $trades = $this->tradeDBHandler->getTradesFromTo($fromDate, $toDate, $state);
        $allHere = $this->areListOfTradesEquals($tradesToGet, $trades);
        assert(sizeof($trades) == sizeof($tradesToGet),
            "Different number of trades expected. Expected ".sizeof($tradesToGet)." got ".sizeof($trades));
        assert($allHere, "Trades were not equals");
    }
    
    public function testGetTradesFromToCurrency(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-06");
        $dbCurrency = "USD_CAD";
        
        $allTrades = $this->generateDummyTrades();
        $tradesToGet = [$allTrades[2]];
        $this->addListOfTrades($allTrades);
        $trades = $this->tradeDBHandler->getTradesFromTo($fromDate, $toDate, -1, $dbCurrency);
        $allHere = $this->areListOfTradesEquals($tradesToGet, $trades);
        assert(sizeof($trades) == sizeof($tradesToGet),
            "Different number of trades expected. Expected ".sizeof($tradesToGet)." got ".sizeof($trades));
        assert($allHere, "Trades were not equals");
    }
    
    private function areListOfTradesEquals($trades_to_get, $trades)
    {
        $allHere = sizeof($trades) == sizeof($trades_to_get);
        foreach($trades as $trade){
            $tradeHere = false;
            foreach($trades_to_get as $expectedTrade){
                if($expectedTrade == $trade){
                    $tradeHere = true;
                }
            }
            $allHere = $tradeHere ? $allHere : $tradeHere;
        }
        return $allHere;
    }
    
    private function addListOfTrades($trades){
        foreach($trades as $trade){
            $trade->setId($this->tradeDBHandler->addTrade($trade));
        }
    }
    
    private function generateDummyTrades()
    {
        $allTrades = [];
        $allTrades[] = new Trade(65, new DateTime("2017-08-03 00:30:00"), "EUR_USD");
        $allTrades[] = new Trade(35, new DateTime("2017-08-05 17:30:00"), "EUR_USD");
        $allTrades[] = new Trade(61, new DateTime("2017-08-04 18:05:00"), "USD_CAD");
        $allTrades[] = new Trade(30, new DateTime("2017-08-02 00:30:00"), "EUR_USD");
        $allTrades[] = new Trade(1, new DateTime("2017-08-10 00:30:00"), "EUR_USD");
        $allTrades[] = new Trade(5, new DateTime("2017-08-01 00:30:00"), "EUR_USD");
        return $allTrades;
    }
    
    private function deleteTableIfExists()
    {
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades");
        }
    }
}

?>