<?php
namespace src\requests;

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
        $todayUTC = new \DateTime();
        $todayUTC = $todayUTC->createFromFormat('Y-m-d H:i:s',gmdate('Y-m-d H:i:s', time()));
        $dbEvents = $this->eventDBHandler->getEventsFromTo($todayUTC, $todayUTC);
        
        foreach($events as $event){
            $event->setId($this->eventDBHandler->tryAddingEvent($event));
            $this->updateEvents($dbEvents, $event);
        }
    }
    
    
    private function updateEvents($dbEvents, $event)
    {
        if(sizeof($dbEvents) > 0){
            foreach ($dbEvents as $db_event){
                if($event->getId() == $db_event->getId()){
                    if($db_event->getState() != $event->getState()){
                        $this->eventDBHandler->updateEvent($event);
                        $todayUTC = new \DateTime();
                        $todayUTC = $todayUTC->createFromFormat('Y-m-d',gmdate('Y-m-d', time()));
                        $this->tradeDBHandler->tryAddingTrade(
                            new Trade($event->getEventId(), $todayUTC, "EUR_USD"));
                    }
                }
            }
        }
    }
}

