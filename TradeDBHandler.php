<?php

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
            if($result->num_rows == 1) {
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
                throw new Exception("Couldn't create database.");
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
            throw new Exception("Table does not exists.");
        }
    }
    
    public function addTrade($trade){
        if($this->doesTableExists()){
            $query = "INSERT INTO ".$this->table_name." 
                        (ID, ID_DB_EVENT, OPEN_TIME, CLOSE_TIME, DV_P_TM5, DV_P_T0, 
                        PREDICTION, PREDICTION_PROBA, GAIN, COMMISSION, STATE) 
                        VALUES (NULL,'".$trade->id_db_event."',NULL,NULL,NULL,NULL,NULL,
                        NULL, NULL, NULL, NULL)";
            if($this->mysqli->query($query) === FALSE){
                echo "Error: " . $query . "<br>" . $this->mysqli->error;
            }
            else{
                return $this->mysqli->insert_id;
            }
        }
        else{
            throw new Exception("Table does not exists.");
        }
    }
}

