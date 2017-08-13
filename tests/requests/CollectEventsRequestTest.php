<?php

$path = str_replace("tests\\requests", "src", __DIR__."/");
$path = str_replace("tests/requests", "src", $path."/");

require_once(str_replace("tests", "src", __DIR__."/").'CollectEventsRequest.php');
require_once($path.'EventDBHandler.php');
require_once($path.'EventParser.php');
require_once($path.'TradeDBHandler.php');
require_once($path.'Event.php');

$pathToVendor = str_replace("tests\\requests", "vendor", __DIR__."/");
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
        
        // TODO Auto-generated CollectEventsRequestTest::setUp()
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
        // TODO Auto-generated CollectEventsRequestTest::tearDown()
        $this->collectEvents = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function test__Execute()
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
}

