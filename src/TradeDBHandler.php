<?php

require_once "Trade.php";

class TradeDBHandler
{
    private $mysqli = null;
    private $table_name = "";
    private $existingTable = false;
    
    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
        $this->table_name = "trades";
        $this->existingTable = $this->checkIfTableExist();
    }
    
    public function isInitialized(){
        return $this->mysqli != null;
    }
    
    public function getTableName(){return $this->table_name;}
    
    public function doesTableExists(){
        return $this->existingTable;

    }
    private function checkIfTableExist()
    {
        if ($result = $this->mysqli->query("SHOW TABLES LIKE '".$this->table_name."'")) {
            if($result->num_rows >= 1) {
                return true;
            }
        }
        return false;}

    
    public function createTable(){
        if(!$this->doesTableExists()){
            $query = "CREATE TABLE ".$this->table_name." (
                        ID int(11) AUTO_INCREMENT UNIQUE,
                        ID_DB_EVENT int(11) NOT NULL UNIQUE,
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
            $this->existingTable = true;
        }
    }
    
    public function deleteTable(){
        if($this->doesTableExists()){
            $this->mysqli->query("DROP TABLE ".$this->table_name);
            $this->existingTable = false;
        }
    }
    
    public function emptyTable(){
        $this->throwIfTableDoesNotExist();
        $query = "TRUNCATE TABLE ".$this->table_name;
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function getTableSize(){
        $this->throwIfTableDoesNotExist();
        $sql1 = $this->mysqli->query("SELECT * FROM ".$this->table_name);
        $row_count= mysqli_num_rows($sql1);
        return $row_count;
    }
    
    public function addTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "INSERT INTO ".$this->table_name." 
                    (ID, ID_DB_EVENT, CREATION_TIME, OPEN_TIME, CLOSE_TIME, DV_P_TM5, DV_P_T0, 
                    PREDICTION, PREDICTION_PROBA, GAIN, COMMISSION, CURRENCY, STATE) 
                    VALUES (NULL,'".$trade->getIDDBEvent()."', '".$trade->getCreationTime()->format('Y-m-d H:i:s')."', 
                    NULL,NULL,NULL,NULL,NULL,
                    NULL, NULL, NULL, '".$trade->getCurrency()."', NULL)";
        if($this->mysqli->query($query) === FALSE){
            throw new ErrorException("Event already in table: ".$this->mysqli->error);
        }
        return $this->mysqli->insert_id;
    }
    
    public function tryAddingTrade($trade){
        try{
            return $this->addTrade($trade);
        }
        catch(Exception $e){
            return $this->getTradeByEventID($trade->getIDDBEvent())->getId();
        }
    }
    
    public function removeTradeById($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "DELETE FROM ".$this->table_name."
                    WHERE ID=".$identifier;
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function openTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->table_name." SET OPEN_TIME = '".$trade->getOpenTime()->format('Y-m-d H:i:s')."', 
                    STATE=".$trade->getState()." WHERE ID=".$trade->getId();
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function fillTradeWithMarketInfo($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->table_name." SET DV_P_TM5 = ".$trade->getDv_p_tm5().",
                        DV_P_T0 = ".$trade->getDv_p_t0().", STATE=".$trade->getState()." WHERE ID=".$trade->getId();
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function predictTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->table_name." SET PREDICTION = ".$trade->getPrediction().",
                    PREDICTION_PROBA = ".$trade->getP_proba().", STATE=".$trade->getState()." WHERE ID="
                        .$trade->getId();
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function closeTrade($trade){
        $this->throwIfTableDoesNotExist();
        $query = "UPDATE ".$this->table_name." SET CLOSE_TIME = '".$trade->getCloseTime()->format('Y-m-d H:i:s')."',
                    GAIN=".$trade->getGain().", COMMISSION=".$trade->getCommission().", STATE="
                        .$trade->getState()." WHERE ID=".$trade->getId();
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    public function getTradeByID($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM ".$this->table_name." WHERE ID=".$identifier;
        $result = $this->mysqli->query($query);
        if(!$result){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
        while($row = $result->fetch_array())
        {
            return Trade::createTradeFromDbArray($row);
        }
    }
    
    public function getTradeByEventId($identifier){
        $this->throwIfTableDoesNotExist();
        $query = "SELECT * FROM ".$this->table_name." WHERE ID_DB_EVENT=".$identifier;
        $result = $this->mysqli->query($query);
        if(!$result){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
        while($row = $result->fetch_array())
        {
            return Trade::createTradeFromDbArray($row);
        }
    }
    
    public function getTradesFromTo($fromDate, $toDate, $state=-1, $currency=""){
        $this->throwIfWrongArgumentType($fromDate, $toDate, $state, $currency);
        $this->throwIfTableDoesNotExist();
        $query = $this->buildSelectQueryFromToState($fromDate, $toDate, $state, $currency);
        $trades = [];
        $result = $this->mysqli->query($query);
        if(!$result){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
        while($row = $result->fetch_array())
        {
            $trades[] = Trade::createTradeFromDbArray($row);
        }
        return $trades;
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
        $query = "SELECT * FROM ".$this->table_name." WHERE DATEDIFF(CREATION_TIME,'"
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
    
    private function throwIfTableDoesNotExist(){
        if(!$this->doesTableExists()){
            throw new ErrorException("Table does not exists.");
        }
    }
}

