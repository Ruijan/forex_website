<?php

$pathToSrc = str_replace("tests", "src", __DIR__."/");
require_once($pathToSrc.'connect.php');
require_once($pathToSrc.'RequestHandlerBuilder.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

class RequestHandlerBuilderTest extends PHPUnit_Framework_TestCase
{

    private $requestHandlerBuilder;

    protected function setUp()
    {
        parent::setUp();
        
        
        $this->requestHandlerBuilder = new RequestHandlerBuilder(/* parameters */);
    }

    protected function tearDown()
    {
        $this->requestHandlerBuilder = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function testMakeRequestHandlerWithRequest()
    {
        try{
            $mysqli = connect_database();
            $request = "fetch_events";
            $requestHandler = $this->requestHandlerBuilder->makeRequestHandlerWithRequest(
                $request, 
                [],
                $mysqli);
            assert(true);
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
            assert(false);
        }
    }
}

