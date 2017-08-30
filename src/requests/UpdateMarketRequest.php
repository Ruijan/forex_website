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
        $this->parameters["dv_p_tm5"] = floatval($this->parameters["dv_p_tm5"]);
        $this->parameters["dv_p_t0"] = floatval($this->parameters["dv_p_t0"]);
        if(!$this->isDecimal($this->parameters["dv_p_tm5"]) || !$this->isDecimal($this->parameters["dv_p_t0"]) ||
            !$this->isValidCurrency($this->parameters["currency"])){
                throw new ErrorException("Invalid Request: bad parameters type. Got "
                    .gettype($this->parameters["dv_p_tm5"]). " "
                    .gettype($this->parameters["dv_p_t0"]). " and "
                    .gettype($this->parameters["currency"]). ". Expected double double and string.");
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
        $todayUTC = $todayUTC->createFromFormat('Y-m-d',gmdate('Y-m-d', time()));
        $trades = $this->tradeDBHandler->getTradesFromTo($todayUTC, $todayUTC, \TradeState::INITIALIZED, 
            $this->parameters["currency"]);
        foreach ($trades as $trade){
            $trade->fillMarketInfo($this->parameters["dv_p_tm5"], $this->parameters["dv_p_t0"]);
            $this->tradeDBHandler->fillTradeWithMarketInfo($trade);
        }
    }
}

