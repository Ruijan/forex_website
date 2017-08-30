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
        $this->newsId = 255;
        $this->eventId = 68956;
        $this->strength = 2;
        $this->announced_time = new DateTime("NOW");
        $this->previous = 0.01;
        $this->nextEvent = 50;
        $this->previousEvent = -300;
        $this->speech = false;
        $this->event = new Event(
            $this->eventId, 
            $this->newsId, 
            $this->speech,
            $this->strength,
            $this->announced_time, 
            $this->previous,
            $this->previousEvent,
            $this->nextEvent);
    }

    protected function tearDown()
    {
        // TODO Auto-generated EventTest::tearDown()
        $this->event = null;
        
        parent::tearDown();
    }

    public function testConstruct()
    {
        assert($this->event->getNewsId() == $this->newsId, "News id should be equal");
        assert($this->event->getEventId() == $this->eventId, "Event ID should be equal");
        assert($this->event->getAnnouncedTime() == $this->announced_time, "Event Announced time should be equal");
        assert($this->event->getPrevious() == $this->previous, "Event previous value should be equal");
        assert($this->event->getNextEvent() == $this->nextEvent, "Next event time value should be equal");
    }
    
    public function testSetIdWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for id. Expected int got: ".gettype(0.5));
        $this->event->setId(0.5);
    }
    
    public function testSetEventIdWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for event_id. Expected int got: ".gettype(0.5));
        $this->event->setEventId(0.5);
    }
    
    public function testSetNewsIdWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for news_id. Expected int got: ".gettype(0.5));
        $this->event->setNewsId(0.5);
    }
    
    public function testSetStrengthWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for strength. Expected int got: ".gettype(0.5));
        $this->event->setStrength(0.5);
    }
    
    public function testSetTimeAnnouncedWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for actual_time. Expected DateTime got: ".gettype("string"));
        $this->event->setAnnouncedTime("string");
    }
    
    public function testSetRealTimeWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for real_time. Expected DateTime got: ".gettype("string"));
        $this->event->setRealTime("string");
    }
    public function testSetActualWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for actual. Expected float or double or int got: ".gettype("0.5"));
        $this->event->setActual("0.5");
    }
    public function testSetPreviousWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for previous. Expected float or double or int got: ".gettype("0.5"));
        $this->event->setPrevious("0.5");
    }
    
    public function testSetStateWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype(0.5));
        $this->event->setState(0.5);
    }
    
    public function testSetNextEventsWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for next_event. Expected int got: ".gettype(0.5));
        $this->event->setNextEvent(0.5);
    }
    
    public function testSetPreviousEventsWithWrongArgumentShouldThrow(){
        $this->expectExceptionMessage("Wrong type for previous_event. Expected int got: ".gettype(0.5));
        $this->event->setPreviousEvent(0.5);
    }
    
    public function testUpdate(){
        $actual = 2.5;
        $realTime = (new DateTime("NOW"))->add(new DateInterval("PT5M"));
        $this->event->update($actual, $realTime);
        assert($this->event->getActual() == $actual, "Actual values should be equal");
        assert($this->event->getReleasedTime() == $realTime, "Real Times should be equal");
        assert($this->event->getState() == 1, "State should have been updated to 1");
    }
    
    public function testCompare(){
        $event = new Event($this->eventId, $this->newsId, $this->speech, $this->strength, $this->announced_time, 
            $this->previous, $this->previousEvent, $this->nextEvent);
        assert($this->event == $event);
    }
    
    public function testGetStringFromEventInitializedState(){
        assert(Event::getStringFromState(EventState::PENDING) == "Pending");
    }
    
    public function testGetStringFromEventUpdatedState(){
        assert(Event::getStringFromState(EventState::UPDATED) == "Passed");
    }
}

