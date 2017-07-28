<?php

require_once('db_events.php');
/**
 * Event test case.
 */
class EventTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Event
     */
    private $event;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated EventTest::setUp()
        
        $this->event = new Event(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated EventTest::tearDown()
        $this->event = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Event->__construct()
     */
    public function test__construct()
    {
        $now = new DateTime('now');
        $this->event->__construct(/* parameters */);
        assert(abs($this->event->t_a->diff($now)->s) < 1);
        assert(abs($this->event->t_u->diff($now)->s) < 1);
        assert(abs($this->event->t_r->diff($now)->s) < 1);
        assert($this->event->t_a->getTimezone()->getName() == "UTC");
        assert($this->event->t_r->getTimezone()->getName() == "UTC");
        assert($this->event->t_u->getTimezone()->getName() == "UTC");
    }

    /**
     * Tests Event->fillFromPost()
     */
    public function testFillFromPost()
    {
        $_POST["news_id"] = 955;
        $_POST["event_id"] = 99995;
        $_POST["t_a"] = "04/03/1991 10:00:05";
        $_POST["t_r"] = "04/03/1991 10:01:22";
        $_POST["actual"] = 0.5;
        $_POST["previous"] = 0.6;
        $_POST["dv_p_tm5"] = 0.00005;
        $_POST["dv_p_t0"] = 0.00050;
        $_POST["prediction"] = 0;
        $_POST["p_proba"] = 0.74;
        $_POST["label"] = 1;
        $_POST["av_success"] = 32.5;
        $_POST["gain"] = 0.46;
        $_POST["state"] = 5;

        $this->event->fillFromPost(/* parameters */);
        assert($_POST["news_id"] == $this->event->news_id);
    }

    /**
     * Tests Event->fillFromDB()
     */
    public function testFillFromDB()
    {
        // TODO Auto-generated EventTest->testFillFromDB()
        $this->markTestIncomplete("fillFromDB test not implemented");
        
        $this->event->fillFromDB(/* parameters */);
    }

    /**
     * Tests Event->tryAddingEventToDB()
     */
    public function testTryAddingEventToDB()
    {
        // TODO Auto-generated EventTest->testTryAddingEventToDB()
        $this->markTestIncomplete("tryAddingEventToDB test not implemented");
        
        $this->event->tryAddingEventToDB(/* parameters */);
    }

    /**
     * Tests Event->isEventInDB()
     */
    public function testIsEventInDB()
    {
        // TODO Auto-generated EventTest->testIsEventInDB()
        $this->markTestIncomplete("isEventInDB test not implemented");
        
        $this->event->isEventInDB(/* parameters */);
    }

    /**
     * Tests Event->addEventToDB()
     */
    public function testAddEventToDB()
    {
        // TODO Auto-generated EventTest->testAddEventToDB()
        $this->markTestIncomplete("addEventToDB test not implemented");
        
        $this->event->addEventToDB(/* parameters */);
    }

    /**
     * Tests Event->modifyInDB()
     */
    public function testModifyInDB()
    {
        // TODO Auto-generated EventTest->testModifyInDB()
        $this->markTestIncomplete("modifyInDB test not implemented");
        
        $this->event->modifyInDB(/* parameters */);
    }

    /**
     * Tests Event::getEventFromID()
     */
    public function testGetEventFromID()
    {
        // TODO Auto-generated EventTest::testGetEventFromID()
        $this->markTestIncomplete("getEventFromID test not implemented");
        
        Event::getEventFromID(/* parameters */);
    }

    /**
     * Tests Event->simpleDisplay()
     */
    public function testSimpleDisplay()
    {
        // TODO Auto-generated EventTest->testSimpleDisplay()
        $this->markTestIncomplete("simpleDisplay test not implemented");
        
        $this->event->simpleDisplay(/* parameters */);
    }

    /**
     * Tests Event->display()
     */
    public function testDisplay()
    {
        // TODO Auto-generated EventTest->testDisplay()
        $this->markTestIncomplete("display test not implemented");
        
        $this->event->display(/* parameters */);
    }

    /**
     * Tests Event->displayAsRow()
     */
    public function testDisplayAsRow()
    {
        // TODO Auto-generated EventTest->testDisplayAsRow()
        $this->markTestIncomplete("displayAsRow test not implemented");
        
        $this->event->displayAsRow(/* parameters */);
    }

    /**
     * Tests Event::displayHeadersAsRow()
     */
    public function testDisplayHeadersAsRow()
    {
        // TODO Auto-generated EventTest::testDisplayHeadersAsRow()
        $this->markTestIncomplete("displayHeadersAsRow test not implemented");
        
        Event::displayHeadersAsRow(/* parameters */);
    }

    /**
     * Tests Event::displayShortHeadersAsRow()
     */
    public function testDisplayShortHeadersAsRow()
    {
        // TODO Auto-generated EventTest::testDisplayShortHeadersAsRow()
        $this->markTestIncomplete("displayShortHeadersAsRow test not implemented");
        
        Event::displayShortHeadersAsRow(/* parameters */);
    }
}

