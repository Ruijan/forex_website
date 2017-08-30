<?php

require_once(str_replace("tests", "src", __DIR__."/").'CollectEventsRequest.php');;

$pathToVendor = str_replace("tests\\requests", "vendor", __DIR__."/");
$pathToVendor = str_replace("tests/requests", "vendor", $pathToVendor."/");
require_once($pathToVendor.'/autoload.php');

use src\requests\CollectEventsRequest;

class CollectEventsRequestTest extends PHPUnit_Framework_TestCase
{

    private $collectEvents;
    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;
    private $eventMock;


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
        $this->collectEvents = new CollectEventsRequest();
    }

    protected function tearDown()
    {
        $this->collectEvents = null;
        
        parent::tearDown();
    }

    public function testExecuteNoEventInDB()
    {
        try{
            $this->collectEvents->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, []);
            $this->eventParserMock->expects($this->once())
            ->method('retrieveTableOfEvents')
            ->willReturn($this->returnArgument(0));
            $this->eventParserMock->expects($this->once())
            ->method('createEventsFromTable')
            ->willReturn($this->returnArgument(0));
            $this->eventParserMock->expects($this->once())
            ->method('getEvents')
            ->willReturn([$this->eventMock]);
            $this->eventDBHandlerMock->expects($this->once())
            ->method('getEventsFromTo')
            ->willReturn([]);
            $this->eventDBHandlerMock->expects($this->once())
            ->method('tryAddingEvent')
            ->willReturn($this->returnArgument(0));
            
            $this->collectEvents->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    public function testAddingTradeFromGroupOfEventsFailure(){
        $allEvents = $this->generateDummyEvents();
        $allEvents[0]->update(325, new DateTime("2017-08-03 12:31:00"));
        $allEvents[1]->update(125, new DateTime("2017-08-03 12:35:00"));
        $allEvents[2]->update(220, new DateTime("2017-08-03 12:36:00"));
        $this->collectEvents->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, []);
        $this->tradeDBHandlerMock->expects($this->exactly(0))
        ->method('tryAddingTrade')
        ->willReturn($this->returnArgument(0));
        $this->collectEvents->addTradeToDbFromEvents($allEvents[3], $allEvents);
    }
    
    public function testAddingTradeFromPendingGroupOfEventsFailure(){
        $allEvents = $this->generateDummyEvents();
        $allEvents[0]->update(325, new DateTime("2017-08-03 12:31:00"));
        $allEvents[2]->update(220, new DateTime("2017-08-03 12:36:00"));
        $this->collectEvents->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, []);
        $this->tradeDBHandlerMock->expects($this->exactly(0))
        ->method('tryAddingTrade')
        ->willReturn($this->returnArgument(0));
        $this->collectEvents->addTradeToDbFromEvents($allEvents[2], $allEvents);
    }
    
    public function testAddingTradeFromGroupOfEventsSuccess(){
        $allEvents = $this->generateDummyEvents();
        $allEvents[0]->update(325, new DateTime("2017-08-03 12:31:00"));
        $allEvents[1]->update(125, new DateTime("2017-08-03 12:35:00"));
        $allEvents[2]->update(220, new DateTime("2017-08-03 12:36:00"));
        $this->collectEvents->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, []);
        $this->tradeDBHandlerMock->expects($this->once())
        ->method('tryAddingTrade')
        ->willReturn($this->returnArgument(0));
        $this->collectEvents->addTradeToDbFromEvents($allEvents[2], $allEvents);
    }
    private function generateDummyEvents()
    {
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false,
            2, new DateTime("2017-08-03 12:30:00"), 0.01, -300, 0);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false,
            2, new DateTime("2017-08-03 12:30:00"), 0.01, 0, 0);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false,
            2, new DateTime("2017-08-03 12:30:00"), 0.01, 0, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false,
            2, new DateTime("2017-08-03 13:30:00"), 0.01, -3600, 500);
        return $allEvents;
    }

    
    public function testExecuteSuccess(){
        try{
            $eventMock = $this->getMockBuilder('Event')
            ->disableOriginalConstructor()->getMock();
            $anId = 25;
            
            $this->collectEvents->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, []);
            $this->eventParserMock->expects($this->once())
            ->method('retrieveTableOfEvents')
            ->willReturn($this->returnArgument(0));
            $this->eventParserMock->expects($this->once())
            ->method('createEventsFromTable')
            ->willReturn($this->returnArgument(0));
            $this->eventParserMock->expects($this->once())
            ->method('getEvents')
            ->willReturn([$this->eventMock]);
            $this->eventDBHandlerMock->expects($this->once())
            ->method('getEventsFromTo')
            ->willReturn([$eventMock]);
            $this->eventDBHandlerMock->expects($this->once())
            ->method('tryAddingEvent')
            ->willReturn($this->returnArgument(0));
            $eventMock->expects($this->once())
            ->method('getId')
            ->willReturn($anId);
            $this->eventMock->expects($this->any())
            ->method('getPreviousEvent')
            ->willReturn(-50);
            $this->eventMock->expects($this->any())
            ->method('getNextEvent')
            ->willReturn(50);
            $this->eventMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($anId);
            $eventMock->expects($this->once())
            ->method('getState')
            ->willReturn(0);
            $this->eventMock->expects($this->once())
            ->method('getState')
            ->willReturn(1);
            $this->eventDBHandlerMock->expects($this->once())
            ->method('updateEvent')
            ->willReturn($this->returnArgument(0));
            $this->tradeDBHandlerMock->expects($this->once())
            ->method('tryAddingTrade')
            ->willReturn($this->returnArgument(0));
            
            $this->collectEvents->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
}

