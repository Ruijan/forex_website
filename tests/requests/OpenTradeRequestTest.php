<?php

use src\requests\OpenTradeRequest;

require_once(str_replace("tests", "src", __DIR__."/").'OpenTradeRequest.php');

$pathToVendor = str_replace("tests\\requests", "vendor", __DIR__."/");
$pathToVendor = str_replace("tests/requests", "vendor", $pathToVendor."/");
require_once($pathToVendor.'/autoload.php');

class OpenTradeRequestTest extends PHPUnit_Framework_TestCase
{
    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;
    private $tradeMock;
    
    private $openTradeRequest;


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
        
        $this->openTradeRequest = new OpenTradeRequest();
    }

    protected function tearDown()
    {
        $this->openTradeRequest = null;
        
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
            ->method("open")
            ->willReturn($this->returnArgument(0));
            $this->tradeDBHandlerMock->expects($this->once())
            ->method("openTrade")
            ->willReturn($this->returnArgument(0));
            $parameters = ["trade_id" => 5];
            $this->openTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, $parameters);
            
            $this->openTradeRequest->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testExecuteWithWrongRequestShouldThrow(){
        $parameters = [];
        $this->openTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->expectExceptionMessage("Ill-formed request: missing parameters");
        $this->openTradeRequest->execute();
    }
    
    public function testExecuteWithInvalidParametersTypeShouldThrow(){
        $parameters = ["trade_id" => "5.5"];
        $this->openTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->expectExceptionMessage("Invalid Opening Request: bad parameters type");
        $this->openTradeRequest->execute();
    }
}

