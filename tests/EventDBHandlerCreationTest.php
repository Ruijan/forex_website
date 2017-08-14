<?php

require_once(str_replace("tests", "src", __DIR__."/").'Event.php');
require_once(str_replace("tests", "src", __DIR__."/").'EventDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');



/**
 * EventDBHandler test case.
 */
class EventDBHandlerCreationTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->event = new Event(254, 65954, new DateTime("NOW"), 0.01, 500);
    }
    
    protected function tearDown()
    {
        $this->eventDBHandler->emptyTable();
        parent::tearDown();
    }
    
    public function __construct()
    {
        $this->mysqli = connect_database();
        $this->eventDBHandler = new EventDBHandler($this->mysqli);
        $this->eventDBHandler->createTable();
    }
    
    public function __destruct()
    {
        $this->deleteTableIfExists();
        $this->eventDBHandler = null;
    }
    
    private function deleteTableIfExists()
    {
        if($this->eventDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE events");
        }
    }
    
    public function test__isTableEmpty()
    {
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function test__addingEvent_expectIncrementInSize(){
        $identifier = $this->eventDBHandler->addEvent($this->event);
        assert($this->eventDBHandler->getTableSize() == 1);
        assert($identifier != 2);
        assert($identifier == 1);
    }
    
    public function test__removingEvent_expectDecrementationInSize(){
        $identifier = $this->eventDBHandler->addEvent($this->event);
        $this->eventDBHandler->removeEventById($identifier);
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function test__getInvalidEventIdShouldThrow(){
        $identifier = $this->eventDBHandler->addEvent($this->event);
        $this->expectExceptionMessage("Event does not exists, id:".($identifier+1));
        $this->eventDBHandler->getEventById($identifier+1);
    }
    
    public function test__addingEvent_expectSameValueInDBandEvent(){
        $identifier = $this->eventDBHandler->addEvent($this->event);
        $this->event->setId($identifier);
        $db_event = $this->eventDBHandler->getEventById($identifier);
        assert($db_event->getEventId() == $this->event->getEventId());
        assert($db_event->getNewsId() == $this->event->getNewsId());
        assert($db_event->getAnnouncedTime() == $this->event->getAnnouncedTime());
        assert($db_event->getPrevious() == $this->event->getPrevious());
        assert($db_event->getNextEvent() == $this->event->getNextEvent());
    }
    
    public function test__tryAddingSameEvent_ExpectSameDBSize(){
        $this->createDummyEvent();
        $this->createDummyEvent();
        assert($this->eventDBHandler->getTableSize() == 1);
    }
    
    public function test__updateEvent_expectSameValueInDBandEvent(){
        $identifier = $this->eventDBHandler->addEvent($this->event);
        $this->event->setId($identifier);
        $this->event->update(2.5, (new DateTime("NOW"))->add(new DateInterval("PT5M")));
        $this->eventDBHandler->updateEvent($this->event);
        $db_event = $this->eventDBHandler->getEventById($identifier);
        assert($db_event->getRealTime() == $this->event->getRealTime(), "Real times should be equal: ".
            $this->event->getRealTime()->format("Y-m-d H:i:s"). " and ".
            $db_event->getRealTime()->format("Y-m-d H:i:s"));
        assert($db_event->getActual() == $this->event->getActual(), "Actual values should be equal");
        assert($db_event->getState() == $this->event->getState(), "State should be equal to 1");
    }
    
    public function test__emptyTable(){
        $this->eventDBHandler->addEvent($this->event);
        $this->eventDBHandler->emptyTable();
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function test_getEventByEventID(){
        $this->createRandomDummyEvent();
        $event2 = $this->createRandomDummyEvent();
        assert($this->eventDBHandler->getEventByEventId($event2->getEventId()) == $event2);
    }
    
    public function test__getInvalidEventEventIdShouldThrow(){
        $this->expectExceptionMessage("Event does not exists, event id:".(50));
        $this->eventDBHandler->getEventByEventId(50);
    }
    
    public function test__getEventsFromTo(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        
        $all_events = $this->generateDummyEvents();
        $events_to_get = [$all_events[0], $all_events[1], $all_events[4]];
        $this->addListOfEvents($all_events);
        $events = $this->eventDBHandler->getEventsFromTo($fromDate, $toDate);
        
        $all_here = $this->areListOfEventsEquals($events_to_get, $events);
        assert(sizeof($events) == sizeof($events_to_get),
            "Different number of events expected. Expected ".sizeof($events_to_get)." got ".sizeof($events));
        assert($all_here, "Events were not equals");
    }
    
    public function test__getEventsFromToState(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        $state = EventState::UPDATED;
        
        $all_events = $this->generateEventsWithDifferentStates();
        $events_to_get = [$all_events[0], $all_events[3]];
        $this->addListOfEvents($all_events);
        $events = $this->eventDBHandler->getEventsFromTo($fromDate, $toDate, $state);
        
        $all_here = $this->areListOfEventsEquals($events_to_get, $events);
        assert(sizeof($events) == sizeof($events_to_get),
            "Different number of events expected. Expected ".sizeof($events_to_get)." got ".sizeof($events));
        assert($all_here, "Events were not equals");
    }
    
    public function test__getEventsFromToWithBadArguments_ShouldThrow(){
        $fromDate = "coucou";
        $toDate = 32;
        $this->expectExceptionMessage("Wrong type for from or to. Expected DateTime got: ".gettype($fromDate).
            " and ".gettype($toDate));
        $this->eventDBHandler->getEventsFromTo($fromDate, $toDate);
    }
    
    public function test__getEventsFromToStateWithBadArguments_ShouldThrow(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        $state = "5";
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype($state));
        $this->eventDBHandler->getEventsFromTo($fromDate, $toDate, $state);
    }
    
    private function areListOfEventsEquals($events_to_get, $events)
    {
        $all_here = sizeof($events) == sizeof($events_to_get);
        foreach($events as $event){
            $eventHere = false;
            foreach($events_to_get as $expected_event){
                if($expected_event == $event){
                    $eventHere = true;
                }
            }
            $all_here = $eventHere ? $all_here : $eventHere;
        }
        return $all_here;
    }

    
    private function addListOfEvents($events){
        foreach($events as $event){
            $event->setId($this->eventDBHandler->addEvent($event));
        }
    }
    
    private function createDummyEvent(){
        $event = new Event(254, 65954, new DateTime("2017-08-03 00:30:00"), 0.01, 500);
        $event->setId($this->eventDBHandler->tryAddingEvent($event));
        return $event;
    }
    
    private function createRandomDummyEvent(){
        $event = new Event(rand(1,10000), 65954, new DateTime("2017-08-03 00:30:00"), 0.01, 500);
        $event->setId($this->eventDBHandler->tryAddingEvent($event));
        return $event;
    }
    
    private function generateDummyEvents()
    {
        $all_events = [];
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-03 00:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-05 12:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-02 00:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-06 00:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-04 00:30:00"), 0.01, 500);
        return $all_events;
    }
    
    private function generateEventsWithDifferentStates()
    {
        $all_events = [];
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-03 00:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-05 12:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-04 12:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-03 12:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-02 00:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-06 00:30:00"), 0.01, 500);
        $all_events[] = new Event(rand(1,10000), 65954, new DateTime("2017-08-04 00:30:00"), 0.01, 500);
        $all_events[0]->update(325, new DateTime("2017-08-03 00:31:00"));
        $all_events[3]->update(325, new DateTime("2017-08-03 12:35:00"));
        return $all_events;
    }

}