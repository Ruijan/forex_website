<?php

require_once(str_replace("tests", "src", __DIR__."/").'HTMLDisplayer.php');

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

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function test__constructSuccess(){
        $displayMode = DisplayMode::TABLE;
        assert((new SimpleHTMLDisplayer($displayMode))->getDisplayMode() == $displayMode);
    }
    
    public function test__constructFailureWithBadTypeExpectThrow(){
        $displayMode = "35";
        $this->expectExceptionMessage("Wrong type for displayMode. Expected int got: ".gettype($displayMode));
        $displayer = new SimpleHTMLDisplayer($displayMode);
    }
    
    public function test__simpleDisplayTrade(){
        $string_display = "1;555;2017-08-04 20:00:00;2017-08-04 20:05:00;2017-08-04 21:00:00;0.00050;-0.00010;1;0.75;0.56;0.12;4";
        $trade = new Trade(555, new DateTime("04-08-2017 20:00:00"));
        $trade->setId(1);
        $trade->fillMarketInfo(0.00050, -0.00010);
        $trade->predict(1, 0.75);
        $trade->open(new DateTime("2017-08-04 20:05:00"));
        $trade->close(0.56, 0.12, new DateTime("2017-08-04 21:00:00"));
        $displayed = $this->htmlDisplayer->displayTrade($trade);
        assert($displayed == $string_display, "Wrong string");
    }
    
    public function test__tableDisplayTrade(){
        $this->htmlDisplayer->setDisplayMode(DisplayMode::TABLE);
        $string_display = "<td class='id'>1</td><td class='id_db_event'>555</td>".
            "<td class='creation_time'>2017-08-04 20:00:00</td>".
            "<td class='open_time'>2017-08-04 20:05:00</td>".
            "<td class='close_time'>2017-08-04 21:00:00</td>".
            "<td class='market'>0.00050</td><td class='market'>-0.00010</td>".
            "<td class='prediction'>1</td><td class='p_prediction'>0.75</td>".
            "<td class='gain'>0.56</td><td class='commission'>0.12</td><td class='state'>Close</td>";
        $trade = new Trade(555, new DateTime("04-08-2017 20:00:00"));
        $trade->setId(1);
        $trade->fillMarketInfo(0.00050, -0.00010);
        $trade->predict(1, 0.75);
        $trade->open(new DateTime("2017-08-04 20:05:00"));
        $trade->close(0.56, 0.12, new DateTime("2017-08-04 21:00:00"));
        $displayed = $this->htmlDisplayer->displayTrade($trade);
        assert($displayed == $string_display, "Wrong string");
    }
    
    public function test__simpleDisplayEvent(){
        $string_display = "1;555;888;2017-08-04 20:00:00;2017-08-04 20:05:00;235;325;1865;1";
        $event = new Event(555, 888, new DateTime("04-08-2017 20:00:00"), 325, 1865);
        $event->setId(1);
        $event->update(235, new DateTime("2017-08-04 20:05:00"));
        $displayed = $this->htmlDisplayer->displayEvent($event);
        assert($displayed == $string_display, "Wrong string");
    }
    
    public function test__tableDisplayEvent(){
        $this->htmlDisplayer->setDisplayMode(DisplayMode::TABLE);
        $string_display = "<td class='id'>1</td><td class='id_event'>555</td><td class='id_news'>888</td>".
            "<td class='announced'>2017-08-04 20:00:00</td><td class='real'>2017-08-04 20:05:00</td>".
            "<td class='actual'>235</td><td class='previous'>325</td><td class='next_event'>1865</td>".
            "<td class='state'>Passed</td>";
        $event = new Event(555, 888, new DateTime("04-08-2017 20:00:00"), 325, 1865);
        $event->setId(1);
        $event->update(235, new DateTime("2017-08-04 20:05:00"));
        $displayed = $this->htmlDisplayer->displayEvent($event);
        assert($displayed == $string_display, "Wrong string");
    }
}

