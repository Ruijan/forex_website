<?php
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');

require_once(str_replace("tests", "src", __DIR__."/").'RequestHandler.php');

require_once(str_replace("tests", "src", __DIR__."/").'EventDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'EventParser.php');

require_once(str_replace("tests", "src", __DIR__."/").'TradeDBHandler.php');

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
    
    public function test__getRequestTypeFromValidString_UpdateMarket(){
        assert(RequestHandler::getRequestTypeFromstring("current_trades") == Request::CURRENT_TRADES);
        assert(RequestHandler::getRequestTypeFromstring("next_events") == Request::NEXT_EVENTS);
        assert(RequestHandler::getRequestTypeFromstring("predictable_trade") == Request::PREDICTABLE_TRADE);
        assert(RequestHandler::getRequestTypeFromstring("next_action") == Request::NEXT_ACTION);
        assert(RequestHandler::getRequestTypeFromstring("update_market") == Request::UPDATE_MARKET);
        assert(RequestHandler::getRequestTypeFromstring("predict_trade") == Request::PREDICT_TRADE);
        assert(RequestHandler::getRequestTypeFromstring("open_trade") == Request::OPEN_TRADE);
        assert(RequestHandler::getRequestTypeFromstring("close_trade") == Request::CLOSE_TRADE);
        assert(RequestHandler::getRequestTypeFromstring("cancel_trade") == Request::CANCEL_TRADE);
        
    }
    
    public function test__getRequestTypeFromInvalidStringShouldThrow(){
        $this->expectExceptionMessage("Invalid Request");
        RequestHandler::getRequestTypeFromstring("update_markets");
    }
    
    public function test__construct(){
        $request = RequestHandler::getRequestTypeFromstring("update_market");
        $this->requestHandler = new RequestHandler($request);
        assert($this->requestHandler->getRequest() == $request);
    }
    
    public function test__setBadRequestTypeShouldThrow(){
        $request = "update_market";
        $this->expectExceptionMessage("Wrong type for request. Expected int got: ".gettype($request));
        $this->requestHandler = new RequestHandler($request);
    }
    
    public function test__setBadRequestShouldThrow(){
        $this->expectExceptionMessage("Invalid Request");
        $this->requestHandler = new RequestHandler(10);
    }
    
    public function test__checkBadInitWhenUpdateMarket(){
        $this->requestHandler = new RequestHandler(Request::UPDATE_MARKET);
        assert($this->requestHandler->isCorrectlyInitialized() == false);
    }
    
    
    private function generateDummyEvents()
    {
        $all_events = [];
        $all_events[] = new Event(254, 65954, new DateTime("2017-08-03 00:30:00"), 0.01, 500);
        $all_events[] = new Event(254, 65954, new DateTime("2017-08-05 12:30:00"), 0.01, 500);
        $all_events[] = new Event(254, 65954, new DateTime("2017-08-02 00:30:00"), 0.01, 500);
        $all_events[] = new Event(254, 65954, new DateTime("2017-08-06 00:30:00"), 0.01, 500);
        $all_events[] = new Event(254, 65954, new DateTime("2017-08-04 00:30:00"), 0.01, 500);
        return $all_events;
    }
}

