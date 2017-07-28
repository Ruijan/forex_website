<?php

class Trade
{
    public $id = null;
    public $id_db_event = null;
    public $open_time = null;
    public $close_time = null;
    public $dv_p_tm5 = null;
    public $dv_p_t0 = null;
    public $prediction = null;
    public $p_proba = null;
    public $gain = null;
    public $commission = null;
    public $state = 0;
    
    public function __construct()
    {}
    
    public function initialize($id, $id_db_event)
    {
        $this->id = $id;
        $this->id_db_event = $id_db_event;
    }
    
    public function isInitialized(){
        return $this->id != null and $this->id_db_event != null;
    }
    
    static public function createWithIdAndEventID($id, $id_db_event){
        $trade = new Trade();
        $trade->initialize($id, $id_db_event);
        return $trade;
    }
    
}

