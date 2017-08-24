<?php

abstract class TradeState{
    const INITIALIZED = 0;
    const FILLED = 1;
    const PREDICTED = 2;
    const OPEN = 3;
    const CLOSE = 4;
}

class Trade
{
    private $identifier = null;
    private $id_db_event = null;
    private $creation_time = null;
    private $open_time = null;
    private $close_time = null;
    private $dv_p_tm5 = 0;
    private $dv_p_t0 = 0;
    private $prediction = 0;
    private $p_proba = 0;
    private $gain = 0;
    private $commission = 0;
    private $currency = "";
    private $state = TradeState::INITIALIZED;
    
    public function getId(){return $this->identifier;}
    public function getIDDBEvent(){return $this->id_db_event;}
    public function getCreationTime(){return $this->creation_time;}
    public function getOpenTime(){return $this->open_time;}
    public function getCloseTime(){return $this->close_time;}
    public function getDv_p_tm5(){return $this->dv_p_tm5;}
    public function getDv_p_t0(){return $this->dv_p_t0;}
    public function getPrediction(){return $this->prediction;}
    public function getP_proba(){return $this->p_proba;}
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

    private function setCreationTime($creation_time)
    {
        if(!is_a($creation_time, 'DateTime')){
            throw new ErrorException("Wrong type for creation_time. Expected DateTime got: ".gettype($creation_time));
        }
        $this->creation_time = $creation_time;
    }
    
    private function setOpenTime($open_time)
    {
        if(!is_a($open_time, 'DateTime')){
            throw new ErrorException("Wrong type for open_time. Expected DateTime got: ".gettype($open_time));
        }
        $this->open_time = $open_time;
    }

    private function setCloseTime($close_time)
    {
        if(!is_a($close_time, 'DateTime')){
            throw new ErrorException("Wrong type for close_time. Expected DateTime got: ".gettype($close_time));
        }
        $this->close_time = $close_time;
    }

    public function setDv_p_tm5($dv_p_tm5)
    {
        if(!is_float($dv_p_tm5) and !is_double($dv_p_tm5)){
            throw new ErrorException("Wrong type for dv_p_tm5. Expected float or double got: ".gettype($dv_p_tm5));
        }
        $this->dv_p_tm5 = $dv_p_tm5;
    }

    public function setDv_p_t0($dv_p_t0)
    {
        if(!is_float($dv_p_t0) and !is_double($dv_p_t0)){
            throw new ErrorException("Wrong type for dv_p_t0. Expected float or double got: ".gettype($dv_p_t0));
        }
        $this->dv_p_t0 = $dv_p_t0;
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

    public function setP_proba($p_proba)
    {
        if(!is_float($p_proba) and !is_double($p_proba)){
            throw new ErrorException("Wrong type for p_proba. Expected float or double got: ".gettype($p_proba));
        }
        if ($p_proba < 0 or $p_proba > 1){
            throw new ErrorException("Prediction probability out of range:".$p_proba.". Should be between 0 and 1");
        }
        $this->p_proba = $p_proba;
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

    public function __construct($id_db_event, $creation_time, $currency)
    {
        $this->id_db_event = $id_db_event;
        $this->setCreationTime($creation_time);
        $this->setCurrency($currency);
    }
    
    public function isInitialized()
    {
        return $this->identifier != null and $this->id_db_event != null;
    }
    
    public function close($gain, $commission, $close_time){
        if($this->state != TradeState::OPEN){
            throw new ErrorException("Cannot switch to close state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setGain($gain);
        $this->setCommission($commission);
        $this->setCloseTime($close_time);
        $this->setState(TradeState::CLOSE);
    }
    
    public function open($open_time){
        if($this->state != TradeState::PREDICTED){
            throw new ErrorException("Cannot switch to open state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setOpenTime($open_time);
        $this->setState(TradeState::OPEN);
    }
    
    public function predict($prediction, $p_predict){
        if($this->state != TradeState::FILLED){
            throw new ErrorException("Cannot switch to predicted state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setPrediction($prediction);
        $this->setP_proba($p_predict);
        $this->setState(TradeState::PREDICTED);
        
    }
    
    public function fillMarketInfo($dv_p_tm5, $dv_p_t0){
        if($this->state != TradeState::INITIALIZED){
            throw new ErrorException("Cannot switch to initialized state. Actual state is : ".
                $this->getStringFromState($this->getState()).". Next expected state is ".
                $this->getStringFromState($this->getState()+1));
        }
        $this->setDv_p_t0($dv_p_t0);
        $this->setDv_p_tm5($dv_p_tm5);
        $this->setState(TradeState::FILLED);
    }
    
    public function getStringFromState($state){
        switch($state){
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

