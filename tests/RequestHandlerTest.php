<?php
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');

require_once(str_replace("tests", "src", __DIR__."/").'RequestHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'EventDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'EventParser.php');
require_once(str_replace("tests", "src", __DIR__."/").'TradeDBHandler.php');

require_once(str_replace("tests", "src/requests", __DIR__."/").'CollectEventsRequest.php');
require_once(str_replace("tests", "src/requests", __DIR__."/").'UpdateMarketRequest.php');
require_once(str_replace("tests", "src/requests", __DIR__."/").'ForexRequest.php');


require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

/**
 * RequestHandler test case.
 */
class RequestHandlerTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->requestHandler = new RequestHandler();
        // TODO Auto-generated RequestHandlerTest::setUp()

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated RequestHandlerTest::tearDown()
        $this->requestHandler = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function testgetRequestTypeFromValidString(){
        $this->requestHandler = new RequestHandler();
        assert($this->requestHandler->getRequestTypeFromstring("current_trades") == Request::CURRENT_TRADES);
        assert($this->requestHandler->getRequestTypeFromstring("next_events") == Request::NEXT_EVENTS);
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
        $this->expectExceptionMessage("Wrong number of request handlers. Got 1 expected 2");
        $this->requestHandler->setRequestHandlers([$marketRequestMock]);
    }
    
    public function testSettingRequestsArrayWithWrongTypeShouldThrow(){
        $marketRequestMock = $this->getMockBuilder('UpdateMarketRequest')
        ->disableOriginalConstructor()->getMock();
        $forexRequestMock = $this->getMockBuilder('ForexRequest')
        ->disableOriginalConstructor()->getMock();
        $this->expectExceptionMessage("Wrong type of request handler.");
        $this->requestHandler->setRequestHandlers([$marketRequestMock, $forexRequestMock]);
    }
    
    public function testSettingRequestsArraySuccess(){
        $marketRequestMock = $this->getMockBuilder('UpdateMarketRequest')
        ->disableOriginalConstructor()->getMock();
        $eventsRequestMock = $this->getMockBuilder('CollectEventsRequest')
        ->disableOriginalConstructor()->getMock();
        
        $handlers = [$eventsRequestMock, $marketRequestMock];
        $this->requestHandler->setRequestHandlers($handlers);
        $requestHandlers = $this->requestHandler->getRequestHandlers();
        assert($requestHandlers[Request::UPDATE_MARKET] == $handlers[1]);
        assert($requestHandlers[Request::FETCH_EVENTS] == $handlers[0]);
    }
    
    public function testExecuteSucces(){
        try{
            $eventParserMock = $this->getMockBuilder('EventParser')
            ->disableOriginalConstructor()->getMock();
            $eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
            ->disableOriginalConstructor()->getMock();
            $tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
            ->disableOriginalConstructor()->getMock();
            $marketRequestMock = $this->getMockBuilder('UpdateMarketRequest')
            ->disableOriginalConstructor()->getMock();
            $eventsRequestMock = $this->getMockBuilder('CollectEventsRequest')
            ->disableOriginalConstructor()->setMethods(array('execute'))->getMock();
            
            $this->requestHandler->init($tradeDBHandlerMock, $eventDBHandlerMock, $eventParserMock);
            $this->requestHandler->setRequest(Request::FETCH_EVENTS);
            $handlers = [$eventsRequestMock, $marketRequestMock];
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
}

