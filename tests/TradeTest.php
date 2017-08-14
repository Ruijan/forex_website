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
        // TODO Auto-generated TradeTest::setUp()
        $this->trade = new Trade($this->id_db_event, $this->creation_time);
        $this->trade->__construct($this->id_db_event, $this->creation_time);
    }

    protected function tearDown()
    {
        // TODO Auto-generated TradeTest::tearDown()
        $this->trade = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function test__emptyConstructor()
    {
        assert(!$this->trade->isInitialized());
    }
    
    public function test__initialization()
    {
        $this->trade->setId(5);
        assert($this->trade->isInitialized());
        assert($this->trade->getId()==5);
        assert($this->trade->getCreationTime() == $this->creation_time);
    }
    
    public function test__initializationWithWrongArgument_expectError()
    {
        $identifier = -1;
        $this->expectExceptionMessage("Id should be positive. Id = ".$identifier);
        $this->trade->setId($identifier);
    }
    
    public function test_setDVPTM5WithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for dv_p_tm5. Expected float or double got: "
            .gettype("string"));
        $this->trade->setDv_p_tm5("string");
    }
    
    public function test_setDVPT0WithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for dv_p_t0. Expected float or double got: "
            .gettype("string"));
        $this->trade->setDv_p_t0("string");
    }
    
    public function test_setPredictionWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for prediction. Expected int got: ".gettype(0.5));
        $this->trade->setPrediction(0.5);
    }
    
    public function test_setPredictionWithWrongResult_expectError(){
        $this->expectExceptionMessage("Prediction value out of range:2. Shoudl be 0 or 1");
        $this->trade->setPrediction(2);
    }
    
    public function test_setPredictionProbaWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for p_proba. Expected float or double got: "
            .gettype("0.5"));
        $this->trade->setP_proba("0.5");
    }
    
    public function test_setPredictionProbaWithOutOfRangeProba_expectError(){
        $this->expectExceptionMessage("Prediction probability out of range:1.2. Should be between 0 and 1");
        $this->trade->setP_proba(1.2);
    }
    
    public function test_setGainWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for gain. Expected float or double or int got: "
            .gettype("0.5"));
        $this->trade->setGain("0.5");
    }
    
    public function test_setCommissionWithWrongArgument_expectError(){
        
        $this->expectExceptionMessage("Wrong type for commission. Expected float or double or int got: "
            .gettype("0.5"));
        $this->trade->setCommission("0.5");
    }
    
    public function test_setStateWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype(0.5));
        $this->trade->setState(0.5);
    }
    
    public function test_closeTrade(){
        $prediction = 1;
        $p_proba = 0.76;
        $open_time = new DateTime('NOW');
        $gain = 0.50;
        $commission = 0.12;
        $close_time = new DateTime('NOW');
        $this->trade->fillMarketInfo(0.005, 0.00010);
        $this->trade->predict($prediction, $p_proba);
        $this->trade->open($open_time);
        $this->trade->close($gain, $commission, $close_time);
        assert($this->trade->getGain() == $gain, "Expect equal gain");
        assert($this->trade->getCommission() == $commission, "Expect equal commission");
        assert($this->trade->getCloseTime() == $close_time, "Expect equal close time");
        assert($this->trade->getState() == TradeState::CLOSE, "Expect sate to be 4");
    }
    
    public function test_closeTradeWhenNotOpenShouldThrow(){
        $gain = 0.50;
        $commission = 0.12;
        $close_time = new DateTime('NOW');
        
        $this->expectExceptionMessage("Cannot switch to close state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->close($gain, $commission, $close_time);
    }
    
    public function test_openTrade(){
        $prediction = 1;
        $p_proba = 0.76;
        $open_time = new DateTime('NOW');
        $this->trade->fillMarketInfo(0.005, 0.00010);
        $this->trade->predict($prediction, $p_proba);
        $this->trade->open($open_time);
        assert($this->trade->getOpenTime() == $open_time, "Expect equal open time");
        assert($this->trade->getState() == TradeState::OPEN, "Expect sate to be 3");
    }
    
    public function test_openTradeWhenNotPredictedShouldThrow(){
        $open_time = new DateTime('NOW');
        
        $this->expectExceptionMessage("Cannot switch to open state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->open($open_time);
    }
    
    public function test_predictTrade(){
        $prediction = 1;
        $p_proba = 0.76;
        $this->trade->fillMarketInfo(0.005, 0.00010);
        $this->trade->predict($prediction, $p_proba);
        assert($this->trade->getPrediction() == $prediction, "Expect equal close time");
        assert($this->trade->getP_proba() == $p_proba, "Expect equal p_proba");
        assert($this->trade->getState() == TradeState::PREDICTED, "Expect state to be 2");
    }
    
    public function test_predictTradeWhenNotFilledShouldThrow(){
        $prediction = 1;
        $p_proba = 0.76;
        
        $this->expectExceptionMessage("Cannot switch to predicted state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->predict($prediction, $p_proba);
    }
    
    public function test_fillMarketInfoTrade(){
        $dv_p_t0 = 0.00500;
        $dv_p_tm5 = 0.00200;
        $this->trade->fillMarketInfo($dv_p_tm5, $dv_p_t0);
        assert($this->trade->getDv_p_t0() == $dv_p_t0, "Expect equal dv_p_t0 time");
        assert($this->trade->getDv_p_tm5() == $dv_p_tm5, "Expect equal dv_p_tm5");
        assert($this->trade->getState() == TradeState::FILLED, "Expect state to be 1");
    }
    
    public function test_fillTradeWhenInPredictedStateShouldThrow(){
        $dv_p_t0 = 0.00500;
        $dv_p_tm5 = 0.00200;
        $prediction = 1;
        $p_proba = 0.76;
        $this->trade->fillMarketInfo($dv_p_tm5, $dv_p_t0);
        $this->trade->predict($prediction, $p_proba);
        $this->expectExceptionMessage("Cannot switch to initialized state. Actual state is : ".
            $this->trade->getStringFromState($this->trade->getState()).". Next expected state is ".
            $this->trade->getStringFromState($this->trade->getState()+1));
        
        $this->trade->fillMarketInfo($dv_p_tm5, $dv_p_t0);
    }
    
    public function test__getStringFromTradeInitializedState(){
        assert($this->trade->getStringFromState(TradeState::INITIALIZED) == "Initialized");
    }
    
    public function test__getStringFromTradeFilledState(){
        assert($this->trade->getStringFromState(TradeState::FILLED) == "Market filled");
    }
    
    public function test__getStringFromTradePredictedState(){
        assert($this->trade->getStringFromState(TradeState::PREDICTED) == "Predicted");
    }
    
    public function test__getStringFromTradeOpenState(){
        assert($this->trade->getStringFromState(TradeState::OPEN) == "Open");
    }
    
    public function test__getStringFromTradeCloseState(){
        assert($this->trade->getStringFromState(TradeState::CLOSE) == "Close");
    }
}

