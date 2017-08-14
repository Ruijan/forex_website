<?php
use src\requests\PredictTradeRequest;

$path = str_replace("tests\\requests", "src", __DIR__."/");
$path = str_replace("tests/requests", "src", $path."/");

require_once(str_replace("tests", "src", __DIR__."/").'PredictTradeRequest.php');
require_once($path.'EventDBHandler.php');
require_once($path.'EventParser.php');
require_once($path.'TradeDBHandler.php');
require_once($path.'Trade.php');

class PredictTradeRequestTest extends PHPUnit_Framework_TestCase
{

    private $predictTradeRequest;

    protected function setUp()
    {
        parent::setUp();
        $this->eventParserMock = $this->getMockBuilder('EventParser')
        ->disableOriginalConstructor()->getMock();
        $this->eventDBHandlerMock = $this->getMockBuilder('EventDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeDBHandlerMock = $this->getMockBuilder('TradeDBHandler')
        ->disableOriginalConstructor()->getMock();
        $this->tradeMock = $this->getMockBuilder('trade')
        ->disableOriginalConstructor()->getMock();
        $this->predictTradeRequest = new PredictTradeRequest();
    }

    protected function tearDown()
    {
        // TODO Auto-generated PredictTradeRequestTest::tearDown()
        $this->predictTradeRequest = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function testExecuteSuccess(){
        try{
            $parameters = ["trade_id" => 25, "prediction" => 0, "probability_prediction" => 0.75];
            $this->predictTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
                $this->eventParserMock, $parameters);
            
            $this->tradeDBHandlerMock->expects($this->once())
            ->method('getTradeByID')
            ->willReturn($this->tradeMock);
            $this->tradeMock->expects($this->once())
            ->method('predict')
            ->willReturn($this->returnArgument(0));
            $this->tradeDBHandlerMock->expects($this->once())
            ->method('predictTrade')
            ->willReturn($this->returnArgument(0));
            
            $this->predictTradeRequest->execute();
            
            assert(true);
        }
        catch(Exception $e){
            throw new ErrorException($e->getMessage());
            assert(false);
        }
    }
    
    public function testExecuteWithWrongParametersNumberAsRequestShouldThrow(){
        $this->expectExceptionMessage("Ill-formed request: missing parameters");
        $parameters = ["trade_id" => 25, "dv_p_tm5" => 0, "probability_prediction" => 0.75];
        $this->predictTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->predictTradeRequest->execute();
    }
    
    public function testExecuteWithWrongParametersTypeAsRequestShouldThrow(){
        $parameters = ["trade_id" => 0.25, "prediction" => 0, "probability_prediction" => 0.75];
        $this->expectExceptionMessage("Invalid Request: bad parameters type");
        $this->predictTradeRequest->init($this->tradeDBHandlerMock, $this->eventDBHandlerMock,
            $this->eventParserMock, $parameters);
        $this->predictTradeRequest->execute();
    }
}

