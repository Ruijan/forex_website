<?php

abstract class EventState
{
    const PENDING = 0;
    const UPDATED = 1;
}

class Event
{
    // property declaration
    public $identifier = 0;
    public $event_id;
    public $news_id;
    public $announced_time;
    public $real_time;
    public $next_event = 0;
    public $actual = 0;
    public $previous = 0;
    public $state = EventState::PENDING;
    
   
    // method declaration
    function __construct($event_id, $news_id, $announced_time, $previous, $next_event) {
        $this->setEventId($event_id);
        $this->setNewsId($news_id);
        $this->setAnnouncedTime($announced_time);
        $this->setRealTime(new DateTime());
        $this->setRealTime($this->getRealTime()->createFromFormat('Y-m-d H:i:s',"1970-01-01 00:00:00"));
        $this->setPrevious($previous);
        $this->setNextEvent($next_event);
    }
    public function getId(){return $this->identifier;}
    public function getNewsId(){return $this->news_id;}
    public function getEventId(){return $this->event_id;}
    public function getAnnouncedTime(){return $this->announced_time;}
    public function getRealTime(){return $this->real_time;}
    public function getActual(){return $this->actual;}
    public function getPrevious(){return $this->previous;}
    public function getNextEvent(){return $this->next_event;}
    public function getState(){return $this->state;}
    
    public function setId($identifier){
        if(!is_int($identifier)){
            throw new ErrorException("Wrong type for id. Expected int got: ".gettype($identifier));
        }
        $this->identifier = $identifier;
    }
    
    public function setEventId($event_id){
        if(!is_int($event_id)){
            throw new ErrorException("Wrong type for event_id. Expected int got: ".gettype($event_id));
        }
        $this->event_id = $event_id;
    }
    
    public function setNewsId($news_id){
        if(!is_int($news_id)){
            throw new ErrorException("Wrong type for news_id. Expected int got: ".gettype($news_id));
        }
        $this->news_id = $news_id;
    }
    
    public function setAnnouncedTime($actual_time){
        if(!is_a($actual_time, 'DateTime')){
            throw new ErrorException("Wrong type for actual_time. Expected DateTime got: ".gettype($actual_time));
        }
        $this->announced_time = $actual_time;
    }
    
    public function setRealTime($real_time){
        if(!is_a($real_time, 'DateTime')){
            throw new ErrorException("Wrong type for real_time. Expected DateTime got: ".gettype($real_time));
        }
        $this->real_time = $real_time;
    }
    
    public function setActual($actual)
    {
        if(!is_float($actual) && !is_int($actual) && !is_double($actual)){
            throw new ErrorException("Wrong type for actual. Expected float or double or int got: ".gettype($actual));
        }
        $this->actual = $actual;
    }
    
    public function setPrevious($previous)
    {
        if(!is_float($previous) && !is_int($previous) && !is_double($previous)){
            throw new ErrorException("Wrong type for previous. Expected float or double or int got: ".gettype($previous));
        }
        $this->previous = $previous;
    }
    
    public function setNextEvent($next_event)
    {
        if(!is_int($next_event) or $next_event < 0){
            throw new ErrorException("Wrong type for next_event. Expected int got: ".gettype($next_event));
        }
        $this->next_event = $next_event;
    }
    
    public function setState($state)
    {
        if(!is_int($state) or $state < 0){
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
        $this->state = $state;
    }
    
    public function update($actual, $real_time){
        $this->setActual($actual);
        $this->setRealTime($real_time);
        $this->setState(EventState::UPDATED);
    }
    
    static function getStringFromState($state){
        switch($state){
            case EventState::PENDING:
                return "Pending";
            case EventState::UPDATED:
                return "Passed";
        }
    }
}
