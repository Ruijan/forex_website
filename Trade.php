<?php

class Trade
{
    private $id = null;
    private $id_db_event = null;
    private $open_time = null;
    private $close_time = null;
    private $dv_p_tm5 = null;
    private $dv_p_t0 = null;
    private $prediction = null;
    private $p_proba = null;
    private $gain = null;
    private $commission = null;
    private $state = 0;
    
    public function getId(){return $this->id;}
    public function getIDDBEvent(){return $this->id_db_event;}
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
            $this->prediction = $prediction;
        }
        else{
            throw new ErrorException("Wrong type for prediction. Expected int got: ".gettype($prediction));
        }
    }

    public function setP_proba($p_proba)
    {
        if(is_float($p_proba) or is_double($p_proba)){
            $this->p_proba = $p_proba;
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

    public function __construct($id_db_event)
    {
        $this->id_db_event = $id_db_event;
    }
    
    public function isInitialized()
    {
        return $this->id != null and $this->id_db_event != null;
    }
    
    public function close($gain, $commission, $close_time){
        $this->setGain($gain);
        $this->setCommission($commission);
        $this->setCloseTime($close_time);
        $this->setState(3);
    }
    
    public function open($open_time){
        $this->setOpenTime($open_time);
        $this->setState(2);
    }
    
    public function predict($prediction, $p_predict){
        $this->setPrediction($prediction);
        $this->setP_proba($p_predict);
        $this->setState(1);
    }
    
    static public function createWithIdAndEventID($id, $id_db_event)
    {
        $trade = new Trade($id_db_event);
        $trade->setId($id);
        return $trade;
    }
    
    static public function createTradeFromDbArray($result)
    {
        $trade = new Trade($result["ID_DB_EVENT"]);
        $trade->setId((int)$result["ID"]);
        $trade->setOpenTime(new DateTime($result["OPEN_TIME"]));
        $trade->setCloseTime(new DateTime($result["CLOSE_TIME"]));
        $trade->setDv_p_tm5((float)$result["DV_P_TM5"]);
        $trade->setDv_p_t0((float)$result["DV_P_T0"]);
        $trade->setPrediction((int)$result["PREDICTION"]);
        $trade->setP_proba((float)$result["PREDICTION_PROBA"]);
        $trade->setGain((float)$result["GAIN"]);
        $trade->setCommission((float)$result["COMMISSION"]);
        $trade->setState((int)$result["STATE"]);
        return $trade;
    }
    
    
}

