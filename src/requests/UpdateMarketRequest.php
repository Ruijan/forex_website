<?php
namespace src\requests;

require_once('ForexRequest.php');

use DateTime;
use ErrorException;

class UpdateMarketRequest extends ForexRequest
{
    
    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($this->parameters["dv_p_tm5"]) or !isset($this->parameters["dv_p_t0"]) or
            !isset($this->parameters["currency"])){
            throw new ErrorException("Ill-formed request: missing parameters");           
        }
        if(!$this->isDecimal($this->parameters["dv_p_tm5"]) || !$this->isDecimal($this->parameters["dv_p_t0"]) ||
            !$this->isValidCurrency($this->parameters["currency"])){
            throw new ErrorException("Invalid Request: bad parameters type");
        }
    }
    
    private function isValidCurrency($currency)
    {
        return $currency == "EUR_USD";
    }

    
    private function isDecimal($var)
    {
        return is_float($var) or is_double($var);
    }
    
    public function execute(){
        $this->validateRequest();
        $now_utc = DateTime::createFromFormat('Y-m-d',(gmdate('Y-m-d', time())));
        $this->tradeDBHandler->setCurrency($this->parameters["currency"]);
        $trades = $this->tradeDBHandler->getTradesFromTo($now_utc, $now_utc, \TradeState::INITIALIZED);
        foreach ($trades as $trade){
            $trade->fillMarketInfo($this->parameters["dv_p_tm5"], $this->parameters["dv_p_t0"]);
            $this->tradeDBHandler->fillTradeWithMarketInfo($trade);
        }
    }
}

