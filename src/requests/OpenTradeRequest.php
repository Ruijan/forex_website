<?php
namespace src\requests;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'Trade.php');
require_once($path.'functions.php');

require_once('ForexRequest.php');

class OpenTradeRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["trade_id"])){
            throw new \ErrorException("Ill-formed request: missing parameters");
        }
        if(!is_numeric($this->parameters["trade_id"])
            || !is_int(getNumeric($this->parameters["trade_id"]))){
            throw new \ErrorException("Invalid Opening Request: bad parameters type");
        }
        $this->parameters["trade_id"] = getNumeric($this->parameters["trade_id"]);
    }
    
    public function execute(){
        $this->validateRequest();
        $trade = $this->tradeDBHandler->getTradeByID($this->parameters["trade_id"]);
        $todayUTC = new \DateTime();
        $todayUTC = $todayUTC->createFromFormat('Y-m-d H:i:s',gmdate('Y-m-d H:i:s', time()));
        $trade->open($todayUTC);
        $this->tradeDBHandler->openTrade($trade);
    }
}

