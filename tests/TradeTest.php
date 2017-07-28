<?php
require_once '../Trade.php';
/**
 * Trade test case.
 */
class TradeTest extends PHPUnit_Framework_TestCase
{
    private $trade;

    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated TradeTest::setUp()
        $this->trade = new Trade();
        
    }

    protected function tearDown()
    {
        // TODO Auto-generated TradeTest::tearDown()
        $this->trade = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function test__emptyConstructor()
    {
        $this->trade->__construct();
        assert(!$this->trade->isInitialized());
    }
    
    public function test__initialization()
    {
        $this->trade->__construct();
        $this->trade->initialize(1,50);
        assert($this->trade->isInitialized());
    }
}

