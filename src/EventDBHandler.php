<?php
require_once 'Event.php';

class EventDBHandler
{
    private $mysqli;
    private $existingTable = False;
    
    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
        $this->existingTable = $this->checkIfTableExist();
    }
    
    public function doesTableExists(){
        return $this->existingTable;
    }
    
    private function checkIfTableExist(){
        if ($result = $this->mysqli->query("SHOW TABLES LIKE 'events'")) {
            if($result->num_rows >= 1) {
                return True;
            }
        }
        return False;
    }
    
    public function createTable(){
        if(!$this->doesTableExists()){
            $query = "CREATE TABLE events (
                        ID int(11) AUTO_INCREMENT UNIQUE,
                        ID_EVENT int(11) NOT NULL UNIQUE,
                        ID_NEWS int(11) NOT NULL,
                        ANNOUNCED_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        REAL_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        ACTUAL double NULL,
                        PREVIOUS double NULL,
                        NEXT_EVENT int NULL,
                        STATE int NULL,
                        PRIMARY KEY  (ID)
                        )";
            if ($this->mysqli->query($query) === FALSE) {
                throw new ErrorException("Couldn't create database.");
            }
            $this->existingTable = true;
        }
    }
    
    public function deleteTable(){
        if($this->doesTableExists()){
            $this->mysqli->query("DROP TABLE events");
            $this->existingTable = false;
        }
    }
    
    public function getTableSize(){
        $this->throwIfTableDoesNotExist();
        $sql1 = $this->mysqli->query("SELECT * FROM events");
        $row_count= mysqli_num_rows($sql1);
        return $row_count;
    }
    
    public function addEvent($event){
        $this->throwIfTableDoesNotExist();
        $query = "INSERT INTO events
                    (ID, ID_EVENT, ID_NEWS, ANNOUNCED_TIME, REAL_TIME, ACTUAL, PREVIOUS,
                    NEXT_EVENT, STATE)
                    VALUES (NULL,".$event->getEventId().",".$event->getNewsId().",
                    '".$event->getAnnouncedTime()->format('Y-m-d H:i:s')."',
                    '".$event->getRealTime()->format('Y-m-d H:i:s')."',".$event->getActual().", 
                    ".$event->getPrevious().", ".$event->getNextEvent().", ".$event->getState().")";
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Event already in table: ". $this->mysqli->error);
        }
        return $this->mysqli->insert_id;
    }
    
    public function tryAddingEvent($event){
        try{
            return $this->addEvent($event);
        }
        catch(Exception $e){
            return $this->getEventByEventId($event->getEventId())->getId();
        }
    }
    
    public function removeEventById($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "DELETE FROM events
                    WHERE ID=".$identifier;
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }    
    }
    
    public function getEventById($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE ID=".$identifier;
        if($result = $this->mysqli->query($query)){
            while($row = $result->fetch_array()){
                return Event::createEventFromDbArray($row);
            }
            throw new Exception("Event does not exists, id:".$identifier);
        }
        else{
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function getEventByEventId($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE ID_EVENT=".$identifier;
        if($result = $this->mysqli->query($query)){
            while($row = $result->fetch_array())
            {
                return Event::createEventFromDbArray($row);
            }
            throw new Exception("Event does not exists, event id:".$identifier);
        }
        else{
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function updateEvent($event){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE events SET ACTUAL = ".$event->getActual().",
                REAL_TIME='".$event->getRealTime()->format('Y-m-d H:i:s')."', 
                STATE=".$event->getState()." WHERE ID=".$event->getId();
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function emptyTable(){
        $this->throwIfTableDoesNotExist();
        $query = "TRUNCATE TABLE events";
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function getEventsFromTo($fromDate, $toDate, $state=-1){
        $this->throwIfWrongArgumentType($fromDate, $toDate, $state);
        $query = $this->buildSelectQueryFromToState($fromDate, $toDate, $state);
        $events = [];
        if($result = $this->mysqli->query($query)){
            while($row = $result->fetch_array())
            {
                $events[] = Event::createEventFromDbArray($row);
            }
        }
        else{
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
        return $events;
    }
    
    private function buildSelectQueryFromToState($fromDate, $toDate, $state)
    {
        $state_suffix = "";
        if($state != -1){
            $state_suffix = " AND STATE=".$state;
        }
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE DATEDIFF(ANNOUNCED_TIME,'".$fromDate->format('Y-m-d H:i:s').
        "') >= 0 AND DATEDIFF(ANNOUNCED_TIME,'".$toDate->format('Y-m-d H:i:s').
        "') <= 0".$state_suffix;
        return $query;
    }
    
    private function throwIfWrongArgumentType($fromDate, $toDate, $state)
    {
        if(!is_a($fromDate, 'DateTime') || !is_a($toDate, 'DateTime')){
            throw new ErrorException("Wrong type for from or to. Expected DateTime got: ".gettype($fromDate)." and ".gettype($toDate));
        }
        if(!is_int($state)){
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
    }
 
    private function throwIfTableDoesNotExist(){
        if(!$this->doesTableExists()){
            throw new ErrorException("Table does not exists.");
        }
    }
}


