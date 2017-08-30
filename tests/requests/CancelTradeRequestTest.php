<?php
require_once(str_replace("tests", "src", __DIR__."/").'CancelTradeRequest.php');

use src\requests\CancelTradeRequest;

class CancelTradeRequestTest extends PHPUnit_Framework_TestCase
{
    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;
    private $tradeMock;
    
    private $cancelTradeRequest;

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
        $this->cancelTradeRequest = new CancelTradeRequest();
    }

    protected function tearDown()
    {
        $this->cancelTradeRequest = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
    }
    
    public function testExecuteSuccess(){
        try{
            $this->tradeDBHandlerMock->expects($this->once())
            ->method("removeTradeById")
            ->willReturn($this->returnArgument(0));
            $parameters = ["trade_id" => 5];
            $this->cancelTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, $parameters);
            
            $this->cancelTradeRequest->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testExecuteWithWrongRequestShouldThrow(){
        $parameters = [];
        $this->cancelTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->expectExceptionMessage("Ill-formed request: missing parameters");
        $this->cancelTradeRequest->execute();
    }
    
    public function testExecuteWithInvalidParametersTypeShouldThrow(){
        $parameters = ["trade_id" => "5.5"];
        $this->cancelTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->expectExceptionMessage("Invalid Request: bad parameters type");
        $this->cancelTradeRequest->execute();
    }

}

