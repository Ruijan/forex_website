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
        $previousEvent = null;
        foreach($lines as $line){
            if  ($line->hasAttribute('event_attr_id') && $line->hasAttribute('id')){
                $event = $this->createEventFromLine($line);
                $this->setActualValueIfExists($event);
                $this->setNextPreviousEventTime($previousEvent, $event);
                $previousEvent = $event;
                
                $this->events[] = $event;
            }
        }
        $this->setNextEventTimeToDiffToMidnight($event);
    }
    
    private function setNextPreviousEventTime($previousEvent, $event)
    {
        if(!is_null($previousEvent)){
            $timeDiff = $event->getAnnouncedTime()->diff($previousEvent->getAnnouncedTime());
            $timeDiff = $timeDiff->s +
                $timeDiff->i*60 +
                $timeDiff->h*60*60 +
                $timeDiff->d*24*60*60;
            $previousEvent->setNextEvent($timeDiff);
            $event->setPreviousEvent(-$timeDiff);
            if($timeDiff == 0){
                $event->setNextEvent($timeDiff);
            }
        }
    }

    
    private function createEventFromLine($line)
    {
        $newsId = explode('_',$line->getAttribute('id'))[1];
        $previousNode = $this->table->getElementByID('eventPrevious_'.$newsId);
        $previous = $this->getFloatFromString($previousNode->nodeValue);
        $eventDateTime = new \DateTime();
        $eventDateTime = $eventDateTime->createFromFormat('Y-m-d H:i:s', 
            $line->getAttribute('event_timestamp'));
        $name = "";
        $speech = false;
        $columns = $line->getElementsByTagName("td");
        $strength = 0;
        foreach($columns as $column){
            if($column->getAttribute('class') == "left event"){
                $name = $column->nodeValue;
                $extensionNodes = $column->getElementsByTagName("span");
                foreach($extensionNodes as $extension){
                    if($extension->getAttribute('title') == "Speech"){
                        $speech = true;
                    }
                }
            }
            if($column->getAttribute('class') == "sentiment"){
                $extensionNodes = $column->getElementsByTagName("i");
                foreach($extensionNodes as $extension){
                    if(strpos($extension->getAttribute('class'), "grayFullBullishIcon") !== FALSE){
                        $strength += 1;
                    }
                }
            }
        }
        $event = new Event((int)$line->getAttribute('event_attr_id'), 
            (int)$newsId, 
            $speech,
            $strength,
            $eventDateTime, 
            $previous, 
            0,
            0);
        return $event;
    }

    
    private function setActualValueIfExists($event)
    {
        $actualNode = $this->table->getElementByID('eventActual_'.$event->getNewsId())->nodeValue;
        $actual = $this->getFloatFromString($actualNode);
        if ($actual != 0)
        {
            $realTime = new DateTime();
            $realTime = $realTime->createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
            $event->update($actual, $realTime);
        }
    }

    
    private function setNextEventTimeToDiffToMidnight($event)
    {
        $endOfTheDay = new DateTime();
        $endOfTheDay->createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
        $endOfTheDay->setTime(23,59,59);
        $timeDiff = $endOfTheDay->diff($event->getAnnouncedTime());
        $timeDiff = $timeDiff->s +
            $timeDiff->i*60 +
            $timeDiff->h*60*60 +
            $timeDiff->d*24*60*60;
        $event->setNextEvent($timeDiff);
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

