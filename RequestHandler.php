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
    
    public function init($eventDBHandler, $eventParser){
        $this->eventDBHandler = $eventDBHandler;
        $this->eventParser = $eventParser;
    }
    
    public function getRequest(){return $this->request;}
    
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
        switch($this->request){
            case Request::TODAY_EVENTS:
                $this->eventDBHandler->createTable();
                $this->eventParser->retrieveTableOfEvents();
                $this->eventParser->createEventsFromTable();
                $events = $this->eventParser->getEvents();
                foreach($events as $event){
                    $this->eventDBHandler->addEvent($event);
                }
                
        }
        
    }
    
}