<?php

abstract class TradeState{
    const Initialized = 0;
    const Filled = 1;
    const Predicted = 2;
    const Open = 3;
    const Close = 4;
}

class Trade
{
    private $id = null;
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
    private $state = TradeState::Initialized;
    
    public function getId(){return $this->id;}
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
    public function getState(){return $this->state;}

    public function setId($id)
    {
        if((is_int($id) or is_float($id))){
            if($id > 0){
                $this->id = (int)$id;
            }
            else{
                throw new ErrorException("Id should be positive. Id = ".$id);
            }
        }
        else{
            throw new ErrorException("Wrong type for id. Expected int or float got: ".gettype($id));
        }
    }

    private function setCreationTime($creation_time)
    {
        if(is_a($creation_time, 'DateTime')){
            $this->creation_time = $creation_time;
        }
        else{
            throw new ErrorException("Wrong type for creation_time. Expected DateTime got: ".gettype($creation_time));
        }
    }
    
    private function setOpenTime($open_time)
    {
        if(is_a($open_time, 'DateTime')){
            $this->open_time = $open_time;
        }
        else{
            throw new ErrorException("Wrong type for open_time. Expected DateTime got: ".gettype($open_time));
        }
    }

    private function setCloseTime($close_time)
    {
        if(is_a($close_time, 'DateTime')){
            $this->close_time = $close_time;
        }
        else{
            throw new ErrorException("Wrong type for close_time. Expected DateTime got: ".gettype($close_time));
        }
    }

    public function setDv_p_tm5($dv_p_tm5)
    {
        if(is_float($dv_p_tm5) or is_double($dv_p_tm5)){
            $this->dv_p_tm5 = $dv_p_tm5;
        }
        else{
            throw new ErrorException("Wrong type for dv_p_tm5. Expected float or double got: ".gettype($dv_p_tm5));
        }
    }

    public function setDv_p_t0($dv_p_t0)
    {
        if(is_float($dv_p_t0) or is_double($dv_p_t0)){
            $this->dv_p_t0 = $dv_p_t0;
        }
        else{
            throw new ErrorException("Wrong type for dv_p_t0. Expected float or double got: ".gettype($dv_p_t0));
        }
    }

    public function setPrediction($prediction)
    {
        if(is_int($prediction)){
            if ($prediction >= 0 and $prediction <=1){
                $this->prediction = $prediction;
            }
            else{
                throw new ErrorException("Prediction value out of range:".$prediction.". Shoudl be 0 or 1");
            }
            
        }
        else{
            throw new ErrorException("Wrong type for prediction. Expected int got: ".gettype($prediction));
        }
    }

    public function setP_proba($p_proba)
    {
        if(is_float($p_proba) or is_double($p_proba)){
            if ($p_proba >= 0 and $p_proba <=1){
                $this->p_proba = $p_proba;
            }
            else{
                throw new ErrorException("Prediction probability out of range:".$p_proba.". Should be between 0 and 1");
            }
        }
        else{
            throw new ErrorException("Wrong type for p_proba. Expected float or double got: ".gettype($p_proba));
        }
    }

    public function setGain($gain)
    {
        if(is_float($gain) or is_int($gain) or is_double($gain)){
            $this->gain = $gain;
        }
        else{
            throw new ErrorException("Wrong type for gain. Expected float or double or int got: ".gettype($gain));
        }
    }

    public function setCommission($commission)
    {
        if(is_float($commission) or is_int($commission) or is_double($commission)){
            $this->commission = $commission;
        }
        else{
            throw new ErrorException("Wrong type for commission. Expected float or double or int got: ".gettype($commission));
        }
    }

    public function setState($state)
    {
        if(is_int($state) and $state >= 0){
            $this->state = $state;
        }
        else{
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
    }

    public function __construct($id_db_event, $creation_time)
    {
        $this->id_db_event = $id_db_event;
        $this->setCreationTime($creation_time);
        
    }
    
    public function isInitialized()
    {
        return $this->id != null and $this->id_db_event != null;
    }
    
    public function close($gain, $commission, $close_time){
        $this->setGain($gain);
        $this->setCommission($commission);
        $this->setCloseTime($close_time);
        $this->setState(TradeState::Close);
    }
    
    public function open($open_time){
        $this->setOpenTime($open_time);
        $this->setState(TradeState::Open);
    }
    
    public function predict($prediction, $p_predict){
        $this->setPrediction($prediction);
        $this->setP_proba($p_predict);
        $this->setState(TradeState::Predicted);
    }
    
    public function fillMarketInfo($dv_p_tm5, $dv_p_t0){
        $this->setDv_p_t0($dv_p_t0);
        $this->setDv_p_tm5($dv_p_tm5);
        $this->setState(TradeState::Filled);
    }
    
    
    static public function createTradeFromDbArray($result)
    {
        $trade = new Trade($result["ID_DB_EVENT"], new DateTime($result["CREATION_TIME"]));
        $trade->setId((int)$result["ID"]);
        if((int)$result["STATE"] >= TradeState::Open){
            $trade->setOpenTime(new DateTime($result["OPEN_TIME"]));
        }
        if((int)$result["STATE"] >= TradeState::Close){
            $trade->setCloseTime(new DateTime($result["CLOSE_TIME"]));
        }
        $trade->setDv_p_tm5((float)$result["DV_P_TM5"]);
        $trade->setDv_p_t0((float)$result["DV_P_T0"]);
        $trade->setPrediction((int)$result["PREDICTION"]);
        $trade->setP_proba((float)$result["PREDICTION_PROBA"]);
        $trade->setGain((float)$result["GAIN"]);
        $trade->setCommission((float)$result["COMMISSION"]);
        $trade->setState((int)$result["STATE"]);
        return $trade;
    }
    
    static public function getStringFromState($state){
        switch($state){
            case TradeState::Initialized:
                return "Initialized";
            case TradeState::Filled:
                return "Market filled";
            case TradeState::Predicted:
                return "Predicted";
            case TradeState::Open:
                return "Open";
            case TradeState::Close:
                return "Close";
        }
    }
    
    
}

