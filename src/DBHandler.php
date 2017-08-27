<?php
namespace src;

use ErrorException;
use Exception;

class DBHandler
{
    protected $mysqli;
    protected $tableName;
    protected $existingTable = False;
    
    public function __construct($mysqli, $tableName)
    {
        $this->mysqli = $mysqli;
        $this->tableName = $tableName;
        $this->existingTable = $this->checkIfTableExist();
    }
    
    public function createTable(){
        $this->existingTable = true;
    }
    
    public function deleteTable(){
        if($this->doesTableExists()){
            $this->mysqli->query("DROP TABLE ".$this->tableName);
            $this->existingTable = false;
        }
    }
    
    public function getTableSize(){
        $this->throwIfTableDoesNotExist();
        $sql1 = $this->mysqli->query("SELECT * FROM ".$this->tableName);
        $rowCount= mysqli_num_rows($sql1);
        return $rowCount;
    }
    
    public function doesTableExists(){
        return $this->existingTable;
    }
    
    public function emptyTable(){
        $this->throwIfTableDoesNotExist();
        $query = "TRUNCATE TABLE ".$this->tableName;
        if($this->mysqli->query($query) === FALSE){
            throw new Exception("Error: " . $query . "<br>" . $this->mysqli->error);
        }
    }
    
    protected function throwIfTableDoesNotExist(){
        if(!$this->doesTableExists()){
            throw new ErrorException("Table does not exists.");
        }
    }
    
    protected function checkIfTableExist()
    {
        if ($result = $this->mysqli->query("SHOW TABLES LIKE '".$this->tableName."'")) {
            if($result->num_rows >= 1) {
                return true;
            }
        }
        return false;
    }
}

