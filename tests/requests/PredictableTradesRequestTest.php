<?php

/**
 * PredictableTradesRequest test case.
 */
use src\requests\PredictableTradesRequest;

require_once(str_replace("tests", "src", __DIR__."/").'PredictableTradesRequest.php');

class PredictableTradesRequestTest extends PHPUnit_Framework_TestCase
{
    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;
    private $tradeMock;
    private $displayerMock;
    private $predictableTradesRequest;

    protected function setUp()
    {
        parent::setUp();
        
        $this->eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        $this->eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeMock = $this->getMockBuilder('Trade')
        ->disableOriginalConstructor()->getMock();
        $this->displayerMock = $this->getMockBuilder('SimpleHTMLDisplayer')
        ->disableOriginalConstructor()->getMock();
        
    }

    protected function tearDown()
    {
        $this->predictableTradesRequest = null;
        
        parent::tearDown();
    }

    public function testSetDisplayerWithWrongTypeShouldThrow(){
        
        $this->expectExceptionMessage("Wrong type for htmlDisplayer. Expected SimpleHTMLDisplayer got: "
            .gettype($this->tradeMock));
        $this->predictableTradesRequest = new PredictableTradesRequest($this->tradeMock);
    }
    
    public function testExecuteSuccess(){
        try{
            $this->tradeDBHandlerMock->expects($this->once())
            ->method("getTradesFromTo")
            ->willReturn([$this->tradeMock, $this->tradeMock]);
            $this->displayerMock->expects($this->any())
            ->method("displayTrade")
            ->willReturn("");
            
            $this->predictableTradesRequest = new PredictableTradesRequest($this->displayerMock);
            $this->predictableTradesRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, []);
            
            $this->predictableTradesRequest->execute();
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
}

