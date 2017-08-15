<?php
namespace src\requests;

require_once('ForexRequest.php');

class OpenTradeRequest extends ForexRequest
{

    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["trade_id"])){
            throw new \ErrorException("Ill-formed request: missing parameters");
        }
        if(!is_int($this->parameters["trade_id"])){
            throw new \ErrorException("Invalid Request: bad parameters type");
        }
    }
    
    public function execute(){
        $this->validateRequest();
        $trade = $this->tradeDBHandler->getTradeByID($this->parameters["trade_id"]);
        $trade->open(\DateTime::createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time()))));
        $this->tradeDBHandler->openTrade($trade);
    }
}

