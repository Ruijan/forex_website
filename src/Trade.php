<?php

abstract class TradeState{
    const INITIALIZED = 0;
    const FILLED = 1;
    const PREDICTED = 2;
    const OPEN = 3;
    const CLOSE = 4;
    const CANCELLED = 5;
}

class Trade
{
    private $identifier = null;
    private $idDbEvent = null;
    private $creationTime = null;
    private $openTime = null;
    private $closeTime = null;
    private $dvPTm5 = 0;
    private $dvPT0 = 0;
    private $prediction = 0;
    private $pProba = 0;
    private $gain = 0;
    private $commission = 0;
    private $currency = "";
    private $state = TradeState::INITIALIZED;
    
    public function getId(){return $this->identifier;}
    public function getIDDBEvent(){return $this->idDbEvent;}
    public function getCreationTime(){return $this->creationTime;}
    public function getOpenTime(){return $this->openTime;}
    public function getCloseTime(){return $this->closeTime;}
    public function getDvPTm5(){return $this->dvPTm5;}
    public function getDvPT0(){return $this->dvPT0;}
    public function getPrediction(){return $this->prediction;}
    public function getPProba(){return $this->pProba;}
    public function getGain(){return $this->gain;}
    public function getCommission(){return $this->commission;}
    public function getCurrency(){return $this->currency;}
    public function getState(){return $this->state;}

    public function setId($identifier)
    {
        if((!is_int($identifier) and !is_float($identifier))){
            throw new ErrorException("Wrong type for id. Expected int or float got: ".gettype($identifier));
        }
        if($identifier <= 0){
            throw new ErrorException("Id should be positive. Id = ".$identifier);
        }
        $this->identifier = (int)$identifier;
    }
    
    public function setCurrency($currency)
    {
        if(!is_string($currency)){
            throw new ErrorException("Wrong type for currency. Expected string got: "
                .gettype($currency));
        }
        $this->currency = $currency;
    }

    private function setCreationTime($creationTime)
    {
        if(!is_a($creationTime, 'DateTime')){
            throw new ErrorException("Wrong type for creation_time. Expected DateTime got: ".gettype($creationTime));
        }
        $this->creationTime = $creationTime;
    }
    
    private function setOpenTime($openTime)
    {
        if(!is_a($openTime, 'DateTime')){
            throw new ErrorException("Wrong type for open_time. Expected DateTime got: ".gettype($openTime));
        }
        $this->openTime = $openTime;
    }

    private function setCloseTime($closeTime)
    {
        if(!is_a($closeTime, 'DateTime')){
            throw new ErrorException("Wrong type for close_time. Expected DateTime got: ".gettype($closeTime));
        }
        $this->closeTime = $closeTime;
    }

    public function setDvPTm5($dvPTm5)
    {
        if(!is_float($dvPTm5) and !is_double($dvPTm5)){
            throw new ErrorException("Wrong type for dv_p_tm5. Expected float or double got: ".gettype($dvPTm5));
        }
        $this->dvPTm5 = $dvPTm5;
    }

    public function setDvPT0($dvPT0)
    {
        if(!is_float($dvPT0) and !is_double($dvPT0)){
            throw new ErrorException("Wrong type for dv_p_t0. Expected float or double got: ".gettype($dvPT0));
        }
        $this->dvPT0 = $dvPT0;
    }

    public function setPrediction($prediction)
    {
        if(!is_int($prediction)){
            throw new ErrorException("Wrong type for prediction. Expected int got: ".gettype($prediction));          
        }
        if ($prediction < 0 or $prediction > 1){
            throw new ErrorException("Prediction value out of range:".$prediction.". Shoudl be 0 or 1");
        }
        $this->prediction = $prediction;
    }

    public function setPProba($pProba)
    {
        if(!is_float($pProba) and !is_double($pProba)){
            throw new ErrorException("Wrong type for p_proba. Expected float or double got: ".gettype($pProba));
        }
        if ($pProba < 0 or $pProba > 1){
            throw new ErrorException("Prediction probability out of range:".$pProba.". Should be between 0 and 1");
        }
        $this->pProba = $pProba;
    }

    public function setGain($gain)
    {
        if(!is_float($gain) and !is_int($gain) and !is_double($gain)){
            throw new ErrorException("Wrong type for gain. Expected float or double or int got: ".gettype($gain));
            
        }
        $this->gain = $gain;
    }

    public function setCommission($commission)
    {
        if(!is_float($commission) and !is_int($commission) and !is_double($commission)){
            throw new ErrorException("Wrong type for commission. Expected float or double or int got: "
                .gettype($commission));
        }
        $this->commission = $commission;
    }

    public function setState($state)
    {
        if(!is_int($state) or $state < 0){
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
        $this->state = $state;
    }

    public function __construct($idDBEvent, $creationTime, $currency)
    {
        $this->idDbEvent = $idDBEvent;
        $this->setCreationTime($creationTime);
        $this->setCurrency($currency);
    }
    
    public function isInitialized()
    {
        return $this->identifier != null and $this->idDbEvent != null;
    }
    
    public function close($gain, $commission, $closeTime){
        if($this->state != TradeState::OPEN){
            throw new ErrorException("Cannot switch to close state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setGain($gain);
        $this->setCommission($commission);
        $this->setCloseTime($closeTime);
        $this->setState(TradeState::CLOSE);
    }
    
    public function open($openTime){
        if($this->state != TradeState::PREDICTED){
            throw new ErrorException("Cannot switch to open state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setOpenTime($openTime);
        $this->setState(TradeState::OPEN);
    }
    
    public function predict($prediction, $pPredict){
        if($this->state != TradeState::FILLED){
            throw new ErrorException("Cannot switch to predicted state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setPrediction($prediction);
        $this->setPProba($pPredict);
        $this->setState(TradeState::PREDICTED);
        
    }
    
    public function fillMarketInfo($dvPTm5, $dvPT0){
        if($this->state != TradeState::INITIALIZED){
            throw new ErrorException("Cannot switch to initialized state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setDvPT0($dvPT0);
        $this->setDvPTm5($dvPTm5);
        $this->setState(TradeState::FILLED);
    }
    
    public function cancel(){
        $this->setState(TradeState::CANCELLED);
    }
    
    public function getStringFromState($state){
        switch($state){
            case TradeState::CANCELLED:
                return "Cancelled";
            case TradeState::INITIALIZED:
                return "Initialized";
            case TradeState::FILLED:
                return "Market filled";
            case TradeState::PREDICTED:
                return "Predicted";
            case TradeState::OPEN:
                return "Open";
            case TradeState::CLOSE:
                return "Close";
        }
    }
}

