<?php
use src\requests\UpdateMarketRequest;

require_once(str_replace("tests", "src", __DIR__."/").'UpdateMarketRequest.php');

$pathToVendor = str_replace("tests\\requests", "vendor", __DIR__."/");
$pathToVendor = str_replace("tests/requests", "vendor", $pathToVendor."/");
require_once($pathToVendor.'/autoload.php');


/**
 * UpdateMarketRequest test case.
 */
class UpdateMarketRequestTest extends PHPUnit_Framework_TestCase
{

    private $eventParserMock;
    private $eventDBHandlerMock;
    private $tradeDBHandlerMock;
    private $tradeMock;

    private $updateMarketRequest;

    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated UpdateMarketRequestTest::setUp()
        $this->eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        $this->eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeMock = $this->getMockBuilder('Trade')
        ->disableOriginalConstructor()->getMock();
        $this->updateMarketRequest = new UpdateMarketRequest(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated UpdateMarketRequestTest::tearDown()
        $this->updateMarketRequest = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function testValidateValidRequest(){
        try{
            $parameters = ["dv_p_tm5" => 0.0050, "dv_p_t0" => 0.0100, "currency" => "EUR_USD"];
            
            $this->updateMarketRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, $parameters);
            $this->updateMarketRequest->validateRequest();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testValidateEmptyRequestShouldThrow(){
        $this->expectExceptionMessage("Ill-formed request: missing parameters");
        $this->updateMarketRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, []);
        $this->updateMarketRequest->validateRequest();
    }
    
    public function testExecuteWithBadRequestShouldThrow(){
        $parameters = ["dv_p_tm5" => "lala", "dv_p_t0" => "lala", "currency" => "EURUSD"];
        $this->expectExceptionMessage("Invalid Request: bad parameters type.".
            " Got double double and string. Expected double double and string.");
        $this->updateMarketRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->updateMarketRequest->execute();
    }
    
    public function testExecuteWithGoodRequestSuccess(){
        try{
            $this->tradeDBHandlerMock->expects($this->once())
            ->method('getTradesFromTo')
            ->willReturn([$this->tradeMock]);
            $this->tradeMock->expects($this->once())
            ->method('fillMarketInfo')
            ->willReturn($this->returnArgument(0));
            $this->tradeDBHandlerMock->expects($this->once())
            ->method('fillTradeWithMarketInfo')
            ->willReturn($this->returnArgument(0));
            
            $parameters = ["dv_p_tm5" => 0.0050, "dv_p_t0" => 0.0100, "currency" => "EUR_USD"];
          
            $this->updateMarketRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock, 
                $this->eventParserMock, $parameters);
            
            $this->updateMarketRequest->execute();
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
        
    }
}

