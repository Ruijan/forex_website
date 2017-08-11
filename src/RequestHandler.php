<?php
abstract class Request{
    const TODAY_EVENTS      = 0;
    const CURRENT_TRADES    = 1;
    const NEXT_EVENTS       = 2;
    const PREDICTABLE_TRADE = 3;
    const NEXT_ACTION       = 4;
    const UPDATE_MARKET     = 5;
    const PREDICT_TRADE     = 6;
    const OPEN_TRADE        = 7;
    const CLOSE_TRADE       = 8;
    const CANCEL_TRADE      = 9;
}


class RequestHandler{
    private $request;
    private $eventDBHandler;
    private $eventParser;
    private $tradeDBHandler;
    
    static public function getRequestTypeFromString($strRequest){
        switch($strRequest){
            case "today_events":
                return Request::TODAY_EVENTS;
            case "current_trades":
                return Request::CURRENT_TRADES;
            case "next_events":
                return Request::NEXT_EVENTS;
            case "predictable_trade":
                return Request::PREDICTABLE_TRADE;
            case "next_action":
                return Request::NEXT_ACTION;
            case "update_market":
                return Request::UPDATE_MARKET;
            case "predict_trade":
                return Request::PREDICT_TRADE;
            case "open_trade":
                return Request::OPEN_TRADE;
            case "close_trade":
                return Request::CLOSE_TRADE;
            case "cancel_trade":
                return Request::CANCEL_TRADE;
        }
        throw new ErrorException("Invalid Request");
    }
    
    static public function doesRequestExist($request){
        if($request >= Request::TODAY_EVENTS &&  $request <= Request::CANCEL_TRADE){
            return true;
        }
        return false;
    }
    
    public function __construct($request){
        $this->setRequest($request);
    }
    
    public function initTodayEventsHandler($eventDBHandler, $eventParser){
        $this->setEventDBHandler($eventDBHandler);
        $this->setEventParser($eventParser);
    }
    
    public function initUpdateMarketHandler($tradeDBHandler){
        $this->setTradeDBHandler($tradeDBHandler);
    }
    
    public function getRequest(){return $this->request;}
    
    public function isCorrectlyInitialized(){
        switch($this->request){
            case Request::TODAY_EVENTS:
                return $this->eventDBHandler != null and $this->eventParser != null;
            case Request::UPDATE_MARKET:
                return $this->tradeDBHandler != null;
        }
        throw new ErrorException("Request not handled by initialization checker");
    }
    
    public function setEventDBHandler($eventDBHandler){
        if(is_a($eventDBHandler, 'EventDBHandler')){
            $this->eventDBHandler = $eventDBHandler;
        }
        else{
            throw new ErrorException("Wrong type for eventDBHandler. Expected EventDBHandler got: ".
                gettype($eventDBHandler));
        }
    }
    
    public function setEventParser($eventParser){
        if(is_a($eventParser, 'EventParser')){
            $this->eventParser = $eventParser;
        }
        else{
            throw new ErrorException("Wrong type for eventParser. Expected EventParser got: ".
                gettype($eventParser));
        }
    }
    
    public function setTradeDBHandler($tradeDBHandler){
        if(is_a($tradeDBHandler, 'TradeDBHandler')){
            $this->tradeDBHandler = $tradeDBHandler;
        }
        else{
            throw new ErrorException("Wrong type for tradeDBHandler. Expected TradeDBHandler got: ".
                gettype($tradeDBHandler));
        }
    }
    
    private function setRequest($request){
        if(is_int($request)){
            if($this->doesRequestExist($request)){
                $this->request = $request;
            }
            else{
                throw new ErrorException("Invalid Request type");
            }
        }
        else{
            throw new ErrorException("Wrong type for request. Expected int got: ".gettype($request));
        }
    }
    
    public function execute(){
        if(!$this->isCorrectlyInitialized()){
            throw new ErrorException("Error in the Initialization");
        }
        switch($this->request){
            case Request::TODAY_EVENTS:
                $this->eventDBHandler->createTable();
                $this->eventParser->retrieveTableOfEvents();
                $this->eventParser->createEventsFromTable();
                $events = $this->eventParser->getEvents();
                $today_UTC = DateTime::createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
                $db_events = $this->eventDBHandler->getEventsFromTo($today_UTC, $today_UTC);
                
                foreach($events as $event){
                    $event->setId($this->eventDBHandler->tryAddingEvent($event));
                    $this->updateEvent($db_events);
                }
                break;
            case Request::UPDATE_MARKET:
                $this->tradeDBHandler->createTable();
        }
    }
    
    private function updateEvent($db_events)
    {
        $events_to_remove = [];
        if(sizeof($db_events) > 0){
            foreach ($db_events as $db_event){
                if($event->getId() == $db_event->getId()){
                    $events_to_remove[] = $db_event;
                    if($db_event->getState() != $event->getState()){
                        $this->eventDBHandler->updateEvent($event);
                    }
                }
            }
            $db_event = array_diff($db_event, $events_to_remove);
        }
    }

    
}