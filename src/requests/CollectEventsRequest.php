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
            foreach ($dbEvents as $dbEvent){
                if($event->getId() == $dbEvent->getId()){
                    if($dbEvent->getState() != $event->getState()){
                        $this->eventDBHandler->updateEvent($event);
                        $this->addTradeToDbFromEvent($event);
                        $this->addTradeToDbFromEvents($event, $dbEvents);
                    }
                }
            }
        }
    }
    
    public function addTradeToDbFromEvent($event)
    {
        if($event->getPreviousEvent() != 0 and $event->getNextEvent() != 0){
            $todayUTC = new \DateTime();
            $todayUTC = $todayUTC->createFromFormat('Y-m-d',gmdate('Y-m-d', time()));
            $this->tradeDBHandler->tryAddingTrade(
                new Trade($event->getId(), $event->getEventId(), $todayUTC, "EUR_USD"));
        }
    }
    
    public function addTradeToDbFromEvents($event, $dbEvents){
        if($event->getPreviousEvent() == 0 or $event->getNextEvent() == 0){
            $groupOfEvents = [];
            $isInGroup = false;
            $allUpdated = true;
            $groupId = "";
            $newsId = "";
            foreach($dbEvents as $dbEvent){
                if($dbEvent->getAnnouncedTime() == $event->getAnnouncedTime() and $dbEvent->getStrength() > 1){
                    $groupOfEvents[] = $dbEvent;
                    $groupId .= $dbEvent->getEventId()."_";
                    $newsId .= $dbEvent->getId()."_";
                    if($dbEvent == $event){
                        $isInGroup = true;
                    }
                    if($dbEvent->getState() == \EventState::PENDING){
                        $allUpdated = false;
                    }
                }
            }
            $groupId = substr($groupId,0,-1);
            $newsId = substr($newsId,0,-1);
            if($isInGroup and $allUpdated){
                $todayUTC = new \DateTime();
                $todayUTC = $todayUTC->createFromFormat('Y-m-d',gmdate('Y-m-d', time()));
                $this->tradeDBHandler->tryAddingTrade(new Trade($newsId, $groupId, $todayUTC, "EUR_USD"));
            }
        }
    }

}

