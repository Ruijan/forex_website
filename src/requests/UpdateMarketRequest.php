<?php
namespace src\requests;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'Trade.php');

require_once('ForexRequest.php');

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
        $todayUTC = new \DateTime();
        $todayUTC->createFromFormat('Y-m-d',gmdate('Y-m-d', time()));
        $this->tradeDBHandler->setCurrency($this->parameters["currency"]);
        $trades = $this->tradeDBHandler->getTradesFromTo($todayUTC, $todayUTC, \TradeState::INITIALIZED);
        foreach ($trades as $trade){
            $trade->fillMarketInfo($this->parameters["dv_p_tm5"], $this->parameters["dv_p_t0"]);
            $this->tradeDBHandler->fillTradeWithMarketInfo($trade);
        }
    }
}

