<?php

require_once(str_replace("tests", "src", __DIR__."/").'CloseTradeRequest.php');

use src\requests\CloseTradeRequest;

class CloseTradeRequestTest extends PHPUnit_Framework_TestCase
{

    private $closerTradeRequest;

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
        
        $this->closerTradeRequest = new CloseTradeRequest();
    }

    protected function tearDown()
    {
        $this->closerTradeRequest = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function testExecuteSuccess(){
        try{
            $this->tradeDBHandlerMock->expects($this->once())
            ->method("getTradeByID")
            ->willReturn($this->tradeMock);
            $this->tradeMock->expects($this->once())
            ->method("close")
            ->willReturn($this->returnArgument(0));
            $this->tradeDBHandlerMock->expects($this->once())
            ->method("closeTrade")
            ->willReturn($this->returnArgument(0));
            $parameters = ["trade_id" => 5, "gain" => 0.75, "commission" => 0.12];
            $this->closerTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, $parameters);
            
            $this->closerTradeRequest->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testExecuteWithWrongRequestShouldThrow(){
        $parameters = ["trade_id" => "5"];
        $this->closerTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->expectExceptionMessage("Ill-formed request: missing parameters");
        $this->closerTradeRequest->execute();
    }
    
    public function testExecuteWithInvalidParametersTypeShouldThrow(){
        $parameters = ["trade_id" => "5", "gain" => 0.75, "commission" => 0.12];
        $this->closerTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->expectExceptionMessage("Invalid Request: bad parameters type");
        $this->closerTradeRequest->execute();
    }
}

