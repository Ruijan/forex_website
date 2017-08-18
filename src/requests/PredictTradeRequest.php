<?php
namespace src\requests;

use ErrorException;

$path = str_replace("requests\\", "",  __DIR__."/");
$path = str_replace("requests", "", $path."/");
require_once('ForexRequest.php');
require_once($path.'Trade.php');

class PredictTradeRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["trade_id"]) or !isset($this->parameters["prediction"])){
            throw new ErrorException("Ill-formed request: missing parameters");
        }
        if(!is_int($this->parameters["prediction"]) || !is_int($this->parameters["trade_id"]) ||
            (isset($this->parameters["probability_prediction"]) and 
                !$this->isDecimal($this->parameters["probability_prediction"]))){
                throw new ErrorException("Invalid Request: bad parameters type");
        }
    }
    
    private function isDecimal($var)
    {
        return is_float($var) or is_double($var);
    }
    
    public function execute(){
        $this->validateRequest();
        $trade = $this->tradeDBHandler->getTradeByID($this->parameters["trade_id"]);
        $probability = -1;
        if(isset($this->parameters["probability_prediction"])){
            $probability = $this->parameters["probability_prediction"];
        }
        $trade->predict($this->parameters["prediction"], $this->parameters["probability_prediction"]);
        $this->tradeDBHandler->predictTrade($trade);
    }
    
    
}

