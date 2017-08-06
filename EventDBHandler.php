<?php
require_once 'Event.php';

class EventDBHandler
{
    private $mysqli;
    public function __construct($mysqli)
    {$this->mysqli = $mysqli;}
    
    public function doesTableExists(){
        if ($result = $this->mysqli->query("SHOW TABLES LIKE 'events'")) {
            if($result->num_rows >= 1) {
                return true;
            }
        }
        else {
            return false;
        }
    }
    
    public function createTable(){
        if(!$this->doesTableExists()){
            
            $query = "CREATE TABLE events (
                        ID int(11) AUTO_INCREMENT,
                        ID_EVENT int(11) NOT NULL,
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
        }
    }
    
    public function deleteTable(){
        if($this->doesTableExists()){
            $this->mysqli->query("DROP TABLE events");
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
            echo "Error: " . $query . "<br>" . $this->mysqli->error;
        }
        else{
            return $this->mysqli->insert_id;
        }
    }
    
    public function removeEventById($id){
        $this->throwIfTableDoesNotExist();
        $query = "DELETE FROM events
                    WHERE ID=".$id;
        if($this->mysqli->query($query) === FALSE){
            echo "Error: " . $query . "<br>" . $this->mysqli->error;
        }    
    }
    
    public function getEventById($id){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE ID=".$id;
        if($result = $this->mysqli->query($query)){
            while($row = $result->fetch_array())
            {
                return Event::createEventFromDbArray($row);
            }
        }
        else{
            echo "Error: " . $query . "<br>" . $this->mysqli->error;
        }
    }
    
    public function updateEvent($event){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE events SET ACTUAL = ".$event->getActual().",
                REAL_TIME='".$event->getRealTime()->format('Y-m-d H:i:s')."', 
                STATE=".$event->getState()." WHERE ID=".$event->getId();
        if($this->mysqli->query($query) === FALSE){
            echo "Error: " . $query . "<br>" . $this->mysqli->error;
        }
    }
    
    public function emptyTable(){
        $this->throwIfTableDoesNotExist();
        $query = "TRUNCATE TABLE events";
        if($this->mysqli->query($query) === FALSE){
            echo "Error: " . $query . "<br>" . $this->mysqli->error;
        }
    }
    
    public function getEventsFromTo($from, $to, $state=-1){
        $this->throwIfWrongArgumentType($from, $to, $state);
        $query = $this->buildSelectQueryFromToState($from, $to, $state);
        $events = [];
        if($result = $this->mysqli->query($query)){
            while($row = $result->fetch_array())
            {
                $events[] = Event::createEventFromDbArray($row);
            }
        }
        else{
            echo "Error: " . $query . "<br>" . $this->mysqli->error;
        }
        return $events;
    }
    
    private function buildSelectQueryFromToState($from, $to, $state)
    {
        $state_suffix = "";
        if($state != -1){
            $state_suffix = " AND STATE=".$state;
        }
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM events WHERE DATEDIFF(ANNOUNCED_TIME,'".$from->format('Y-m-d H:i:s').
        "') >= 0 AND DATEDIFF(ANNOUNCED_TIME,'".$to->format('Y-m-d H:i:s').
        "') <= 0".$state_suffix;
        return $query;
    }
    
    private function throwIfWrongArgumentType($from, $to, $state)
    {
        if(!is_a($from, 'DateTime') || !is_a($to, 'DateTime')){
            throw new ErrorException("Wrong type for from or to. Expected DateTime got: ".gettype($from)." and ".gettype($to));
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


