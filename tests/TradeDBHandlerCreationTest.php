<?php
require_once 'TradeDBHandlerTest.php';
require_once(str_replace("tests", "src", __DIR__."/").'Trade.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

class TradeDBHandlerCreationTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mysqli = connect_database();
        $this->currency = "EUR_USD";
        $this->tradeDBHandler = new TradeDBHandler($this->mysqli, $this->currency);
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades_".$this->currency);
        }
        $this->tradeDBHandler->createTable();
        
    }
    
    protected function tearDown()
    {
        // TODO Auto-generated TradeDBHandlerTest::tearDown()
        $this->tradeDBHandler->deleteTable();
        $this->deleteTableIfExists();
        $this->tradeDBHandler = null;
        $this->mysqli->close();
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
        $trade1 = $this->createRandomDummyTrade();
        $trade2 = $this->createRandomDummyTrade();
        assert($this->tradeDBHandler->getTableSize() == 2);
        assert($trade2->getId() == 2);
    }
    
    public function test_getTradeByEventID(){
        $trade1 = $this->createRandomDummyTrade();
        $trade2 = $this->createRandomDummyTrade();
        assert($this->tradeDBHandler->getTradeByEventId($trade2->getIDDBEvent()) == $trade2);
    }
    
    public function test_removingTrade_expectDecrementationInSize(){
        $trade = new Trade(999, new DateTime('NOW'));
        $id = $this->tradeDBHandler->addTrade($trade);
        $this->tradeDBHandler->removeTradeById($id);
        assert($this->tradeDBHandler->getTableSize() == 0);
    }
    
    public function test_addingTrade_expectExactParameters(){
        $trade = $this->createRandomDummyTrade();
        $db_trade = $this->tradeDBHandler->getTradeByID($trade->getId());
        assert($trade->getCreationTime() == $db_trade->getCreationTime(), "Expect same Creation Time: ".
            $trade->getCreationTime()->format('Y-m-d H:i:s'). " got ".
            $trade->getCreationTime()->format('Y-m-d H:i:s'));
        assert($trade->getId() == $db_trade->getId(), "Expect same ID");
        assert($trade->getIDDBEvent() == $db_trade->getIDDBEvent(), "Expect same event Id");
    }
    
    public function test_tryAddingTradeSecondTime_SizeShouldBeOne(){
        $this->tryCreatingTwoSameDummyTrades();
        assert($this->tradeDBHandler->getTableSize() == 1);
    }
    
    private function createDummyTrade()
    {
        $trade = new Trade(60, new DateTime('NOW'));
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        return $trade;
    }
    
    private function tryCreatingTwoSameDummyTrades()
    {
        $trade1 = new Trade(60, new DateTime('NOW'));
        $trade2 = new Trade(60, new DateTime('NOW'));
        $trade1->setId($this->tradeDBHandler->addTrade($trade1));
        $trade2->setId($this->tradeDBHandler->tryAddingTrade($trade2));
    }
    
    private function createRandomDummyTrade()
    {
        $trade = new Trade(rand(1,10000), new DateTime('NOW'));
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        return $trade;
    }

    public function test_openTrade_shouldUpdateOpenTime(){
        $trade = $this->openTrade();
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
        $trade = new Trade(60, new DateTime('NOW'));
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        $trade->fillMarketInfo(0.0005, 0.0001);
        $trade->predict(1, 0.05);
        $trade->open(new DateTime('NOW'));
        $this->tradeDBHandler->openTrade($trade);
        return $trade;
    }

    public function test_closeTrade_shouldUpdateCloseTimeGainCommissionState(){
        $trade = $this->openCloseTrade();
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
        $this->tradeDBHandler->closeTrade($trade);
        return $trade;
    }
    
    public function test_fillMarketTrade_shouldUpdateMarketState(){
        $trade = new Trade(60, new DateTime('NOW'));
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        $trade->fillMarketInfo(0.005, 0.0001);
        $this->tradeDBHandler->fillTradeWithMarketInfo($trade);
        $db_trade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfFilledDBTradeEqualTrade($trade, $db_trade);
    }
    
    private function checkIfFilledDBTradeEqualTrade($trade, $db_trade)
    {
        assert($db_trade->getDv_p_tm5() == $trade->getDv_p_tm5(), "Expect equal p_prediction in DB");
        assert($db_trade->getDv_p_t0() == $trade->getDv_p_t0(), "Expect equal prediction in DB");
        assert($db_trade->getState() == $trade->getState(), "Expect equal state of 1");
    }

    public function test_predictTrade_shouldUpdatePredictPProbaState(){
        $trade = new Trade(60, new DateTime('NOW'));
        $id = $this->tradeDBHandler->addTrade($trade);
        $trade->setId($id);
        $trade->fillMarketInfo(0.0005, 0.0001);
        $trade->predict(1, 0.75);
        $this->tradeDBHandler->predictTrade($trade);
        $db_trade = $this->tradeDBHandler->getTradeByID($trade->getId());
        $this->checkIfPredictedDBTradeEqualTrade($trade, $db_trade);
    }
    
    private function checkIfPredictedDBTradeEqualTrade($trade, $db_trade)
    {
        assert($db_trade->getP_proba() == $trade->getP_proba(), "Expect equal p_prediction in DB");
        assert($db_trade->getPrediction() == $trade->getPrediction(), "Expect equal prediction in DB");
        assert($db_trade->getState() == $trade->getState(), "Expect equal state of 2");
    }
    
    public function test__getTradesFromToWithBadArguments_ShouldThrow(){
        $from = "coucou";
        $to = 32;
        $this->expectExceptionMessage("Wrong type for from or to. Expected DateTime got: ".gettype($from).
            " and ".gettype($to));
        $events = $this->tradeDBHandler->getTradesFromTo($from, $to);
    }
    
    public function test__getTradesFromToStateWithBadArguments_ShouldThrow(){
        $from = new DateTime("2017-08-03");
        $to = new DateTime("2017-08-05");
        $state = "5";
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype($state));
        $events = $this->tradeDBHandler->getTradesFromTo($from, $to, $state);
    }
    
    public function test__getTradesFromTo(){
        $from = new DateTime("2017-08-03");
        $to = new DateTime("2017-08-05");
        
        $all_trades = $this->generateDummyTrades();
        $trades_to_get = [$all_trades[0], $all_trades[1], $all_trades[2]];
        $this->addListOfTrades($all_trades);
        $trades = $this->tradeDBHandler->getTradesFromTo($from, $to);
        
        $all_here = $this->areListOfTradesEquals($trades_to_get, $trades);
        assert(sizeof($trades) == sizeof($trades_to_get),
            "Different number of trades expected. Expected ".sizeof($trades_to_get)." got ".sizeof($trades));
        assert($all_here, "Trades were not equals");
    }
    
    public function test__getTradesFromToState(){
        $from = new DateTime("2017-08-03");
        $to = new DateTime("2017-08-06");
        $state = TradeState::OPEN;
        
        $all_trades = $this->generateDummyTrades();
        $trades_to_get = [$all_trades[2]];
        $this->addListOfTrades($all_trades);
        $all_trades[2]->fillMarketInfo(0.002,0.003);
        $all_trades[2]->predict(1, 0.75);
        $all_trades[2]->open(new DateTime("2017-08-05 17:30:00"));
        $this->tradeDBHandler->fillTradeWithMarketInfo($all_trades[2]);
        $this->tradeDBHandler->predictTrade($all_trades[2]);
        $this->tradeDBHandler->openTrade($all_trades[2]);
        $trades = $this->tradeDBHandler->getTradesFromTo($from, $to, $state);
        $all_here = $this->areListOfTradesEquals($trades_to_get, $trades);
        assert(sizeof($trades) == sizeof($trades_to_get),
            "Different number of trades expected. Expected ".sizeof($trades_to_get)." got ".sizeof($trades));
        assert($all_here, "Trades were not equals");
    }
    
    private function areListOfTradesEquals($trades_to_get, $trades)
    {
        $all_here = sizeof($trades) == sizeof($trades_to_get);
        foreach($trades as $trade){
            $tradeHere = false;
            foreach($trades_to_get as $expected_trade){
                if($expected_trade == $trade){
                    $tradeHere = true;
                }
            }
            $all_here = $tradeHere ? $all_here : $tradeHere;
        }
        return $all_here;
    }
    
    private function addListOfTrades($trades){
        foreach($trades as $trade){
            $trade->setId($this->tradeDBHandler->addTrade($trade));
        }
    }
    
    private function generateDummyTrades()
    {
        $all_trades = [];
        $all_trades[] = new Trade(65, new DateTime("2017-08-03 00:30:00"));
        $all_trades[] = new Trade(35, new DateTime("2017-08-05 17:30:00"));
        $all_trades[] = new Trade(61, new DateTime("2017-08-04 18:05:00"));
        $all_trades[] = new Trade(30, new DateTime("2017-08-02 00:30:00"));
        $all_trades[] = new Trade(1, new DateTime("2017-08-10 00:30:00"));
        $all_trades[] = new Trade(5, new DateTime("2017-08-01 00:30:00"));
        return $all_trades;
    }
    
    private function deleteTableIfExists()
    {
        if($this->tradeDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE trades_".$this->currency);
        }
    }
}

?>