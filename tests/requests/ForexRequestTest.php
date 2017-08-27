<?php
use src\requests\ForexRequest;

require_once(str_replace("tests", "src", __DIR__."/").'ForexRequest.php');

/**
 * ForexRequest test case.
 */
class ForexRequestTest extends PHPUnit_Framework_TestCase
{

    private $forexRequest;

    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated ForexRequestTest::setUp()
        
        $this->forexRequest = new ForexRequest(/* parameters */);
    }

    protected function tearDown()
    {
        // TODO Auto-generated ForexRequestTest::tearDown()
        $this->forexRequest = null;
        
        parent::tearDown();
    }


    public function testSetWrongTypeEventDBHandlerShouldThrow(){
        $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventDBHandlerMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        $eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        
        $this->expectExceptionMessage("Wrong type for eventDBHandler. Expected EventDBHandler got: ".
            gettype($eventDBHandlerMock));
        $this->forexRequest->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock, []);
    }
    
    public function testSetWrongTypeEventParserShouldThrow(){
        $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventParserMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        
        $this->expectExceptionMessage("Wrong type for eventParser. Expected EventParser got: ".
            gettype($eventParserMock));
        $this->forexRequest->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock, []);
    }
    
    public function testSetWrongTypeTradeDBHandlerShouldThrow(){
        $tradeDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        
        $this->expectExceptionMessage("Wrong type for tradeDBHandler. Expected TradeDBHandler got: ".
            gettype($tradeDBHandlerMock));
        $this->forexRequest->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock, []);
    }
    
    public function testSetWrongTypeParametersShouldThrow(){
        $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        
        $this->expectExceptionMessage("Wrong type for parameters. Expected Array got: ".
            gettype(""));
        $this->forexRequest->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock, "");
    }
    
    public function testInitSuccessfully(){
        $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        
        $this->forexRequest->init($tradeDBHandlerMock, $eventDBHandlerMock, 
            $eventParserMock, ["currency" => "EUR_USD"]);
        
        assert($this->forexRequest->getParameters()["currency"] == "EUR_USD");
    }
}

