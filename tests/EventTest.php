<?php
require_once(str_replace("tests", "src", __DIR__."/").'Event.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');
/**
 * Event test case.
 */
class EventTest extends PHPUnit_Framework_TestCase
{

    private $event;

    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated EventTest::setUp()
        $this->news_id = 255;
        $this->event_id = 68956;
        $this->announced_time = new DateTime("NOW");
        $this->previous = 0.01;
        $this->next_event = 50;
        $this->event = new Event($this->event_id, $this->news_id, $this->announced_time, $this->previous, $this->next_event);
    }

    protected function tearDown()
    {
        // TODO Auto-generated EventTest::tearDown()
        $this->event = null;
        
        parent::tearDown();
    }

    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function test__construct()
    {
        assert($this->event->getNewsId() == $this->news_id, "News id should be equal");
        assert($this->event->getEventId() == $this->event_id, "Event ID should be equal");
        assert($this->event->getAnnouncedTime() == $this->announced_time, "Event Announced time should be equal");
        assert($this->event->getPrevious() == $this->previous, "Event previous value should be equal");
        assert($this->event->getNextEvent() == $this->next_event, "Next event time value should be equal");
    }
    
    public function test_setIdWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for id. Expected int got: ".gettype(0.5));
        $this->event->setId(0.5);
    }
    
    public function test_setEventIdWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for event_id. Expected int got: ".gettype(0.5));
        $this->event->setEventId(0.5);
    }
    
    public function test_setNewsIdWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for news_id. Expected int got: ".gettype(0.5));
        $this->event->setNewsId(0.5);
    }
    
    public function test_setTimeAnnouncedWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for actual_time. Expected DateTime got: ".gettype("string"));
        $this->event->setAnnouncedTime("string");
    }
    
    public function test_setRealTimeWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for real_time. Expected DateTime got: ".gettype("string"));
        $this->event->setRealTime("string");
    }
    public function test_setActualWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for actual. Expected float or double or int got: ".gettype("0.5"));
        $this->event->setActual("0.5");
    }
    public function test_setPreviousWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for previous. Expected float or double or int got: ".gettype("0.5"));
        $this->event->setPrevious("0.5");
    }
    
    public function test_setStateWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype(0.5));
        $this->event->setState(0.5);
    }
    
    public function test_setNextEventsWithWrongArgument_expectError(){
        $this->expectExceptionMessage("Wrong type for next_event. Expected int got: ".gettype(0.5));
        $this->event->setNextEvent(0.5);
    }
    
    public function test_update(){
        $actual = 2.5;
        $real_time = (new DateTime("NOW"))->add(new DateInterval("PT5M"));
        $this->event->update($actual, $real_time);
        assert($this->event->getActual() == $actual, "Actual values should be equal");
        assert($this->event->getRealTime() == $real_time, "Real Times should be equal");
        assert($this->event->getState() == 1, "State should have been updated to 1");
    }
    
    public function test__compare(){
        $event = new Event($this->event_id, $this->news_id, $this->announced_time, $this->previous, $this->next_event);
        assert($this->event == $event);
    }
    
    public function test__getStringFromEventInitializedState(){
        assert(Event::getStringFromState(EventState::Pending) == "Pending");
    }
    
    public function test__getStringFromEventUpdatedState(){
        assert(Event::getStringFromState(EventState::Updated) == "Passed");
    }
}

