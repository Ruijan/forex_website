<?php
namespace src\requests;

use ErrorException;

class ForexRequest
{
    protected $eventDBHandler;
    protected $eventParser;
    protected $tradeDBHandler;
    
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
    
    public function validateRequest(){
        
    }
    
    public function execute(){
        
    }
    
    public function init($tradeDBHandler, $eventDBHandler, $eventParser){
        $this->setEventDBHandler($eventDBHandler);
        $this->setEventParser($eventParser);
        $this->setTradeDBHandler($tradeDBHandler);
    }
}

