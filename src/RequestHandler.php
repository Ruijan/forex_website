<?php

require_once('EventDBHandler.php');
require_once('EventParser.php');
require_once('TradeDBHandler.php');

$pathToRequest = __DIR__."/requests/";

require_once($pathToRequest.'CollectEventsRequest.php');
require_once($pathToRequest.'GUIDisplayerRequest.php');
require_once($pathToRequest.'UpdateMarketRequest.php');
require_once($pathToRequest.'ForexRequest.php');
require_once($pathToRequest.'CancelTradeRequest.php');
require_once($pathToRequest.'CloseTradeRequest.php');
require_once($pathToRequest.'OpenTradeRequest.php');
require_once($pathToRequest.'PredictableTradesRequest.php');
require_once($pathToRequest.'PredictTradeRequest.php');
require_once($pathToRequest.'NextActionRequest.php');

abstract class Request{
    const FETCH_EVENTS      = 0;
    const GUI               = 1;
    const PREDICTABLE_TRADE = 2;
    const NEXT_ACTION       = 3;
    const UPDATE_MARKET     = 4;
    const PREDICT_TRADE     = 5;
    const OPEN_TRADE        = 6;
    const CLOSE_TRADE       = 7;
    const CANCEL_TRADE      = 8;
}


class RequestHandler{
    private $request = -1;
    private $eventDBHandler;
    private $eventParser;
    private $tradeDBHandler;
    private $requestHandlers;
    
    public function getRequestTypeFromString($strRequest){
        switch($strRequest){
            case "fetch_events":
                return Request::FETCH_EVENTS;
            case "":
                return Request::GUI;
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
        $this->eventDBHandler->createTable();
        $this->tradeDBHandler->createTable();
    }
    
    public function getRequest(){return $this->request;}
    
    public function isCorrectlyInitialized(){
        return $this->eventDBHandler != null and $this->eventParser != null and 
        $this->tradeDBHandler != null and $this->doesRequestExist($this->request) and 
        $this->eventDBHandler->doesTableExists() and $this->tradeDBHandler->doesTableExists();
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
        if(sizeof($requestHandlers) < 8){
            throw new ErrorException("Wrong number of request handlers. Got ".
                sizeof($requestHandlers)." expected 9");
        }
        foreach ($requestHandlers as $handler){
            $this->tryAddingRequestHandler($handler);
        }
    }
    private function tryAddingRequestHandler($handler)
    {
        if(strstr(get_class($handler),"UpdateMarketRequest")){
            $this->requestHandlers[Request::UPDATE_MARKET] = $handler;
        }
        elseif(strstr(get_class($handler),"GUIDisplayerRequest")){
            $this->requestHandlers[Request::GUI] = $handler;
        }
        elseif (strstr(get_class($handler),"CollectEventsRequest")){
            $this->requestHandlers[Request::FETCH_EVENTS] = $handler;
        }
        elseif (strstr(get_class($handler),"PredictTradeRequest")){
            $this->requestHandlers[Request::PREDICT_TRADE] = $handler;
        }
        elseif (strstr(get_class($handler),"PredictableTradesRequest")){
            $this->requestHandlers[Request::PREDICTABLE_TRADE] = $handler;
        }
        elseif (strstr(get_class($handler),"OpenTradeRequest")){
            $this->requestHandlers[Request::OPEN_TRADE] = $handler;
        }
        elseif (strstr(get_class($handler),"CloseTradeRequest")){
            $this->requestHandlers[Request::CLOSE_TRADE] = $handler;
        }
        elseif (strstr(get_class($handler),"NextActionRequest")){
            $this->requestHandlers[Request::NEXT_ACTION] = $handler;
        }
        elseif (strstr(get_class($handler),"CancelTradeRequest")){
            $this->requestHandlers[Request::CANCEL_TRADE] = $handler;
        }
        else{
            throw new ErrorException("Wrong type of request handler: ".get_class($handler));
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