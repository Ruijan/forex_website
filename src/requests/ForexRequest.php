<?php
namespace src\requests;

use ErrorException;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'EventDBHandler.php');
require_once($path.'EventParser.php');
require_once($path.'TradeDBHandler.php');

class ForexRequest
{
    protected $eventDBHandler;
    protected $eventParser;
    protected $tradeDBHandler;
    protected $parameters;
    
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
    
    public function setParameters($parameters){
        if(!is_array($parameters)){
            throw new ErrorException("Wrong type for parameters. Expected Array got: ".
                gettype($parameters));
        }
        $this->parameters = $parameters;
    }
    
    public function getParameters(){return $this->parameters;}
    
    public function validateRequest(){
        
    }
    
    public function execute(){
        
    }
    
    public function init($tradeDBHandler, $eventDBHandler, $eventParser, $parameters){
        $this->setEventDBHandler($eventDBHandler);
        $this->setEventParser($eventParser);
        $this->setTradeDBHandler($tradeDBHandler);
        $this->setParameters($parameters);
    }
}

