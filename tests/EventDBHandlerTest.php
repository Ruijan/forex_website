<?php

require_once(str_replace("tests", "src", __DIR__."/").'Event.php');
require_once(str_replace("tests", "src", __DIR__."/").'EventDBHandler.php');
require_once(str_replace("tests", "src", __DIR__."/").'connect.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');



/**
 * EventDBHandler test case.
 */
class EventDBHandlerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {

        parent::setUp();
        $this->mysqli = connect_database();
        $this->eventDBHandler = new EventDBHandler($this->mysqli);
        $this->eventDBHandler->createTable();
        $this->event = new Event(254, 65954, false, new DateTime("NOW"), 0.01, -300, 500);
    }
    
    protected function tearDown()
    {
        $this->eventDBHandler->emptyTable();
        $this->mysqli->close();
        parent::tearDown();
    }
    
    public function __destruct()
    {
        $this->mysqli = connect_database();
        $this->deleteTableIfExists();
        $this->mysqli->close();
    }
    
    private function deleteTableIfExists()
    {
        if($this->eventDBHandler->doesTableExists())
        {
            $this->mysqli->query("DROP TABLE events");
        }
    }
    
    public function testIsTableEmpty()
    {
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function testAddingEventExpectIncrementInSize(){
        $identifier = $this->eventDBHandler->tryAddingEvent($this->event);
        assert($this->eventDBHandler->getTableSize() == 1);
        assert($identifier != 2);
        assert($identifier == 1);
    }
    
    public function testRemovingEventExpectDecrementationInSize(){
        $identifier = $this->eventDBHandler->tryAddingEvent($this->event);
        $this->eventDBHandler->removeEventById($identifier);
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function testGetInvalidEventIdShouldThrow(){
        $identifier = $this->eventDBHandler->tryAddingEvent($this->event);
        $this->expectExceptionMessage("Event does not exists, id:".($identifier+1));
        $this->eventDBHandler->getEventById($identifier+1);
    }
    
    public function testAddingEventExpectSameValueInDBandEvent(){
        $identifier = $this->eventDBHandler->tryAddingEvent($this->event);
        $this->event->setId($identifier);
        $dbEvent = $this->eventDBHandler->getEventById($identifier);
        assert($dbEvent->getEventId() == $this->event->getEventId());
        assert($dbEvent->getNewsId() == $this->event->getNewsId());
        assert($dbEvent->getAnnouncedTime() == $this->event->getAnnouncedTime());
        assert($dbEvent->getPrevious() == $this->event->getPrevious());
        assert($dbEvent->getNextEvent() == $this->event->getNextEvent());
    }
    
    public function testTryAddingSameEventExpectSameDBSize(){
        $this->createDummyEvent();
        $this->createDummyEvent();
        assert($this->eventDBHandler->getTableSize() == 1);
    }
    
    public function testUpdateEventExpectSameValueInDBandEvent(){
        $identifier = $this->eventDBHandler->tryAddingEvent($this->event);
        $this->event->setId($identifier);
        $this->event->update(2.5, (new DateTime("NOW"))->add(new DateInterval("PT5M")));
        $this->eventDBHandler->updateEvent($this->event);
        $dbEvent = $this->eventDBHandler->getEventById($identifier);
        assert($dbEvent->getReleasedTime() == $this->event->getReleasedTime(), "Real times should be equal: ".
            $this->event->getReleasedTime()->format("Y-m-d H:i:s"). " and ".
            $dbEvent->getReleasedTime()->format("Y-m-d H:i:s"));
        assert($dbEvent->getActual() == $this->event->getActual(), "Actual values should be equal");
        assert($dbEvent->getState() == $this->event->getState(), "State should be equal to 1");
    }
    
    public function testEmptyTable(){
        $this->eventDBHandler->tryAddingEvent($this->event);
        $this->eventDBHandler->emptyTable();
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function testGetEventByNewsID(){
        $this->createRandomDummyEvent();
        $event2 = $this->createRandomDummyEvent();
        assert($this->eventDBHandler->getEventByNewsId($event2->getNewsId()) == $event2);
    }
    
    public function testGetInvalidEventFromNewsIdShouldThrow(){
        $this->expectExceptionMessage("Event does not exists, event id:".(50));
        $this->eventDBHandler->getEventByNewsId(50);
    }
    
    public function testGetEventsFromTo(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        
        $allEvents = $this->generateDummyEvents();
        $eventsToGet = [$allEvents[0], $allEvents[1], $allEvents[4]];
        $this->addListOfEvents($allEvents);
        $events = $this->eventDBHandler->getEventsFromTo($fromDate, $toDate);
        
        $allHere = $this->areListOfEventsEquals($eventsToGet, $events);
        assert(sizeof($events) == sizeof($eventsToGet),
            "Different number of events expected. Expected ".sizeof($eventsToGet)." got ".sizeof($events));
        assert($allHere, "Events were not equals");
    }
    
    public function testGetEventsFromToState(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        $state = EventState::UPDATED;
        
        $allEvents = $this->generateEventsWithDifferentStates();
        $eventsToGet = [$allEvents[0], $allEvents[3]];
        $this->addListOfEvents($allEvents);
        $events = $this->eventDBHandler->getEventsFromTo($fromDate, $toDate, $state);
        
        $allHere = $this->areListOfEventsEquals($eventsToGet, $events);
        assert(sizeof($events) == sizeof($eventsToGet),
            "Different number of events expected. Expected ".sizeof($eventsToGet)." got ".sizeof($events));
        assert($allHere, "Events were not equals");
    }
    
    public function testGetEventsFromToWithBadArgumentsShouldThrow(){
        $fromDate = "coucou";
        $toDate = 32;
        $this->expectExceptionMessage("Wrong type for from or to. Expected DateTime got: ".gettype($fromDate).
            " and ".gettype($toDate));
        $this->eventDBHandler->getEventsFromTo($fromDate, $toDate);
    }
    
    public function testGetEventsFromToStateWithBadArgumentsShouldThrow(){
        $fromDate = new DateTime("2017-08-03");
        $toDate = new DateTime("2017-08-05");
        $state = "5";
        $this->expectExceptionMessage("Wrong type for state. Expected int got: ".gettype($state));
        $this->eventDBHandler->getEventsFromTo($fromDate, $toDate, $state);
    }
    
    private function areListOfEventsEquals($events_to_get, $events)
    {
        $allHere = sizeof($events) == sizeof($events_to_get);
        foreach($events as $event){
            $eventHere = false;
            foreach($events_to_get as $expectedEvent){
                if($expectedEvent == $event){
                    $eventHere = true;
                }
            }
            $allHere = $eventHere ? $allHere : $eventHere;
        }
        return $allHere;
    }

    
    private function addListOfEvents($events){
        foreach($events as $event){
            $event->setId($this->eventDBHandler->tryAddingEvent($event));
        }
    }
    
    private function createDummyEvent(){
        $event = new Event(254, 65954, false, new DateTime("2017-08-03 00:30:00"), 0.01, -300, 500);
        $event->setId($this->eventDBHandler->tryAddingEvent($event));
        return $event;
    }
    
    private function createRandomDummyEvent(){
        $event = new Event(rand(1,10000), rand(1,10000), false, new DateTime("2017-08-03 00:30:00"), 0.01, -300, 500);
        $event->setId($this->eventDBHandler->tryAddingEvent($event));
        return $event;
    }
    
    private function generateDummyEvents()
    {
        $allEvents = [];
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-03 00:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-05 12:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-02 00:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-06 00:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-04 00:30:00"), 0.01, -300, 500);
        return $allEvents;
    }
    
    private function generateEventsWithDifferentStates()
    {
        $allEvents = [];
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-03 00:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-05 12:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-04 12:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-03 12:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-02 00:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-06 00:30:00"), 0.01, -300, 500);
        $allEvents[] = new Event(rand(1,10000), rand(1,10000), false, 
            new DateTime("2017-08-04 00:30:00"), 0.01, -300, 500);
        $allEvents[0]->update(325, new DateTime("2017-08-03 00:31:00"));
        $allEvents[3]->update(325, new DateTime("2017-08-03 12:35:00"));
        return $allEvents;
    }

}