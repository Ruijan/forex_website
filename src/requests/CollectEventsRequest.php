<?php
namespace src\requests;

use DateTime;
use Trade;

$path = str_replace("requests\\", "",  __DIR__."/");
$path = str_replace("requests", "", $path."/");
require_once('ForexRequest.php');
require_once($path.'Trade.php');
require_once($path.'Event.php');

class CollectEventsRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function execute(){
        $this->eventParser->retrieveTableOfEvents();
        $this->eventParser->createEventsFromTable();
        $events = $this->eventParser->getEvents();
        $today_UTC = DateTime::createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
        $db_events = $this->eventDBHandler->getEventsFromTo($today_UTC, $today_UTC);
        
        foreach($events as $event){
            $event->setId($this->eventDBHandler->tryAddingEvent($event));
            $this->updateEvents($db_events, $event);
        }
    }
    
    
    private function updateEvents($db_events, $event)
    {
        if(sizeof($db_events) > 0){
            foreach ($db_events as $db_event){
                if($event->getId() == $db_event->getId()){
                    if($db_event->getState() != $event->getState()){
                        $this->eventDBHandler->updateEvent($event);
                        $now_utc = DateTime::createFromFormat('Y-m-d',(gmdate('Y-m-d', time())));
                        $this->tradeDBHandler->tryAddingTrade(
                            new Trade($event->getEventId(), $now_utc));
                    }
                }
            }
        }
    }
}

