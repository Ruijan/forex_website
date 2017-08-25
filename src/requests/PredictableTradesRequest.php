<?php
namespace src\requests;

$path = str_replace("requests\\", "", __DIR__."/");
$path = str_replace("requests/", "", $path."/");
require_once($path.'Trade.php');
require_once($path.'HTMLDisplayer.php');

require_once('ForexRequest.php');

class PredictableTradesRequest extends ForexRequest
{
    private $displayer;
    public function __construct()
    {}
    
    public function setHTMLDisplayer($displayer){
        if(!is_a($displayer,"SimpleHTMLDisplayer")){
            throw new \ErrorException("Wrong type for htmlDisplayer. Expected SimpleHTMLDisplayer got: "
                .gettype($displayer));
        }
        $this->displayer = $displayer;
    }
    
    public function execute(){
        $todayUTC = new \DateTime();
        $todayUTC = $todayUTC->createFromFormat('Y-m-d',(gmdate('Y-m-d', time())));
        $trades = $this->tradeDBHandler->getTradesFromTo($todayUTC, $todayUTC, \TradeState::FILLED);
        foreach ($trades as $trade){
            echo $this->displayer->displayTrade($trade)."<br/>";
        }
        
    }
}

