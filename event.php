<?php
	class Event {
	    public $id;
	    public $time;
	    public $actual;
	    public $previous;
	    public $forecast;
	    public $class;
	    public $is_in_database;
	    function __construct() {
	       $this->is_in_database = false;
	       $this->actual = '';
	       $this->previous = '';
	       $this->class = -1;
	   	}
	    public function displayEvent() {
	    	echo $this->id.';'.$this->time.';'.$this->actual.';'.$this->previous.';'.$this->forecast.";".$this->class;
	    }
	    public function displayEventInTable() {
	    	echo '<tr><td>'.$this->id.'</td><td>'.$this->time.'</td><td>'.$this->actual.'</td><td>'.$this->previous.'</td><td>'.$this->forecast."</td><td>".$this->class.'</td><tr>';
	    }
	    public function getPrediction($dv_p_tm5, $dv_p_t0){
	    	if($this->actual != '' and  $this->previous != ''){
	    		$dv_e = (float)$this->actual - (float)$this->previous;
	    		$pyscript = 'C:/xampp/htdocs/Pixelnos/forex/python/getPrediction.py';
				$python = 'C:/Users/MSI-GP60/Anaconda3/python.exe';
				$command=escapeshellcmd($python.' '.$pyscript.' '.$this->id.' '.$dv_p_tm5.' '.$dv_p_t0.' '.$dv_e);
				$this->class = shell_exec($command);
				if($this->class >= 0){
					$this->is_in_database = true;
				}
	    	}
	    }
	}


?>