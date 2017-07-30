<?php

require_once "../Trade.php";

class TradeDBHandler
{
    private $mysqli = null;
    private $currency = "";
    public $table_name = "";
    
    public function __construct($mysqli, $currency)
    {
        $this->mysqli = $mysqli;
        $this->currency = $currency;
        $this->table_name = "trades_".$currency;
    }
    
    public function isInitialized(){
        return $this->mysqli != null and $this->currency != "";
    }
    
    public function doesTableExists(){
        if ($result = $this->mysqli->query("SHOW TABLES LIKE '".$this->table_name."'")) {
            if($result->num_rows >= 1) {
                return true;
            }
        }
        else {
            return false;
        }
    }
    
    public function createTable(){
        if(!$this->doesTableExists()){
          
            $query = "CREATE TABLE ".$this->table_name." (
                        ID int(11) AUTO_INCREMENT,
                        ID_DB_EVENT int(11) NOT NULL,
                        CREATION_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        OPEN_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        CLOSE_TIME datetime DEFAULT '0000-00-00 00:00:00',
                        DV_P_TM5 double NULL,
                        DV_P_T0 double NULL,
                        PREDICTION int NULL,
                        PREDICTION_PROBA double NULL,
                        GAIN double NULL,
                        COMMISSION double NULL,
                        STATE int NULL,
                        PRIMARY KEY  (ID)
                        )";
            if ($this->mysqli->query($query) === FALSE) {
                throw new ErrorException("Couldn't create database.");
            }
        }
    }
    
    public function deleteTable(){
        if($this->doesTableExists()){
            $this->mysqli->query("DROP TABLE ".$this->table_name);
        }
    }
    
    public function getTableSize(){
        if($this->doesTableExists()){
            $sql1 = $this->mysqli->query("SELECT * FROM ".$this->table_name);
            $row_count= mysqli_num_rows($sql1);
            return $row_count;
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function addTrade($trade){
        if($this->doesTableExists()){
            $query = "INSERT INTO ".$this->table_name." 
                        (ID, ID_DB_EVENT, CREATION_TIME, OPEN_TIME, CLOSE_TIME, DV_P_TM5, DV_P_T0, 
                        PREDICTION, PREDICTION_PROBA, GAIN, COMMISSION, STATE) 
                        VALUES (NULL,'".$trade->getIDDBEvent()."', '".$trade->getCreationTime()->format('Y-m-d H:i:s')."', 
                        NULL,NULL,NULL,NULL,NULL,
                        NULL, NULL, NULL, NULL)";
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
            else{
                return $this->mysqli->insert_id;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function removeTradeById($id){
        if($this->doesTableExists()){
            $query = "DELETE FROM ".$this->table_name."
                        WHERE ID=".$id;
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function openTrade($trade){
        if($this->doesTableExists()){
            $query = "UPDATE ".$this->table_name." SET OPEN_TIME = '".$trade->getOpenTime()->format('Y-m-d H:i:s')."', 
                        STATE=".$trade->getState()." WHERE ID=".$trade->getId();
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function predictTrade($trade){
        if($this->doesTableExists()){
            $query = "UPDATE ".$this->table_name." SET PREDICTION = ".$trade->getPrediction().",
                        PREDICTION_PROBA = ".$trade->getP_proba().", STATE=".$trade->getState()." WHERE ID=".$trade->getId();
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function closeTrade($trade){
        if($this->doesTableExists()){
            $query = "UPDATE ".$this->table_name." SET CLOSE_TIME = '".$trade->getCloseTime()->format('Y-m-d H:i:s')."',
                        GAIN=".$trade->getGain().", COMMISSION=".$trade->getCommission().", STATE=".$trade->getState()." WHERE ID=".$trade->getId();
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
    
    public function getTradeByID($id){
        if($this->doesTableExists()){
            $query = "SELECT * FROM ".$this->table_name." WHERE ID=".$id;
            if($result = $this->mysqli->query($query)){
                while($row = $result->fetch_array())
                {
                    return Trade::createTradeFromDbArray($row);
                }
            }
            else{
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
        }
        else{
            throw new ErrorException("Table does not exists.");
        }
    }
}

