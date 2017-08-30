<?php
namespace src\requests;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'Trade.php');
require_once($path.'functions.php');
require_once('ForexRequest.php');


class CancelTradeRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["trade_id"])){
                throw new \ErrorException("Ill-formed request: missing parameters");
        }
        if(!is_numeric($this->parameters["trade_id"])
            || !is_int(getNumeric($this->parameters["trade_id"]))){
                throw new \ErrorException("Invalid Request: bad parameters type");
        }
    }
    public function execute(){
        $this->validateRequest();
        $this->tradeDBHandler->removeTradeByID($this->parameters["trade_id"]);
    }
}

