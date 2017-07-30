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
        if($this->doesTableExists()){
            $sql1 = $this->mysqli->query("SELECT * FROM events");
            $row_count= mysqli_num_rows($sql1);
            return $row_count;
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function addEvent($event){
        if($this->doesTableExists()){
            $query = "INSERT INTO events
                        (ID, ID_EVENT, ID_NEWS, ANNOUNCED_TIME, REAL_TIME, ACTUAL, PREVIOUS,
                        NEXT_EVENT, STATE)
                        VALUES (NULL,".$event->getEventId().",".$event->getNewsId().",
                        '".$event->getAnnouncedTime()->format('Y-m-d H:i:s')."',
                        '".$event->getRealTime()->format('Y-m-d H:i:s')."',".$event->getActual().", 
                        ".$event->getPrevious().", ".$event->getNextEvent().", ".$event->getId().")";
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
            else{
                return $this->mysqli->insert_id;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function removeEventById($id){
        if($this->doesTableExists()){
            $query = "DELETE FROM events
                        WHERE ID=".$id;
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function getEventById($id){
        if($this->doesTableExists()){
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
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function updateEvent($event){
        if($this->doesTableExists()){
            $query = "UPDATE events SET ACTUAL = ".$event->getActual().",
                    REAL_TIME='".$event->getRealTime()->format('Y-m-d H:i:s')."', 
                    STATE=".$event->getState()." WHERE ID=".$event->getId();
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
}

