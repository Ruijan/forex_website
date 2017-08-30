<?php

require_once(str_replace("tests", "src", __DIR__."/").'HTMLDisplayer.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');

/**
 * HTMLDisplayer test case.
 */
class SimpleHTMLDisplayerTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var HTMLDisplayer
     */
    private $htmlDisplayer;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->htmlDisplayer = new SimpleHTMLDisplayer();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated HTMLDisplayerTest::tearDown()
        $this->htmlDisplayer = null;
        
        parent::tearDown();
    }
    
    public function testConstructSuccess(){
        $displayMode = DisplayMode::TABLE;
        assert((new SimpleHTMLDisplayer($displayMode))->getDisplayMode() == $displayMode);
    }
    
    public function testConstructFailureWithBadTypeExpectThrow(){
        $displayMode = "35";
        $this->expectExceptionMessage("Wrong type for displayMode. Expected int got: ".gettype($displayMode));
        new SimpleHTMLDisplayer($displayMode);
    }
    
    public function testSimpleDisplayTrade(){
        $stringDisplay = $this->createSimpleTradeString();
        $trade = new Trade(555, new DateTime("04-08-2017 20:00:00"), "EUR_USD");
        $trade->setId(1);
        $trade->fillMarketInfo(0.00050, -0.00010);
        $trade->predict(1, 0.75);
        $trade->open(new DateTime("2017-08-04 20:05:00"));
        $trade->close(0.56, 0.12, new DateTime("2017-08-04 21:00:00"));
        $displayed = $this->htmlDisplayer->displayTrade($trade);
        assert($displayed == $stringDisplay, "Wrong string");
    }
    private function createSimpleTradeString()
    {
        $idsStr = "1;555;";
        $dates = "2017-08-04 20:00:00;2017-08-04 20:05:00;2017-08-04 21:00:00;";
        $marketInfos = "0.00050;-0.00010;";
        $prediction = "1;0.75;";
        $gain = "0.56;0.12;";
        $state = "4";
        $stringDisplay = $idsStr.$dates.$marketInfos.$prediction.$gain.$state;
        return $stringDisplay;
    }

    
    public function testTableDisplayTrade(){
        $this->htmlDisplayer->setDisplayMode(DisplayMode::TABLE);
        $stringDisplay = "<td class='id'>1</td><td class='id_db_event'>555</td>".
            "<td class='creation_time'>2017-08-04 20:00:00</td>".
            "<td class='open_time'>2017-08-04 20:05:00</td>".
            "<td class='close_time'>2017-08-04 21:00:00</td>".
            "<td class='market'>0.00050</td><td class='market'>-0.00010</td>".
            "<td class='prediction'>1</td><td class='p_prediction'>0.75</td>".
            "<td class='gain'>0.56</td><td class='commission'>0.12</td>".
            "<td class='currency'>EUR_USD</td><td class='state Close won'>Close</td>";
        $trade = new Trade(555, new DateTime("04-08-2017 20:00:00"), "EUR_USD");
        $trade->setId(1);
        $trade->fillMarketInfo(0.00050, -0.00010);
        $trade->predict(1, 0.75);
        $trade->open(new DateTime("2017-08-04 20:05:00"));
        $trade->close(0.56, 0.12, new DateTime("2017-08-04 21:00:00"));
        $displayed = $this->htmlDisplayer->displayTrade($trade);
        assert($displayed == $stringDisplay, "Wrong string");
    }
    
    public function testSimpleDisplayEvent(){
        $stringDisplay = "1;555;888;2017-08-04 20:00:00;2017-08-04 20:05:00;235;325;1865;1";
        $event = new Event(555, 888, false, 2, new DateTime("04-08-2017 20:00:00"), 325, -300, 1865);
        $event->setId(1);
        $event->update(235, new DateTime("2017-08-04 20:05:00"));
        $displayed = $this->htmlDisplayer->displayEvent($event);
        assert($displayed == $stringDisplay, "Wrong string");
    }
    
    public function testTableDisplayEvent(){
        $this->htmlDisplayer->setDisplayMode(DisplayMode::TABLE);
        $stringDisplay = "<td class='id'>1</td><td class='id_event'>555</td><td class='id_news'>888</td>".
            "<td class='announced'>2017-08-04 20:00:00</td><td class='real'>2017-08-04 20:05:00</td>".
            "<td class='actual'>235</td><td class='previous'>325</td><td class='next_event'>1865</td>".
            "<td class='state Passed'>Passed</td>";
        $event = new Event(555, 888, false, 2, new DateTime("04-08-2017 20:00:00"), 325, -300, 1865);
        $event->setId(1);
        $event->update(235, new DateTime("2017-08-04 20:05:00"));
        $displayed = $this->htmlDisplayer->displayEvent($event);
        assert($displayed == $stringDisplay, "Wrong string");
    }
}

