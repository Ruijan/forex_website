<?php

abstract class EventState
{
    const PENDING = 0;
    const UPDATED = 1;
}

class Event
{
    // property declaration
    private $identifier = 0;
    private $eventId;
    private $newsId;
    private $speech;
    private $strength;
    private $announcedTime;
    private $releasedTime;
    private $nextEventTime = 0;
    private $previousEventTime = 0;
    private $actual = 0;
    private $previous = 0;
    private $state = EventState::PENDING;
    
   
    // method declaration
    public function __construct($eventId, $newsId, $speech,
        $strength, $announcedTime, $previous, $previousEventTime,
        $nextEventTime) {
        $this->setEventId($eventId);
        $this->setNewsId($newsId);
        $this->setSpeech($speech);
        $this->setStrength($strength);
        $this->setAnnouncedTime($announcedTime);
        $this->setRealTime(new DateTime());
        $this->setRealTime($this->getReleasedTime()->createFromFormat('Y-m-d H:i:s',"1970-01-01 00:00:00"));
        $this->setPrevious($previous);
        $this->setPreviousEvent($previousEventTime);
        $this->setNextEvent($nextEventTime);
    }
    public function getId(){return $this->identifier;}
    public function getNewsId(){return $this->newsId;}
    public function getEventId(){return $this->eventId;}
    public function isASpeech(){return $this->speech;}
    public function getStrength(){return $this->strength;}
    public function getAnnouncedTime(){return $this->announcedTime;}
    public function getReleasedTime(){return $this->releasedTime;}
    public function getActual(){return $this->actual;}
    public function getPrevious(){return $this->previous;}
    public function getNextEvent(){return $this->nextEventTime;}
    public function getPreviousEvent(){return $this->previousEventTime;}
    public function getState(){return $this->state;}
    
    public function setId($identifier){
        if(!is_int($identifier)){
            throw new ErrorException("Wrong type for id. Expected int got: ".gettype($identifier));
        }
        $this->identifier = $identifier;
    }
    
    public function setEventId($eventId){
        if(!is_int($eventId)){
            throw new ErrorException("Wrong type for event_id. Expected int got: ".gettype($eventId));
        }
        $this->eventId = $eventId;
    }
    
    public function setNewsId($newsId){
        if(!is_int($newsId)){
            throw new ErrorException("Wrong type for news_id. Expected int got: ".gettype($newsId));
        }
        $this->newsId = $newsId;
    }
    
    public function setSpeech($speech){
        if(!is_bool($speech)){
            throw new ErrorException("Wrong type for speech. Expected bool got: ".gettype($speech));
        }
        $this->speech = $speech;
    }
    
    public function setStrength($strength){
        if(!is_int($strength) and $strength >= 0){
            throw new ErrorException("Wrong type for strength. Expected int got: ".gettype($strength));
        }
        $this->strength = $strength;
    }
    
    public function setAnnouncedTime($actualTime){
        if(!is_a($actualTime, 'DateTime')){
            throw new ErrorException("Wrong type for actual_time. Expected DateTime got: ".gettype($actualTime));
        }
        $this->announcedTime = $actualTime;
    }
    
    public function setRealTime($releasedTime){
        if(!is_a($releasedTime, 'DateTime')){
            throw new ErrorException("Wrong type for real_time. Expected DateTime got: ".gettype($releasedTime));
        }
        $this->releasedTime = $releasedTime;
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
            throw new ErrorException("Wrong type for previous. Expected float or double or int got: "
                .gettype($previous));
        }
        $this->previous = $previous;
    }
    
    public function setPreviousEvent($previousEventTime)
    {
        if(!is_int($previousEventTime) or $previousEventTime > 0){
            throw new ErrorException("Wrong type for previous_event. Expected int got: ".gettype($previousEventTime));
        }
        $this->previousEventTime = $previousEventTime;
    }
    
    public function setNextEvent($nextEventTime)
    {
        if(!is_int($nextEventTime) or $nextEventTime < 0){
            throw new ErrorException("Wrong type for next_event. Expected int got: ".gettype($nextEventTime));
        }
        $this->nextEventTime = $nextEventTime;
    }
    
    
    
    public function setState($state)
    {
        if(!is_int($state) or $state < 0){
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
        $this->state = $state;
    }
    
    public function update($actual, $releasedTime){
        $this->setActual($actual);
        $this->setRealTime($releasedTime);
        $this->setState(EventState::UPDATED);
    }
    
    public static function getStringFromState($state){
        switch($state){
            case EventState::PENDING:
                return "Pending";
            case EventState::UPDATED:
                return "Passed";
        }
    }
}
