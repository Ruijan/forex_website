<?php
namespace src\requests;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'Trade.php');
require_once($path.'Event.php');
require_once($path.'HTMLDisplayer.php');

require_once('ForexRequest.php');

class NextActionRequest extends ForexRequest
{
    private $displayer;
    public function __construct($displayer)
    {
        $this->setHTMLDisplayer($displayer);
    }
    
    public function setHTMLDisplayer($displayer){
        
        if(!is_a($displayer,"SimpleHTMLDisplayer")){
            throw new \ErrorException("Wrong type for htmlDisplayer. Expected SimpleHTMLDisplayer got: "
                .gettype($displayer));
        }
        $this->displayer = $displayer;
    }
    public function validateRequest(){
        if(!isset($this->parameters["currency"])){
            throw new \ErrorException("Ill-formed request: missing parameters");
        }
        if(!$this->isValidCurrency($this->parameters["currency"])){
            throw new \ErrorException("Wrong currency: ".$this->parameters["currency"]);
        }
    }
    
    private function isValidCurrency($currency)
    {
        return $currency == "EUR_USD";
    }
    
    public function execute(){
        $this->validateRequest();
        $todayUTC = new \DateTime();
        $todayUTC = $todayUTC->createFromFormat('Y-m-d',(gmdate('Y-m-d', time())));
        $trades = $this->tradeDBHandler->getTradesFromTo($todayUTC, $todayUTC, \TradeState::PREDICTED);
        foreach ($trades as $trade){
            $event = $this->eventDBHandler->getEventByEventId($trade->getIDDBEvent());
            echo $this->displayer->displayTrade($trade).$this->displayer->displayEvent($event)."<br/>";
        }
        
    }
}

