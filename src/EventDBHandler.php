<?php
use src\DBHandler;

require_once 'Event.php';
require_once 'DBHandler.php';

class EventDBHandler extends DBHandler
{
    
    public function __construct($mysqli)
    {
        parent::__construct($mysqli, "events");
    }
    
    public function createTable(){
        if(!$this->doesTableExists()){
            $query = "CREATE TABLE events (
                        ID int(11) AUTO_INCREMENT UNIQUE,
                        ID_EVENT int(11) NOT NULL,
                        ID_NEWS int(11) NOT NULL UNIQUE,
                        SPEECH int(1),
                        STRENGTH int(1),
                        ANNOUNCED_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        REAL_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        ACTUAL double NULL,
                        PREVIOUS double NULL,
                        PREVIOUS_EVENT int NULL,
                        NEXT_EVENT int NULL,
                        STATE int NULL,
                        PRIMARY KEY  (ID)
                        )";
            if ($this->mysqli->query($query) === FALSE) {
                throw new ErrorException("Couldn't create database.");
            }
            parent::createTable();
        }
    }
    
    private function addEvent($event){
        $this->throwIfTableDoesNotExist();
        $query = "INSERT INTO events
                    (ID, ID_EVENT, ID_NEWS, SPEECH, STRENGTH, ANNOUNCED_TIME, REAL_TIME, ACTUAL, PREVIOUS,
                    PREVIOUS_EVENT, NEXT_EVENT, STATE)
                    VALUES (NULL,"
                    .$event->getEventId().
                    ",".$event->getNewsId().
                    ",".($event->isASpeech() ? "1" : "0").
                    ",".$event->getStrength().
                    ",'".$event->getAnnouncedTime()->format('Y-m-d H:i:s').
                    "','".$event->getReleasedTime()->format('Y-m-d H:i:s').
                    "',".$event->getActual().
                    ", ".$event->getPrevious().
                    ", ".$event->getPreviousEvent().
                    ", ".$event->getNextEvent().
                    ", ".$event->getState().")";
        if($this->mysqli->query($query) === FALSE){
            throw new Exception(" Event already in table: ". $this->mysqli->error);
        }
        return $this->mysqli->insert_id;
    }
    
    public function tryAddingEvent($event){
        try{
            return $this->addEvent($event);
        }
        catch(Exception $e){
            if(stripos($e->getMessage(),"Event already in table:") === FALSE){
                throw new Exception($e->getMessage());
            }
            return $this->getEventByNewsId($event->getNewsId())->getId();
        }
    }
    
    public function removeEventById($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "DELETE FROM events WHERE ID=".$identifier;
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function getEventById($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE ID=".$identifier;
        $result = $this->mysqli->query($query);
        $this->throwIfQueryFailed($query, $result);
        while($row = $result->fetch_array()){
            return $this->createEventFromDbArray($row);
        }
        throw new Exception("Event does not exists, id:".$identifier);
    }
    
    private function throwIfQueryFailed($query, $result)
    {
        if($result === False){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function getEventByNewsId($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE ID_NEWS=".$identifier;
        $result = $this->mysqli->query($query);
        $this->throwIfQueryFailed($query, $result);
        while($row = $result->fetch_array()){
            return $this->createEventFromDbArray($row);
        }
        throw new Exception("Event does not exists, event id:".$identifier);
    }
    
    public function updateEvent($event){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE events SET ACTUAL = ".$event->getActual().",
                REAL_TIME='".$event->getReleasedTime()->format('Y-m-d H:i:s')."', 
                STATE=".$event->getState()." WHERE ID=".$event->getId();
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function emptyTable(){
        $this->throwIfTableDoesNotExist();
        $query = "TRUNCATE TABLE events";
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function getEventsFromTo($fromDate, $toDate, $state=-1){
        $this->throwIfWrongArgumentType($fromDate, $toDate, $state);
        $query = $this->buildSelectQueryFromToState($fromDate, $toDate, $state);
        $events = [];
        $result = $this->mysqli->query($query);
        $this->throwIfQueryFailed($query, $result);
        while($row = $result->fetch_array())
        {
            $events[] = $this->createEventFromDbArray($row);
        }
        return $events;
    }
    
    private function createEventFromDbArray($result)
    {
        $event = new Event((int)$result["ID_EVENT"], 
            (int)$result["ID_NEWS"], 
            (bool)$result["SPEECH"],
            (int)$result["STRENGTH"], 
            new DateTime($result["ANNOUNCED_TIME"]), 
            (float)$result["PREVIOUS"], 
            (int)$result["PREVIOUS_EVENT"],
            (int)$result["NEXT_EVENT"]);
        $event->setId((int)$result["ID"]);
        if((int)$result["STATE"] == EventState::UPDATED){
            $event->update((float)$result["ACTUAL"],new DateTime($result["REAL_TIME"]));
        }
        return $event;
    }
    
    private function buildSelectQueryFromToState($fromDate, $toDate, $state)
    {
        $stateSuffix = "";
        if($state != -1){
            $stateSuffix = " AND STATE=".$state;
        }
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE DATEDIFF(ANNOUNCED_TIME,'".$fromDate->format('Y-m-d H:i:s').
        "') >= 0 AND DATEDIFF(ANNOUNCED_TIME,'".$toDate->format('Y-m-d H:i:s').
        "') <= 0".$stateSuffix;
        return $query;
    }
    
    private function throwIfWrongArgumentType($fromDate, $toDate, $state)
    {
        if(!is_a($fromDate, 'DateTime') || !is_a($toDate, 'DateTime')){
            throw new ErrorException("Wrong type for from or to. Expected DateTime got: "
                .gettype($fromDate)." and ".gettype($toDate));
        }
        if(!is_int($state)){
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
    }
}


