<?php
namespace src\requests;

require_once(str_replace("requests", "", __DIR__).'Trade.php');
require_once('ForexRequest.php');

use DateTime;
use ErrorException;

class UpdateMarketRequest extends ForexRequest
{
    
    
    public function __construct()
    {}
    
    public function validateRequest(){
        if(!isset($_POST["dv_p_tm5"]) or !isset($_POST["dv_p_t0"]) or !isset($_POST["currency"])){
            throw new ErrorException("Ill-formed request: missing parameters");           
        }
        if(!$this->isDecimal($_POST["dv_p_tm5"]) || !$this->isDecimal($_POST["dv_p_t0"]) ||
            !$this->isValidCurrency()){
            throw new ErrorException("Invalid Request: bad parameters type");
        }
    }
    
    private function isValidCurrency()
    {
        return $_POST["currency"] == "EUR_USD";
    }

    
    private function isDecimal($var)
    {
        return is_float($var) or is_double($var);
    }
    
    public function execute(){
        $this->validateRequest();
        $now_utc = DateTime::createFromFormat('Y-m-d',(gmdate('Y-m-d', time())));
        $trades = $this->tradeDBHandler->getTradesFromTo($now_utc, $now_utc, \TradeState::INITIALIZED);
        foreach ($trades as $trade){
            $trade->fillMarketInfo($_POST["dv_p_tm5"], $_POST["dv_p_t0"]);
            $this->tradeDBHandler->fillTradeWithMarketInfo($trade);
        }
    }
}

