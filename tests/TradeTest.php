<?php
require_once '../Trade.php';
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
        $id = -1;
        $this->expectExceptionMessage("Id should be positive. Id = ".$id);
        $this->trade->setId($id);
    }
    
    /*public function test_setOpenTimeWithWrongArgument_expectError(){
        $this->trade->setId(5);
        $this->expectExceptionMessage("Wrong type for open_time. Expected DateTime got: ".gettype(10));
        $this->trade->setOpenTime(10);
    }
    
    public function test_setCloseTimeWithWrongArgument_expectError(){
        $this->trade->setId(5);
        $this->expectExceptionMessage("Wrong type for close_time. Expected DateTime got: ".gettype(10));
        $this->trade->setCloseTime(10);
    }*/
    
    public function test_setDVPTM5WithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for dv_p_tm5. Expected float or double got: ".gettype("string"));
        $this->trade->setDv_p_tm5("string");
    }
    
    public function test_setDVPT0WithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for dv_p_t0. Expected float or double got: ".gettype("string"));
        $this->trade->setDv_p_t0("string");
    }
    
    public function test_setPredictionWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for prediction. Expected int got: ".gettype(0.5));
        $this->trade->setPrediction(0.5);
    }
    
    public function test_setPredictionProbaWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for p_proba. Expected float or double got: ".gettype("0.5"));
        $this->trade->setP_proba("0.5");
    }
    
    public function test_setGainWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for gain. Expected float or double or int got: ".gettype("0.5"));
        $this->trade->setGain("0.5");
    }
    
    public function test_setCommissionWithWrongArgument_expectError(){
        
        $this->expectExceptionMessage("Wrong type for commission. Expected float or double or int got: ".gettype("0.5"));
        $this->trade->setCommission("0.5");
    }
    
    public function test_setStateWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype(0.5));
        $this->trade->setState(0.5);
    }
    
    public function test_closeTrade(){
        $gain = 0.50;
        $commission = 0.12;
        $close_time = new DateTime('NOW');
        $this->trade->close($gain, $commission, $close_time);
        assert($this->trade->getGain() == $gain, "Expect equal gain");
        assert($this->trade->getCommission() == $commission, "Expect equal commission");
        assert($this->trade->getCloseTime() == $close_time, "Expect equal close time");
        assert($this->trade->getState() == 3, "Expect sate to be 3");
    }
    
    public function test_openTrade(){
        $open_time = new DateTime('NOW');
        $this->trade->open($open_time);
        assert($this->trade->getOpenTime() == $open_time, "Expect equal open time");
        assert($this->trade->getState() == 2, "Expect sate to be 2");
    }
    
    public function test_predictTrade(){
        $prediction = 1;
        $p_proba = 0.76;
        $this->trade->predict($prediction, $p_proba);
        assert($this->trade->getPrediction() == $prediction, "Expect equal close time");
        assert($this->trade->getP_proba() == $p_proba, "Expect equal p_proba");
        assert($this->trade->getState() == 1, "Expect state to be 1");
    }
}

