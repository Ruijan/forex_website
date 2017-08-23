<?php

$pathToSrc = str_replace("tests", "src", __DIR__."/");
require_once($pathToSrc.'connect.php');

require_once($pathToSrc.'RequestHandler.php');

require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

/**
 * RequestHandler test case.
 */
class RequestHandlerTest extends PHPUnit_Framework_TestCase
{

    private $requestHandler;
    private $marketRequestMock;
    private $eventsRequestMock;
    private $predictTradeMock;
    private $predictableTradeMock;
    private $openTradeMock;
    private $closeTradeMock;
    private $cancelTradeMock;
    private $nextActionMock;

    protected function setUp()
    {
        parent::setUp();
        $this->marketRequestMock = $this->getMockBuilder('UpdateMarketRequest')
        ->disableOriginalConstructor()->getMock();
        $this->eventsRequestMock = $this->getMockBuilder('CollectEventsRequest')
        ->disableOriginalConstructor()->getMock();
        $this->predictTradeMock = $this->getMockBuilder('PredictTradeRequest')
        ->disableOriginalConstructor()->getMock();
        $this->predictableTradeMock = $this->getMockBuilder('PredictableTradesRequest')
        ->disableOriginalConstructor()->getMock();
        $this->openTradeMock = $this->getMockBuilder('OpenTradeRequest')
        ->disableOriginalConstructor()->getMock();
        $this->closeTradeMock = $this->getMockBuilder('CloseTradeRequest')
        ->disableOriginalConstructor()->getMock();
        $this->cancelTradeMock = $this->getMockBuilder('CancelTradeRequest')
        ->disableOriginalConstructor()->getMock();
        $this->nextActionMock = $this->getMockBuilder('NextActionRequest')
        ->disableOriginalConstructor()->getMock();
        $this->requestHandler = new RequestHandler();
        // TODO Auto-generated RequestHandlerTest::setUp()

    }

    protected function tearDown()
    {
        // TODO Auto-generated RequestHandlerTest::tearDown()
        $this->requestHandler = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function testgetRequestTypeFromValidString(){
        $this->requestHandler = new RequestHandler();
        assert($this->requestHandler->getRequestTypeFromstring("fetch_events") == Request::FETCH_EVENTS);
        assert($this->requestHandler->getRequestTypeFromstring("predictable_trade") == Request::PREDICTABLE_TRADE);
        assert($this->requestHandler->getRequestTypeFromstring("next_action") == Request::NEXT_ACTION);
        assert($this->requestHandler->getRequestTypeFromstring("update_market") == Request::UPDATE_MARKET);
        assert($this->requestHandler->getRequestTypeFromstring("predict_trade") == Request::PREDICT_TRADE);
        assert($this->requestHandler->getRequestTypeFromstring("open_trade") == Request::OPEN_TRADE);
        assert($this->requestHandler->getRequestTypeFromstring("close_trade") == Request::CLOSE_TRADE);
        assert($this->requestHandler->getRequestTypeFromstring("cancel_trade") == Request::CANCEL_TRADE);
    }
    
    public function testGetRequestTypeFromInvalidStringShouldThrow(){
        $this->expectExceptionMessage("Invalid Request");
        $this->requestHandler->getRequestTypeFromstring("update_markets");
    }
    
    public function testConstruct(){
        $request = $this->requestHandler->getRequestTypeFromstring("update_market");
        $this->requestHandler->setRequest($request);
        assert($this->requestHandler->getRequest() == $request);
    }
    
    public function testSetBadRequestTypeShouldThrow(){
        $request = "update_market";
        $this->expectExceptionMessage("Wrong type for request. Expected int got: ".gettype($request));
        $this->requestHandler->setRequest($request);
    }
    
    public function testSetBadRequestShouldThrow(){
        $this->expectExceptionMessage("Invalid Request");
        $this->requestHandler->setRequest(10);
    }
    
    public function testCheckBadInitWhenUpdateMarket(){
        $this->requestHandler->setRequest(Request::UPDATE_MARKET);
        assert($this->requestHandler->isCorrectlyInitialized() == false);
    }
    
    public function testCheckBadInitWithEmptyRequest(){
        $eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        
        $this->requestHandler->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock);
        assert($this->requestHandler->isCorrectlyInitialized() == false);
    }
    
    public function testSettingRequestsArrayWithWrongSizeShouldThrow(){
        $marketRequestMock = $this->getMockBuilder('UpdateMarketRequest')
        ->disableOriginalConstructor()->getMock();
        $this->expectExceptionMessage("Wrong number of request handlers. Got 1 expected 8");
        $this->requestHandler->setRequestHandlers([$marketRequestMock]);
    }
    
    public function testSettingRequestsArrayWithWrongTypeShouldThrow(){
        $forexRequestMock = $this->getMockBuilder('ForexRequest')
        ->disableOriginalConstructor()->getMock();
        $this->expectExceptionMessage("Wrong type of request handler: ".get_class($forexRequestMock));
        $handlers = [$this->eventsRequestMock, $this->marketRequestMock, $this->predictTradeMock, 
            $this->predictableTradeMock,
            $this->openTradeMock, $this->closeTradeMock, $this->cancelTradeMock, $forexRequestMock
        ];
        $this->requestHandler->setRequestHandlers($handlers);
    }
    
    public function testSettingRequestsArraySuccess(){
        $handlers = [$this->eventsRequestMock,
            $this->marketRequestMock,
            $this->predictTradeMock,
            $this->predictableTradeMock,
            $this->openTradeMock,
            $this->closeTradeMock,
            $this->cancelTradeMock,
            $this->nextActionMock
        ];
        $this->requestHandler->setRequestHandlers($handlers);
        $requestHandlers = $this->requestHandler->getRequestHandlers();
        assert($requestHandlers[Request::FETCH_EVENTS] == $handlers[0]);
        assert($requestHandlers[Request::UPDATE_MARKET] == $handlers[1]);
        assert($requestHandlers[Request::PREDICT_TRADE] == $handlers[2]);
        assert($requestHandlers[Request::PREDICTABLE_TRADE] == $handlers[3]);
        assert($requestHandlers[Request::OPEN_TRADE] == $handlers[4]);
        assert($requestHandlers[Request::CLOSE_TRADE] == $handlers[5]);
        assert($requestHandlers[Request::CANCEL_TRADE] == $handlers[6]);
        assert($requestHandlers[Request::NEXT_ACTION] == $handlers[7]);
    }
    
    public function testExecuteSucces(){
        try{
            $eventParserMock = $this->getMockBuilder('EventParser')
            ->disableOriginalConstructor()->getMock();
            $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
            ->disableOriginalConstructor()->setMethods(array('createTable', 'doesTableExists'))->getMock();
            $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
            ->disableOriginalConstructor()->setMethods(array('createTable', 'doesTableExists'))->getMock();
            $eventsRequestMock = $this->getMockBuilder('CollectEventsRequest')
            ->disableOriginalConstructor()->setMethods(array('execute'))->getMock();
            
            $eventDBHandlerMock->expects($this->once())
            ->method('createTable')
            ->willReturn($this->returnArgument(0));
            $tradeDBHandlerMock->expects($this->once())
            ->method('createTable')
            ->willReturn($this->returnArgument(0));
            $eventDBHandlerMock->expects($this->once())
            ->method('doesTableExists')
            ->willReturn(True);
            $tradeDBHandlerMock->expects($this->once())
            ->method('doesTableExists')
            ->willReturn(True);
            
            $this->requestHandler->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock);
            $this->requestHandler->setRequest(Request::FETCH_EVENTS);
            $handlers = [$eventsRequestMock, 
                $this->marketRequestMock, 
                $this->predictTradeMock, 
                $this->predictableTradeMock,
                $this->openTradeMock, 
                $this->closeTradeMock, 
                $this->cancelTradeMock, 
                $this->nextActionMock
            ];
            $this->requestHandler->setRequestHandlers($handlers);
            
            $eventsRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn($this->returnArgument(0));
            
            $this->requestHandler->execute();
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testExecuteBadInitialization(){
        $this->expectExceptionMessage("Error in the Initialization");
        $this->requestHandler->execute();
        
    }
}

