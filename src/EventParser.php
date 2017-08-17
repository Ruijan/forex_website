<?php

require_once 'Event.php';

class EventParser
{
    private $link;
    private $table;
    private $events;
    public function __construct($link)
    {
        $this->setLink($link);
    }
    
    public function getLink(){return $this->link;}
    public function getTable(){return $this->table;}
    public function getEvents(){return $this->events;}
    
    public function setLink($link){
        if(!is_string($link)){
            throw new ErrorException("Wrong type for link. Expected string got: ".gettype($link));
        }
        $this->link = $link;
    }
    
    public function retrieveTableOfEvents(){
        $dom = new DOMDocument('1.0');
        @$dom->loadHTMLFile($this->link);
        $this->table = $dom;
    }
    
    public function createEventsFromTable(){
        $this->events = array();
        $lines = $this->table->getElementByID('ecEventsTable')->getElementsByTagName('tbody')[0]
        ->getElementsByTagName("tr");
        $previous_event = null;
        foreach($lines as $line){
            if  ($line->hasAttribute('event_attr_id') && $line->hasAttribute('id')){
                $event = $this->createEventFromLine($line);
                $this->setActualValueIfExists($event);
                $this->setNextEventTime($previous_event, $event);
                $previous_event = $event;
                
                $this->events[] = $event;
            }
        }
        $this->setNextEventTimeToDiffToMidnight($event);
    }
    
    private function setNextEventTime($previous_event, $event)
    {
        if(!is_null($previous_event)){
            $time_diff = $event->getAnnouncedTime()->diff($previous_event->getAnnouncedTime());
            $time_diff = $time_diff->s +
                $time_diff->i*60 +
                $time_diff->h*60*60 +
                $time_diff->d*24*60*60;
            $previous_event->setNextEvent($time_diff);
            if($time_diff == 0){
                $event->setNextEvent($time_diff);
            }
        }
    }

    
    private function createEventFromLine($line)
    {
        $news_id = explode('_',$line->getAttribute('id'))[1];
        $previous_node = $this->table->getElementByID('eventPrevious_'.$news_id)->nodeValue;
        $previous = $this->getFloatFromString($previous_node);
        $eventDateTime = new \DateTime();
        $eventDateTime->createFromFormat('Y-m-d H:i:s', $line->getAttribute('event_timestamp'));
        $event = new Event((int)$line->getAttribute('event_attr_id'), 
            (int)$news_id , 
            $eventDateTime, 
            $previous, 0);
        return $event;
    }

    
    private function setActualValueIfExists($event)
    {
        $actual_node = $this->table->getElementByID('eventActual_'.$event->getNewsId())->nodeValue;
        $actual = $this->getFloatFromString($actual_node);
        if (!is_null($actual))
        {
            $realTime = new DateTime();
            $realTime->createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
            $event->update($actual, $realTime);
        }
    }

    
    private function setNextEventTimeToDiffToMidnight($event)
    {
        $endOfTheDay = new DateTime();
        $endOfTheDay->createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
        $endOfTheDay->setTime(23,59,59);
        $time_diff = $endOfTheDay->diff($event->getAnnouncedTime());
        $time_diff = $time_diff->s +
            $time_diff->i*60 +
            $time_diff->h*60*60 +
            $time_diff->d*24*60*60;
        $event->setNextEvent($time_diff);
    }

    
    public function getFloatFromString($string){
        $value = "";
        for($index = 0; $index < strlen($string); $index += 1){
            $character = $string[$index];
            if(is_numeric($character) or $character == '.' or $character == '-'){
                $value = $value.$character;
            }
        }
        return (float)$value;
    }
    
    
}

