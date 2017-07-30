<?php
/**
 * Created by PhpStorm.
 * User: MSI-GP60
 * Date: 7/15/2016
 * Time: 8:09 PM
 *
 * =========================================================
 * FUNCTIONS
 * connect_database()
 * createNewUser($mysqli, $id, $name)
 * createSettingsDepedency($mysqli, $id_user)
 * createTowersDepedency($mysqli, $id_user)
 * getUserSetting($mysqli, $id_user, $setting)
 * getUserTowerData($mysqli, $id_user, $tower_name)
 * isUserExist($mysqli, $id, $name)
 * checkUserParameter($mysqli, $id_game, $column_name, $value)
 * deleteUserByID($mysqli, $id)
 * deleteUserByGameID($mysqli, $id_game)
 * deleteSettingsDepedency($mysqli, $id_user)
 * deleteTowersDepedency($mysqli, $id_user)
 * updateUserLastConnectionTime($mysqli, $id_game, $time)
 */
require("connect.php");

class Event
{
    // property declaration
    public $id = 0;
    public $event_id;
    public $news_id;
    public $announced_time;
    public $real_time;
    public $actual = 0;
    public $previous = 0;
    public $state = 0;
    public $next_event = 0;

    // method declaration
    function __construct($event_id, $news_id, $announced_time, $previous, $next_event) {
        $this->setEventId($event_id);
        $this->setNewsId($news_id);
        $this->setAnnouncedTime($announced_time);
        $this->setRealTime(new DateTime("0000-00-00 00:00:00"));
        $this->setPrevious($previous);
        $this->setNextEvent($next_event);
        
    }
    public function getId(){return $this->id;}
    public function getNewsId(){return $this->news_id;}
    public function getEventId(){return $this->event_id;}
    public function getAnnouncedTime(){return $this->announced_time;}
    public function getRealTime(){return $this->real_time;}
    public function getActual(){return $this->actual;}
    public function getPrevious(){return $this->previous;}
    public function getNextEvent(){return $this->next_event;}
    public function getState(){return $this->state;}
    
    public function setId($id){
        if(is_int($id)){
            $this->id = $id;
        }
        else{
            throw new ErrorException("Wrong type for id. Expected int got: ".gettype($id));
        }
    }
    
    public function setEventId($event_id){
        if(is_int($event_id)){
            $this->event_id = $event_id;
        }
        else{
            throw new ErrorException("Wrong type for event_id. Expected int got: ".gettype($event_id));
        }
    }
    
    public function setNewsId($news_id){
        if(is_int($news_id)){
            $this->news_id = $news_id;
        }
        else{
            throw new ErrorException("Wrong type for news_id. Expected int got: ".gettype($news_id));
        }
    }
    
    public function setAnnouncedTime($actual_time){
        if(is_a($actual_time, 'DateTime')){
            $this->announced_time = $actual_time;
        }
        else{
            throw new ErrorException("Wrong type for actual_time. Expected DateTime got: ".gettype($actual_time));
        }
    }
    
    public function setRealTime($real_time){
        if(is_a($real_time, 'DateTime')){
            $this->real_time = $real_time;
        }
        else{
            throw new ErrorException("Wrong type for real_time. Expected DateTime got: ".gettype($real_time));
        }
    }
    
    public function setActual($actual)
    {
        if(is_float($actual) or is_int($actual) or is_double($actual)){
            $this->actual = $actual;
        }
        else{
            throw new ErrorException("Wrong type for actual. Expected float or double or int got: ".gettype($actual));
        }
    }
    
    public function setPrevious($previous)
    {
        if(is_float($previous) or is_int($previous) or is_double($previous)){
            $this->previous = $previous;
        }
        else{
            throw new ErrorException("Wrong type for previous. Expected float or double or int got: ".gettype($previous));
        }
    }
    
    public function setNextEvent($next_event)
    {
        if(is_int($next_event) and $next_event >= 0){
            $this->next_event = $next_event;
        }
        else{
            throw new ErrorException("Wrong type for next_event. Expected int got: ".gettype($next_event));
        }
    }
    
    public function setState($state)
    {
        if(is_int($state) and $state >= 0){
            $this->state = $state;
        }
        else{
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
    }
    
    static public function createEventFromDbArray($result)
    {
        $event = new Event((int)$result["ID_EVENT"], (int)$result["ID_NEWS"], 
            new DateTime($result["ANNOUNCED_TIME"]), (float)$result["PREVIOUS"], (int)$result["NEXT_EVENT"]);
        $event->setId((int)$result["ID"]);
        $event->setActual((float)$result["ACTUAL"]);
        $event->setState((int)$result["STATE"]);
        $event->setRealTime(new DateTime($result["REAL_TIME"]));
        return $event;
    }
    
    public function update($actual, $real_time){
        $this->setActual($actual);
        $this->setRealTime($real_time);
        $this->setState(1);
    }
}
