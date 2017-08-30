<?php
namespace src\requests;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'Trade.php');
require_once($path.'functions.php');

require_once('ForexRequest.php');

class CloseTradeRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["trade_id"]) or !isset($this->parameters["gain"]) or
            !isset($this->parameters["commission"])){
            throw new \ErrorException("Ill-formed request: missing parameters");
        }
        if(!is_numeric($this->parameters["trade_id"])
            or !is_int(getNumeric($this->parameters["trade_id"])) or 
            !is_numeric($this->parameters["gain"])
            or !$this->isDecimal(getNumeric($this->parameters["gain"])) or
            !is_numeric($this->parameters["commission"])
            or !$this->isDecimal(getNumeric($this->parameters["commission"]))){
            throw new \ErrorException("Invalid Request: bad parameters type");
        }
    }
    
    public function execute(){
        $this->validateRequest();
        $trade = $this->tradeDBHandler->getTradeByID($this->parameters["trade_id"]);
        $todayUTC = new \DateTime();
        $todayUTC = $todayUTC->createFromFormat('Y-m-d H:i:s',gmdate('Y-m-d H:i:s', time()));
        $trade->close($this->parameters["gain"],
            $this->parameters["commission"],
            $todayUTC);
        $this->tradeDBHandler->closeTrade($trade);
    }
    
    private function isDecimal($var)
    {
        return is_float($var) or is_double($var);
    }
}

