<?php

require_once(str_replace("tests", "src", __DIR__."/").'GUIDisplayerRequest.php');;

$pathToVendor = str_replace("tests\\requests", "vendor", __DIR__."/");
$pathToVendor = str_replace("tests/requests", "vendor", $pathToVendor."/");
require_once($pathToVendor.'/autoload.php');


class GUIDisplayerRequestTest extends PHPUnit_Framework_TestCase
{

    private $gUIDisplayerRequest;
    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;

    protected function setUp()
    {
        parent::setUp();
        
        $this->eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        $this->eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->eventMock = $this->getMockBuilder('Event')
        ->disableOriginalConstructor()->getMock();
        
        $this->gUIDisplayerRequest = new GUIDisplayerRequest();
    }

    protected function tearDown()
    {
        // TODO Auto-generated GUIDisplayerRequestTest::tearDown()
        $this->gUIDisplayerRequest = null;
        
        parent::tearDown();
    }

    public function testExecuteDisplayedEvents()
    {
        try{
            $this->gUIDisplayerRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, ["stat" => "events"]);
            $this->eventDBHandlerMock->expects($this->once())
            ->method('getEventsFromTo')
            ->willReturn("");
            $this->gUIDisplayerRequest->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testExecuteDisplayedTrades()
    {
        try{
            $this->gUIDisplayerRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, ["stat" => "trades"]);
            $this->tradeDBHandlerMock->expects($this->once())
            ->method('getTradesFromTo')
            ->willReturn("");
            $this->gUIDisplayerRequest->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
}

