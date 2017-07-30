<?php

require_once 'EventDBHandlerTest.php';
require_once '../Event.php';

/**
 * EventDBHandler test case.
 */
class EventDBHandlerCreationTest extends EventDBHandlerTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->event = new Event(254, 65954, new DateTime("NOW"), 0.01, 500);
        $this->eventDBHandler->createTable();
    }
    
    protected function tearDown()
    {
        $this->eventDBHandler->deleteTable();
        parent::tearDown();
    }
    
    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function test_isTableEmpty()
    {
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function test_addingEvent_expectIncrementInSize(){
        $id = $this->eventDBHandler->addEvent($this->event);
        assert($this->eventDBHandler->getTableSize() == 1);
        assert($id != 2);
        assert($id == 1);
    }
    
    public function test_removingEvent_expectDecrementationInSize(){
        $id = $this->eventDBHandler->addEvent($this->event);
        $this->eventDBHandler->removeEventById($id);
        assert($this->eventDBHandler->getTableSize() == 0);
    }
    
    public function test_addingEvent_expectSameValueInDBandEvent(){
        $id = $this->eventDBHandler->addEvent($this->event);
        $this->event->setId($id);
        $db_event = $this->eventDBHandler->getEventById($id);
        assert($db_event->getEventId() == $this->event->getEventId());
        assert($db_event->getNewsId() == $this->event->getNewsId());
        assert($db_event->getAnnouncedTime() == $this->event->getAnnouncedTime());
        assert($db_event->getPrevious() == $this->event->getPrevious());
        assert($db_event->getNextEvent() == $this->event->getNextEvent());
    }
    
    public function test_updateEvent_expectSameValueInDBandEvent(){
        $id = $this->eventDBHandler->addEvent($this->event);
        $this->event->setId($id);
        $this->event->update(2.5, (new DateTime("NOW"))->add(new DateInterval("PT5M")));
        $this->eventDBHandler->updateEvent($this->event);
        $db_event = $this->eventDBHandler->getEventById($id);
        assert($db_event->getRealTime() == $this->event->getRealTime(), "Real times should be equal: ".
            $this->event->getRealTime()->format("Y-m-d H:i:s"). " and ".$db_event->getRealTime()->format("Y-m-d H:i:s"));
        assert($db_event->getActual() == $this->event->getActual(), "Actual values should be equal");
        assert($db_event->getState() == $this->event->getState(), "State should be equal to 1");
    }
}