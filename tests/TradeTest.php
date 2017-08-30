<?php
require_once(str_replace("tests", "src", __DIR__."/").'Trade.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');
/**
 * Trade test case.
 */
class TradeTest extends PHPUnit_Framework_TestCase
{
    private $trade;

    protected function setUp()
    {
        parent::setUp();
        $this->id_db_event = 50;
        $this->creation_time = new DateTime('NOW');
        $this->currency = "EUR_USD";
        // TODO Auto-generated TradeTest::setUp()
        $this->trade = new Trade($this->id_db_event, $this->creation_time, "EUR_USD");
    }

    protected function tearDown()
    {
        // TODO Auto-generated TradeTest::tearDown()
        $this->trade = null;
        
        parent::tearDown();
    }


    public function testEmptyConstructor()
    {
        assert(!$this->trade->isInitialized());
    }
    
    public function testInitialization()
    {
        $this->trade->setId(5);
        assert($this->trade->isInitialized());
        assert($this->trade->getCurrency() == "EUR_USD");
        assert($this->trade->getId()==5);
        assert($this->trade->getCreationTime() == $this->creation_time);
    }
    
    public function testSetCurrencyWithWrongArgumentShouldThrow(){
        $currency = 5;
        $this->expectExceptionMessage("Wrong type for currency. Expected string got: "
            .gettype($currency));
        $this->trade->setCurrency($currency);
    }
    
    public function testInitializationWithWrongArgumentShouldThrow()
    {
        $identifier = -1;
        $this->expectExceptionMessage("Id should be positive. Id = ".$identifier);
        $this->trade->setId($identifier);
    }
    
    public function testSetDVPTM5WithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for dv_p_tm5. Expected float or double got: "
            .gettype("string"));
        $this->trade->setDvPTm5("string");
    }
    
    public function testSetDVPT0WithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for dv_p_t0. Expected float or double got: "
            .gettype("string"));
        $this->trade->setDvPT0("string");
    }
    
    public function testSetPredictionWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for prediction. Expected int got: ".gettype(0.5));
        $this->trade->setPrediction(0.5);
    }
    
    public function testSetPredictionWithWrongResultShouldThrow(){
        $this->expectExceptionMessage("Prediction value out of range:2. Shoudl be 0 or 1");
        $this->trade->setPrediction(2);
    }
    
    public function testSetPredictionProbaWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for p_proba. Expected float or double got: "
            .gettype("0.5"));
        $this->trade->setPProba("0.5");
    }
    
    public function testSetPredictionProbaWithOutOfRangeProbaShouldThrow(){
        $this->expectExceptionMessage("Prediction probability out of range:1.2. Should be between 0 and 1");
        $this->trade->setPProba(1.2);
    }
    
    public function testSetGainWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for gain. Expected float or double or int got: "
            .gettype("0.5"));
        $this->trade->setGain("0.5");
    }
    
    public function testSetCommissionWithWrongArgumentShouldThrow(){
        
        $this->expectExceptionMessage("Wrong type for commission. Expected float or double or int got: "
            .gettype("0.5"));
        $this->trade->setCommission("0.5");
    }
    
    public function testSetStateWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype(0.5));
        $this->trade->setState(0.5);
    }
    
    public function testCloseTrade(){
        $prediction = 1;
        $pProba = 0.76;
        $openTime = new DateTime('NOW');
        $gain = 0.50;
        $commission = 0.12;
        $closeTime = new DateTime('NOW');
        $this->trade->fillMarketInfo(0.005, 0.00010);
        $this->trade->predict($prediction, $pProba);
        $this->trade->open($openTime);
        $this->trade->close($gain, $commission, $closeTime);
        assert($this->trade->getGain() == $gain, "Expect equal gain");
        assert($this->trade->getCommission() == $commission, "Expect equal commission");
        assert($this->trade->getCloseTime() == $closeTime, "Expect equal close time");
        assert($this->trade->getState() == TradeState::CLOSE, "Expect sate to be 4");
    }
    
    public function testCloseTradeWhenNotOpenShouldThrow(){
        $gain = 0.50;
        $commission = 0.12;
        $closeTime = new DateTime('NOW');
        
        $this->expectExceptionMessage("Cannot switch to close state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->close($gain, $commission, $closeTime);
    }
    
    public function testOpenTrade(){
        $prediction = 1;
        $pProba = 0.76;
        $openTime = new DateTime('NOW');
        $this->trade->fillMarketInfo(0.005, 0.00010);
        $this->trade->predict($prediction, $pProba);
        $this->trade->open($openTime);
        assert($this->trade->getOpenTime() == $openTime, "Expect equal open time");
        assert($this->trade->getState() == TradeState::OPEN, "Expect sate to be 3");
    }
    
    public function testOpenTradeWhenNotPredictedShouldThrow(){
        $openTime = new DateTime('NOW');
        
        $this->expectExceptionMessage("Cannot switch to open state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->open($openTime);
    }
    
    public function testPredictTrade(){
        $prediction = 1;
        $pProba = 0.76;
        $this->trade->fillMarketInfo(0.005, 0.00010);
        $this->trade->predict($prediction, $pProba);
        assert($this->trade->getPrediction() == $prediction, "Expect equal close time");
        assert($this->trade->getPProba() == $pProba, "Expect equal p_proba");
        assert($this->trade->getState() == TradeState::PREDICTED, "Expect state to be 2");
    }
    
    public function testPredictTradeWhenNotFilledShouldThrow(){
        $prediction = 1;
        $pProba = 0.76;
        
        $this->expectExceptionMessage("Cannot switch to predicted state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->predict($prediction, $pProba);
    }
    
    public function testFillMarketInfoTrade(){
        $dvPT0 = 0.00500;
        $dvTPm5 = 0.00200;
        $this->trade->fillMarketInfo($dvTPm5, $dvPT0);
        assert($this->trade->getDvPT0() == $dvPT0, "Expect equal dv_p_t0 time");
        assert($this->trade->getDvPTm5() == $dvTPm5, "Expect equal dv_p_tm5");
        assert($this->trade->getState() == TradeState::FILLED, "Expect state to be 1");
    }
    
    public function testFillTradeWhenInPredictedStateShouldThrow(){
        $dvPT0 = 0.00500;
        $dvPTm5 = 0.00200;
        $prediction = 1;
        $pProba = 0.76;
        $this->trade->fillMarketInfo($dvPTm5, $dvPT0);
        $this->trade->predict($prediction, $pProba);
        $this->expectExceptionMessage("Cannot switch to initialized state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->fillMarketInfo($dvPTm5, $dvPT0);
    }
    
    public function testCancelling(){
        $this->trade->cancel();
        assert($this->trade->getState() == TradeState::CANCELLED);
    }
    
    public function testGetStringFromTradeInitializedState(){
        assert($this->trade->getStringFromState(TradeState::INITIALIZED) == "Initialized");
    }
    
    public function testGetStringFromTradeFilledState(){
        assert($this->trade->getStringFromState(TradeState::FILLED) == "Market filled");
    }
    
    public function testGetStringFromTradePredictedState(){
        assert($this->trade->getStringFromState(TradeState::PREDICTED) == "Predicted");
    }
    
    public function testGetStringFromTradeOpenState(){
        assert($this->trade->getStringFromState(TradeState::OPEN) == "Open");
    }
    
    public function testGetStringFromTradeCloseState(){
        assert($this->trade->getStringFromState(TradeState::CLOSE) == "Close");
    }
}

