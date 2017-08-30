<?php

use src\requests\NextActionRequest;

require_once(str_replace("tests", "src", __DIR__."/").'NextActionRequest.php');

class NextActionRequestTest extends PHPUnit_Framework_TestCase
{
    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;
    private $tradeMock;
    private $displayerMock;
    private $nextActionRequest;

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
        $this->eventMock = $this->getMockBuilder('Event')
        ->disableOriginalConstructor()->getMock();
        $this->displayerMock = $this->getMockBuilder('SimpleHTMLDisplayer')
        ->disableOriginalConstructor()->getMock();
        
    }

    protected function tearDown()
    {
        $this->nextActionRequest = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
    }
    
    public function testSetDisplayerWithWrongTypeShouldThrow(){
        $htmlDisplayer = $this->tradeMock;
        $this->expectExceptionMessage("Wrong type for htmlDisplayer. Expected SimpleHTMLDisplayer got: "
            .gettype($htmlDisplayer));
        $this->nextActionRequest = new NextActionRequest($htmlDisplayer);
    }
    
    public function testExecuteWithBadRequestShouldThrow(){
        $parameters = ["currency" => "EURUSD"];
        $this->expectExceptionMessage("Wrong currency: ".$parameters["currency"]);
        $this->nextActionRequest = new NextActionRequest($this->displayerMock);
        $this->nextActionRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->nextActionRequest->execute();
    }
    
    public function testExecuteSuccess(){
        try{
            $parameters = ["currency" => "EUR_USD"];
            $this->tradeDBHandlerMock->expects($this->once())
            ->method("getTradesFromTo")
            ->willReturn([$this->tradeMock, $this->tradeMock]);
            $this->eventDBHandlerMock->expects($this->any())
            ->method("getEventByEventId")
            ->willReturn([$this->eventMock]);
            $this->displayerMock->expects($this->any())
            ->method("displayTrade")
            ->willReturn("");
            $this->displayerMock->expects($this->any())
            ->method("displayEvent")
            ->willReturn("");
            
            $this->nextActionRequest = new NextActionRequest($this->displayerMock);
            $this->nextActionRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, $parameters);
            
            $this->nextActionRequest->execute();
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
}

