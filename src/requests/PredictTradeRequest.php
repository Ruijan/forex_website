<?php
namespace src\requests;

use ErrorException;

$path = str_replace("requests\\", "",  __DIR__."/");
$path = str_replace("requests", "", $path."/");
require_once('ForexRequest.php');
require_once($path.'Trade.php');
require_once($path.'functions.php');



class PredictTradeRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["trade_id"]) or !isset($this->parameters["prediction"])){
            throw new ErrorException("Ill-formed request: missing parameters");
        }
        
        if(!is_numeric($this->parameters["prediction"])  || !is_numeric($this->parameters["trade_id"]) 
            || !is_int(getNumeric($this->parameters["trade_id"])) ||
            (isset($this->parameters["probability_prediction"]) and 
                !is_numeric($this->parameters["probability_prediction"]))){
                throw new ErrorException("Invalid Request: bad parameters type");
        }
        $this->parameters["prediction"] = intval($this->parameters["prediction"]);
        $this->parameters["trade_id"] = intval($this->parameters["trade_id"]);
        if(isset($this->parameters["probability_prediction"])){
            $this->parameters["probability_prediction"] = intval($this->parameters["probability_prediction"]);
        }
    }
    
    private function isDecimal($var)
    {
        return is_float($var) or is_double($var);
    }
    
    public function execute(){
        $this->validateRequest();
        $trade = $this->tradeDBHandler->getTradeByID($this->parameters["trade_id"]);
        $probability = 0.5;
        if(isset($this->parameters["probability_prediction"])){
            $probability = $this->parameters["probability_prediction"];
        }
        $trade->predict($this->parameters["prediction"], $probability);
        $this->tradeDBHandler->predictTrade($trade);
    }
    
    
}

