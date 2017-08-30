<?php

use src\DBHandler;

require_once "Trade.php";
require_once "DBHandler.php";

class TradeDBHandler extends DBHandler
{    
    public function __construct($mysqli)
    {
        parent::__construct($mysqli, "trades");
    }
    
    public function isInitialized(){
        return $this->mysqli != null;
    }
    
    public function getTableName(){return $this->tableName;}

    public function createTable(){
        if(!$this->doesTableExists()){
            $query = "CREATE TABLE ".$this->tableName." (
                        ID int(11) AUTO_INCREMENT UNIQUE,
                        ID_NEWS int(11) NOT NULL UNIQUE,
                        CREATION_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        OPEN_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        CLOSE_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        DV_P_TM5 double NULL,
                        DV_P_T0 double NULL,
                        PREDICTION int NULL,
                        PREDICTION_PROBA double NULL,
                        GAIN double NULL,
                        COMMISSION double NULL,
                        CURRENCY char(50) NULL,
                        STATE int NULL,
                        PRIMARY KEY  (ID)
                        )";
            if ($this->mysqli->query($query) === FALSE) {
                throw new ErrorException("Couldn't create database.");
            }
            parent::createTable();
        }
    }
    
    public function addTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "INSERT INTO ".$this->tableName." 
                    (ID, ID_NEWS, CREATION_TIME, OPEN_TIME, CLOSE_TIME, DV_P_TM5, DV_P_T0, 
                    PREDICTION, PREDICTION_PROBA, GAIN, COMMISSION, CURRENCY, STATE) 
                    VALUES (NULL,'".$trade->getNewsId()."', '".$trade->getCreationTime()->format('Y-m-d H:i:s')."', 
                    NULL,NULL,NULL,NULL,NULL,
                    NULL, NULL, NULL, '".$trade->getCurrency()."', 0)";
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
        return $this->mysqli->insert_id;
    }
    
    public function tryAddingTrade($trade){
        try{
            return $this->addTrade($trade);
        }
        catch(Exception $e){
            return $this->getTradeByNewsId($trade->getNewsId())->getId();
        }
    }
    
    public function removeTradeById($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "DELETE FROM ".$this->tableName."
                    WHERE ID=".$identifier;
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function openTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->tableName." SET OPEN_TIME = '".$trade->getOpenTime()->format('Y-m-d H:i:s')."', 
                    STATE=".$trade->getState()." WHERE ID=".$trade->getId();
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function fillTradeWithMarketInfo($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->tableName." SET DV_P_TM5 = ".$trade->getDvPTm5().",
                        DV_P_T0 = ".$trade->getDvPT0().", STATE=".$trade->getState()." WHERE ID=".$trade->getId();
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function predictTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->tableName." SET PREDICTION = ".$trade->getPrediction().",
                    PREDICTION_PROBA = ".$trade->getPProba().", STATE=".$trade->getState()." WHERE ID="
                        .$trade->getId();
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function closeTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->tableName." SET CLOSE_TIME = '".$trade->getCloseTime()->format('Y-m-d H:i:s')."',
                    GAIN=".$trade->getGain().", COMMISSION=".$trade->getCommission().", STATE="
                        .$trade->getState()." WHERE ID=".$trade->getId();
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function cancelTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->tableName." SET STATE="
            .$trade->getState()." WHERE ID=".$trade->getId();
        $this->throwIfQueryFailed($query, $this->mysqli->query($query));
    }
    
    public function getTradeByID($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM ".$this->tableName." WHERE ID=".$identifier;
        $result = $this->mysqli->query($query);
        $this->throwIfQueryFailed($query, $result);
        while($row = $result->fetch_array())
        {
            return $this->createTradeFromDbArray($row);
        }
    }
    
    private function throwIfQueryFailed($query, $result)
    {
        if(!$result){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }

    
    public function getTradeByNewsId($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM ".$this->tableName." WHERE ID_NEWS=".$identifier;
        $result = $this->mysqli->query($query);
        $this->throwIfQueryFailed($query, $result);
        while($row = $result->fetch_array())
        {
            return $this->createTradeFromDbArray($row);
        }
    }
    
    public function getTradesFromTo($fromDate, $toDate, $state=-1, $currency=""){
        $this->throwIfWrongArgumentType($fromDate, $toDate, $state, $currency);
        $this->throwIfTableDoesNotExist();
        $query = $this->buildSelectQueryFromToState($fromDate, $toDate, $state, $currency);
        $trades = [];
        $result = $this->mysqli->query($query);
        $this->throwIfQueryFailed($query, $result);
        while($row = $result->fetch_array()){
            $trades[] = $this->createTradeFromDbArray($row);
        }
        return $trades;
    }
    
    public function createTradeFromDbArray($result)
    {
        $trade = new Trade($result["ID_NEWS"], new DateTime($result["CREATION_TIME"]), $result["CURRENCY"]);
        $trade->setId((int)$result["ID"]);
        if((int)$result["STATE"] >= TradeState::FILLED){
            $trade->fillMarketInfo((float)$result["DV_P_TM5"], (float)$result["DV_P_T0"]);
        }
        if((int)$result["STATE"] >= TradeState::PREDICTED){
            $trade->predict((int)$result["PREDICTION"], (float)$result["PREDICTION_PROBA"]);
        }
        if((int)$result["STATE"] >= TradeState::OPEN){
            $trade->open(new DateTime($result["OPEN_TIME"]));
        }
        
        if((int)$result["STATE"] >= TradeState::CLOSE){
            $trade->close((float)$result["GAIN"], (float)$result["COMMISSION"], 
                new DateTime($result["CLOSE_TIME"]));
        }
        $trade->setState((int)$result["STATE"]);
        return $trade;
    }
    
    private function buildSelectQueryFromToState($fromDate, $toDate, $state, $currency)
    {
        $state_suffix = "";
        $currency_suffix = "";
        if($state != -1){
            $state_suffix = " AND STATE=".$state;
        }
        if($currency != ""){
            $currency_suffix = " AND CURRENCY='".$currency."'";
        }
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM ".$this->tableName." WHERE DATEDIFF(CREATION_TIME,'"
            .$fromDate->format('Y-m-d H:i:s').
        "') >= 0 AND DATEDIFF(CREATION_TIME,'".$toDate->format('Y-m-d H:i:s').
        "') <= 0".$state_suffix.$currency_suffix;
        return $query;
    }
    
    private function throwIfWrongArgumentType($fromDate, $toDate, $state, $currency)
    {
        if(!is_a($fromDate, 'DateTime') || !is_a($toDate, 'DateTime')){
            throw new ErrorException("Wrong type for from or to. Expected DateTime got: "
                .gettype($fromDate)." and ".gettype($toDate));
        }
        if(!is_int($state)){
            throw new ErrorException("Wrong type for state. Expected int got: ".gettype($state));
        }
        if(!is_string($currency)){
            throw new ErrorException("Wrong type for currency. Expected string got: ".gettype($currency));
        }
    }
}

