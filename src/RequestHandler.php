<?php
abstract class Request{
    const FETCH_EVENTS      = 0;
    const PREDICTABLE_TRADE = 1;
    const NEXT_ACTION       = 2;
    const UPDATE_MARKET     = 3;
    const PREDICT_TRADE     = 4;
    const OPEN_TRADE        = 5;
    const CLOSE_TRADE       = 6;
    const CANCEL_TRADE      = 7;
}


class RequestHandler{
    private $request = -1;
    private $eventDBHandler;
    private $eventParser;
    private $tradeDBHandler;
    private $requestHandlers;
    
    public function getRequestTypeFromString($strRequest){
        switch($strRequest){
            case "FETCH_EVENTS":
                return Request::FETCH_EVENTS;
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
    
    public function doesRequestExist($request){
        if($request >= Request::FETCH_EVENTS &&  $request <= Request::CANCEL_TRADE){
            return true;
        }
        return false;
    }
    
    public function __construct(){
    }
    
    public function init($tradeDBHandler, $eventDBHandler, $eventParser){
        $this->setEventDBHandler($eventDBHandler);
        $this->setEventParser($eventParser);
        $this->setTradeDBHandler($tradeDBHandler);
    }
    
    public function getRequest(){return $this->request;}
    
    public function isCorrectlyInitialized(){
        return $this->eventDBHandler != null and $this->eventParser != null and 
        $this->tradeDBHandler != null and $this->doesRequestExist($this->request);
    }
    
    public function setEventDBHandler($eventDBHandler){
        if(!is_a($eventDBHandler, 'EventDBHandler')){
            throw new ErrorException("Wrong type for eventDBHandler. Expected EventDBHandler got: ".
                gettype($eventDBHandler));
        }
        $this->eventDBHandler = $eventDBHandler;
    }
    
    public function setEventParser($eventParser){
        if(!is_a($eventParser, 'EventParser')){
            throw new ErrorException("Wrong type for eventParser. Expected EventParser got: ".
                gettype($eventParser));
        }
        $this->eventParser = $eventParser;
    }
    
    public function setTradeDBHandler($tradeDBHandler){
        if(!is_a($tradeDBHandler, 'TradeDBHandler')){
            throw new ErrorException("Wrong type for tradeDBHandler. Expected TradeDBHandler got: ".
                gettype($tradeDBHandler));
        }
        $this->tradeDBHandler = $tradeDBHandler;
    }
    
    public function setRequest($request){
        if(!is_int($request)){
            throw new ErrorException("Wrong type for request. Expected int got: ".gettype($request));
        }
        if(!$this->doesRequestExist($request)){
            throw new ErrorException("Invalid Request type");
        }
        $this->request = $request;
    }
    
    public function setRequestHandlers($requestHandlers){
        if(sizeof($requestHandlers) < 2){
            throw new ErrorException("Wrong number of request handlers. Got ".
                sizeof($requestHandlers)." expected 2");
        }
        foreach ($requestHandlers as $handler){
            if(is_a($handler,"UpdateMarketRequest")){
                $this->requestHandlers[Request::UPDATE_MARKET] = $handler;
            }
            elseif (is_a($handler,"CollectEventsRequest")){
                $this->requestHandlers[Request::FETCH_EVENTS] = $handler;
            }
            else{
                throw new ErrorException("Wrong type of request handler.");
            }
        }
    }
    
    public function getRequestHandlers(){return $this->requestHandlers;}
    
    public function execute(){
        if(!$this->isCorrectlyInitialized()){
            throw new ErrorException("Error in the Initialization");
        }
        $this->requestHandlers[$this->request]->execute();
    }
    

    
}